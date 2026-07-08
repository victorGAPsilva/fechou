<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use DateTimeImmutable;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = auth_user();

        if (!$user) {
            redirect('/login');
        }

        $companyId = (int) $user['company_id'];
        $totalQuotes = $this->countRows('quotes', $companyId);
        $totalClients = $this->countRows('clients', $companyId);
        $proposalTotal = $this->sumRows('quotes', $companyId, 'total');
        $approvedTotal = $this->sumRows('quotes', $companyId, 'total', "AND status = 'accepted'");
        $acceptedQuotes = $this->countRows('quotes', $companyId, "AND status = 'accepted'");

        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'metrics' => [
                [
                    'label' => 'Orcamentos',
                    'value' => (string) $totalQuotes,
                    'delta' => $this->monthlyDeltaText(
                        $this->monthlyValue('quotes', $companyId),
                        $this->monthlyValue('quotes', $companyId, 'COUNT(*)', '', 1)
                    ),
                ],
                [
                    'label' => 'Clientes',
                    'value' => (string) $totalClients,
                    'delta' => $this->monthlyDeltaText(
                        $this->monthlyValue('clients', $companyId),
                        $this->monthlyValue('clients', $companyId, 'COUNT(*)', '', 1)
                    ),
                ],
                [
                    'label' => 'Valor em propostas',
                    'value' => money_format_ptbr($proposalTotal),
                    'delta' => $this->monthlyDeltaText(
                        $this->monthlyValue('quotes', $companyId, 'COALESCE(SUM(total), 0)'),
                        $this->monthlyValue('quotes', $companyId, 'COALESCE(SUM(total), 0)', '', 1)
                    ),
                ],
                [
                    'label' => 'Valor aprovado',
                    'value' => money_format_ptbr($approvedTotal),
                    'delta' => $this->monthlyDeltaText(
                        $this->monthlyValue('quotes', $companyId, 'COALESCE(SUM(total), 0)', "AND status = 'accepted'"),
                        $this->monthlyValue('quotes', $companyId, 'COALESCE(SUM(total), 0)', "AND status = 'accepted'", 1)
                    ),
                ],
            ],
            'funnel' => $this->quoteFunnel($companyId),
            'recentQuotes' => $this->recentQuotes($companyId),
            'resourceSummary' => [
                ['label' => 'Clientes ativos', 'value' => (string) $this->countRows('clients', $companyId, "AND status = 'active'")],
                ['label' => 'Servicos ativos', 'value' => (string) $this->countRows('services', $companyId, "AND status = 'active'")],
                ['label' => 'Produtos ativos', 'value' => (string) $this->countRows('products', $companyId, "AND status = 'active'")],
                ['label' => 'Taxa de aprovacao', 'value' => $this->approvalRate($totalQuotes, $acceptedQuotes)],
            ],
        ]);
    }

    private function countRows(string $table, int $companyId, string $extraWhere = ''): int
    {
        $statement = Database::connection()->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE company_id = :company_id {$extraWhere}"
        );
        $statement->execute(['company_id' => $companyId]);

        return (int) $statement->fetchColumn();
    }

    private function sumRows(string $table, int $companyId, string $column, string $extraWhere = ''): float
    {
        $statement = Database::connection()->prepare(
            "SELECT COALESCE(SUM({$column}), 0) FROM {$table} WHERE company_id = :company_id {$extraWhere}"
        );
        $statement->execute(['company_id' => $companyId]);

        return (float) $statement->fetchColumn();
    }

    private function monthlyValue(
        string $table,
        int $companyId,
        string $expression = 'COUNT(*)',
        string $extraWhere = '',
        int $monthsAgo = 0
    ): float {
        $start = (new DateTimeImmutable('first day of this month 00:00:00'))->modify("-{$monthsAgo} month");
        $end = $start->modify('+1 month');

        $statement = Database::connection()->prepare(
            "SELECT {$expression}
             FROM {$table}
             WHERE company_id = :company_id
               AND created_at >= :start_date
               AND created_at < :end_date
               {$extraWhere}"
        );
        $statement->execute([
            'company_id' => $companyId,
            'start_date' => $start->format('Y-m-d H:i:s'),
            'end_date' => $end->format('Y-m-d H:i:s'),
        ]);

        return (float) $statement->fetchColumn();
    }

    private function monthlyDeltaText(float $current, float $previous): string
    {
        if ($previous <= 0 && $current <= 0) {
            return 'Sem movimento este mes';
        }

        if ($previous <= 0) {
            return 'Novo movimento este mes';
        }

        $percent = (($current - $previous) / $previous) * 100;
        $signal = $percent >= 0 ? '+' : '';

        return $signal . number_format($percent, 0, ',', '.') . '% vs mes anterior';
    }

    private function quoteFunnel(int $companyId): array
    {
        $labels = [
            'draft' => 'Rascunho',
            'sent' => 'Enviado',
            'viewed' => 'Visualizado',
            'accepted' => 'Aceito',
            'rejected' => 'Recusado',
            'canceled' => 'Cancelado',
        ];

        $statement = Database::connection()->prepare(
            'SELECT status, COUNT(*) AS total
             FROM quotes
             WHERE company_id = :company_id
             GROUP BY status'
        );
        $statement->execute(['company_id' => $companyId]);

        $totals = [];
        foreach ($statement->fetchAll() as $row) {
            $totals[(string) $row['status']] = (int) $row['total'];
        }

        $max = max(1, ...array_values($totals ?: [0]));
        $funnel = [];

        foreach ($labels as $status => $label) {
            $count = $totals[$status] ?? 0;
            $funnel[] = [
                'status' => $status,
                'label' => $label,
                'count' => $count,
                'height' => max(10, (int) round(($count / $max) * 100)),
                'accent' => $status === 'accepted',
            ];
        }

        return $funnel;
    }

    private function recentQuotes(int $companyId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT quotes.quote_number, quotes.title, quotes.status, quotes.total, quotes.created_at, clients.name AS client_name
             FROM quotes
             LEFT JOIN clients ON clients.id = quotes.client_id AND clients.company_id = quotes.company_id
             WHERE quotes.company_id = :company_id
             ORDER BY quotes.created_at DESC
             LIMIT 5'
        );
        $statement->execute(['company_id' => $companyId]);

        return $statement->fetchAll();
    }

    private function approvalRate(int $totalQuotes, int $acceptedQuotes): string
    {
        if ($totalQuotes <= 0) {
            return '0%';
        }

        return number_format(($acceptedQuotes / $totalQuotes) * 100, 0, ',', '.') . '%';
    }
}
