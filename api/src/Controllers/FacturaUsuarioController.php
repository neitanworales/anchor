<?php

require_once __DIR__ . '/CrudController.php';
require_once __DIR__ . '/../Repository/FacturaUsuarioRepository.php';

class FacturaUsuarioController extends CrudController
{
    public function __construct()
    {
        $this->repo = new FacturaUsuarioRepository();
        $this->allowedFields = $this->repo->getFillable();
        $this->resourceLabel = 'factura usuario';
    }

    public function create($request)
    {
        $payload = $this->extractPayload($request);
        if (empty($payload)) {
            return $this->fail('payload is required', 422);
        }

        $payload = $this->applyApprovalDefaults($payload);
        $id = $this->repo->create($payload);
        if (!$id) {
            return $this->fail('failed to create ' . $this->resourceLabel, 500);
        }

        $row = $this->repo->findById($id);
        return $this->ok(array('item' => $this->camelize($row)), $this->resourceLabel . ' created');
    }

    public function update($request)
    {
        $id = isset($request['id']) ? (int) $request['id'] : 0;
        if ($id <= 0) {
            return $this->fail('id is required', 422);
        }

        $payload = $this->extractPayload($request);
        if (empty($payload)) {
            return $this->fail('payload is required', 422);
        }

        $payload = $this->applyApprovalDefaults($payload);
        $ok = $this->repo->updateById($id, $payload);
        if (!$ok) {
            return $this->fail('failed to update ' . $this->resourceLabel, 500);
        }

        $row = $this->repo->findById($id);
        return $this->ok(array('item' => $this->camelize($row)), $this->resourceLabel . ' updated');
    }

    private function applyApprovalDefaults(array $payload)
    {
        if (array_key_exists('aprobado', $payload)) {
            $aprobado = (int) $payload['aprobado'];
            if ($aprobado === 1 && empty($payload['date_approved'])) {
                $payload['date_approved'] = date('Y-m-d H:i:s');
            }
        }

        return $payload;
    }
}
