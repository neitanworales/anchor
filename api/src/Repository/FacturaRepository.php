<?php

require_once __DIR__ . '/BaseRepository.php';

class FacturaRepository extends BaseRepository
{
    protected $table = 'facturas';

    private $fillable = array(
        'uuid',
        'version_cfdi',
        'serie',
        'folio',
        'fecha_emision',
        'fecha_timbrado',
        'tipo_comprobante',
        'moneda',
        'tipo_cambio',
        'subtotal',
        'descuento',
        'total',
        'metodo_pago',
        'forma_pago',
        'lugar_expedicion',
        'exportacion',
        'rfc_emisor',
        'nombre_emisor',
        'regimen_emisor',
        'rfc_receptor',
        'nombre_receptor',
        'domicilio_fiscal_receptor',
        'regimen_receptor',
        'uso_cfdi',
        'sello_cfd',
        'no_certificado',
        'certificado',
        'sello_sat',
        'no_certificado_sat',
        'rfc_pac',
        'xml_original',
        'ruta_xml',
        'estatus_sat'
    );

    public function getFillable()
    {
        return $this->fillable;
    }

    public function create(array $data)
    {
        return $this->insertRow($this->normalizePayload($data), $this->fillable);
    }

    public function updateById($id, array $data)
    {
        return $this->updateRow($id, $this->normalizePayload($data), $this->fillable);
    }

    private function normalizePayload(array $data)
    {
        $nullableFields = array(
            'fecha_emision',
            'fecha_timbrado',
            'tipo_cambio',
            'subtotal',
            'descuento',
            'total',
        );

        foreach ($nullableFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    public function findByUserFilters(array $filters, $limit = 100, $offset = 0)
    {
        $sql = 'SELECT f.* FROM facturas f INNER JOIN factura_usuarios fu ON fu.factura_id = f.id';
        $conditions = array();
        $params = array();
        $types = '';

        $this->addFilter($conditions, $params, $types, 'fu.usuario_id = ?', 'registradoPor', $filters, 'i');
        $this->addFilter($conditions, $params, $types, 'fu.aprobado_por = ?', 'aprobadoPor', $filters, 'i');
        $this->addFilter($conditions, $params, $types, 'fu.aprobado = ?', 'aprobado', $filters, 'i');

        $this->addFilter($conditions, $params, $types, 'f.tipo_comprobante = ?', 'tipoFactura', $filters, 's');
        $this->addFilterLike($conditions, $params, $types, 'f.nombre_emisor', 'negocio', $filters);
        $this->addFilter($conditions, $params, $types, 'f.rfc_emisor = ?', 'rfcEmisor', $filters, 's');
        $this->addFilter($conditions, $params, $types, 'f.rfc_receptor = ?', 'rfcReceptor', $filters, 's');
        $this->addFilter($conditions, $params, $types, 'f.metodo_pago = ?', 'metodoPago', $filters, 's');
        $this->addFilter($conditions, $params, $types, 'f.forma_pago = ?', 'formaPago', $filters, 's');
        $this->addFilter($conditions, $params, $types, 'f.moneda = ?', 'moneda', $filters, 's');
        $this->addFilter($conditions, $params, $types, 'f.estatus_sat = ?', 'estatusSat', $filters, 's');
        $this->addFilter($conditions, $params, $types, 'f.uuid = ?', 'uuid', $filters, 's');
        $this->addFilter($conditions, $params, $types, 'f.serie = ?', 'serie', $filters, 's');
        $this->addFilter($conditions, $params, $types, 'f.folio = ?', 'folio', $filters, 's');

        $this->addDateRange($conditions, $params, $types, 'f.fecha_emision', 'fechaDesde', 'fechaHasta', $filters);
        $this->addDateRange($conditions, $params, $types, 'f.fecha_timbrado', 'fechaTimbradoDesde', 'fechaTimbradoHasta', $filters);

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY f.id DESC LIMIT ? OFFSET ?';
        $params[] = (int) $limit;
        $params[] = (int) $offset;
        $types .= 'ii';

        $stmt = $this->db->prepare($sql);
        $this->bindDynamicParams($stmt, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    public function getDashboardSummary($userId, $days = 30, $limit = 5)
    {
        $summary = array(
            'capturadas_ultimos_30' => 0,
            'en_revision' => 0,
            'aprobadas' => 0,
        );

        $sql = 'SELECT '
            . 'COUNT(*) AS total, '
            . 'SUM(CASE WHEN fu.aprobado = 0 THEN 1 ELSE 0 END) AS en_revision, '
            . 'SUM(CASE WHEN fu.aprobado = 1 THEN 1 ELSE 0 END) AS aprobadas, '
            . 'SUM(CASE WHEN fu.date_created >= (NOW() - INTERVAL ? DAY) THEN 1 ELSE 0 END) AS capturadas_ultimos_30 '
            . 'FROM factura_usuarios fu '
            . 'WHERE fu.usuario_id = ?';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $days, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            $summary['capturadas_ultimos_30'] = (int) $row['capturadas_ultimos_30'];
            $summary['en_revision'] = (int) $row['en_revision'];
            $summary['aprobadas'] = (int) $row['aprobadas'];
        }

        $sql = 'SELECT f.tipo_comprobante, f.nombre_emisor, f.estatus_sat, '
            . 'fu.aprobado, fu.aprobado_por, fu.date_created '
            . 'FROM factura_usuarios fu '
            . 'INNER JOIN facturas f ON f.id = fu.factura_id '
            . 'WHERE fu.usuario_id = ? '
            . 'ORDER BY fu.date_created DESC '
            . 'LIMIT ?';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $activity = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return array(
            'summary' => $summary,
            'activity' => $activity,
        );
    }

    private function addFilter(array &$conditions, array &$params, string &$types, string $clause, string $key, array $filters, string $type)
    {
        if (!isset($filters[$key]) || $filters[$key] === '') {
            return;
        }
        $conditions[] = $clause;
        $params[] = $filters[$key];
        $types .= $type;
    }

    private function addFilterLike(array &$conditions, array &$params, string &$types, string $column, string $key, array $filters)
    {
        if (!isset($filters[$key]) || $filters[$key] === '') {
            return;
        }
        $conditions[] = $column . ' LIKE ?';
        $params[] = '%' . $filters[$key] . '%';
        $types .= 's';
    }

    private function addDateRange(array &$conditions, array &$params, string &$types, string $column, string $fromKey, string $toKey, array $filters)
    {
        if (isset($filters[$fromKey]) && $filters[$fromKey] !== '') {
            $conditions[] = $column . ' >= ?';
            $params[] = $filters[$fromKey];
            $types .= 's';
        }
        if (isset($filters[$toKey]) && $filters[$toKey] !== '') {
            $conditions[] = $column . ' <= ?';
            $params[] = $filters[$toKey];
            $types .= 's';
        }
    }

    private function bindDynamicParams($stmt, $types, array $params)
    {
        $refs = array();
        $refs[] = &$types;
        foreach ($params as $key => $value) {
            $refs[] = &$params[$key];
        }

        call_user_func_array(array($stmt, 'bind_param'), $refs);
    }
}
