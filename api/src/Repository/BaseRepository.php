<?php

require_once __DIR__ . '/../Database/Connection.php';

abstract class BaseRepository
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findById($id)
    {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE ' . $this->primaryKey . ' = ? LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    public function findAll($limit = 100, $offset = 0)
    {
        $sql = 'SELECT * FROM ' . $this->table . ' LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    public function deleteById($id)
    {
        $sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->primaryKey . ' = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    protected function insertRow(array $data, array $allowedFields)
    {
        $payload = $this->filterData($data, $allowedFields);
        if (empty($payload)) {
            return 0;
        }

        $columns = array_keys($payload);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = 'INSERT INTO ' . $this->table . ' (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')';
        $stmt = $this->db->prepare($sql);

        $values = array_values($payload);
        $types = str_repeat('s', count($values));
        $this->bindParams($stmt, $types, $values);

        $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();

        return $id;
    }

    protected function updateRow($id, array $data, array $allowedFields)
    {
        $payload = $this->filterData($data, $allowedFields);
        if (empty($payload)) {
            return false;
        }

        $setParts = array();
        foreach (array_keys($payload) as $column) {
            $setParts[] = $column . ' = ?';
        }

        $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $setParts) . ' WHERE ' . $this->primaryKey . ' = ?';
        $stmt = $this->db->prepare($sql);

        $values = array_values($payload);
        $values[] = $id;
        $types = str_repeat('s', count($values) - 1) . 'i';
        $this->bindParams($stmt, $types, $values);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    private function filterData(array $data, array $allowedFields)
    {
        if (empty($allowedFields)) {
            return $data;
        }

        $filtered = array();
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $filtered[$field] = $data[$field];
            }
        }

        return $filtered;
    }

    private function bindParams($stmt, $types, array $params)
    {
        $refs = array();
        $refs[] = &$types;
        foreach ($params as $key => $value) {
            $refs[] = &$params[$key];
        }

        call_user_func_array(array($stmt, 'bind_param'), $refs);
    }
}
