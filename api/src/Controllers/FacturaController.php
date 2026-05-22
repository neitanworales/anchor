<?php

require_once __DIR__ . '/CrudController.php';
require_once __DIR__ . '/../Repository/FacturaRepository.php';

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
}
