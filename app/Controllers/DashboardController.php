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
        $averageTicket = $totalQuotes > 0 ? $proposalTotal / $totalQuotes : 0.0;

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
                ['label' => 'Ticket medio', 'value' => money_format_ptbr($averageTicket)],
            ],
            'monthlySeries' => $this->monthlySeries($companyId),
            'topClients' => $this->topClients($companyId),
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
        $colors = [
            'draft' => '#94a3b8',
            'sent' => '#3b82f6',
            'viewed' => '#8b5cf6',
            'accepted' => '#16a34a',
            'rejected' => '#ef4444',
            'canceled' => '#f59e0b',
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
                'color' => $colors[$status],
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

    private function monthlySeries(int $companyId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS period_key,
                    COUNT(*) AS quotes_count,
                    COALESCE(SUM(total), 0) AS total_value
             FROM quotes
             WHERE company_id = :company_id
               AND created_at >= :start_date
             GROUP BY period_key"
        );
        $start = (new DateTimeImmutable('first day of this month 00:00:00'))->modify('-5 months');
        $statement->execute([
            'company_id' => $companyId,
            'start_date' => $start->format('Y-m-d H:i:s'),
        ]);

        $rows = [];
        foreach ($statement->fetchAll() as $row) {
            $rows[(string) $row['period_key']] = [
                'quotes_count' => (int) $row['quotes_count'],
                'total_value' => (float) $row['total_value'],
            ];
        }

        $series = [];
        $maxValue = 0.0;

        for ($i = 0; $i < 6; $i++) {
            $month = $start->modify("+{$i} months");
            $key = $month->format('Y-m');
            $value = (float) ($rows[$key]['total_value'] ?? 0);
            $maxValue = max($maxValue, $value);

            $series[] = [
                'label' => $month->format('m/Y'),
                'quotes_count' => (int) ($rows[$key]['quotes_count'] ?? 0),
                'total_value' => $value,
                'height' => 12,
            ];
        }

        foreach ($series as &$item) {
            $item['height'] = $maxValue > 0 ? max(12, (int) round(((float) $item['total_value'] / $maxValue) * 100)) : 12;
        }
        unset($item);

        return $series;
    }

    private function topClients(int $companyId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT clients.name AS client_name,
                    COUNT(quotes.id) AS quotes_count,
                    COALESCE(SUM(quotes.total), 0) AS total_value
             FROM quotes
             LEFT JOIN clients ON clients.id = quotes.client_id AND clients.company_id = quotes.company_id
             WHERE quotes.company_id = :company_id
             GROUP BY clients.id, clients.name
             ORDER BY total_value DESC, quotes_count DESC
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
