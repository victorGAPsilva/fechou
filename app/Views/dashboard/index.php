<?php

declare(strict_types=1);
?>

<section class="section-stack">
    <div class="stats-grid">
        <?php foreach ($metrics as $metric): ?>
            <article class="stat-card">
                <span><?= e($metric['label']) ?></span>
                <strong><?= e($metric['value']) ?></strong>
                <small><?= e($metric['delta']) ?> em relação ao período anterior</small>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="dashboard-grid">
        <article class="panel panel-chart">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Conversão</span>
                    <h2>Visão geral do funil</h2>
                </div>
                <span class="badge badge-positive">+12% este mês</span>
            </div>

            <div class="chart-placeholder">
                <div class="chart-bar" style="height: 42%"></div>
                <div class="chart-bar" style="height: 58%"></div>
                <div class="chart-bar" style="height: 68%"></div>
                <div class="chart-bar chart-bar-accent" style="height: 84%"></div>
                <div class="chart-bar" style="height: 61%"></div>
                <div class="chart-bar" style="height: 77%"></div>
            </div>
        </article>

        <article class="panel panel-list">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Próximos passos</span>
                    <h2>Base pronta para crescer</h2>
                </div>
            </div>

            <ul class="check-list">
                <li>Multiempresa com isolamento por organização</li>
                <li>Autenticação com sessão segura</li>
                <li>Layout responsivo com tema claro e escuro</li>
                <li>Estrutura preparada para clientes, serviços e orçamentos</li>
            </ul>
        </article>
    </div>
</section>