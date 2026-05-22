<?php

require_once __DIR__ . '/BaseRepository.php';

class PagoDocumentoRelacionadoRepository extends BaseRepository
{
    protected $table = 'pagos_documentos_relacionados';

    private $fillable = array(
        'pago_id',
        'uuid_documento',
        'serie',
        'folio',
        'moneda_dr',
        'metodo_pago_dr',
        'num_parcialidad',
        'saldo_anterior',
        'importe_pagado',
        'saldo_insoluto',
        'objeto_imp_dr'
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

    public function findByPagoId($pagoId)
    {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE pago_id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $pagoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}
