<?php

declare(strict_types=1);
?>

<section class="section-stack">
    <div class="page-actions">
        <div>
            <span class="eyebrow">Cadastros</span>
            <h2>Produtos</h2>
            <p class="muted">Controle itens, estoque e fornecedores com visual limpo e rápido.</p>
        </div>

        <a class="button button-primary" href="<?= e(url('/products/new')) ?>">Novo produto</a>
    </div>

    <form class="search-bar" method="get" action="<?= e(url('/products')) ?>">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Pesquisar por nome, categoria ou fornecedor">
        <button class="button button-ghost" type="submit">Buscar</button>
    </form>

    <div class="panel">
        <div class="panel-head">
            <div>
                <span class="eyebrow">Total</span>
                <h2><?= e((string) $totalProducts) ?> produto(s)</h2>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Fornecedor</th>
                        <th>Quantidade</th>
                        <th>Estoque</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <strong><?= e($product['name']) ?></strong><br>
                                <small class="muted"><?= e($product['description'] ?? '-') ?></small>
                            </td>
                            <td><?= e($product['category_name'] ?? '-') ?></td>
                            <td><?= e($product['supplier_name'] ?? '-') ?></td>
                            <td><?= e((string) $product['quantity']) ?></td>
                            <td><?= e((string) $product['stock_quantity']) ?></td>
                            <td><?= e(money_format_ptbr((float) $product['price'])) ?></td>
                            <td>
                                <span class="badge <?= ($product['status'] ?? 'active') === 'active' ? 'badge-positive' : 'badge-muted' ?>">
                                    <?= e(($product['status'] ?? 'active') === 'active' ? 'Ativo' : 'Inativo') ?>
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="button button-ghost" href="<?= e(url('/products/' . $product['id'] . '/edit')) ?>">Editar</a>
                                    <form method="post" action="<?= e(url('/products/' . $product['id'] . '/delete')) ?>" class="inline-form" data-confirm-delete="Excluir este produto?">
                                        <?= csrf_field() ?>
                                        <button class="button button-danger" type="submit">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="8"><div class="empty-inline">Nenhum produto encontrado.</div></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>