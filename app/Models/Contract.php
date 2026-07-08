<?php

declare(strict_types=1);

namespace App\Models;

final class Contract extends BaseModel
{
    public function paginateByCompany(int $companyId, string $search = '', int $limit = 20): array
    {
        $sql = '
            SELECT contracts.*, clients.name AS client_name, quotes.quote_number
            FROM contracts
            INNER JOIN clients ON clients.id = contracts.client_id AND clients.company_id = contracts.company_id
            LEFT JOIN quotes ON quotes.id = contracts.quote_id AND quotes.company_id = contracts.company_id
            WHERE contracts.company_id = :company_id';
        $params = ['company_id' => $companyId];

        if ($search !== '') {
            $sql .= ' AND (contracts.contract_number LIKE :search OR contracts.title LIKE :search OR clients.name LIKE :search OR quotes.quote_number LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY contracts.created_at DESC LIMIT ' . max(1, $limit);

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function countByCompany(int $companyId, string $search = ''): int
    {
        $sql = '
            SELECT COUNT(*)
            FROM contracts
            INNER JOIN clients ON clients.id = contracts.client_id AND clients.company_id = contracts.company_id
            LEFT JOIN quotes ON quotes.id = contracts.quote_id AND quotes.company_id = contracts.company_id
            WHERE contracts.company_id = :company_id';
        $params = ['company_id' => $companyId];

        if ($search !== '') {
            $sql .= ' AND (contracts.contract_number LIKE :search OR contracts.title LIKE :search OR clients.name LIKE :search OR quotes.quote_number LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function findByIdAndCompany(int $id, int $companyId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT contracts.*, clients.name AS client_name, quotes.quote_number
             FROM contracts
             INNER JOIN clients ON clients.id = contracts.client_id AND clients.company_id = contracts.company_id
             LEFT JOIN quotes ON quotes.id = contracts.quote_id AND quotes.company_id = contracts.company_id
             WHERE contracts.id = :id AND contracts.company_id = :company_id
             LIMIT 1'
        );
        $statement->execute([
            'id' => $id,
            'company_id' => $companyId,
        ]);

        $contract = $statement->fetch();

        return $contract ?: null;
    }

    public function nextNumber(int $companyId): string
    {
        $statement = $this->db->prepare('SELECT COUNT(*) + 1 FROM contracts WHERE company_id = :company_id');
        $statement->execute(['company_id' => $companyId]);

        return 'CON-' . str_pad((string) $statement->fetchColumn(), 5, '0', STR_PAD_LEFT);
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO contracts
                (company_id, client_id, quote_id, contract_number, title, status, starts_at, ends_at, value_total, payment_terms, scope, terms, notes, signed_at, created_at, updated_at)
             VALUES
                (:company_id, :client_id, :quote_id, :contract_number, :title, :status, :starts_at, :ends_at, :value_total, :payment_terms, :scope, :terms, :notes, :signed_at, NOW(), NOW())'
        );

        $statement->execute($this->mapPayload($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $companyId, array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE contracts SET
                client_id = :client_id,
                quote_id = :quote_id,
                contract_number = :contract_number,
                title = :title,
                status = :status,
                starts_at = :starts_at,
                ends_at = :ends_at,
                value_total = :value_total,
                payment_terms = :payment_terms,
                scope = :scope,
                terms = :terms,
                notes = :notes,
                signed_at = :signed_at,
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
        $statement = $this->db->prepare('DELETE FROM contracts WHERE id = :id AND company_id = :company_id');

        return $statement->execute([
            'id' => $id,
            'company_id' => $companyId,
        ]);
    }

    private function mapPayload(array $data): array
    {
        return [
            'company_id' => $data['company_id'],
            'client_id' => $data['client_id'],
            'quote_id' => $data['quote_id'] ?: null,
            'contract_number' => $data['contract_number'],
            'title' => $data['title'],
            'status' => $data['status'],
            'starts_at' => $data['starts_at'] ?: null,
            'ends_at' => $data['ends_at'] ?: null,
            'value_total' => (float) str_replace(',', '.', (string) ($data['value_total'] ?? 0)),
            'payment_terms' => trim((string) ($data['payment_terms'] ?? '')) ?: null,
            'scope' => trim((string) ($data['scope'] ?? '')) ?: null,
            'terms' => trim((string) ($data['terms'] ?? '')) ?: null,
            'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
            'signed_at' => ($data['status'] ?? '') === 'signed' && empty($data['signed_at']) ? date('Y-m-d H:i:s') : ($data['signed_at'] ?: null),
        ];
    }
}
