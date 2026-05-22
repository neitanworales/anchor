<?php

require_once __DIR__ . '/CrudController.php';
require_once __DIR__ . '/../Repository/FacturaImpuestoRepository.php';

class FacturaImpuestoController extends CrudController
{
    public function __construct()
    {
        $this->repo = new FacturaImpuestoRepository();
        $this->allowedFields = $this->repo->getFillable();
        $this->resourceLabel = 'factura impuesto';
    }

    public function listByFactura($request)
    {
        $facturaId = isset($request['facturaId']) ? (int) $request['facturaId'] : (isset($request['factura_id']) ? (int) $request['factura_id'] : 0);
        if ($facturaId <= 0) {
            return $this->fail('facturaId is required', 422);
        }

        $rows = $this->repo->findByFacturaId($facturaId);
        return $this->ok(array('items' => $this->camelize($rows)), 'factura impuestos list');
    }
}
