<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Service;
use App\Services\QuotePdfGenerator;

final class QuoteController extends Controller
{
    public function index(): void
    {
        $user = $this->requireUser();
        $search = trim((string) ($_GET['q'] ?? ''));
        $quoteModel = new Quote();

        $this->render('quotes/index', [
            'title' => 'Orçamentos',
            'quotes' => $quoteModel->paginateByCompany((int) $user['company_id'], $search, 20),
            'totalQuotes' => $quoteModel->countByCompany((int) $user['company_id'], $search),
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $user = $this->requireUser();
        $this->renderBuilder('Novo orçamento', '/quotes', $this->builderData((int) $user['company_id']));
    }

    public function store(): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            $this->failRedirect('/quotes/new', 'A sessão expirou. Tente novamente.', $_POST);
        }

        $payload = $this->sanitizeQuotePayload($_POST, (int) $user['company_id']);

        if ($payload['client_id'] === null) {
            $this->failRedirect('/quotes/new', 'Selecione um cliente.', $_POST);
        }

        if (!(new Client())->findByIdAndCompany((int) $payload['client_id'], (int) $user['company_id'])) {
            $this->failRedirect('/quotes/new', 'Cliente inválido para esta empresa.', $_POST);
        }

        if ($payload['items'] === []) {
            $this->failRedirect('/quotes/new', 'Adicione pelo menos um item válido ao orçamento.', $_POST);
        }

        try {
            (new Quote())->createDraft($payload, $payload['items']);
        } catch (\Throwable) {
            $this->failRedirect('/quotes/new', 'Não foi possível salvar o orçamento. Revise os dados e tente novamente.', $_POST);
        }

        clear_old();
        set_flash('success', 'Orçamento criado com sucesso.');
        redirect('/quotes');
    }

    public function edit(string $id): void
    {
        $user = $this->requireUser();
        $quoteModel = new Quote();
        $quote = $quoteModel->findByIdAndCompany((int) $id, (int) $user['company_id']);

        if (!$quote) {
            set_flash('error', 'Orçamento não encontrado.');
            redirect('/quotes');
        }

        $data = $this->builderData((int) $user['company_id']);
        $data['quote'] = $quote;
        $data['items'] = $quoteModel->getItems((int) $quote['id']);

        $this->renderBuilder('Editar orçamento', '/quotes/' . $id, $data);
    }

