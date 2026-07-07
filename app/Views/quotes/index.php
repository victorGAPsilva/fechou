<?php

declare(strict_types=1);
?>

<section class="section-stack">
    <div class="page-actions">
        <div>
            <span class="eyebrow">Propostas</span>
            <h2>Orçamentos</h2>
            <p class="muted">Crie propostas visualmente fortes e acompanhe o status de cada envio.</p>
        </div>

        <a class="button button-primary" href="<?= e(url('/quotes/new')) ?>">Novo orçamento</a>
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
                                <span class="badge <?= ($quote['status'] ?? 'draft') === 'accepted' ? 'badge-positive' : 'badge-muted' ?>"><?= e(mb_strtoupper((string) ($quote['status'] ?? 'draft'))) ?></span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="button button-ghost" href="<?= e(url('/quotes/' . $quote['id'] . '/edit')) ?>">Editar</a>
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
