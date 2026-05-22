<?php

require_once __DIR__ . '/BaseRepository.php';

class AuthTokenRepository extends BaseRepository
{
    protected $table = 'auth_tokens';
    private $supportsTokenType = null;

    private function hasTokenTypeColumn()
    {
        if ($this->supportsTokenType !== null) {
            return $this->supportsTokenType;
        }

        $result = $this->db->query("SHOW COLUMNS FROM auth_tokens LIKE 'token_type'");
        $this->supportsTokenType = $result && $result->num_rows > 0;
        if ($result) {
            $result->close();
        }

        return $this->supportsTokenType;
    }

    public function createToken($userId, $token, $expiresAt, $tokenType = 'auth')
    {
        if ($this->hasTokenTypeColumn()) {
            $sql = 'INSERT INTO auth_tokens (user_id, token, token_type, expires_at, created_at) VALUES (?, ?, ?, ?, NOW())';
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('isss', $userId, $token, $tokenType, $expiresAt);
        } else {
            $sql = 'INSERT INTO auth_tokens (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())';
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('iss', $userId, $token, $expiresAt);
        }

        $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();

        return $id;
    }

    public function findActiveToken($token)
    {
        if ($this->hasTokenTypeColumn()) {
            $sql = 'SELECT * FROM auth_tokens WHERE token = ? AND token_type = ? AND revoked_at IS NULL AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $tokenType = 'auth';
            $stmt->bind_param('ss', $token, $tokenType);
        } else {
            $sql = 'SELECT * FROM auth_tokens WHERE token = ? AND revoked_at IS NULL AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $token);
        }

        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    public function revokeByToken($token)
    {
        $sql = 'UPDATE auth_tokens SET revoked_at = NOW() WHERE token = ? AND revoked_at IS NULL';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $token);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function createPasswordResetToken($userId, $token, $expiresAt)
    {
        $tokenToStore = $this->hasTokenTypeColumn() ? $token : 'rst_' . $token;
        $this->createToken($userId, $tokenToStore, $expiresAt, 'password_reset');

        return $token;
    }

    public function findActivePasswordResetToken($token)
    {
        $tokenToFind = $this->hasTokenTypeColumn() ? $token : 'rst_' . $token;

        if ($this->hasTokenTypeColumn()) {
            $sql = 'SELECT * FROM auth_tokens WHERE token = ? AND token_type = ? AND revoked_at IS NULL AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $tokenType = 'password_reset';
            $stmt->bind_param('ss', $tokenToFind, $tokenType);
        } else {
            $sql = 'SELECT * FROM auth_tokens WHERE token = ? AND revoked_at IS NULL AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $tokenToFind);
        }

        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    public function consumePasswordResetToken($token)
    {
        $tokenToRevoke = $this->hasTokenTypeColumn() ? $token : 'rst_' . $token;
        return $this->revokeByToken($tokenToRevoke);
    }
}
