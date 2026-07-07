<?php

declare(strict_types=1);

$theme = $_COOKIE['theme'] ?? 'dark';
$appName = config('app.name', 'Fechou');
?><!DOCTYPE html>
<html lang="pt-BR" data-theme="<?= e($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? $appName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="shell shell-auth">
    <div class="background-orb background-orb-one"></div>
    <div class="background-orb background-orb-two"></div>

    <main class="auth-grid">
        <section class="auth-brand">
            <div class="brand-mark">F</div>
            <span class="eyebrow">SaaS profissional para orçamentos</span>
            <h1>Feche mais vendas com orçamentos que parecem de produto premium.</h1>
            <p>Fechou foi desenhado para transformar propostas em conversão, com uma experiência rápida, elegante e confiável.</p>

            <div class="trust-list">
                <span>Multiempresa</span>
                <span>PDF profissional</span>
                <span>Dark Mode</span>
                <span>Multiusuário</span>
            </div>
        </section>

        <section class="auth-card">
            <div class="card-header">
                <div>
                    <span class="eyebrow">Bem-vindo</span>
                    <h2><?= e($title ?? $appName) ?></h2>
                </div>

                <button type="button" class="theme-toggle" data-theme-toggle aria-label="Alternar tema">
                    <span class="theme-toggle-track"><span class="theme-toggle-thumb"></span></span>
                </button>
            </div>

            <?php if ($success = flash('success')): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>

            <?php if ($error = flash('error')): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </section>
    </main>

    <script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>