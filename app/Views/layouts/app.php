<?php

declare(strict_types=1);

$theme = $_COOKIE['theme'] ?? 'dark';
$currentUser = auth_user();
$appName = config('app.name', 'Fechou');
$currentPath = current_path();
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
<body class="shell shell-app">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-mark brand-mark-small">F</div>
            <div>
                <strong><?= e($appName) ?></strong>
                <span>Orçamentos premium</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a class="<?= $currentPath === '/dashboard' ? 'active' : '' ?>" href="<?= e(url('/dashboard')) ?>">Dashboard</a>
            <a class="<?= str_starts_with($currentPath, '/clients') ? 'active' : '' ?>" href="<?= e(url('/clients')) ?>">Clientes</a>
            <a class="<?= str_starts_with($currentPath, '/services') ? 'active' : '' ?>" href="<?= e(url('/services')) ?>">Serviços</a>
            <a class="<?= str_starts_with($currentPath, '/products') ? 'active' : '' ?>" href="<?= e(url('/products')) ?>">Produtos</a>
            <a class="<?= str_starts_with($currentPath, '/quotes') ? 'active' : '' ?>" href="<?= e(url('/quotes')) ?>">Orçamentos</a>
            <a class="<?= str_starts_with($currentPath, '/contracts') ? 'active' : '' ?>" href="<?= e(url('/contracts')) ?>">Contratos</a>
        </nav>

        <form method="post" action="<?= e(url('/logout')) ?>">
            <?= csrf_field() ?>
            <button class="button button-ghost sidebar-logout" type="submit">Sair</button>
        </form>
    </aside>

    <div class="workspace">
        <header class="topbar">
            <div>
                <span class="eyebrow">Operação ativa</span>
                <h1><?= e($title ?? $appName) ?></h1>
            </div>

            <div class="topbar-actions">
                <button type="button" class="theme-toggle" data-theme-toggle aria-label="Alternar tema">
                    <span class="theme-toggle-track"><span class="theme-toggle-thumb"></span></span>
                </button>
                <div class="profile-chip">
                    <span class="profile-avatar"><?= e(mb_strtoupper(mb_substr((string) ($currentUser['name'] ?? 'U'), 0, 1))) ?></span>
                    <div>
                        <strong><?= e($currentUser['name'] ?? 'Usuário') ?></strong>
                        <span><?= e($currentUser['email'] ?? '') ?></span>
                    </div>
                </div>
            </div>
        </header>

        <main class="content-area">
            <?php if ($success = flash('success')): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>

            <?php if ($error = flash('error')): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>

    <script src="<?= e(asset('js/app.js')) ?>"></script>
    <script src="<?= e(asset('js/clients.js')) ?>"></script>
    <script src="<?= e(asset('js/quotes.js')) ?>"></script>
</body>
</html>
