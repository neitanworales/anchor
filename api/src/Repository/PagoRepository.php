<?php

require_once __DIR__ . '/BaseRepository.php';

class PagoRepository extends BaseRepository
{
    protected $table = 'pagos';

    private $fillable = array(
        'factura_id',
        'fecha_pago',
        'forma_pago',
        'moneda_pago',
        'tipo_cambio_pago',
        'monto',
        'num_operacion',
        'rfc_banco_emisor',
        'cuenta_ordenante',
        'rfc_banco_receptor',
        'cuenta_beneficiario'
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
