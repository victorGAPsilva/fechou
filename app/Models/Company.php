<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Company extends BaseModel
{
    public static function findById(int $id): ?array
    {
        $db = \App\Core\Database::connection();
        $statement = $db->prepare('SELECT * FROM companies WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $company = $statement->fetch();

        return $company ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO companies (name, legal_name, document, primary_color, secondary_color, status, created_at, updated_at)
             VALUES (:name, :legal_name, :document, :primary_color, :secondary_color, :status, NOW(), NOW())'
        );

        $statement->execute([
            'name' => $data['name'],
            'legal_name' => $data['legal_name'] ?? null,
            'document' => $data['document'] ?? null,
            'primary_color' => $data['primary_color'] ?? '#0f172a',
            'secondary_color' => $data['secondary_color'] ?? '#38bdf8',
            'status' => $data['status'] ?? 'active',
        ]);

        return (int) $this->db->lastInsertId();
    }
}