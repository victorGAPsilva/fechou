<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Company;
use App\Models\User;
use Throwable;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (auth_check()) {
            redirect('/dashboard');
        }

        $this->render('auth/login', [
            'title' => 'Entrar no Fechou',
        ], 'layouts/guest');
    }

    public function login(): void
    {
        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            set_flash('error', 'A sessão expirou. Tente novamente.');
            redirect('/login');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $remember = isset($_POST['remember']);

        if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Informe e-mail e senha para continuar.');
            with_old($_POST);
            redirect('/login');
        }

        $matches = [];

        foreach (User::findAllByEmail($email) as $candidate) {
            if (password_verify($password, (string) $candidate['password'])) {
                $matches[] = $candidate;
            }
        }

        if (count($matches) !== 1) {
            $message = count($matches) > 1
                ? 'Encontramos mais de uma conta com essas credenciais. Entre em contato com o suporte.'
                : 'Credenciais inválidas. Verifique seus dados.';

            set_flash('error', $message);
            with_old($_POST);
            redirect('/login');
        }

        $user = $matches[0];

        if (($user['status'] ?? 'active') !== 'active') {
            set_flash('error', 'Sua conta está inativa.');
            redirect('/login');
        }

        Auth::login((int) $user['id']);

        $userModel = new User();
        $userModel->updateLastLogin((int) $user['id']);

        if ($remember) {
            $this->rememberUser((int) $user['id']);
        } else {
            $userModel->clearRememberToken((int) $user['id']);
            $this->forgetRememberCookie();
        }

        clear_old();
        set_flash('success', 'Bem-vindo de volta.');
        redirect('/dashboard');
    }

    public function showRegister(): void
    {
        if (auth_check()) {
            redirect('/dashboard');
        }

        $this->render('auth/register', [
            'title' => 'Criar conta',
        ], 'layouts/guest');
    }

    public function register(): void
    {
        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            set_flash('error', 'A sessão expirou. Tente novamente.');
            redirect('/register');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $companyName = trim((string) ($_POST['company_name'] ?? ''));

        if ($name === '' || $email === '' || $password === '' || $companyName === '') {
            set_flash('error', 'Preencha todos os campos obrigatórios.');
            with_old($_POST);
            redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Informe um e-mail válido.');
            with_old($_POST);
            redirect('/register');
        }

        if (mb_strlen($password) < 8) {
            set_flash('error', 'A senha precisa ter pelo menos 8 caracteres.');
            with_old($_POST);
            redirect('/register');
        }

        $db = Database::connection();

        try {
            $db->beginTransaction();

            $companyId = (new Company())->create([
                'name' => $companyName,
            ]);

            $userId = (new User())->create([
                'company_id' => $companyId,
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'owner',
            ]);

            $db->commit();
        } catch (Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            set_flash('error', 'Não foi possível criar sua conta. Verifique os dados e tente novamente.');
            with_old($_POST);
            redirect('/register');
        }

        Auth::login($userId);
        clear_old();
        set_flash('success', 'Conta criada com sucesso.');

        redirect('/dashboard');
    }

    public function showForgotPassword(): void
    {
        if (auth_check()) {
            redirect('/dashboard');
        }

        $this->render('auth/forgot-password', [
            'title' => 'Recuperar senha',
        ], 'layouts/guest');
    }

    public function forgotPassword(): void
    {
        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            set_flash('error', 'A sessão expirou. Tente novamente.');
            redirect('/forgot-password');
        }

        $email = trim((string) ($_POST['email'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Informe o e-mail da conta.');
            with_old($_POST);
            redirect('/forgot-password');
        }

        $links = [];

        foreach (User::findAllByEmail($email) as $user) {
            if (($user['status'] ?? 'active') !== 'active') {
                continue;
            }

            $links[] = $this->createPasswordResetLink($user);
        }

        clear_old();
        $message = 'Se o e-mail existir, enviaremos as instruções de recuperação.';

        if ($links !== [] && config('app.env', 'local') === 'local') {
            $message .= ' Link local de teste: ' . implode(' | ', $links);
        }

        set_flash('success', $message);
        redirect('/login');
    }

    public function showResetPassword(): void
    {
        if (auth_check()) {
            redirect('/dashboard');
        }

        $token = trim((string) ($_GET['token'] ?? ''));

        if (!$this->findValidPasswordReset($token)) {
            set_flash('error', 'Link de redefinição inválido ou expirado.');
            redirect('/login');
        }

        $this->render('auth/reset-password', [
            'title' => 'Redefinir senha',
            'token' => $token,
        ], 'layouts/guest');
    }

    public function resetPassword(): void
    {
        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            set_flash('error', 'A sessão expirou. Tente novamente.');
            redirect('/login');
        }

        $token = trim((string) ($_POST['token'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        $reset = $this->findValidPasswordReset($token);

        if (!$reset) {
            set_flash('error', 'Link de redefinição inválido ou expirado.');
            redirect('/login');
        }

        if (mb_strlen($password) < 8) {
            set_flash('error', 'A senha precisa ter pelo menos 8 caracteres.');
            redirect('/reset-password?token=' . urlencode($token));
        }

        if ($password !== $passwordConfirmation) {
            set_flash('error', 'A confirmação de senha não confere.');
            redirect('/reset-password?token=' . urlencode($token));
        }

        (new User())->updatePassword((int) $reset['user_id'], $password);
        $this->deletePasswordResets((int) $reset['user_id']);
        $this->forgetRememberCookie();

        set_flash('success', 'Senha redefinida com sucesso. Entre com sua nova senha.');
        redirect('/login');
    }

    public function logout(): void
    {
        if (!verify_csrf_token($_POST['_token'] ?? null)) {
            set_flash('error', 'A sessão expirou. Tente novamente.');
            redirect('/dashboard');
        }

        Auth::logout();
        set_flash('success', 'Você saiu do sistema com segurança.');
        redirect('/login');
    }

    private function rememberUser(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        (new User())->updateRememberToken($userId, hash('sha256', $token));

        setcookie('fechou_remember', $userId . ':' . $token, [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function forgetRememberCookie(): void
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

    private function createPasswordResetLink(array $user): string
    {
        $token = bin2hex(random_bytes(32));
        $this->deletePasswordResets((int) $user['id']);

        $statement = Database::connection()->prepare(
            'INSERT INTO password_resets (company_id, user_id, token, expires_at, created_at)
             VALUES (:company_id, :user_id, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())'
        );
        $statement->execute([
            'company_id' => $user['company_id'],
            'user_id' => $user['id'],
            'token' => hash('sha256', $token),
        ]);

        return url('/reset-password?token=' . urlencode($token));
    }

    private function findValidPasswordReset(string $token): ?array
    {
        if ($token === '' || strlen($token) < 40) {
            return null;
        }

        $statement = Database::connection()->prepare(
            'SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() LIMIT 1'
        );
        $statement->execute([
            'token' => hash('sha256', $token),
        ]);

        $reset = $statement->fetch();

        return $reset ?: null;
    }

    private function deletePasswordResets(int $userId): void
    {
        $statement = Database::connection()->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
        $statement->execute(['user_id' => $userId]);
    }
}
