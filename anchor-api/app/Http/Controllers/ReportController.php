<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportAddExpenseRequest;
use App\Http\Requests\ReportStoreRequest;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $q = Report::query()
            ->where('user_id', $request->user()->id)
            ->with(['expenses.category', 'approvals']);

        if ($request->filled('status'))
            $q->where('status', $request->string('status'));

        return response()->json($q->orderByDesc('id')->paginate(20));
    }

    public function store(ReportStoreRequest $request)
    {
        $company = $request->attributes->get('company');
        $report = Report::create([
            'company_id' => $company->id,
            'user_id' => $request->user()->id,
            ...$request->validated(),
            'status' => 'DRAFT',
            'total' => 0,
        ]);

        return response()->json($report, 201);
    }

    public function addExpense(ReportAddExpenseRequest $request, Report $report)
    {
        if ($report->user_id !== $request->user()->id)
            return response()->json(['message' => 'Forbidden'], 403);
        if ($report->status !== 'DRAFT')
            return response()->json(['message' => 'Report not editable'], 409);

        $expense = Expense::where('id', $request->validated()['expense_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$expense)
            return response()->json(['message' => 'Expense not found'], 404);
        if ($expense->status === 'LOCKED')
            return response()->json(['message' => 'Expense locked'], 409);
        if ($expense->company_id !== $report->company_id) {
            return response()->json(['message' => 'Cross-company not allowed'], 422);
        }

        DB::transaction(function () use ($report, $expense) {
            $report->expenses()->syncWithoutDetaching([$expense->id]);
            $expense->status = 'IN_REPORT';
            $expense->save();
            $this->recalcTotal($report);
        });

        return response()->json($report->fresh()->load('expenses.category'));
    }

    public function submit(Request $request, Report $report)
    {
        if ($report->user_id !== $request->user()->id)
            return response()->json(['message' => 'Forbidden'], 403);
        if ($report->status !== 'DRAFT')
            return response()->json(['message' => 'Report not in DRAFT'], 409);

        $report->load('expenses.category');

        if ($report->expenses->isEmpty()) {
            return response()->json(['message' => 'Report must contain at least 1 expense'], 422);
        }

        // Reglas MVP:
        // 1) requires_cfdi => cada gasto debe tener cfdi_uuid
        foreach ($report->expenses as $e) {
            if ($e->category?->requires_cfdi) {
                if ($e->receipt_type !== 'CFDI' || !$e->cfdi_uuid) {
                    return response()->json([
                        'message' => 'CFDI required for one or more expenses',
                        'expense_id' => $e->id,
                        'category' => $e->category->name,
                    ], 422);
                }
            }
        }

        // 2) max_per_report por categoría
        $sumByCategory = [];
        foreach ($report->expenses as $e) {
            $cid = $e->category_id;
            $sumByCategory[$cid] = ($sumByCategory[$cid] ?? 0) + (float) $e->amount;
        }
        foreach ($report->expenses as $e) {
            $cat = $e->category;
            if ($cat && $cat->max_per_report !== null) {
                $totalCat = $sumByCategory[$cat->id] ?? 0;
                if ($totalCat > (float) $cat->max_per_report) {
                    return response()->json([
                        'message' => 'Category limit exceeded',
                        'category_id' => $cat->id,
                        'category' => $cat->name,
                        'limit' => (float) $cat->max_per_report,
                        'total' => $totalCat,
                    ], 422);
                }
            }
        }

        DB::transaction(function () use ($report) {
            $this->recalcTotal($report);
            $report->status = 'SUBMITTED';
            $report->save();

            // Lock gastos (ya no editables)
            $report->expenses()->update(['status' => 'LOCKED']);
        });

        return response()->json($report->fresh()->load('expenses.category'));
    }

    public function approve(Request $request, Report $report)
    {
        if (!in_array($request->user()->role, ['APPROVER', 'ADMIN'], true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        if ($report->status !== 'SUBMITTED')
            return response()->json(['message' => 'Report not SUBMITTED'], 409);

        $data = $request->validate(['comment' => ['nullable', 'string']]);

        DB::transaction(function () use ($request, $report, $data) {
            $report->approvals()->create([
                'approver_id' => $request->user()->id,
                'action' => 'APPROVE',
                'comment' => $data['comment'] ?? null,
            ]);
            $report->status = 'APPROVED';
            $report->save();
        });

        return response()->json($report->fresh()->load('approvals'));
    }

    public function reject(Request $request, Report $report)
    {
        if (!in_array($request->user()->role, ['APPROVER', 'ADMIN'], true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        if ($report->status !== 'SUBMITTED')
            return response()->json(['message' => 'Report not SUBMITTED'], 409);

        $data = $request->validate([
            'comment' => ['required', 'string', 'min:3'],
        ]);

        DB::transaction(function () use ($request, $report, $data) {
            $report->approvals()->create([
                'approver_id' => $request->user()->id,
                'action' => 'REJECT',
                'comment' => $data['comment'],
            ]);
            $report->status = 'REJECTED';
            $report->save();

            // Al rechazar: desbloquear gastos y regresarlos a DRAFT para corrección
            $report->expenses()->update(['status' => 'DRAFT']);
        });

        return response()->json($report->fresh()->load('approvals'));
    }

    public function markPaid(Request $request, Report $report)
    {
        if ($request->user()->role !== 'ADMIN')
            return response()->json(['message' => 'Forbidden'], 403);
        if ($report->status !== 'APPROVED')
            return response()->json(['message' => 'Report not APPROVED'], 409);

        $report->status = 'PAID';
        $report->save();

        return response()->json($report);
    }

    public function exportCsv(Request $request, Report $report)
    {
        if ($request->user()->role !== 'ADMIN')
            return response()->json(['message' => 'Forbidden'], 403);

        $report->load(['user', 'expenses.category']);

        $response = new StreamedResponse(function () use ($report) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'report_id',
                'report_status',
                'employee',
                'expense_id',
                'expense_date',
                'category',
                'vendor',
                'amount',
                'tax_iva',
                'currency',
                'receipt_type',
                'cfdi_uuid'
            ]);

            foreach ($report->expenses as $e) {
                fputcsv($out, [
                    $report->id,
                    $report->status,
                    $report->user->email,
                    $e->id,
                    $e->expense_date?->format('Y-m-d'),
                    $e->category?->name,
                    $e->vendor,
                    (string) $e->amount,
                    (string) ($e->tax_iva ?? ''),
                    $e->currency,
                    $e->receipt_type,
                    $e->cfdi_uuid,
                ]);
            }
            fclose($out);
        });

        $filename = "report_{$report->id}.csv";
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");
        return $response;
    }

    private function recalcTotal(Report $report): void
    {
        $sum = $report->expenses()->sum('amount');
        $report->total = $sum;
        $report->save();
    }
}