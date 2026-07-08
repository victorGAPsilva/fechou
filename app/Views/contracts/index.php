<?php

declare(strict_types=1);

$statusLabels = [
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
            <h2>Contratos</h2>
            <p class="muted">Transforme propostas aprovadas em contratos com valor, vigência e termos organizados.</p>
        </div>

        <a class="button button-primary" href="<?= e(url('/contracts/new')) ?>">Novo contrato</a>
    </div>

    <form class="search-bar" method="get" action="<?= e(url('/contracts')) ?>">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Pesquisar por número, título, cliente ou orçamento">
        <button class="button button-ghost" type="submit">Buscar</button>
    </form>

    <div class="panel">
        <div class="panel-head">
            <div>
                <span class="eyebrow">Total</span>
                <h2><?= e((string) $totalContracts) ?> contrato(s)</h2>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Título</th>
                        <th>Cliente</th>
                        <th>Orçamento</th>
                        <th>Vigência</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contracts as $contract): ?>
                        <tr>
                            <td><?= e($contract['contract_number']) ?></td>
                            <td>
                                <strong><?= e($contract['title']) ?></strong><br>
                                <small class="muted"><?= e($contract['payment_terms'] ?? '-') ?></small>
                            </td>
                            <td><?= e($contract['client_name'] ?? '-') ?></td>
                            <td><?= e($contract['quote_number'] ?? '-') ?></td>
                            <td>
                                <?= e($contract['starts_at'] ? date('d/m/Y', strtotime((string) $contract['starts_at'])) : '-') ?>
                                -
                                <?= e($contract['ends_at'] ? date('d/m/Y', strtotime((string) $contract['ends_at'])) : '-') ?>
                            </td>
                            <td><?= e(money_format_ptbr((float) $contract['value_total'])) ?></td>
                            <td>
                                <span class="badge <?= in_array(($contract['status'] ?? 'draft'), ['signed', 'active', 'completed'], true) ? 'badge-positive' : 'badge-muted' ?>">
                                    <?= e($statusLabels[$contract['status'] ?? 'draft'] ?? (string) ($contract['status'] ?? 'draft')) ?>
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="button button-ghost" href="<?= e(url('/contracts/' . $contract['id'] . '/edit')) ?>">Editar</a>
                                    <form method="post" action="<?= e(url('/contracts/' . $contract['id'] . '/delete')) ?>" class="inline-form" data-confirm-delete="Excluir este contrato?">
                                        <?= csrf_field() ?>
                                        <button class="button button-danger" type="submit">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($contracts)): ?>
                        <tr>
                            <td colspan="8"><div class="empty-inline">Nenhum contrato criado ainda.</div></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
