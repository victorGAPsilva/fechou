<?php

declare(strict_types=1);
?>

<section class="section-stack">
    <div class="page-actions">
        <div>
            <span class="eyebrow">Cadastros</span>
            <h2>Serviços</h2>
            <p class="muted">Cadastre serviços padrão e reaproveite em orçamentos com cálculo rápido.</p>
        </div>

        <a class="button button-primary" href="<?= e(url('/services/new')) ?>">Novo serviço</a>
    </div>

    <form class="search-bar" method="get" action="<?= e(url('/services')) ?>">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Pesquisar por nome, categoria ou descrição">
        <button class="button button-ghost" type="submit">Buscar</button>
    </form>

    <div class="panel">
        <div class="panel-head">
            <div>
                <span class="eyebrow">Total</span>
                <h2><?= e((string) $totalServices) ?> serviço(s)</h2>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Unidade</th>
                        <th>Tempo médio</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td>
                                <strong><?= e($service['name']) ?></strong><br>
                                <small class="muted"><?= e($service['description'] ?? '-') ?></small>
                            </td>
                            <td><?= e($service['category_name'] ?? '-') ?></td>
                            <td><?= e($service['unit'] ?? '-') ?></td>
                            <td><?= e($service['average_time_minutes'] ? $service['average_time_minutes'] . ' min' : '-') ?></td>
                            <td><?= e(money_format_ptbr((float) $service['price'])) ?></td>
                            <td>
                                <span class="badge <?= ($service['status'] ?? 'active') === 'active' ? 'badge-positive' : 'badge-muted' ?>">
                                    <?= e(($service['status'] ?? 'active') === 'active' ? 'Ativo' : 'Inativo') ?>
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="button button-ghost" href="<?= e(url('/services/' . $service['id'] . '/edit')) ?>">Editar</a>
                                    <form method="post" action="<?= e(url('/services/' . $service['id'] . '/delete')) ?>" class="inline-form" data-confirm-delete="Excluir este serviço?">
                                        <?= csrf_field() ?>
                                        <button class="button button-danger" type="submit">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="7"><div class="empty-inline">Nenhum serviço encontrado.</div></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>