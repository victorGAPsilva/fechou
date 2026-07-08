<?php

declare(strict_types=1);

$funnelTotal = array_sum(array_map(static fn (array $step): int => (int) $step['count'], $funnel ?? []));
$statusLabels = [
    'draft' => 'Rascunho',
    'sent' => 'Enviado',
    'viewed' => 'Visualizado',
    'accepted' => 'Aceito',
    'rejected' => 'Recusado',
    'canceled' => 'Cancelado',
];
?>

<section class="section-stack">
    <div class="stats-grid">
        <?php foreach ($metrics as $metric): ?>
            <article class="stat-card">
                <span><?= e($metric['label']) ?></span>
                <strong><?= e($metric['value']) ?></strong>
                <small><?= e($metric['delta']) ?></small>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="dashboard-grid">
        <article class="panel panel-chart">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Conversao</span>
                    <h2>Funil de orcamentos</h2>
                </div>
                <span class="badge <?= $funnelTotal > 0 ? 'badge-positive' : 'badge-muted' ?>">
                    <?= e((string) $funnelTotal) ?> no total
                </span>
            </div>

            <div class="chart-placeholder">
                <?php foreach ($funnel as $step): ?>
                    <div
                        class="chart-bar <?= !empty($step['accent']) ? 'chart-bar-accent' : '' ?>"
                        style="height: <?= e((string) $step['height']) ?>%; --bar-color: <?= e($step['color'] ?? '#c7d0dc') ?>"
                        title="<?= e($step['label'] . ': ' . $step['count']) ?>"
                    ></div>
                <?php endforeach; ?>
            </div>

            <div class="summary-stack dashboard-summary">
                <?php foreach ($funnel as $step): ?>
                    <div class="summary-line">
                        <span class="funnel-label" style="--bar-color: <?= e($step['color'] ?? '#c7d0dc') ?>"><?= e($step['label']) ?></span>
                        <strong><?= e((string) $step['count']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="panel-head panel-head-spaced">
                <div>
                    <span class="eyebrow">Receita</span>
                    <h2>Evolucao mensal</h2>
                </div>
            </div>

            <div class="monthly-chart">
                <?php foreach ($monthlySeries as $month): ?>
                    <div class="monthly-column">
                        <div
                            class="monthly-bar"
                            style="height: <?= e((string) $month['height']) ?>%"
                            title="<?= e($month['label'] . ': ' . money_format_ptbr((float) $month['total_value'])) ?>"
                        ></div>
                        <small><?= e($month['label']) ?></small>
                        <strong><?= e((string) $month['quotes_count']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="panel panel-list">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Operacao</span>
                    <h2>Resumo da base</h2>
                </div>
            </div>

            <div class="summary-stack dashboard-summary">
                <?php foreach ($resourceSummary as $item): ?>
                    <div class="summary-line">
                        <span><?= e($item['label']) ?></span>
                        <strong><?= e($item['value']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="panel-head panel-head-spaced">
                <div>
                    <span class="eyebrow">Clientes</span>
                    <h2>Ranking por valor</h2>
                </div>
            </div>

            <?php if (!empty($topClients)): ?>
                <div class="summary-stack dashboard-summary">
                    <?php foreach ($topClients as $client): ?>
                        <div class="summary-line">
                            <span><?= e($client['client_name'] ?: 'Cliente nao informado') ?></span>
                            <strong><?= e(money_format_ptbr((float) $client['total_value'])) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted">Nenhum cliente com orcamento ainda.</p>
            <?php endif; ?>

            <div class="panel-head panel-head-spaced">
                <div>
                    <span class="eyebrow">Recentes</span>
                    <h2>Ultimos orcamentos</h2>
                </div>
            </div>

            <?php if (!empty($recentQuotes)): ?>
                <div class="check-list dashboard-list">
                    <?php foreach ($recentQuotes as $quote): ?>
                        <div class="dashboard-list-item">
                            <div>
                                <strong><?= e($quote['quote_number']) ?></strong>
                                <span><?= e($quote['title'] ?: 'Orcamento sem titulo') ?></span>
                                <small><?= e($quote['client_name'] ?: 'Cliente nao informado') ?></small>
                            </div>
                            <div class="dashboard-list-meta">
                                <span class="badge <?= ($quote['status'] ?? '') === 'accepted' ? 'badge-positive' : 'badge-muted' ?>">
                                    <?= e($statusLabels[$quote['status'] ?? 'draft'] ?? (string) ($quote['status'] ?? 'draft')) ?>
                                </span>
                                <strong><?= e(money_format_ptbr((float) $quote['total'])) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted">Nenhum orcamento criado ainda.</p>
            <?php endif; ?>
        </article>
    </div>
</section>
