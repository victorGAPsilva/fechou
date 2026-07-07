<?php

declare(strict_types=1);
?>

<form class="form-stack" method="post" action="<?= e(url('/login')) ?>" novalidate>
    <?= csrf_field() ?>

    <div class="field-group">
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" autocomplete="email" value="<?= e(old('email')) ?>" placeholder="voce@empresa.com">
    </div>

    <div class="field-group">
        <label for="password">Senha</label>
        <div class="input-with-action">
            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Sua senha">
            <button type="button" class="inline-action" data-toggle-password="#password">Mostrar</button>
        </div>
    </div>

    <div class="form-row form-row-between">
        <label class="check-field">
            <input type="checkbox" name="remember" <?= old('remember') ? 'checked' : '' ?>>
            <span>Lembrar login</span>
        </label>

        <a href="<?= e(url('/forgot-password')) ?>">Esqueci minha senha</a>
    </div>

    <button class="button button-primary" type="submit">Entrar</button>

    <p class="form-footnote">
        Ainda não tem conta? <a href="<?= e(url('/register')) ?>">Criar conta</a>
    </p>
</form>