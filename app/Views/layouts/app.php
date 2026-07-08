<?php

declare(strict_types=1);

$theme = $_COOKIE['theme'] ?? 'dark';
$currentUser = auth_user();
$appName = config('app.name', 'Fechou');
$currentPath = current_path();
$flashSuccess = flash('success');
$flashError = flash('error');
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
            <div class="brand-mark brand-mark-small">
                <img
                    src="<?= e(asset('images/fechoulogo2-48.png')) ?>"
                    srcset="<?= e(asset('images/fechoulogo2-48.png')) ?> 48w, <?= e(asset('images/fechoulogo2-96.png')) ?> 96w"
                    sizes="40px"
                    width="40"
                    height="40"
                    alt="<?= e($appName) ?>"
                    decoding="async"
                >
            </div>
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
    <button type="button" class="mobile-nav-backdrop" data-mobile-nav-close aria-label="Fechar menu"></button>

    <div class="workspace">
        <header class="topbar">
            <button type="button" class="mobile-nav-toggle" data-mobile-nav-toggle aria-label="Abrir menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
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
            <?= $content ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.FechouFlash = <?= json_encode(['success' => $flashSuccess, 'error' => $flashError], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    </script>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
    <script src="<?= e(asset('js/clients.js')) ?>"></script>
    <script src="<?= e(asset('js/quotes.js')) ?>"></script>
</body>
</html>
