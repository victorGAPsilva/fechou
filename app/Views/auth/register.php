<?php

declare(strict_types=1);
?>

<form class="form-stack" method="post" action="<?= e(url('/register')) ?>" novalidate>
    <?= csrf_field() ?>

    <div class="field-group">
        <label for="company_name">Nome da empresa</label>
        <input id="company_name" name="company_name" type="text" value="<?= e(old('company_name')) ?>" placeholder="Fechou Serviços">
    </div>

    <div class="field-group">
        <label for="name">Seu nome</label>
        <input id="name" name="name" type="text" value="<?= e(old('name')) ?>" placeholder="Nome completo">
    </div>

    <div class="field-group">
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" autocomplete="email" value="<?= e(old('email')) ?>" placeholder="voce@empresa.com">
    </div>

    <div class="field-group">
        <label for="password">Senha</label>
        <div class="input-with-action">
            <input id="password" name="password" type="password" autocomplete="new-password" placeholder="Crie uma senha forte">
            <button type="button" class="inline-action" data-toggle-password="#password">Mostrar</button>
        </div>
    </div>

    <button class="button button-primary" type="submit">Criar conta</button>

    <p class="form-footnote">
        Já possui acesso? <a href="<?= e(url('/login')) ?>">Entrar</a>
    </p>
</form>