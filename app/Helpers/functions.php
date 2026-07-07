<?php

declare(strict_types=1);

use App\Core\Auth;

function config(string $key, mixed $default = null): mixed
{
    static $cache = [];

    $parts = explode('.', $key);
    $file = array_shift($parts);

    if (!isset($cache[$file])) {
        $path = CONFIG_PATH . '/' . $file . '.php';
        $cache[$file] = is_file($path) ? require $path : [];
    }

    $value = $cache[$file];

    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }

        $value = $value[$part];
    }

    return $value ?? $default;
}

function url(string $path = ''): string
{
    $base = rtrim((string) config('app.url', ''), '/');

    if ($path === '' || $path === '/') {
        return $base;
    }

    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function current_path(): string
{
    return rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $key, mixed $default = null): mixed
{
    if (func_num_args() === 2) {
        return $_SESSION['_flash'][$key] ?? $default;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return $value;
}

function set_flash(string $key, mixed $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function with_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(?string $token): bool
{
    return is_string($token) && hash_equals((string) ($_SESSION['_csrf'] ?? ''), $token);
}

function auth_user(): ?array
{
    return Auth::user();
}

function auth_check(): bool
{
    return Auth::check();
}

function money_format_ptbr(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}