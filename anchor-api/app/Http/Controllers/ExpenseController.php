<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseStoreRequest;
use App\Models\Expense;
use App\Models\ExpenseFile;
use Illuminate\Http\Request;
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

        if ($request->filled('status'))
            $q->where('status', $request->string('status'));

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
        if ($expense->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        if ($expense->status === 'LOCKED') {
            return response()->json(['message' => 'Expense locked'], 409);
        }

        $data = $request->validate([
            'type' => ['required', 'in:IMG,PDF,XML'],
            'file' => ['required', 'file', 'max:10240'], // 10MB
        ]);

        $file = $data['file'];
        $path = $file->store("expenses/{$expense->id}", 'local');

        $ef = ExpenseFile::create([
            'expense_id' => $expense->id,
            'type' => $data['type'],
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return response()->json($ef, 201);
    }

    public function parseCfdiXml(Request $request, Expense $expense)
    {
        if ($expense->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // busca el último XML adjunto
        $xmlFile = $expense->files()->where('type', 'XML')->orderByDesc('id')->first();
        if (!$xmlFile) {
            return response()->json(['message' => 'No XML file found for this expense'], 404);
        }

        $fullPath = Storage::disk('local')->path($xmlFile->path);
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

        // Namespaces típicos CFDI 4.0
        $ns = $xml->getNamespaces(true);

        // Intenta acceder con prefijos conocidos
        $cfdi = $xml->children($ns['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4');
        $emisor = $cfdi->Emisor ?? null;
        $receptor = $cfdi->Receptor ?? null;

        // UUID suele venir en Complemento->TimbreFiscalDigital
        $uuid = null;
        $fechaTimbrado = null;

        if (isset($cfdi->Complemento)) {
            $complemento = $cfdi->Complemento;
            $tfdNs = $ns['tfd'] ?? 'http://www.sat.gob.mx/TimbreFiscalDigital';
            $tfd = $complemento->children($tfdNs)->TimbreFiscalDigital ?? null;

            if ($tfd) {
                $attrs = $tfd->attributes();
                $uuid = (string) ($attrs['UUID'] ?? '');
                $fechaTimbrado = (string) ($attrs['FechaTimbrado'] ?? '');
            }
        }

        // Atributos del comprobante
        $compAttrs = $xml->attributes();
        $fecha = (string) ($compAttrs['Fecha'] ?? '');     // Emisión
        $total = (string) ($compAttrs['Total'] ?? '');

        $rfcEmisor = $emisor ? (string) ($emisor->attributes()['Rfc'] ?? '') : '';
        $nombreEmisor = $emisor ? (string) ($emisor->attributes()['Nombre'] ?? '') : '';

        // Update expense (sin “validar SAT”, solo autollenado)
        DB::transaction(function () use ($expense, $uuid, $rfcEmisor, $nombreEmisor, $fecha, $total) {
            $expense->receipt_type = 'CFDI';
            if ($uuid)
                $expense->cfdi_uuid = $uuid;
            if ($rfcEmisor)
                $expense->cfdi_emitter_rfc = $rfcEmisor;
            if ($nombreEmisor)
                $expense->cfdi_emitter_name = $nombreEmisor;
            if ($fecha)
                $expense->cfdi_issue_datetime = $fecha;

            // si el gasto estaba vacío, sugerimos vendor/amount
            if (!$expense->vendor && $nombreEmisor)
                $expense->vendor = $nombreEmisor;
            if ($total && (float) $expense->amount <= 0)
                $expense->amount = (float) $total;

            $expense->save();
        });

        return response()->json([
            'expense' => $expense->fresh()->load('files'),
            'parsed' => [
                'uuid' => $uuid,
                'emitter_rfc' => $rfcEmisor,
                'emitter_name' => $nombreEmisor,
                'issue_datetime' => $fecha,
                'total' => $total,
                'fecha_timbrado' => $fechaTimbrado,
            ],
        ]);
    }
}