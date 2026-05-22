<?php

require_once __DIR__ . '/CrudController.php';
require_once __DIR__ . '/../Repository/PagoRepository.php';

class PagoController extends CrudController
{
    public function __construct()
    {
        $this->repo = new PagoRepository();
        $this->allowedFields = $this->repo->getFillable();
        $this->resourceLabel = 'pago';
    }

    public function listByFactura($request)
    {
        $facturaId = isset($request['facturaId']) ? (int) $request['facturaId'] : (isset($request['factura_id']) ? (int) $request['factura_id'] : 0);
        if ($facturaId <= 0) {
            return $this->fail('facturaId is required', 422);
        }

        $rows = $this->repo->findByFacturaId($facturaId);
        return $this->ok(array('items' => $this->camelize($rows)), 'pagos list');
    }
}
