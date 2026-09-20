<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = ['name', 'email', 'password', 'currency'];

    /**
     * Authenticate by normalized email and verify the stored password hash.
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->rawFirst(
            "SELECT * FROM users WHERE LOWER(email) = ? LIMIT 1",
            [strtolower(trim($email))]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        // Update last login timestamp
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

        return $user;
    }

    /**
     * Register a new user, returns the new ID or throws on duplicate email.
     */
    public function register(string $name, string $email, string $password, string $currency = '$'): int
    {
        if ($this->exists('email', $email)) {
            throw new \RuntimeException('An account with this email already exists.');
        }

        return $this->create([
            'name'     => $name,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'currency' => $currency,
        ]);
    }

    /**
     * Update user profile fields (name, email, currency). Password is separate.
     */
    public function updateProfile(int $userId, array $data): bool
    {
        $allowed = array_intersect_key($data, array_flip(['name', 'email', 'currency']));
        if (empty($allowed)) return false;
        return $this->update($userId, $allowed);
    }

    /**
     * Change user password after verifying the current password.
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->find($userId);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return false;
        }
        $this->update($userId, ['password' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12])]);
        return true;
    }
}
