<?php

declare(strict_types=1);

$quote = $quote ?? null;
$items = $items ?? [];
$existingItems = !empty($items) ? $items : [['item_type' => 'custom', 'item_ref_id' => null, 'description' => '', 'quantity' => 1, 'unit_label' => 'un', 'unit_price' => 0, 'discount' => 0, 'total' => 0]];
$templates = $templates ?? [];
$totals = $totals ?? ['subtotal' => 0, 'discount_total' => 0, 'shipping_total' => 0, 'total' => 0];
?>

<section class="section-stack quote-builder" data-quote-builder>
    <div class="page-actions">
        <div>
            <span class="eyebrow">Propostas</span>
            <h2><?= e($title) ?></h2>
            <p class="muted">Monte o orçamento em menos de dois minutos com cálculo em tempo real.</p>
        </div>

        <a class="button button-ghost" href="<?= e(url('/quotes')) ?>">Voltar</a>
    </div>

    <form class="quote-form" method="post" action="<?= e(url($action)) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="quote-layout">
            <div class="quote-main">
                <div class="panel quote-hero">
                    <div class="quote-hero-top">
                        <div>
                            <span class="eyebrow">Identificação</span>
                            <h3>Orçamento profissional</h3>
                        </div>

                        <span class="badge badge-muted">Rascunho inteligente</span>
                    </div>

                    <div class="form-grid form-grid-2">
                        <div class="field-group">
                            <label for="quote_number">Número</label>
                            <input id="quote_number" name="quote_number" type="text" value="<?= e(old('quote_number', $quote['quote_number'] ?? '')) ?>" placeholder="Auto gerado">
                        </div>

                        <div class="field-group">
                            <label for="title">Título</label>
                            <input id="title" name="title" type="text" value="<?= e(old('title', $quote['title'] ?? '')) ?>" placeholder="Ex.: Reforma de fachada">
                        </div>

                        <div class="field-group">
                            <label for="client_id">Cliente</label>
                            <select id="client_id" name="client_id">
                                <option value="">Selecione...</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= e((string) $client['id']) ?>" <?= (string) old('client_id', $quote['client_id'] ?? '') === (string) $client['id'] ? 'selected' : '' ?>>
                                        <?= e($client['name']) ?><?= !empty($client['company_name']) ? ' - ' . e($client['company_name']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field-group">
                            <label for="template_key">Modelo</label>
                            <select id="template_key" name="template_key">
                                <?php foreach ($templates as $template): ?>
                                    <option value="<?= e($template['key']) ?>" <?= (string) old('template_key', $quote['template_key'] ?? 'modern') === $template['key'] ? 'selected' : '' ?>><?= e($template['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <?php foreach (['draft' => 'Rascunho', 'sent' => 'Enviado', 'viewed' => 'Visualizado', 'accepted' => 'Aceito', 'rejected' => 'Recusado', 'canceled' => 'Cancelado'] as $statusValue => $statusLabel): ?>
                                    <option value="<?= e($statusValue) ?>" <?= (string) old('status', $quote['status'] ?? 'draft') === $statusValue ? 'selected' : '' ?>><?= e($statusLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field-group">
                            <label for="validity_days">Validade (dias)</label>
                            <input id="validity_days" name="validity_days" type="number" min="1" value="<?= e(old('validity_days', $quote['validity_days'] ?? 7)) ?>">
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <div>
                            <span class="eyebrow">Itens</span>
                            <h3>Produtos e serviços</h3>
                        </div>

                        <div class="row-actions">
                            <button type="button" class="button button-ghost" data-add-item data-item-type="service">Adicionar serviço</button>
                            <button type="button" class="button button-ghost" data-add-item data-item-type="product">Adicionar produto</button>
                            <button type="button" class="button button-ghost" data-add-item data-item-type="custom">Item livre</button>
                        </div>
                    </div>

                    <div class="quote-items" data-items-container>
                        <?php foreach ($existingItems as $index => $item): ?>
                            <div class="quote-item" data-item-row>
                                <div class="quote-item-grid">
                                    <div class="field-group">
                                        <label>Tipo</label>
                                        <select name="items[<?= e((string) $index) ?>][item_type]" data-item-type>
                                            <option value="custom" <?= ($item['item_type'] ?? 'custom') === 'custom' ? 'selected' : '' ?>>Livre</option>
                                            <option value="service" <?= ($item['item_type'] ?? '') === 'service' ? 'selected' : '' ?>>Serviço</option>
                                            <option value="product" <?= ($item['item_type'] ?? '') === 'product' ? 'selected' : '' ?>>Produto</option>
                                        </select>
                                    </div>

                                    <div class="field-group">
                                        <label>Selecionar catálogo</label>
                                        <select data-catalog-select>
                                            <option value="">Escolher item</option>
                                        </select>
                                    </div>

                                    <div class="field-group field-span-2">
                                        <label>Descrição</label>
                                        <input type="text" name="items[<?= e((string) $index) ?>][description]" value="<?= e($item['description'] ?? '') ?>" data-description placeholder="Descrição do item">
                                    </div>

                                    <div class="field-group">
                                        <label>Quantidade</label>
                                        <input type="number" step="0.01" min="0" name="items[<?= e((string) $index) ?>][quantity]" value="<?= e((string) ($item['quantity'] ?? 1)) ?>" data-quantity>
                                    </div>

                                    <div class="field-group">
                                        <label>Unidade</label>
                                        <input type="text" name="items[<?= e((string) $index) ?>][unit_label]" value="<?= e($item['unit_label'] ?? 'un') ?>" data-unit-label placeholder="un">
                                    </div>

                                    <div class="field-group">
                                        <label>Valor unitário</label>
                                        <input type="text" name="items[<?= e((string) $index) ?>][unit_price]" value="<?= e((string) ($item['unit_price'] ?? 0)) ?>" data-unit-price placeholder="0,00">
                                    </div>

                                    <div class="field-group">
                                        <label>Desconto</label>
                                        <input type="text" name="items[<?= e((string) $index) ?>][discount]" value="<?= e((string) ($item['discount'] ?? 0)) ?>" data-discount placeholder="0,00">
                                    </div>

                                    <div class="field-group field-span-2">
                                        <label>Total</label>
                                        <input type="text" value="<?= e((string) ($item['total'] ?? 0)) ?>" data-row-total readonly>
                                    </div>

                                    <input type="hidden" name="items[<?= e((string) $index) ?>][item_ref_id]" value="<?= e((string) ($item['item_ref_id'] ?? '')) ?>" data-item-ref-id>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <div>
                            <span class="eyebrow">Condições</span>
                            <h3>Termos da proposta</h3>
                        </div>
                    </div>

                    <div class="form-grid form-grid-2">
                        <div class="field-group field-span-2">
                            <label for="notes">Observações</label>
                            <textarea id="notes" name="notes" rows="4" placeholder="Observações gerais..."><?= e(old('notes', $quote['notes'] ?? '')) ?></textarea>
                        </div>

                        <div class="field-group">
                            <label for="payment_terms">Condições de pagamento</label>
                            <input id="payment_terms" name="payment_terms" type="text" value="<?= e(old('payment_terms', $quote['payment_terms'] ?? '')) ?>" placeholder="Entrada + parcelas">
                        </div>

                        <div class="field-group">
                            <label for="warranty">Garantia</label>
                            <input id="warranty" name="warranty" type="text" value="<?= e(old('warranty', $quote['warranty'] ?? '')) ?>" placeholder="90 dias">
                        </div>

                        <div class="field-group">
                            <label for="shipping_total">Frete</label>
                            <input id="shipping_total" name="shipping_total" type="text" value="<?= e(old('shipping_total', $quote['shipping_total'] ?? 0)) ?>" data-shipping-total placeholder="0,00">
                        </div>

                        <div class="field-group">
                            <label for="global_discount">Desconto geral</label>
                            <input id="global_discount" name="global_discount" type="text" value="<?= e(old('global_discount', $quote['discount_total'] ?? 0)) ?>" data-global-discount placeholder="0,00">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="button button-primary" type="submit">Salvar orçamento</button>
                    </div>
                </div>
            </div>

            <aside class="quote-summary panel">
                <div class="panel-head">
                    <div>
                        <span class="eyebrow">Resumo</span>
                        <h3>Total do orçamento</h3>
                    </div>
                </div>

                <div class="summary-stack">
                    <div class="summary-line"><span>Subtotal</span><strong data-summary-subtotal><?= e(money_format_ptbr((float) $totals['subtotal'])) ?></strong></div>
                    <div class="summary-line"><span>Frete</span><strong data-summary-shipping><?= e(money_format_ptbr((float) $totals['shipping_total'])) ?></strong></div>
                    <div class="summary-line"><span>Desconto</span><strong data-summary-discount><?= e(money_format_ptbr((float) $totals['discount_total'])) ?></strong></div>
                    <div class="summary-line summary-line-total"><span>Total</span><strong data-summary-total><?= e(money_format_ptbr((float) $totals['total'])) ?></strong></div>
                </div>

                <div class="quote-template-stack">
                    <span class="eyebrow">Modelos</span>
                    <div class="template-cards">
                        <?php foreach ($templates as $template): ?>
                            <div class="template-card <?= (string) old('template_key', $quote['template_key'] ?? 'modern') === $template['key'] ? 'template-card-active' : '' ?>">
                                <strong><?= e($template['label']) ?></strong>
                                <small>Visual premium pronto para envio.</small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
        </div>
    </form>

    <script>
        window.orcaproQuoteData = <?= json_encode([
            'services' => $services,
            'products' => $products,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
</section>
