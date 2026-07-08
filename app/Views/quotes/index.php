<?php

declare(strict_types=1);

$pdfTemplates = [
    'modern' => 'Moderno',
    'minimal' => 'Minimalista',
    'executive' => 'Executivo',
    'premium' => 'Premium',
];

$statusLabels = [
    'draft' => 'Rascunho',
    'sent' => 'Enviado',
    'viewed' => 'Visualizado',
    'accepted' => 'Aceito',
    'rejected' => 'Recusado',
    'canceled' => 'Cancelado',
];
$exportQuery = $search !== '' ? '?q=' . urlencode((string) $search) : '';
?>

<section class="section-stack">
    <div class="page-actions">
        <div>
            <span class="eyebrow">Propostas</span>
            <h2>Orçamentos</h2>
            <p class="muted">Crie propostas visualmente fortes e acompanhe o status de cada envio.</p>
        </div>

        <div class="row-actions">
            <a class="button button-ghost" href="<?= e(url('/quotes/export' . $exportQuery)) ?>" data-no-loading="true">Exportar CSV</a>
            <a class="button button-primary" href="<?= e(url('/quotes/new')) ?>">Novo orçamento</a>
        </div>
    </div>

    <form class="search-bar" method="get" action="<?= e(url('/quotes')) ?>">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Pesquisar por número, título ou cliente">
        <button class="button button-ghost" type="submit">Buscar</button>
    </form>

    <div class="panel">
        <div class="panel-head">
            <div>
                <span class="eyebrow">Total</span>
                <h2><?= e((string) $totalQuotes) ?> orçamento(s)</h2>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Título</th>
                        <th>Cliente</th>
                        <th>Válido por</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quotes as $quote): ?>
                        <tr>
                            <td><?= e($quote['quote_number']) ?></td>
                            <td>
                                <strong><?= e($quote['title']) ?></strong><br>
                                <small class="muted">Template: <?= e($quote['template_key']) ?></small>
                            </td>
                            <td><?= e($quote['client_name'] ?? '-') ?></td>
                            <td><?= e((string) ($quote['validity_days'] ?? 0)) ?> dias</td>
                            <td><?= e(money_format_ptbr((float) $quote['total'])) ?></td>
                            <td>
                                <form method="post" action="<?= e(url('/quotes/' . $quote['id'] . '/status')) ?>" class="status-select-form" data-auto-submit>
                                    <?= csrf_field() ?>
                                    <select name="status" aria-label="Status do orçamento">
                                        <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
                                            <option value="<?= e($statusKey) ?>" <?= ($quote['status'] ?? 'draft') === $statusKey ? 'selected' : '' ?>>
                                                <?= e($statusLabel) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="button button-ghost" href="<?= e(url('/quotes/' . $quote['id'] . '/edit')) ?>">Editar</a>
                                    <form method="get" action="<?= e(url('/quotes/' . $quote['id'] . '/pdf')) ?>" class="pdf-select-form" data-no-loading="true">
                                        <select name="template" aria-label="Modelo do PDF">
                                            <?php foreach ($pdfTemplates as $templateKey => $templateLabel): ?>
                                                <option value="<?= e($templateKey) ?>" <?= ($quote['template_key'] ?? 'modern') === $templateKey ? 'selected' : '' ?>>
                                                    <?= e($templateLabel) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="button button-ghost" type="submit">Baixar PDF</button>
                                    </form>
                                    <a class="button button-ghost" href="<?= e(url('/contracts/new?quote_id=' . $quote['id'])) ?>">Contrato</a>
                                    <form method="post" action="<?= e(url('/quotes/' . $quote['id'] . '/delete')) ?>" class="inline-form" data-confirm-delete="Excluir este orçamento?">
                                        <?= csrf_field() ?>
                                        <button class="button button-danger" type="submit">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($quotes)): ?>
                        <tr>
                            <td colspan="7"><div class="empty-inline">Nenhum orçamento criado ainda.</div></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
