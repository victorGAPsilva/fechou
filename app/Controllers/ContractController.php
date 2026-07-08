<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Quote;

final class ContractController extends Controller
{
    public function index(): void
    {
        $user = $this->requireUser();
        $search = trim((string) ($_GET['q'] ?? ''));
        $contractModel = new Contract();

        $this->render('contracts/index', [
            'title' => 'Contratos',
            'contracts' => $contractModel->paginateByCompany((int) $user['company_id'], $search, 20),
            'totalContracts' => $contractModel->countByCompany((int) $user['company_id'], $search),
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $user = $this->requireUser();
        $companyId = (int) $user['company_id'];
        $contract = $this->prefillFromQuote($companyId);

        $contract['contract_number'] ??= (new Contract())->nextNumber($companyId);
        $contract['status'] ??= 'draft';

        $this->showForm('Novo contrato', '/contracts', $contract, $companyId);
    }

    public function store(): void
    {
        $user = $this->requireUser();
        $companyId = (int) $user['company_id'];

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            $this->failRedirect('/contracts/new', 'A sessão expirou. Tente novamente.', $_POST);
        }

        $data = $this->sanitize($_POST, $companyId);
        $this->validatePayload($data, '/contracts/new', $_POST);

        try {
            (new Contract())->create($data);
        } catch (\Throwable) {
            $this->failRedirect('/contracts/new', 'Não foi possível salvar o contrato. Revise os dados e tente novamente.', $_POST);
        }

        clear_old();
        set_flash('success', 'Contrato criado com sucesso.');
        redirect('/contracts');
    }

    public function edit(string $id): void
    {
        $user = $this->requireUser();
        $companyId = (int) $user['company_id'];
        $contract = (new Contract())->findByIdAndCompany((int) $id, $companyId);

        if (!$contract) {
            set_flash('error', 'Contrato não encontrado.');
            redirect('/contracts');
        }

        $this->showForm('Editar contrato', '/contracts/' . $id, $contract, $companyId);
    }

    public function update(string $id): void
    {
        $user = $this->requireUser();
        $companyId = (int) $user['company_id'];

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            $this->failRedirect('/contracts/' . $id . '/edit', 'A sessão expirou. Tente novamente.', $_POST);
        }

        $contractModel = new Contract();
        $contract = $contractModel->findByIdAndCompany((int) $id, $companyId);

        if (!$contract) {
            set_flash('error', 'Contrato não encontrado.');
            redirect('/contracts');
        }

        $data = $this->sanitize($_POST, $companyId);
        $data['signed_at'] = $contract['signed_at'] ?? null;
        $this->validatePayload($data, '/contracts/' . $id . '/edit', $_POST);

        try {
            $contractModel->update((int) $id, $companyId, $data);
        } catch (\Throwable) {
            $this->failRedirect('/contracts/' . $id . '/edit', 'Não foi possível salvar o contrato. Revise os dados e tente novamente.', $_POST);
        }

        clear_old();
        set_flash('success', 'Contrato atualizado com sucesso.');
        redirect('/contracts');
    }

