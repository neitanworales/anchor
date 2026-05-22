<?php

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../Models/User.php';

class UserRepository extends BaseRepository
{
    protected $table = 'users';

    public function findModelById($id)
    {
        $row = $this->findById($id);
        return $row ? new User($row) : null;
    }

    public function findByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = ? LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ? new User($row) : null;
    }

    public function findByVerificationCode($code)
    {
        $sql = 'SELECT * FROM users WHERE verification_code = ? LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ? new User($row) : null;
    }

    public function updateVerificationCode($userId, $verificationCode)
    {
        $sql = 'UPDATE users SET verification_code = ? WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $verificationCode, $userId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function create(User $user)
    {
        $sql = 'INSERT INTO users (name, second_name, last_name, second_last_name, user_status, email, password_hash, registered_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())';
        $stmt = $this->db->prepare($sql);

        $stmt->bind_param(
            'sssssss',
            $user->name,
            $user->second_name,
            $user->last_name,
            $user->second_last_name,
            $user->user_status,
            $user->email,
            $user->password_hash
        );

        $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();

        return $id;
    }

    public function update(User $user)
    {
        $sql = 'UPDATE users
            SET name = ?, second_name = ?, last_name = ?, second_last_name = ?, user_status = ?, email = ?, password_hash = ?
            WHERE id = ?';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'sssssssi',
            $user->name,
            $user->second_name,
            $user->last_name,
            $user->second_last_name,
            $user->user_status,
            $user->email,
            $user->password_hash,
            $user->id
        );

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function updatePasswordHash($userId, $newHash)
    {
        $sql = 'UPDATE users SET password_hash = ? WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $newHash, $userId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}
