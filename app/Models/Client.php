<?php

declare(strict_types=1);

namespace App\Models;

final class Client extends BaseModel
{
    public function paginateByCompany(int $companyId, string $search = '', int $limit = 20): array
    {
        $sql = 'SELECT * FROM clients WHERE company_id = :company_id';
        $params = ['company_id' => $companyId];

        if ($search !== '') {
            $sql .= ' AND (name LIKE :search OR email LIKE :search OR phone LIKE :search OR company_name LIKE :search OR document LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY created_at DESC LIMIT ' . max(1, $limit);

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function countByCompany(int $companyId, string $search = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM clients WHERE company_id = :company_id';
        $params = ['company_id' => $companyId];

        if ($search !== '') {
            $sql .= ' AND (name LIKE :search OR email LIKE :search OR phone LIKE :search OR company_name LIKE :search OR document LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function findByIdAndCompany(int $id, int $companyId): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM clients WHERE id = :id AND company_id = :company_id LIMIT 1');
        $statement->execute([
            'id' => $id,
            'company_id' => $companyId,
        ]);

        $client = $statement->fetch();

        return $client ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO clients
                (company_id, name, company_name, email, phone, whatsapp, document, zip_code, street, number, complement, neighborhood, city, state, notes, status, created_at, updated_at)
             VALUES
                (:company_id, :name, :company_name, :email, :phone, :whatsapp, :document, :zip_code, :street, :number, :complement, :neighborhood, :city, :state, :notes, :status, NOW(), NOW())'
        );

        $statement->execute($this->mapPayload($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $companyId, array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE clients SET
                name = :name,
                company_name = :company_name,
                email = :email,
                phone = :phone,
                whatsapp = :whatsapp,
                document = :document,
                zip_code = :zip_code,
                street = :street,
                number = :number,
                complement = :complement,
                neighborhood = :neighborhood,
                city = :city,
                state = :state,
                notes = :notes,
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
        $statement = $this->db->prepare('DELETE FROM clients WHERE id = :id AND company_id = :company_id');

        return $statement->execute([
            'id' => $id,
            'company_id' => $companyId,
        ]);
    }

    private function mapPayload(array $data): array
    {
        return [
            'company_id' => $data['company_id'] ?? null,
            'name' => trim((string) ($data['name'] ?? '')),
            'company_name' => trim((string) ($data['company_name'] ?? '')) ?: null,
            'email' => trim((string) ($data['email'] ?? '')) ?: null,
            'phone' => trim((string) ($data['phone'] ?? '')) ?: null,
            'whatsapp' => trim((string) ($data['whatsapp'] ?? '')) ?: null,
            'document' => trim((string) ($data['document'] ?? '')) ?: null,
            'zip_code' => trim((string) ($data['zip_code'] ?? '')) ?: null,
            'street' => trim((string) ($data['street'] ?? '')) ?: null,
            'number' => trim((string) ($data['number'] ?? '')) ?: null,
            'complement' => trim((string) ($data['complement'] ?? '')) ?: null,
            'neighborhood' => trim((string) ($data['neighborhood'] ?? '')) ?: null,
            'city' => trim((string) ($data['city'] ?? '')) ?: null,
            'state' => strtoupper(trim((string) ($data['state'] ?? ''))),
            'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
            'status' => $data['status'] ?? 'active',
        ];
    }
}