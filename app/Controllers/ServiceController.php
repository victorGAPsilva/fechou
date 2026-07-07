<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Service;

final class ServiceController extends Controller
{
    public function index(): void
    {
        $user = $this->requireUser();
        $search = trim((string) ($_GET['q'] ?? ''));
        $serviceModel = new Service();

        $this->render('services/index', [
            'title' => 'Serviços',
            'services' => $serviceModel->paginateByCompany((int) $user['company_id'], $search, 20),
            'totalServices' => $serviceModel->countByCompany((int) $user['company_id'], $search),
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $this->showForm('Novo serviço', '/services', []);
    }

    public function store(): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            $this->failRedirect('/services/new', 'A sessão expirou. Tente novamente.', $_POST);
        }

        $data = $this->sanitize($_POST);
        $data['company_id'] = (int) $user['company_id'];
        $data['service_category_id'] = $this->resolveCategoryId((int) $user['company_id'], (string) ($data['category_name'] ?? ''));

        if ($data['name'] === '') {
            $this->failRedirect('/services/new', 'Informe o nome do serviço.', $_POST);
        }

        (new Service())->create($data);

        clear_old();
        set_flash('success', 'Serviço criado com sucesso.');
        redirect('/services');
    }

    public function edit(string $id): void
    {
        $user = $this->requireUser();
        $service = (new Service())->findByIdAndCompany((int) $id, (int) $user['company_id']);

        if (!$service) {
            set_flash('error', 'Serviço não encontrado.');
            redirect('/services');
        }

        $this->showForm('Editar serviço', '/services/' . $id, $service);
    }

    public function update(string $id): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            $this->failRedirect('/services/' . $id . '/edit', 'A sessão expirou. Tente novamente.', $_POST);
        }

        $serviceModel = new Service();
        $service = $serviceModel->findByIdAndCompany((int) $id, (int) $user['company_id']);

        if (!$service) {
            set_flash('error', 'Serviço não encontrado.');
            redirect('/services');
        }

        $data = $this->sanitize($_POST);
        $data['company_id'] = (int) $user['company_id'];
        $data['service_category_id'] = $this->resolveCategoryId((int) $user['company_id'], (string) ($data['category_name'] ?? ''));

        if ($data['name'] === '') {
            $this->failRedirect('/services/' . $id . '/edit', 'Informe o nome do serviço.', $_POST);
        }

        $serviceModel->update((int) $id, (int) $user['company_id'], $data);

        clear_old();
        set_flash('success', 'Serviço atualizado com sucesso.');
        redirect('/services');
    }

    public function destroy(string $id): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            set_flash('error', 'A sessão expirou. Tente novamente.');
            redirect('/services');
        }

        (new Service())->delete((int) $id, (int) $user['company_id']);
        set_flash('success', 'Serviço excluído com sucesso.');
        redirect('/services');
    }

    private function showForm(string $title, string $action, array $service): void
    {
        $this->render('services/form', [
            'title' => $title,
            'action' => $action,
            'service' => $service,
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
            'description' => trim((string) ($input['description'] ?? '')),
            'unit' => trim((string) ($input['unit'] ?? 'un')),
            'average_time_minutes' => trim((string) ($input['average_time_minutes'] ?? '')),
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

        $statement = \App\Core\Database::connection()->prepare('SELECT id FROM service_categories WHERE company_id = :company_id AND name = :name LIMIT 1');
        $statement->execute([
            'company_id' => $companyId,
            'name' => $categoryName,
        ]);

        $id = $statement->fetchColumn();

        if ($id !== false) {
            return (int) $id;
        }

        $insert = \App\Core\Database::connection()->prepare('INSERT INTO service_categories (company_id, name, created_at, updated_at) VALUES (:company_id, :name, NOW(), NOW())');
        $insert->execute([
            'company_id' => $companyId,
            'name' => $categoryName,
        ]);

        return (int) \App\Core\Database::connection()->lastInsertId();
    }
}