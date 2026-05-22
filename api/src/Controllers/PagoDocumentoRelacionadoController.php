<?php

require_once __DIR__ . '/CrudController.php';
require_once __DIR__ . '/../Repository/PagoDocumentoRelacionadoRepository.php';

class PagoDocumentoRelacionadoController extends CrudController
{
    public function __construct()
    {
        $this->repo = new PagoDocumentoRelacionadoRepository();
        $this->allowedFields = $this->repo->getFillable();
        $this->resourceLabel = 'pago documento relacionado';
    }

    public function listByPago($request)
    {
        $pagoId = isset($request['pagoId']) ? (int) $request['pagoId'] : (isset($request['pago_id']) ? (int) $request['pago_id'] : 0);
        if ($pagoId <= 0) {
            return $this->fail('pagoId is required', 422);
        }

        $rows = $this->repo->findByPagoId($pagoId);
        return $this->ok(array('items' => $this->camelize($rows)), 'pago documentos relacionados list');
    }
}
