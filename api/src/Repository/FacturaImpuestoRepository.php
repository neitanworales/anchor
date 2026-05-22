<?php

require_once __DIR__ . '/BaseRepository.php';

class FacturaImpuestoRepository extends BaseRepository
{
    protected $table = 'factura_impuestos';

    private $fillable = array(
        'factura_id',
        'concepto_id',
        'tipo',
        'impuesto',
        'tipo_factor',
        'tasa_cuota',
        'base',
        'importe'
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

    public function findByFacturaId($facturaId)
    {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE factura_id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $facturaId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}