    public function destroy(string $id): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            set_flash('error', 'A sessão expirou. Tente novamente.');
            redirect('/contracts');
        }

        (new Contract())->delete((int) $id, (int) $user['company_id']);
        set_flash('success', 'Contrato excluído com sucesso.');
        redirect('/contracts');
    }

    private function showForm(string $title, string $action, array $contract, int $companyId): void
    {
        $this->render('contracts/form', [
            'title' => $title,
            'action' => $action,
            'contract' => $contract,
            'clients' => $this->listClients($companyId),
            'quotes' => $this->listQuotes($companyId),
        ]);
    }

    private function prefillFromQuote(int $companyId): array
    {
        $quoteId = (int) ($_GET['quote_id'] ?? 0);

        if ($quoteId <= 0) {
            return [];
        }

        $quote = (new Quote())->findByIdAndCompany($quoteId, $companyId);

        if (!$quote) {
            set_flash('error', 'Orçamento não encontrado para gerar contrato.');

            return [];
        }

        return [
            'client_id' => $quote['client_id'],
            'quote_id' => $quote['id'],
            'title' => 'Contrato - ' . $quote['title'],
            'value_total' => $quote['total'],
            'payment_terms' => $quote['payment_terms'] ?? '',
            'scope' => $quote['notes'] ?? '',
        ];
    }

    private function sanitize(array $input, int $companyId): array
    {
        $contractModel = new Contract();

        return [
            'company_id' => $companyId,
            'client_id' => (int) ($input['client_id'] ?? 0),
            'quote_id' => (int) ($input['quote_id'] ?? 0),
            'contract_number' => trim((string) ($input['contract_number'] ?? '')) ?: $contractModel->nextNumber($companyId),
            'title' => trim((string) ($input['title'] ?? '')),
            'status' => in_array(($input['status'] ?? 'draft'), ['draft', 'sent', 'signed', 'active', 'completed', 'canceled'], true) ? (string) $input['status'] : 'draft',
            'starts_at' => trim((string) ($input['starts_at'] ?? '')),
            'ends_at' => trim((string) ($input['ends_at'] ?? '')),
            'value_total' => trim((string) ($input['value_total'] ?? '0')),
            'payment_terms' => trim((string) ($input['payment_terms'] ?? '')),
            'scope' => trim((string) ($input['scope'] ?? '')),
            'terms' => trim((string) ($input['terms'] ?? '')),
            'notes' => trim((string) ($input['notes'] ?? '')),
            'signed_at' => null,
        ];
    }

    private function validatePayload(array $data, string $redirectPath, array $payload): void
    {
        $companyId = (int) $data['company_id'];

        if ($data['client_id'] <= 0 || !(new Client())->findByIdAndCompany((int) $data['client_id'], $companyId)) {
            $this->failRedirect($redirectPath, 'Selecione um cliente válido.', $payload);
        }

        if ($data['quote_id'] > 0) {
            $quote = (new Quote())->findByIdAndCompany((int) $data['quote_id'], $companyId);

            if (!$quote || (int) $quote['client_id'] !== (int) $data['client_id']) {
                $this->failRedirect($redirectPath, 'Selecione um orçamento válido para o cliente informado.', $payload);
            }
        }

        if ($data['title'] === '') {
            $this->failRedirect($redirectPath, 'Informe o título do contrato.', $payload);
        }

        if (!$this->isNonNegativeNumber((string) $data['value_total'])) {
            $this->failRedirect($redirectPath, 'Informe um valor válido para o contrato.', $payload);
        }

        if (!$this->isValidDate((string) $data['starts_at']) || !$this->isValidDate((string) $data['ends_at'])) {
            $this->failRedirect($redirectPath, 'Informe datas válidas para o contrato.', $payload);
        }

        if ($data['starts_at'] !== '' && $data['ends_at'] !== '' && $data['starts_at'] > $data['ends_at']) {
            $this->failRedirect($redirectPath, 'A data final não pode ser anterior à data inicial.', $payload);
        }
    }

    private function listClients(int $companyId): array
    {
        $statement = Database::connection()->prepare('SELECT id, name, company_name FROM clients WHERE company_id = :company_id ORDER BY name ASC');
        $statement->execute(['company_id' => $companyId]);

        return $statement->fetchAll();
    }

    private function listQuotes(int $companyId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT quotes.id, quotes.client_id, quotes.quote_number, quotes.title, quotes.total, clients.name AS client_name
             FROM quotes
             INNER JOIN clients ON clients.id = quotes.client_id AND clients.company_id = quotes.company_id
             WHERE quotes.company_id = :company_id
             ORDER BY quotes.created_at DESC'
        );
        $statement->execute(['company_id' => $companyId]);

        return $statement->fetchAll();
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

    private function isNonNegativeNumber(string $value): bool
    {
        $normalized = str_replace(',', '.', trim($value));

        return is_numeric($normalized) && (float) $normalized >= 0;
    }

    private function isValidDate(string $date): bool
    {
        if ($date === '') {
            return true;
        }

        $parsed = date_create_from_format('Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
