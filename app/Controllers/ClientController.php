<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;

final class ClientController extends Controller
{
    public function index(): void
    {
        $user = $this->requireUser();
        $search = trim((string) ($_GET['q'] ?? ''));
        $clientModel = new Client();

        $this->render('clients/index', [
            'title' => 'Clientes',
            'clients' => $clientModel->paginateByCompany((int) $user['company_id'], $search, 20),
            'totalClients' => $clientModel->countByCompany((int) $user['company_id'], $search),
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $this->showForm('Criar cliente', '/clients', []);
    }

    public function export(): void
    {
        $user = $this->requireUser();
        $search = trim((string) ($_GET['q'] ?? ''));
        $clients = (new Client())->paginateByCompany((int) $user['company_id'], $search, 10000);

        $rows = array_map(static fn (array $client): array => [
            $client['name'] ?? '',
            $client['company_name'] ?? '',
            $client['email'] ?? '',
            $client['phone'] ?? '',
            $client['whatsapp'] ?? '',
            $client['document'] ?? '',
            $client['city'] ?? '',
            $client['state'] ?? '',
            $client['status'] ?? '',
        ], $clients);

        $this->downloadCsv('clientes.csv', ['Nome', 'Empresa', 'E-mail', 'Telefone', 'WhatsApp', 'Documento', 'Cidade', 'Estado', 'Status'], $rows);
    }

    public function store(): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            $this->failRedirect('/clients/new', 'A sessão expirou. Tente novamente.', $_POST);
        }

        $data = $this->sanitize($_POST);

        if ($data['name'] === '') {
            $this->failRedirect('/clients/new', 'Informe o nome do cliente.', $_POST);
        }

        $data['company_id'] = (int) $user['company_id'];

        (new Client())->create($data);

        clear_old();
        set_flash('success', 'Cliente criado com sucesso.');
        redirect('/clients');
    }

    public function edit(string $id): void
    {
        $user = $this->requireUser();
        $client = (new Client())->findByIdAndCompany((int) $id, (int) $user['company_id']);

        if (!$client) {
            set_flash('error', 'Cliente não encontrado.');
            redirect('/clients');
        }

        $this->showForm('Editar cliente', '/clients/' . $id, $client);
    }

    public function update(string $id): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            $this->failRedirect('/clients/' . $id . '/edit', 'A sessão expirou. Tente novamente.', $_POST);
        }

        $clientModel = new Client();
        $client = $clientModel->findByIdAndCompany((int) $id, (int) $user['company_id']);

        if (!$client) {
            set_flash('error', 'Cliente não encontrado.');
            redirect('/clients');
        }

        $data = $this->sanitize($_POST);

        if ($data['name'] === '') {
            $this->failRedirect('/clients/' . $id . '/edit', 'Informe o nome do cliente.', $_POST);
        }

        $data['company_id'] = (int) $user['company_id'];
        $clientModel->update((int) $id, (int) $user['company_id'], $data);

        clear_old();
        set_flash('success', 'Cliente atualizado com sucesso.');
        redirect('/clients');
    }

    public function destroy(string $id): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            set_flash('error', 'A sessão expirou. Tente novamente.');
            redirect('/clients');
        }

        (new Client())->delete((int) $id, (int) $user['company_id']);
        set_flash('success', 'Cliente excluído com sucesso.');
        redirect('/clients');
    }

    private function showForm(string $title, string $action, array $client): void
    {
        $this->render('clients/form', [
            'title' => $title,
            'action' => $action,
            'client' => $client,
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
            'company_name' => trim((string) ($input['company_name'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'phone' => trim((string) ($input['phone'] ?? '')),
            'whatsapp' => trim((string) ($input['whatsapp'] ?? '')),
            'document' => trim((string) ($input['document'] ?? '')),
            'zip_code' => trim((string) ($input['zip_code'] ?? '')),
            'street' => trim((string) ($input['street'] ?? '')),
            'number' => trim((string) ($input['number'] ?? '')),
            'complement' => trim((string) ($input['complement'] ?? '')),
            'neighborhood' => trim((string) ($input['neighborhood'] ?? '')),
            'city' => trim((string) ($input['city'] ?? '')),
            'state' => trim((string) ($input['state'] ?? '')),
            'notes' => trim((string) ($input['notes'] ?? '')),
            'status' => in_array(($input['status'] ?? 'active'), ['active', 'inactive'], true) ? (string) $input['status'] : 'active',
        ];
    }
}
