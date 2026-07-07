<?php

declare(strict_types=1);
?>

<form class="form-stack" method="post" action="<?= e(url('/forgot-password')) ?>" novalidate>
    <?= csrf_field() ?>

    <div class="field-group">
        <label for="email">E-mail da conta</label>
        <input id="email" name="email" type="email" autocomplete="email" value="<?= e(old('email')) ?>" placeholder="voce@empresa.com">
    </div>

    <button class="button button-primary" type="submit">Enviar instruções</button>

    <p class="form-footnote">
        Lembrou a senha? <a href="<?= e(url('/login')) ?>">Voltar para o login</a>
    </p>
</form>