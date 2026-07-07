<?php

declare(strict_types=1);

namespace App\Models;

final class Product extends BaseModel
{
    public function listActiveByCompany(int $companyId): array
    {
        $statement = $this->db->prepare(
            'SELECT products.id, products.name, products.price, products.unit, product_categories.name AS category_name
             FROM products
             LEFT JOIN product_categories ON product_categories.id = products.product_category_id
             WHERE products.company_id = :company_id AND products.status = "active"
             ORDER BY products.name ASC'
        );
        $statement->execute(['company_id' => $companyId]);

        return $statement->fetchAll();
    }

    public function paginateByCompany(int $companyId, string $search = '', int $limit = 20): array
    {
        $sql = '
            SELECT products.*, product_categories.name AS category_name
            FROM products
            LEFT JOIN product_categories ON product_categories.id = products.product_category_id
            WHERE products.company_id = :company_id';
        $params = ['company_id' => $companyId];

        if ($search !== '') {
            $sql .= ' AND (products.name LIKE :search OR products.description LIKE :search OR products.supplier_name LIKE :search OR product_categories.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY products.created_at DESC LIMIT ' . max(1, $limit);

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function countByCompany(int $companyId, string $search = ''): int
    {
        $sql = '
            SELECT COUNT(*)
            FROM products
            LEFT JOIN product_categories ON product_categories.id = products.product_category_id
            WHERE products.company_id = :company_id';
        $params = ['company_id' => $companyId];

        if ($search !== '') {
            $sql .= ' AND (products.name LIKE :search OR products.description LIKE :search OR products.supplier_name LIKE :search OR product_categories.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function findByIdAndCompany(int $id, int $companyId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT products.*, product_categories.name AS category_name
             FROM products
             LEFT JOIN product_categories ON product_categories.id = products.product_category_id
             WHERE products.id = :id AND products.company_id = :company_id
             LIMIT 1'
        );
        $statement->execute([
            'id' => $id,
            'company_id' => $companyId,
        ]);

        $product = $statement->fetch();

        return $product ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO products
                (company_id, product_category_id, supplier_name, name, description, unit, quantity, stock_quantity, price, status, created_at, updated_at)
             VALUES
                (:company_id, :product_category_id, :supplier_name, :name, :description, :unit, :quantity, :stock_quantity, :price, :status, NOW(), NOW())'
        );

        $statement->execute($this->mapPayload($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $companyId, array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE products SET
                product_category_id = :product_category_id,
                supplier_name = :supplier_name,
                name = :name,
                description = :description,
                unit = :unit,
                quantity = :quantity,
                stock_quantity = :stock_quantity,
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
        $statement = $this->db->prepare('DELETE FROM products WHERE id = :id AND company_id = :company_id');

        return $statement->execute([
            'id' => $id,
            'company_id' => $companyId,
        ]);
    }

    private function mapPayload(array $data): array
    {
        return [
            'company_id' => $data['company_id'] ?? null,
            'product_category_id' => $data['product_category_id'] ?? null,
            'supplier_name' => trim((string) ($data['supplier_name'] ?? '')) ?: null,
            'name' => trim((string) ($data['name'] ?? '')),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'unit' => trim((string) ($data['unit'] ?? 'un')),
            'quantity' => (int) ($data['quantity'] ?? 0),
            'stock_quantity' => (int) ($data['stock_quantity'] ?? 0),
            'price' => (float) str_replace(',', '.', (string) ($data['price'] ?? 0)),
            'status' => in_array(($data['status'] ?? 'active'), ['active', 'inactive'], true) ? (string) $data['status'] : 'active',
        ];
    }
}