<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            self::loginFromRememberCookie();
        }

        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $user = User::findById((int) $_SESSION['user_id']);

        if (!$user || ($user['status'] ?? 'active') !== 'active') {
            self::logout();

            return null;
        }

        return $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
    }

    public static function logout(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        if ($userId > 0) {
            (new User())->clearRememberToken($userId);
        }

        self::forgetRememberCookie();
        unset($_SESSION['user_id']);
        session_regenerate_id(true);
    }

    private static function loginFromRememberCookie(): void
    {
        $cookie = (string) ($_COOKIE['fechou_remember'] ?? '');

        if ($cookie === '' || !str_contains($cookie, ':')) {
            return;
        }

        [$userId, $token] = explode(':', $cookie, 2);
        $userId = (int) $userId;

        if ($userId <= 0 || strlen($token) < 40) {
            self::forgetRememberCookie();

            return;
        }

        $user = User::findById($userId);
        $storedHash = (string) ($user['remember_token'] ?? '');
        $tokenHash = hash('sha256', $token);

        if (!$user || ($user['status'] ?? 'active') !== 'active' || $storedHash === '' || !hash_equals($storedHash, $tokenHash)) {
            self::forgetRememberCookie();

            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
    }

    private static function forgetRememberCookie(): void
    {
        setcookie('fechou_remember', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        unset($_COOKIE['fechou_remember']);
    }
}
