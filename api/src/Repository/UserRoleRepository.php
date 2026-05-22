<?php

require_once __DIR__ . '/BaseRepository.php';

class UserRoleRepository extends BaseRepository
{
    protected $table = 'user_roles';

    public function getRolesByUserId($userId)
    {
        $sql = 'SELECT roles.id, roles.name FROM user_roles INNER JOIN roles ON user_roles.role_id = roles.id WHERE user_roles.user_id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return array_map(function ($row) {
            return [
                'id' => (int) $row['id'],
                'name' => strtolower(trim((string) $row['name']))
            ];
        }, $rows);
    }

    public function addRoles($userId, $roles)
    {
        $roles = (array) $roles;
        if (empty($roles)) {
            return 0;
        }

        $sql = 'INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)';
        $stmt = $this->db->prepare($sql);
        $added = 0;

        foreach ($roles as $role) {
            $normalizedRole = strtolower(trim((string) $role));
            if ($normalizedRole === '') {
                continue;
            }

            $stmt->bind_param('is', $userId, $normalizedRole);
            $stmt->execute();
            $added += (int) $stmt->affected_rows;
        }

        $stmt->close();
        return $added;
    }

    public function removeRoles($userId, $roles)
    {
        $roles = array_values(array_filter(array_map(function ($role) {
            return strtolower(trim((string) $role));
        }, (array) $roles), function ($role) {
            return $role !== '';
        }));

        if (empty($roles)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $sql = "DELETE FROM user_roles WHERE user_id = ? AND role IN ($placeholders)";
        $stmt = $this->db->prepare($sql);

        $types = 'i' . str_repeat('s', count($roles));
        $params = array_merge(array($userId), $roles);

        $bindParams = array($types);
        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }

        call_user_func_array(array($stmt, 'bind_param'), $bindParams);
        $stmt->execute();
        $removed = (int) $stmt->affected_rows;
        $stmt->close();

        return $removed;
    }
}
