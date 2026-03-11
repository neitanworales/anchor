<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseStoreRequest;
use App\Models\Expense;
use App\Models\ExpenseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');
        $q = Expense::query()
            ->where('company_id', $company->id)
            ->where('user_id', $request->user()->id)
            ->with(['category', 'files']);

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }
        return response()->json($q->orderByDesc('id')->paginate(20));
    }

    public function store(ExpenseStoreRequest $request)
    {
        $company = $request->attributes->get('company');
        $expense = Expense::create([
            'company_id' => $company->id,
            'user_id' => $request->user()->id,
            ...$request->validated(),
            'currency' => $request->input('currency', 'MXN'),
            'status' => 'DRAFT',
        ]);

        return response()->json($expense->load(['category', 'files']), 201);
    }

    public function uploadFile(Request $request, Expense $expense)
    {
        $company = $request->attributes->get('company');

        if ($expense->company_id !== $company->id || $expense->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($expense->status === 'LOCKED') {
            return response()->json(['message' => 'Expense locked'], 409);
        }

        $data = $request->validate([
            'type' => ['required', 'in:IMG,PDF,XML'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $data['file'];
        $path = $file->store("expenses/{$expense->id}", 'local');

        $ef = ExpenseFile::create([
            'expense_id' => $expense->id,
            'type' => $data['type'],
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        if ($data['type'] === 'XML') {
            $expense->xml_uploaded = true;
            $expense->xml_original_name = $file->getClientOriginalName();
            $expense->save();
        }

        return response()->json($ef, 201);
    }

    public function parseCfdiXml(Request $request, Expense $expense)
    {
        $company = $request->attributes->get('company');

        if ($expense->company_id !== $company->id || $expense->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $xmlFile = $expense->files()->where('type', 'XML')->orderByDesc('id')->first();
        if (!$xmlFile) {
            return response()->json(['message' => 'No XML file found for this expense'], 404);
        }

        $fullPath = \Storage::disk('local')->path($xmlFile->path);
        if (!file_exists($fullPath)) {
            return response()->json(['message' => 'Stored XML missing'], 500);
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($fullPath);

        if (!$xml) {
            return response()->json([
                'message' => 'Invalid XML',
                'errors' => array_map(fn($e) => trim($e->message), libxml_get_errors()),
            ], 422);
        }

        $namespaces = $xml->getNamespaces(true);

        $comprobanteAttrs = $xml->attributes();

        $fecha = (string) ($comprobanteAttrs['Fecha'] ?? '');
        $total = (string) ($comprobanteAttrs['Total'] ?? '');
        $subtotal = (string) ($comprobanteAttrs['SubTotal'] ?? '');
        $moneda = (string) ($comprobanteAttrs['Moneda'] ?? 'MXN');
        $tipo = (string) ($comprobanteAttrs['TipoDeComprobante'] ?? '');

        $cfdiNs = $namespaces['cfdi'] ?? null;
        $emisor = null;
        $receptor = null;

        if ($cfdiNs) {
            $children = $xml->children($cfdiNs);
            $emisor = $children->Emisor ?? null;
            $receptor = $children->Receptor ?? null;
        } else {
            $emisor = $xml->Emisor ?? null;
            $receptor = $xml->Receptor ?? null;
        }

        $rfcEmisor = $emisor ? (string) ($emisor->attributes()['Rfc'] ?? '') : '';
        $nombreEmisor = $emisor ? (string) ($emisor->attributes()['Nombre'] ?? '') : '';
        $rfcReceptor = $receptor ? (string) ($receptor->attributes()['Rfc'] ?? '') : '';

        $uuid = null;
        $fechaTimbrado = null;

        if (isset($namespaces['tfd'])) {
            $complemento = $cfdiNs ? ($xml->children($cfdiNs)->Complemento ?? null) : ($xml->Complemento ?? null);

            if ($complemento) {
                $tfdChildren = $complemento->children($namespaces['tfd']);
                $timbre = $tfdChildren->TimbreFiscalDigital ?? null;

                if ($timbre) {
                    $timbreAttrs = $timbre->attributes();
                    $uuid = (string) ($timbreAttrs['UUID'] ?? '');
                    $fechaTimbrado = (string) ($timbreAttrs['FechaTimbrado'] ?? '');
                }
            }
        }

        // Evitar UUID duplicado dentro de la misma empresa
        if ($uuid) {
            $duplicate = Expense::where('company_id', $company->id)
                ->where('cfdi_uuid', $uuid)
                ->where('id', '!=', $expense->id)
                ->exists();

            if ($duplicate) {
                return response()->json([
                    'message' => 'This CFDI UUID already exists in this company',
                    'uuid' => $uuid,
                ], 422);
            }
        }

        \DB::transaction(function () use ($expense, $tipo, $uuid, $rfcEmisor, $nombreEmisor, $rfcReceptor, $moneda, $subtotal, $total, $fecha, $xmlFile) {
            $expense->receipt_type = 'CFDI';
            $expense->cfdi_type = $tipo ?: null;
            $expense->cfdi_uuid = $uuid ?: null;
            $expense->cfdi_emitter_rfc = $rfcEmisor ?: null;
            $expense->cfdi_emitter_name = $nombreEmisor ?: null;
            $expense->cfdi_receiver_rfc = $rfcReceptor ?: null;
            $expense->cfdi_currency = $moneda ?: 'MXN';
            $expense->cfdi_subtotal = $subtotal !== '' ? (float) $subtotal : null;
            $expense->cfdi_total = $total !== '' ? (float) $total : null;
            $expense->cfdi_issue_datetime = $fecha ?: null;
            $expense->xml_uploaded = true;
            $expense->xml_original_name = $xmlFile->original_name;

            // Sugerencias automáticas al gasto
            if (!$expense->vendor && $nombreEmisor) {
                $expense->vendor = $nombreEmisor;
            }

            if ((!$expense->amount || (float) $expense->amount <= 0) && $total !== '') {
                $expense->amount = (float) $total;
            }

            if (!$expense->currency && $moneda) {
                $expense->currency = $moneda;
            } elseif ($moneda) {
                $expense->currency = $moneda;
            }

            if ($fecha) {
                $expense->expense_date = substr($fecha, 0, 10);
            }

            $expense->save();
        });

        $warnings = [];
        if (in_array($tipo, ['E', 'T', 'N'], true)) {
            $warnings[] = 'CFDI type not recommended for standard expense flow in MVP';
        }
        if ($tipo === 'P') {
            $warnings[] = 'CFDI type P (Pago) detected';
        }

        return response()->json([
            'expense' => $expense->fresh()->load('files'),
            'parsed' => [
                'cfdi_type' => $tipo,
                'uuid' => $uuid,
                'emitter_rfc' => $rfcEmisor,
                'emitter_name' => $nombreEmisor,
                'receiver_rfc' => $rfcReceptor,
                'currency' => $moneda,
                'subtotal' => $subtotal,
                'total' => $total,
                'issue_datetime' => $fecha,
                'fecha_timbrado' => $fechaTimbrado,
            ],
            'warnings' => $warnings,
        ]);
    }
}