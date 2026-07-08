<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\ContractController;
use App\Controllers\DashboardController;
use App\Controllers\ProductController;
use App\Controllers\QuoteController;
use App\Controllers\ServiceController;
use App\Core\Router;

return static function (Router $router): void {
    $router->get('/', [AuthController::class, 'showLogin']);
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);

    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);

    $router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
    $router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
    $router->get('/reset-password', [AuthController::class, 'showResetPassword']);
    $router->post('/reset-password', [AuthController::class, 'resetPassword']);

    $router->post('/logout', [AuthController::class, 'logout']);

    $router->get('/dashboard', [DashboardController::class, 'index']);

    $router->get('/clients', [ClientController::class, 'index']);
    $router->get('/clients/new', [ClientController::class, 'create']);
    $router->post('/clients', [ClientController::class, 'store']);
    $router->get('/clients/{id}/edit', [ClientController::class, 'edit']);
    $router->post('/clients/{id}', [ClientController::class, 'update']);
    $router->post('/clients/{id}/delete', [ClientController::class, 'destroy']);

    $router->get('/services', [ServiceController::class, 'index']);
    $router->get('/services/new', [ServiceController::class, 'create']);
    $router->post('/services', [ServiceController::class, 'store']);
    $router->get('/services/{id}/edit', [ServiceController::class, 'edit']);
    $router->post('/services/{id}', [ServiceController::class, 'update']);
    $router->post('/services/{id}/delete', [ServiceController::class, 'destroy']);

    $router->get('/products', [ProductController::class, 'index']);
    $router->get('/products/new', [ProductController::class, 'create']);
    $router->post('/products', [ProductController::class, 'store']);
    $router->get('/products/{id}/edit', [ProductController::class, 'edit']);
    $router->post('/products/{id}', [ProductController::class, 'update']);
    $router->post('/products/{id}/delete', [ProductController::class, 'destroy']);

    $router->get('/quotes', [QuoteController::class, 'index']);
    $router->get('/quotes/new', [QuoteController::class, 'create']);
    $router->post('/quotes', [QuoteController::class, 'store']);
    $router->get('/quotes/{id}/pdf', [QuoteController::class, 'downloadPdf']);
    $router->get('/quotes/{id}/edit', [QuoteController::class, 'edit']);
    $router->post('/quotes/{id}', [QuoteController::class, 'update']);
    $router->post('/quotes/{id}/delete', [QuoteController::class, 'destroy']);

    $router->get('/contracts', [ContractController::class, 'index']);
    $router->get('/contracts/new', [ContractController::class, 'create']);
    $router->post('/contracts', [ContractController::class, 'store']);
    $router->get('/contracts/{id}/edit', [ContractController::class, 'edit']);
    $router->post('/contracts/{id}', [ContractController::class, 'update']);
    $router->post('/contracts/{id}/delete', [ContractController::class, 'destroy']);
};
