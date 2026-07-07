<?php

declare(strict_types=1);

namespace App\Models;

final class Service extends BaseModel
{
    public function listActiveByCompany(int $companyId): array
    {
        $statement = $this->db->prepare(
            'SELECT services.id, services.name, services.price, services.unit, service_categories.name AS category_name
             FROM services
             LEFT JOIN service_categories ON service_categories.id = services.service_category_id
             WHERE services.company_id = :company_id AND services.status = "active"
             ORDER BY services.name ASC'
        );
        $statement->execute(['company_id' => $companyId]);

        return $statement->fetchAll();
    }

    public function paginateByCompany(int $companyId, string $search = '', int $limit = 20): array
    {
        $sql = '
            SELECT services.*, service_categories.name AS category_name
            FROM services
            LEFT JOIN service_categories ON service_categories.id = services.service_category_id
            WHERE services.company_id = :company_id';
        $params = ['company_id' => $companyId];

        if ($search !== '') {
            $sql .= ' AND (services.name LIKE :search OR services.description LIKE :search OR service_categories.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY services.created_at DESC LIMIT ' . max(1, $limit);

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function countByCompany(int $companyId, string $search = ''): int
    {
        $sql = '
            SELECT COUNT(*)
            FROM services
            LEFT JOIN service_categories ON service_categories.id = services.service_category_id
            WHERE services.company_id = :company_id';
        $params = ['company_id' => $companyId];

        if ($search !== '') {
            $sql .= ' AND (services.name LIKE :search OR services.description LIKE :search OR service_categories.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function findByIdAndCompany(int $id, int $companyId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT services.*, service_categories.name AS category_name
             FROM services
             LEFT JOIN service_categories ON service_categories.id = services.service_category_id
             WHERE services.id = :id AND services.company_id = :company_id
             LIMIT 1'
        );
        $statement->execute([
            'id' => $id,
            'company_id' => $companyId,
        ]);

        $service = $statement->fetch();

        return $service ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO services
                (company_id, service_category_id, name, description, unit, average_time_minutes, price, status, created_at, updated_at)
             VALUES
                (:company_id, :service_category_id, :name, :description, :unit, :average_time_minutes, :price, :status, NOW(), NOW())'
        );

        $statement->execute($this->mapPayload($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $companyId, array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE services SET
                service_category_id = :service_category_id,
                name = :name,
                description = :description,
                unit = :unit,
                average_time_minutes = :average_time_minutes,
                price = :price,
                status = :status,
                updated_at = NOW()
             WHERE id = :id AND company_id = :company_id'
        );

        return $statement->execute($this->mapPayload($data) + [
            'id' => $id,
            'company_id' => $companyId,
        ]);
    }

    public function delete(int $id, int $companyId): bool
    {
        $statement = $this->db->prepare('DELETE FROM services WHERE id = :id AND company_id = :company_id');

        return $statement->execute([
            'id' => $id,
            'company_id' => $companyId,
        ]);
    }

    private function mapPayload(array $data): array
    {
        return [
            'company_id' => $data['company_id'] ?? null,
            'service_category_id' => $data['service_category_id'] ?? null,
            'name' => trim((string) ($data['name'] ?? '')),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'unit' => trim((string) ($data['unit'] ?? 'un')),
            'average_time_minutes' => $data['average_time_minutes'] !== '' ? (int) $data['average_time_minutes'] : null,
            'price' => (float) str_replace(',', '.', (string) ($data['price'] ?? 0)),
            'status' => in_array(($data['status'] ?? 'active'), ['active', 'inactive'], true) ? (string) $data['status'] : 'active',
        ];
    }
}