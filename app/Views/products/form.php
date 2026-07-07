<?php

declare(strict_types=1);

$product = $product ?? [];
?>

<section class="section-stack">
    <div class="page-actions">
        <div>
            <span class="eyebrow">Cadastros</span>
            <h2><?= e($title) ?></h2>
        </div>

        <a class="button button-ghost" href="<?= e(url('/products')) ?>">Voltar</a>
    </div>

    <form class="form-stack panel form-panel" method="post" action="<?= e(url($action)) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="form-grid form-grid-2">
            <div class="field-group">
                <label for="name">Nome *</label>
                <input id="name" name="name" type="text" value="<?= e(old('name', $product['name'] ?? '')) ?>" placeholder="Ex.: Tinta acrílica">
            </div>

            <div class="field-group">
                <label for="category_name">Categoria</label>
                <input id="category_name" name="category_name" type="text" value="<?= e(old('category_name', $product['category_name'] ?? '')) ?>" placeholder="Ex.: Pintura">
            </div>

            <div class="field-group">
                <label for="supplier_name">Fornecedor</label>
                <input id="supplier_name" name="supplier_name" type="text" value="<?= e(old('supplier_name', $product['supplier_name'] ?? '')) ?>" placeholder="Fornecedor principal">
            </div>

            <div class="field-group">
                <label for="unit">Unidade</label>
                <input id="unit" name="unit" type="text" value="<?= e(old('unit', $product['unit'] ?? 'un')) ?>" placeholder="un, m², caixa">
            </div>

            <div class="field-group">
                <label for="quantity">Quantidade</label>
                <input id="quantity" name="quantity" type="number" min="0" value="<?= e(old('quantity', $product['quantity'] ?? '0')) ?>" placeholder="1">
            </div>

            <div class="field-group">
                <label for="stock_quantity">Estoque</label>
                <input id="stock_quantity" name="stock_quantity" type="number" min="0" value="<?= e(old('stock_quantity', $product['stock_quantity'] ?? '0')) ?>" placeholder="10">
            </div>

            <div class="field-group">
                <label for="price">Preço</label>
                <input id="price" name="price" type="text" value="<?= e(old('price', isset($product['price']) ? (string) $product['price'] : '')) ?>" placeholder="89,90">
            </div>

            <div class="field-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?= (old('status', $product['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Ativo</option>
                    <option value="inactive" <?= (old('status', $product['status'] ?? 'active') === 'inactive') ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <div class="field-group field-span-2">
                <label for="description">Descrição</label>
                <textarea id="description" name="description" rows="4" placeholder="Descrição do produto..."><?= e(old('description', $product['description'] ?? '')) ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button class="button button-primary" type="submit">Salvar produto</button>
        </div>
    </form>
</section>