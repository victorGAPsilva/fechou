<?php

declare(strict_types=1);

namespace App\Models;

final class Quote extends BaseModel
{
    public function paginateByCompany(int $companyId, string $search = '', int $limit = 20): array
    {
        $sql = '
            SELECT quotes.*, clients.name AS client_name
            FROM quotes
            LEFT JOIN clients ON clients.id = quotes.client_id
            WHERE quotes.company_id = :company_id';
        $params = ['company_id' => $companyId];

        if ($search !== '') {
            $sql .= ' AND (quotes.quote_number LIKE :search OR quotes.title LIKE :search OR clients.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY quotes.created_at DESC LIMIT ' . max(1, $limit);

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function countByCompany(int $companyId, string $search = ''): int
    {
        $sql = '
            SELECT COUNT(*)
            FROM quotes
            LEFT JOIN clients ON clients.id = quotes.client_id
            WHERE quotes.company_id = :company_id';
        $params = ['company_id' => $companyId];

        if ($search !== '') {
            $sql .= ' AND (quotes.quote_number LIKE :search OR quotes.title LIKE :search OR clients.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function findByIdAndCompany(int $id, int $companyId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT quotes.*, clients.name AS client_name
             FROM quotes
             LEFT JOIN clients ON clients.id = quotes.client_id
             WHERE quotes.id = :id AND quotes.company_id = :company_id
             LIMIT 1'
        );
        $statement->execute([
            'id' => $id,
            'company_id' => $companyId,
        ]);

        $quote = $statement->fetch();

        return $quote ?: null;
    }

    public function getItems(int $quoteId): array
    {
        $statement = $this->db->prepare('SELECT * FROM quote_items WHERE quote_id = :quote_id ORDER BY sort_order ASC, id ASC');
        $statement->execute(['quote_id' => $quoteId]);

        return $statement->fetchAll();
    }

    public function nextNumber(int $companyId): string
    {
        $statement = $this->db->prepare('SELECT COUNT(*) + 1 FROM quotes WHERE company_id = :company_id');
        $statement->execute(['company_id' => $companyId]);

        return 'ORC-' . str_pad((string) $statement->fetchColumn(), 5, '0', STR_PAD_LEFT);
    }

    public function createDraft(array $data, array $items): int
    {
        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare(
                'INSERT INTO quotes
                    (company_id, client_id, quote_number, title, status, template_key, discount_total, shipping_total, subtotal_total, total, validity_days, payment_terms, warranty, notes, created_at, updated_at)
                 VALUES
                    (:company_id, :client_id, :quote_number, :title, :status, :template_key, :discount_total, :shipping_total, :subtotal_total, :total, :validity_days, :payment_terms, :warranty, :notes, NOW(), NOW())'
            );
            $statement->execute([
                'company_id' => $data['company_id'],
                'client_id' => $data['client_id'],
                'quote_number' => $data['quote_number'],
                'title' => $data['title'],
                'status' => $data['status'],
                'template_key' => $data['template_key'],
                'discount_total' => $data['discount_total'],
                'shipping_total' => $data['shipping_total'],
                'subtotal_total' => $data['subtotal_total'],
                'total' => $data['total'],
                'validity_days' => $data['validity_days'],
                'payment_terms' => $data['payment_terms'],
                'warranty' => $data['warranty'],
                'notes' => $data['notes'],
            ]);

            $quoteId = (int) $this->db->lastInsertId();
            $this->syncItems($quoteId, $items);
            $this->db->commit();

            return $quoteId;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function updateDraft(int $id, int $companyId, array $data, array $items): bool
    {
        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare(
                'UPDATE quotes SET
                    client_id = :client_id,
                    title = :title,
                    status = :status,
                    template_key = :template_key,
                    discount_total = :discount_total,
                    shipping_total = :shipping_total,
                    subtotal_total = :subtotal_total,
                    total = :total,
                    validity_days = :validity_days,
                    payment_terms = :payment_terms,
                    warranty = :warranty,
                    notes = :notes,
                    updated_at = NOW()
                 WHERE id = :id AND company_id = :company_id'
            );
            $statement->execute([
                'id' => $id,
                'company_id' => $companyId,
                'client_id' => $data['client_id'],
                'title' => $data['title'],
                'status' => $data['status'],
                'template_key' => $data['template_key'],
                'discount_total' => $data['discount_total'],
                'shipping_total' => $data['shipping_total'],
                'subtotal_total' => $data['subtotal_total'],
                'total' => $data['total'],
                'validity_days' => $data['validity_days'],
                'payment_terms' => $data['payment_terms'],
                'warranty' => $data['warranty'],
                'notes' => $data['notes'],
            ]);

            $this->syncItems($id, $items);
            $this->db->commit();

            return true;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function delete(int $id, int $companyId): bool
    {
        $statement = $this->db->prepare('DELETE FROM quotes WHERE id = :id AND company_id = :company_id');

        return $statement->execute([
            'id' => $id,
            'company_id' => $companyId,
        ]);
    }

    public function calculateTotals(array $items, float $shippingTotal, float $globalDiscount): array
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $subtotal += (float) $item['total'];
        }

        $total = max(0, $subtotal + $shippingTotal - $globalDiscount);

        return [
            'subtotal' => $subtotal,
            'total' => $total,
        ];
    }

    private function syncItems(int $quoteId, array $items): void
    {
        $delete = $this->db->prepare('DELETE FROM quote_items WHERE quote_id = :quote_id');
        $delete->execute(['quote_id' => $quoteId]);

        $statement = $this->db->prepare(
            'INSERT INTO quote_items
                (quote_id, item_type, item_ref_id, description, quantity, unit_label, unit_price, discount, total, sort_order, created_at, updated_at)
             VALUES
                (:quote_id, :item_type, :item_ref_id, :description, :quantity, :unit_label, :unit_price, :discount, :total, :sort_order, NOW(), NOW())'
        );

        foreach ($items as $index => $item) {
            $statement->execute([
                'quote_id' => $quoteId,
                'item_type' => $item['item_type'],
                'item_ref_id' => $item['item_ref_id'] ?: null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_label' => $item['unit_label'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'],
                'total' => $item['total'],
                'sort_order' => $index + 1,
            ]);
        }
    }
}
