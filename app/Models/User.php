<?php

declare(strict_types=1);

namespace App\Models;

final class User extends BaseModel
{
    public static function findById(int $id): ?array
    {
        $db = \App\Core\Database::connection();
        $statement = $db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $user = $statement->fetch();

        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $db = \App\Core\Database::connection();
        $statement = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute([
            'email' => mb_strtolower(trim($email)),
        ]);

        $user = $statement->fetch();

        return $user ?: null;
    }

    public static function findAllByEmail(string $email): array
    {
        $db = \App\Core\Database::connection();
        $statement = $db->prepare('SELECT * FROM users WHERE email = :email ORDER BY id ASC');
        $statement->execute([
            'email' => mb_strtolower(trim($email)),
        ]);

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO users
                (company_id, name, email, password, role, status, email_verified_at, created_at, updated_at)
             VALUES
                (:company_id, :name, :email, :password, :role, :status, :email_verified_at, NOW(), NOW())'
        );

        $statement->execute([
            'company_id' => $data['company_id'],
            'name' => $data['name'],
            'email' => mb_strtolower(trim($data['email'])),
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'owner',
            'status' => $data['status'] ?? 'active',
            'email_verified_at' => $data['email_verified_at'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateLastLogin(int $id): void
    {
        $statement = $this->db->prepare('UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function updateRememberToken(int $id, string $tokenHash): void
    {
        $statement = $this->db->prepare('UPDATE users SET remember_token = :remember_token, updated_at = NOW() WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'remember_token' => $tokenHash,
        ]);
    }

    public function clearRememberToken(int $id): void
    {
        $statement = $this->db->prepare('UPDATE users SET remember_token = NULL, updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function updatePassword(int $id, string $password): void
    {
        $statement = $this->db->prepare('UPDATE users SET password = :password, remember_token = NULL, updated_at = NOW() WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }
}
