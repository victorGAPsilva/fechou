<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

final class ProductController extends Controller
{
    public function index(): void
    {
        $user = $this->requireUser();
        $search = trim((string) ($_GET['q'] ?? ''));
        $productModel = new Product();

        $this->render('products/index', [
            'title' => 'Produtos',
            'products' => $productModel->paginateByCompany((int) $user['company_id'], $search, 20),
            'totalProducts' => $productModel->countByCompany((int) $user['company_id'], $search),
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $this->showForm('Novo produto', '/products', []);
    }

    public function export(): void
    {
        $user = $this->requireUser();
        $search = trim((string) ($_GET['q'] ?? ''));
        $products = (new Product())->paginateByCompany((int) $user['company_id'], $search, 10000);

        $rows = array_map(static fn (array $product): array => [
            $product['name'] ?? '',
            $product['category_name'] ?? '',
            $product['supplier_name'] ?? '',
            $product['unit'] ?? '',
            $product['quantity'] ?? '',
            $product['stock_quantity'] ?? '',
            money_format_ptbr((float) ($product['price'] ?? 0)),
            $product['status'] ?? '',
            $product['description'] ?? '',
        ], $products);

        $this->downloadCsv('produtos.csv', ['Nome', 'Categoria', 'Fornecedor', 'Unidade', 'Quantidade', 'Estoque', 'Preco', 'Status', 'Descricao'], $rows);
    }

    public function store(): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            $this->failRedirect('/products/new', 'A sessão expirou. Tente novamente.', $_POST);
        }

        $data = $this->sanitize($_POST);
        $data['company_id'] = (int) $user['company_id'];

        if ($data['name'] === '') {
            $this->failRedirect('/products/new', 'Informe o nome do produto.', $_POST);
        }

        if (!$this->isNonNegativeNumber($data['price'])) {
            $this->failRedirect('/products/new', 'Informe um preço válido para o produto.', $_POST);
        }

        if (!$this->isNonNegativeInteger($data['quantity']) || !$this->isNonNegativeInteger($data['stock_quantity'])) {
            $this->failRedirect('/products/new', 'Informe quantidades válidas para o produto.', $_POST);
        }

        $data['product_category_id'] = $this->resolveCategoryId((int) $user['company_id'], (string) ($data['category_name'] ?? ''));

        (new Product())->create($data);

        clear_old();
        set_flash('success', 'Produto criado com sucesso.');
        redirect('/products');
    }

    public function edit(string $id): void
    {
        $user = $this->requireUser();
        $product = (new Product())->findByIdAndCompany((int) $id, (int) $user['company_id']);

        if (!$product) {
            set_flash('error', 'Produto não encontrado.');
            redirect('/products');
        }

        $this->showForm('Editar produto', '/products/' . $id, $product);
    }

    public function update(string $id): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            $this->failRedirect('/products/' . $id . '/edit', 'A sessão expirou. Tente novamente.', $_POST);
        }

        $productModel = new Product();
        $product = $productModel->findByIdAndCompany((int) $id, (int) $user['company_id']);

        if (!$product) {
            set_flash('error', 'Produto não encontrado.');
            redirect('/products');
        }

        $data = $this->sanitize($_POST);
        $data['company_id'] = (int) $user['company_id'];

        if ($data['name'] === '') {
            $this->failRedirect('/products/' . $id . '/edit', 'Informe o nome do produto.', $_POST);
        }

        if (!$this->isNonNegativeNumber($data['price'])) {
            $this->failRedirect('/products/' . $id . '/edit', 'Informe um preço válido para o produto.', $_POST);
        }

        if (!$this->isNonNegativeInteger($data['quantity']) || !$this->isNonNegativeInteger($data['stock_quantity'])) {
            $this->failRedirect('/products/' . $id . '/edit', 'Informe quantidades válidas para o produto.', $_POST);
        }

        $data['product_category_id'] = $this->resolveCategoryId((int) $user['company_id'], (string) ($data['category_name'] ?? ''));

        $productModel->update((int) $id, (int) $user['company_id'], $data);

        clear_old();
        set_flash('success', 'Produto atualizado com sucesso.');
        redirect('/products');
    }

    public function destroy(string $id): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            set_flash('error', 'A sessão expirou. Tente novamente.');
            redirect('/products');
        }

        (new Product())->delete((int) $id, (int) $user['company_id']);
        set_flash('success', 'Produto excluído com sucesso.');
        redirect('/products');
    }

    private function showForm(string $title, string $action, array $product): void
    {
        $this->render('products/form', [
            'title' => $title,
            'action' => $action,
            'product' => $product,
        ]);
    }

    private function requireUser(): array
    {
        $user = auth_user();

        if (!$user) {
            redirect('/login');
        }

        return $user;
    }

    private function failRedirect(string $path, string $message, array $payload): void
    {
        set_flash('error', $message);
        with_old($payload);
        redirect($path);
    }

    private function sanitize(array $input): array
    {
        return [
            'name' => trim((string) ($input['name'] ?? '')),
            'category_name' => trim((string) ($input['category_name'] ?? '')),
            'supplier_name' => trim((string) ($input['supplier_name'] ?? '')),
            'description' => trim((string) ($input['description'] ?? '')),
            'unit' => trim((string) ($input['unit'] ?? 'un')),
            'quantity' => trim((string) ($input['quantity'] ?? '0')),
            'stock_quantity' => trim((string) ($input['stock_quantity'] ?? '0')),
            'price' => trim((string) ($input['price'] ?? '0')),
            'status' => in_array(($input['status'] ?? 'active'), ['active', 'inactive'], true) ? (string) $input['status'] : 'active',
        ];
    }

    private function resolveCategoryId(int $companyId, string $categoryName): ?int
    {
        $categoryName = trim($categoryName);

        if ($categoryName === '') {
            return null;
        }

        $statement = \App\Core\Database::connection()->prepare('SELECT id FROM product_categories WHERE company_id = :company_id AND name = :name LIMIT 1');
        $statement->execute([
            'company_id' => $companyId,
            'name' => $categoryName,
        ]);

        $id = $statement->fetchColumn();

        if ($id !== false) {
            return (int) $id;
        }

        $insert = \App\Core\Database::connection()->prepare('INSERT INTO product_categories (company_id, name, created_at, updated_at) VALUES (:company_id, :name, NOW(), NOW())');
        $insert->execute([
            'company_id' => $companyId,
            'name' => $categoryName,
        ]);

        return (int) \App\Core\Database::connection()->lastInsertId();
    }

    private function isNonNegativeNumber(string $value): bool
    {
        $normalized = str_replace(',', '.', trim($value));

        return is_numeric($normalized) && (float) $normalized >= 0;
    }

    private function isNonNegativeInteger(string $value): bool
    {
        return ctype_digit(trim($value));
    }
}
