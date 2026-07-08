<?php

declare(strict_types=1);
?>

<form class="form-stack" method="post" action="<?= e(url('/reset-password')) ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

    <div class="field-group">
        <label for="password">Nova senha</label>
        <div class="input-with-action">
            <input id="password" name="password" type="password" autocomplete="new-password" placeholder="Mínimo de 8 caracteres">
            <button type="button" class="inline-action" data-toggle-password="#password">Mostrar</button>
        </div>
    </div>

    <div class="field-group">
        <label for="password_confirmation">Confirmar senha</label>
        <div class="input-with-action">
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Repita a nova senha">
            <button type="button" class="inline-action" data-toggle-password="#password_confirmation">Mostrar</button>
        </div>
    </div>

    <button class="button button-primary" type="submit">Redefinir senha</button>

    <p class="form-footnote">
        Lembrou a senha? <a href="<?= e(url('/login')) ?>">Voltar para o login</a>
    </p>
</form>
