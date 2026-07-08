<?php

declare(strict_types=1);

$activeClients = 0;
$inactiveClients = 0;
$exportQuery = $search !== '' ? '?q=' . urlencode((string) $search) : '';

foreach ($clients as $client) {
    if (($client['status'] ?? 'active') === 'active') {
        $activeClients++;
        continue;
    }

    $inactiveClients++;
}
?>

<section class="section-stack clients-page">
    <div class="panel clients-hero">
        <div class="page-actions clients-hero-top">
            <div>
                <span class="eyebrow">Cadastros</span>
                <h2>Clientes</h2>
                <p class="muted">Organize seus contatos com isolamento por empresa, busca rápida e visual de operação premium.</p>
            </div>

            <div class="row-actions">
                <a class="button button-ghost" href="<?= e(url('/clients/export' . $exportQuery)) ?>" data-no-loading="true">Exportar CSV</a>
                <a class="button button-primary" href="<?= e(url('/clients/new')) ?>">Novo cliente</a>
            </div>
        </div>

        <div class="stats-grid clients-stats-grid">
            <article class="stat-card">
                <span class="eyebrow">Total</span>
                <strong><?= e((string) $totalClients) ?></strong>
                <small>clientes cadastrados</small>
            </article>

            <article class="stat-card">
                <span class="eyebrow">Ativos</span>
                <strong><?= e((string) $activeClients) ?></strong>
                <small>em operação</small>
            </article>

            <article class="stat-card">
                <span class="eyebrow">Inativos</span>
                <strong><?= e((string) $inactiveClients) ?></strong>
                <small>pausados ou arquivados</small>
            </article>
        </div>
    </div>

    <form class="search-bar" method="get" action="<?= e(url('/clients')) ?>">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Pesquisar por nome, telefone, e-mail ou documento">
        <button class="button button-ghost" type="submit">Buscar</button>
    </form>

    <div class="panel">
        <div class="panel-head">
            <div>
                <span class="eyebrow">Lista</span>
                <h2>Carteira de clientes</h2>
            </div>

            <span class="badge badge-muted"><?= e((string) $totalClients) ?> registros</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Contato</th>
                        <th>Documento</th>
                        <th>Cidade</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td>
                                <div class="table-person">
                                    <div class="table-avatar">
                                        <?= e(mb_strtoupper(mb_substr((string) $client['name'], 0, 1))) ?>
                                    </div>
                                    <div>
                                <strong><?= e($client['name']) ?></strong><br>
                                <span class="muted"><?= e($client['company_name'] ?? '-') ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?= e($client['phone'] ?? '-') ?></div>
                                <small class="muted"><?= e($client['email'] ?? '-') ?></small>
                            </td>
                            <td><?= e($client['document'] ?? '-') ?></td>
                            <td><?= e(trim(($client['city'] ?? '-') . ' / ' . ($client['state'] ?? '-'))) ?></td>
                            <td>
                                <span class="badge <?= ($client['status'] ?? 'active') === 'active' ? 'badge-positive' : 'badge-muted' ?>">
                                    <?= e(($client['status'] ?? 'active') === 'active' ? 'Ativo' : 'Inativo') ?>
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="button button-ghost" href="<?= e(url('/clients/' . $client['id'] . '/edit')) ?>">Editar</a>
                                    <form method="post" action="<?= e(url('/clients/' . $client['id'] . '/delete')) ?>" class="inline-form" data-confirm-delete="Excluir este cliente?">
                                        <?= csrf_field() ?>
                                        <button class="button button-danger" type="submit">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($clients)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-inline empty-inline-soft">
                                    <strong>Nenhum cliente encontrado.</strong>
                                    <span class="muted">Crie o primeiro cadastro para começar a organizar sua carteira.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
