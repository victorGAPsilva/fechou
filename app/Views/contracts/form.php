<?php

declare(strict_types=1);

$contract = $contract ?? [];
$statuses = [
    'draft' => 'Rascunho',
    'sent' => 'Enviado',
    'signed' => 'Assinado',
    'active' => 'Ativo',
    'completed' => 'Concluído',
    'canceled' => 'Cancelado',
];
?>

<section class="section-stack">
    <div class="page-actions">
        <div>
            <span class="eyebrow">Formalização</span>
            <h2><?= e($title) ?></h2>
            <p class="muted">Registre escopo, condições e vigência do acordo fechado com o cliente.</p>
        </div>

        <a class="button button-ghost" href="<?= e(url('/contracts')) ?>">Voltar</a>
    </div>

    <form class="form-stack panel form-panel" method="post" action="<?= e(url($action)) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="form-grid form-grid-2">
            <div class="field-group">
                <label for="contract_number">Número</label>
                <input id="contract_number" name="contract_number" type="text" value="<?= e(old('contract_number', $contract['contract_number'] ?? '')) ?>" placeholder="Auto gerado">
            </div>

            <div class="field-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php foreach ($statuses as $statusValue => $statusLabel): ?>
                        <option value="<?= e($statusValue) ?>" <?= (string) old('status', $contract['status'] ?? 'draft') === $statusValue ? 'selected' : '' ?>>
                            <?= e($statusLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group">
                <label for="client_id">Cliente *</label>
                <select id="client_id" name="client_id">
                    <option value="">Selecione...</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= e((string) $client['id']) ?>" <?= (string) old('client_id', $contract['client_id'] ?? '') === (string) $client['id'] ? 'selected' : '' ?>>
                            <?= e($client['name']) ?><?= !empty($client['company_name']) ? ' - ' . e($client['company_name']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group">
                <label for="quote_id">Orçamento vinculado</label>
                <select id="quote_id" name="quote_id">
                    <option value="">Sem vínculo</option>
                    <?php foreach ($quotes as $quote): ?>
                        <option value="<?= e((string) $quote['id']) ?>" <?= (string) old('quote_id', $contract['quote_id'] ?? '') === (string) $quote['id'] ? 'selected' : '' ?>>
                            <?= e($quote['quote_number']) ?> - <?= e($quote['client_name']) ?> - <?= e(money_format_ptbr((float) $quote['total'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-group field-span-2">
                <label for="title">Título *</label>
                <input id="title" name="title" type="text" value="<?= e(old('title', $contract['title'] ?? '')) ?>" placeholder="Ex.: Contrato de prestação de serviços">
            </div>

            <div class="field-group">
                <label for="starts_at">Início</label>
                <input id="starts_at" name="starts_at" type="date" value="<?= e(old('starts_at', $contract['starts_at'] ?? '')) ?>">
            </div>

            <div class="field-group">
                <label for="ends_at">Fim</label>
                <input id="ends_at" name="ends_at" type="date" value="<?= e(old('ends_at', $contract['ends_at'] ?? '')) ?>">
            </div>

            <div class="field-group">
                <label for="value_total">Valor</label>
                <input id="value_total" name="value_total" type="text" value="<?= e(old('value_total', isset($contract['value_total']) ? (string) $contract['value_total'] : '0')) ?>" placeholder="0,00">
            </div>

            <div class="field-group">
                <label for="payment_terms">Condições de pagamento</label>
                <input id="payment_terms" name="payment_terms" type="text" value="<?= e(old('payment_terms', $contract['payment_terms'] ?? '')) ?>" placeholder="Entrada + parcelas">
            </div>

            <div class="field-group field-span-2">
                <label for="scope">Escopo</label>
                <textarea id="scope" name="scope" rows="5" placeholder="Descreva o que será entregue..."><?= e(old('scope', $contract['scope'] ?? '')) ?></textarea>
            </div>

            <div class="field-group field-span-2">
                <label for="terms">Termos</label>
                <textarea id="terms" name="terms" rows="6" placeholder="Condições, responsabilidades, prazos e regras do contrato..."><?= e(old('terms', $contract['terms'] ?? '')) ?></textarea>
            </div>

            <div class="field-group field-span-2">
                <label for="notes">Observações internas</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Notas internas para acompanhamento..."><?= e(old('notes', $contract['notes'] ?? '')) ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button class="button button-primary" type="submit">Salvar contrato</button>
        </div>
    </form>
</section>
