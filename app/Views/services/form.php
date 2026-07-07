<?php

declare(strict_types=1);

$service = $service ?? [];
?>

<section class="section-stack">
    <div class="page-actions">
        <div>
            <span class="eyebrow">Cadastros</span>
            <h2><?= e($title) ?></h2>
        </div>

        <a class="button button-ghost" href="<?= e(url('/services')) ?>">Voltar</a>
    </div>

    <form class="form-stack panel form-panel" method="post" action="<?= e(url($action)) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="form-grid form-grid-2">
            <div class="field-group">
                <label for="name">Nome *</label>
                <input id="name" name="name" type="text" value="<?= e(old('name', $service['name'] ?? '')) ?>" placeholder="Ex.: Instalação elétrica">
            </div>

            <div class="field-group">
                <label for="category_name">Categoria</label>
                <input id="category_name" name="category_name" type="text" value="<?= e(old('category_name', $service['category_name'] ?? '')) ?>" placeholder="Ex.: Elétrica">
            </div>

            <div class="field-group field-span-2">
                <label for="description">Descrição</label>
                <textarea id="description" name="description" rows="4" placeholder="Descrição do serviço..."><?= e(old('description', $service['description'] ?? '')) ?></textarea>
            </div>

            <div class="field-group">
                <label for="unit">Unidade</label>
                <input id="unit" name="unit" type="text" value="<?= e(old('unit', $service['unit'] ?? 'un')) ?>" placeholder="un, m², hora">
            </div>

            <div class="field-group">
                <label for="average_time_minutes">Tempo médio (min)</label>
                <input id="average_time_minutes" name="average_time_minutes" type="number" min="0" value="<?= e(old('average_time_minutes', $service['average_time_minutes'] ?? '')) ?>" placeholder="60">
            </div>

            <div class="field-group">
                <label for="price">Preço</label>
                <input id="price" name="price" type="text" value="<?= e(old('price', isset($service['price']) ? (string) $service['price'] : '')) ?>" placeholder="120,00">
            </div>

            <div class="field-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?= (old('status', $service['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Ativo</option>
                    <option value="inactive" <?= (old('status', $service['status'] ?? 'active') === 'inactive') ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button class="button button-primary" type="submit">Salvar serviço</button>
        </div>
    </form>
</section>