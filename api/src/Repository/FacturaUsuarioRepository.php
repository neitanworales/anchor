<?php

require_once __DIR__ . '/BaseRepository.php';

class FacturaUsuarioRepository extends BaseRepository
{
    protected $table = 'factura_usuarios';

    private $fillable = array(
        'usuario_id',
        'factura_id',
        'aprobado',
        'aprobado_por',
        'date_created',
        'date_approved'
    );

    public function getFillable()
    {
        return $this->fillable;
    }

    public function create(array $data)
    {
        return $this->insertRow($data, $this->fillable);
    }

    public function updateById($id, array $data)
    {
        return $this->updateRow($id, $data, $this->fillable);
    }
}
