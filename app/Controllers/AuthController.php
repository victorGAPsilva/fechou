<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Company;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
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

        if ($email === '' || $password === '') {
            set_flash('error', 'Informe e-mail e senha para continuar.');
            with_old($_POST);
            redirect('/login');
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            set_flash('error', 'Credenciais inválidas. Verifique seus dados.');
            with_old($_POST);
            redirect('/login');
        }

        if (($user['status'] ?? 'active') !== 'active') {
            set_flash('error', 'Sua conta está inativa.');
            redirect('/login');
        }

        Auth::login((int) $user['id']);
        (new User())->updateLastLogin((int) $user['id']);

        if ($remember) {
            setcookie('fechou_remember', hash('sha256', (string) $user['id']), [
                'expires' => time() + 60 * 60 * 24 * 30,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        clear_old();
        set_flash('success', 'Bem-vindo de volta.');
        redirect('/dashboard');
    }

    public function showRegister(): void
    {
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

        $companyModel = new Company();
        $companyId = $companyModel->create([
            'name' => $companyName,
        ]);

        $userModel = new User();
        $userId = $userModel->create([
            'company_id' => $companyId,
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'owner',
        ]);

        Auth::login($userId);
        clear_old();
        set_flash('success', 'Conta criada com sucesso. Complete a verificação de e-mail no próximo passo.');

        redirect('/dashboard');
    }

    public function showForgotPassword(): void
    {
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

        set_flash('success', 'Se o e-mail existir, enviaremos as instruções de recuperação.');
        redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        set_flash('success', 'Você saiu do sistema com segurança.');
        redirect('/login');
    }
}