    public function update(string $id): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            $this->failRedirect('/quotes/' . $id . '/edit', 'A sessão expirou. Tente novamente.', $_POST);
        }

        $quoteModel = new Quote();
        $quote = $quoteModel->findByIdAndCompany((int) $id, (int) $user['company_id']);

        if (!$quote) {
            set_flash('error', 'Orçamento não encontrado.');
            redirect('/quotes');
        }

        $payload = $this->sanitizeQuotePayload($_POST, (int) $user['company_id']);

        if ($payload['client_id'] === null) {
            $this->failRedirect('/quotes/' . $id . '/edit', 'Selecione um cliente.', $_POST);
        }

        if (!(new Client())->findByIdAndCompany((int) $payload['client_id'], (int) $user['company_id'])) {
            $this->failRedirect('/quotes/' . $id . '/edit', 'Cliente inválido para esta empresa.', $_POST);
        }

        if ($payload['items'] === []) {
            $this->failRedirect('/quotes/' . $id . '/edit', 'Adicione pelo menos um item válido ao orçamento.', $_POST);
        }

        try {
            $quoteModel->updateDraft((int) $id, (int) $user['company_id'], $payload, $payload['items']);
        } catch (\Throwable) {
            $this->failRedirect('/quotes/' . $id . '/edit', 'Não foi possível salvar o orçamento. Revise os dados e tente novamente.', $_POST);
        }

        clear_old();
        set_flash('success', 'Orçamento atualizado com sucesso.');
        redirect('/quotes');
    }

    public function destroy(string $id): void
    {
        $user = $this->requireUser();

        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            set_flash('error', 'A sessão expirou. Tente novamente.');
            redirect('/quotes');
        }

        (new Quote())->delete((int) $id, (int) $user['company_id']);
        set_flash('success', 'Orçamento excluído com sucesso.');
        redirect('/quotes');
    }

    public function downloadPdf(string $id): void
    {
        $user = $this->requireUser();
        $quoteModel = new Quote();
        $quote = $quoteModel->findByIdAndCompany((int) $id, (int) $user['company_id']);

        if (!$quote) {
            set_flash('error', 'Orçamento não encontrado.');
            redirect('/quotes');
        }

        $items = $quoteModel->getItems((int) $quote['id']);
        $pdf = (new QuotePdfGenerator())->render($quote, $items);
        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $quote['quote_number']) ?: 'orcamento';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    private function renderBuilder(string $title, string $action, array $data): void
    {
        $this->render('quotes/form', [
            'title' => $title,
            'action' => $action,
            'clients' => $data['clients'],
            'services' => $data['services'],
            'products' => $data['products'],
            'quote' => $data['quote'] ?? null,
            'items' => $data['items'] ?? [],
            'templates' => $data['templates'],
            'totals' => $data['totals'],
        ]);
    }

    private function builderData(int $companyId): array
    {
        return [
            'clients' => $this->listClients($companyId),
            'services' => (new Service())->listActiveByCompany($companyId),
            'products' => (new Product())->listActiveByCompany($companyId),
            'templates' => [
                ['key' => 'modern', 'label' => 'Moderno'],
                ['key' => 'minimal', 'label' => 'Minimalista'],
                ['key' => 'executive', 'label' => 'Executivo'],
                ['key' => 'premium', 'label' => 'Premium'],
            ],
            'quote' => null,
            'items' => [],
            'totals' => [
                'subtotal' => 0.0,
                'discount_total' => 0.0,
                'shipping_total' => 0.0,
                'total' => 0.0,
            ],
        ];
    }

    private function listClients(int $companyId): array
    {
        $statement = Database::connection()->prepare('SELECT id, name, company_name FROM clients WHERE company_id = :company_id ORDER BY name ASC');
        $statement->execute(['company_id' => $companyId]);

        return $statement->fetchAll();
    }

    private function sanitizeQuotePayload(array $input, int $companyId): array
    {
        $items = [];
        $rawItems = $input['items'] ?? [];
        $clientId = trim((string) ($input['client_id'] ?? ''));

        foreach ($rawItems as $row) {
            $description = trim((string) ($row['description'] ?? ''));
            $quantity = (float) str_replace(',', '.', (string) ($row['quantity'] ?? '0'));
            $unitPrice = (float) str_replace(',', '.', (string) ($row['unit_price'] ?? '0'));
            $discount = (float) str_replace(',', '.', (string) ($row['discount'] ?? '0'));
            $total = max(0, ($quantity * $unitPrice) - $discount);

            if ($description === '' || $quantity <= 0 || $unitPrice <= 0) {
                continue;
            }

            $items[] = [
                'item_type' => in_array(($row['item_type'] ?? 'custom'), ['service', 'product', 'custom'], true) ? (string) $row['item_type'] : 'custom',
                'item_ref_id' => (int) ($row['item_ref_id'] ?? 0),
                'description' => $description,
                'quantity' => $quantity,
                'unit_label' => trim((string) ($row['unit_label'] ?? 'un')) ?: 'un',
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'total' => $total,
            ];
        }

        $shippingTotal = max(0, (float) str_replace(',', '.', (string) ($input['shipping_total'] ?? 0)));
        $globalDiscount = max(0, (float) str_replace(',', '.', (string) ($input['global_discount'] ?? 0)));

        $quoteModel = new Quote();
        $calculated = $quoteModel->calculateTotals($items, $shippingTotal, $globalDiscount);

        return [
            'company_id' => $companyId,
            'client_id' => $clientId !== '' ? (int) $clientId : null,
            'quote_number' => trim((string) ($input['quote_number'] ?? '')) ?: $quoteModel->nextNumber($companyId),
            'title' => trim((string) ($input['title'] ?? '')) ?: 'Novo orçamento',
            'status' => in_array(($input['status'] ?? 'draft'), ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'canceled'], true) ? (string) $input['status'] : 'draft',
            'template_key' => in_array(($input['template_key'] ?? 'modern'), ['modern', 'minimal', 'executive', 'premium'], true) ? (string) $input['template_key'] : 'modern',
            'discount_total' => $globalDiscount,
            'shipping_total' => $shippingTotal,
            'subtotal_total' => $calculated['subtotal'],
            'total' => $calculated['total'],
            'validity_days' => max(1, (int) ($input['validity_days'] ?? 7)),
            'payment_terms' => trim((string) ($input['payment_terms'] ?? '')),
            'warranty' => trim((string) ($input['warranty'] ?? '')),
            'notes' => trim((string) ($input['notes'] ?? '')),
            'items' => $items,
        ];
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
}
