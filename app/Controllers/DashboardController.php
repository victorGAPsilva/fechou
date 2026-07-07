<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class DashboardController extends Controller
{
    public function index(): void
    {
        if (!auth_check()) {
            redirect('/login');
        }

        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'metrics' => [
                ['label' => 'Orçamentos', 'value' => '12', 'delta' => '+18%'],
                ['label' => 'Clientes', 'value' => '8', 'delta' => '+5%'],
                ['label' => 'Valor em propostas', 'value' => 'R$ 42.800,00', 'delta' => '+21%'],
                ['label' => 'Valor aprovado', 'value' => 'R$ 18.400,00', 'delta' => '+9%'],
            ],
        ]);
    }
}