<?php

require_once __DIR__ . '/../Repository/UserRepository.php';
require_once __DIR__ . '/../Repository/AuthTokenRepository.php';
//require_once __DIR__ . '/../Repository/UserRoleRepository.php';
//require_once __DIR__ . '/EmailService.php';

class AuthService
{
    private $users;
    private $tokens;
    private $roles;
    private $email;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->tokens = new AuthTokenRepository();
        $this->roles = new UserRoleRepository();
        //$this->email = new EmailService();
    }

    public function login($email, $plainPassword)
    {
        $user = $this->users->findByEmail($email);
        if (!$user) {
            return null;
        }

        if (!$this->verifyAndUpgradePassword($user, $plainPassword)) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
        $this->tokens->createToken((int) $user->id, $token, $expiresAt);

        $userRoles = $this->roles->getRolesByUserId((int) $user->id);

        return array(
            'token' => $token,
            'expires_at' => $expiresAt,
            'user' => array(
                'id' => (int) $user->id,
                'firstName' => $user->name,
                'secondName' => $user->second_name,
                'lastName' => $user->last_name,
                'secondLastName' => $user->second_last_name,
                'email' => $user->email,
                'status' => $user->user_status,
            ),
            'roles' => $userRoles,
        );
    }

    public function validateToken($token)
    {
        $tokenRow = $this->tokens->findActiveToken($token);
        if (!$tokenRow) {
            return null;
        }

        return $this->users->findModelById((int) $tokenRow['user_id']);
    }

    public function logout($token)
    {
        return $this->tokens->revokeByToken($token);
    }

    public function requestPasswordReset($email)
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $user = $this->users->findByEmail($email);

        // Avoid account enumeration. Always return a valid-shaped response.
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $this->tokens->createPasswordResetToken((int) $user->id, $token, $expiresAt);
            $this->sendPasswordResetEmail($user, $token, $expiresAt);
        }

        return array(
            'expires_at' => $expiresAt,
            'email_sent' => true,
        );
    }

    public function resetPasswordByToken($token, $newPassword)
    {
        $tokenRow = $this->tokens->findActivePasswordResetToken($token);
        if (!$tokenRow) {
            return false;
        }

        $updated = $this->updatePassword((int) $tokenRow['user_id'], $newPassword);
        if (!$updated) {
            return false;
        }

        $this->tokens->consumePasswordResetToken($token);
        return true;
    }

    public function validatePasswordResetToken($token)
    {
        return $this->tokens->findActivePasswordResetToken($token);
    }

    private function sendPasswordResetEmail($user, $token, $expiresAt)
    {
        $resetUrl = $this->buildPasswordResetUrl($token);
        $sent = $this->email->sendPasswordResetEmail($user, $resetUrl, $expiresAt);
        if (!$sent) {
            error_log('Password reset email failed for: ' . (isset($user->email) ? $user->email : 'unknown'));
        }
        return $sent;
    }

    private function buildPasswordResetUrl($token)
    {
        $baseUrl = getenv('PASSWORD_RESET_URL_BASE');
        if (!$baseUrl) {
            $baseUrl = 'https://ywampachuca.org/retourbano/recovery-password';
        }

        $separator = (strpos($baseUrl, '?') === false) ? '?' : '&';
        return $baseUrl . $separator . 'token=' . urlencode($token);
    }

    public function hashPassword($plainPassword)
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function updatePassword($userId, $newPassword)
    {
        $hash = $this->hashPassword($newPassword);
        return $this->users->updatePasswordHash((int) $userId, $hash);
    }

    public function userHasAnyRole($userId, $allowedRoles)
    {
        $allowed = array_map(function ($role) {
            return strtolower(trim((string) $role));
        }, (array) $allowedRoles);

        $currentRoles = $this->roles->getRolesByUserId((int) $userId);
        if (empty($currentRoles) || empty($allowed)) {
            return false;
        }

        // "super" acts as a global privileged role for role-protected endpoints.
        if (in_array('super', $currentRoles, true)) {
            return true;
        }

        $roleAliases = array(
            'admin' => array('admin', 'administrador', 'administrator'),
            'staff' => array('staff', 'lider', 'leader'),
        );

        $expandedAllowed = array();
        foreach ($allowed as $role) {
            if (isset($roleAliases[$role])) {
                $expandedAllowed = array_merge($expandedAllowed, $roleAliases[$role]);
            } else {
                $expandedAllowed[] = $role;
            }
        }

        $expandedAllowed = array_values(array_unique($expandedAllowed));

        foreach ($currentRoles as $role) {
            if (in_array($role, $expandedAllowed, true)) {
                return true;
            }
        }

        return false;
    }

    private function verifyAndUpgradePassword($user, $plainPassword)
    {
        $storedHash = isset($user->password_hash) ? $user->password_hash : null;
        if (!$storedHash) {
            return false;
        }

        if (password_verify($plainPassword, $storedHash)) {
            if (password_needs_rehash($storedHash, PASSWORD_BCRYPT, array('cost' => 12))) {
                $this->users->updatePasswordHash((int) $user->id, $this->hashPassword($plainPassword));
            }
            return true;
        }

        if ($this->isLegacyPasswordMatch($plainPassword, $storedHash)) {
            $this->users->updatePasswordHash((int) $user->id, $this->hashPassword($plainPassword));
            return true;
        }

        return false;
    }

    private function isLegacyPasswordMatch($plainPassword, $storedHash)
    {
        // Transitional compatibility for legacy records before full migration.
        if (hash_equals($storedHash, $plainPassword)) {
            return true;
        }

        if (hash_equals($storedHash, md5($plainPassword))) {
            return true;
        }

        if (hash_equals($storedHash, sha1($plainPassword))) {
            return true;
        }

        return false;
    }
}
