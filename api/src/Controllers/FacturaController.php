<?php

require_once __DIR__ . '/CrudController.php';
require_once __DIR__ . '/../Repository/FacturaRepository.php';
require_once __DIR__ . '/../Repository/FacturaUsuarioRepository.php';
require_once __DIR__ . '/../Services/AuthService.php';

class FacturaController extends CrudController
{
    public function __construct()
    {
        $this->repo = new FacturaRepository();
        $this->allowedFields = $this->repo->getFillable();
        $this->resourceLabel = 'factura';
    }

    public function listByUsuario($request)
    {
        if (!$this->requireAuth()) {
            return $this->fail('unauthorized', 401);
        }

        $registradoPor = isset($request['registradoPor']) ? (int) $request['registradoPor'] : 0;
        $aprobadoPor = isset($request['aprobadoPor']) ? (int) $request['aprobadoPor'] : 0;

        if ($registradoPor <= 0 && $aprobadoPor <= 0) {
            return $this->fail('registradoPor or aprobadoPor is required', 422);
        }

        $limit = isset($request['limit']) ? (int) $request['limit'] : 100;
        $offset = isset($request['offset']) ? (int) $request['offset'] : 0;

        $filters = array(
            'registradoPor' => $registradoPor > 0 ? $registradoPor : null,
            'aprobadoPor' => $aprobadoPor > 0 ? $aprobadoPor : null,
            'aprobado' => isset($request['aprobado']) ? (int) $request['aprobado'] : null,
            'tipoFactura' => isset($request['tipoFactura']) ? $request['tipoFactura'] : null,
            'negocio' => isset($request['negocio']) ? $request['negocio'] : null,
            'rfcEmisor' => isset($request['rfcEmisor']) ? $request['rfcEmisor'] : null,
            'rfcReceptor' => isset($request['rfcReceptor']) ? $request['rfcReceptor'] : null,
            'metodoPago' => isset($request['metodoPago']) ? $request['metodoPago'] : null,
            'formaPago' => isset($request['formaPago']) ? $request['formaPago'] : null,
            'moneda' => isset($request['moneda']) ? $request['moneda'] : null,
            'estatusSat' => isset($request['estatusSat']) ? $request['estatusSat'] : null,
            'uuid' => isset($request['uuid']) ? $request['uuid'] : null,
            'serie' => isset($request['serie']) ? $request['serie'] : null,
            'folio' => isset($request['folio']) ? $request['folio'] : null,
            'fechaDesde' => isset($request['fechaDesde']) ? $request['fechaDesde'] : null,
            'fechaHasta' => isset($request['fechaHasta']) ? $request['fechaHasta'] : null,
            'fechaTimbradoDesde' => isset($request['fechaTimbradoDesde']) ? $request['fechaTimbradoDesde'] : null,
            'fechaTimbradoHasta' => isset($request['fechaTimbradoHasta']) ? $request['fechaTimbradoHasta'] : null,
        );

        $rows = $this->repo->findByUserFilters($filters, $limit, $offset);
        return $this->ok(array(
            'items' => $this->camelize($rows),
        ), 'facturas list');
    }

    public function create($request)
    {
        $user = $this->requireAuth();
        if (!$user) {
            return $this->fail('unauthorized', 401);
        }

        $payload = $this->extractPayload($request);
        if (empty($payload)) {
            return $this->fail('payload is required', 422);
        }

        $id = $this->repo->create($payload);
        if (!$id) {
            return $this->fail('failed to create factura', 500);
        }

        $facturaUsuarioRepo = new FacturaUsuarioRepository();
        $facturaUsuarioRepo->create(array(
            'usuario_id' => (int) $user->id,
            'factura_id' => (int) $id,
            'aprobado' => 0,
        ));

        $row = $this->repo->findById($id);
        return $this->ok(array(
            'item' => $this->camelize($row),
        ), 'factura created');
    }

    public function dashboardSummary($request)
    {
        $user = $this->requireAuth();
        if (!$user) {
            return $this->fail('unauthorized', 401);
        }

        $days = isset($request['days']) ? (int) $request['days'] : 30;
        $limit = isset($request['limit']) ? (int) $request['limit'] : 5;

        $filters = array(
            'aprobado' => isset($request['aprobado']) && $request['aprobado'] !== '' ? (int) $request['aprobado'] : null,
            'tipoComprobante' => isset($request['tipoComprobante']) ? $request['tipoComprobante'] : null,
        );

        $data = $this->repo->getDashboardSummary((int) $user->id, $days, $limit, $filters);
        return $this->ok(array(
            'summary' => $this->camelize($data['summary']),
            'activity' => $this->camelize($data['activity']),
        ), 'dashboard summary');
    }
}
