<?php

declare(strict_types=1);

$client = $client ?? [];
?>

<section class="section-stack client-form-page">
    <div class="page-actions">
        <div>
            <span class="eyebrow">Cadastros</span>
            <h2><?= e($title) ?></h2>
            <p class="muted">Cadastre dados de contato e endereço com uma ficha visual clara e pronta para operação.</p>
        </div>

        <a class="button button-ghost" href="<?= e(url('/clients')) ?>">Voltar</a>
    </div>

    <div class="client-form-layout">
        <form class="form-stack panel form-panel client-form-main" method="post" action="<?= e(url($action)) ?>" novalidate>
            <?= csrf_field() ?>

            <div class="panel-head client-form-head">
                <div>
                    <span class="eyebrow">Identificação</span>
                    <h3>Ficha do cliente</h3>
                </div>

                <span class="badge badge-muted">Multiempresa</span>
            </div>

            <div class="form-grid form-grid-2">
                <div class="field-group">
                    <label for="name">Nome *</label>
                    <input id="name" name="name" type="text" value="<?= e(old('name', $client['name'] ?? '')) ?>" placeholder="Nome do cliente">
                </div>

                <div class="field-group">
                    <label for="company_name">Empresa</label>
                    <input id="company_name" name="company_name" type="text" value="<?= e(old('company_name', $client['company_name'] ?? '')) ?>" placeholder="Nome da empresa">
                </div>

                <div class="field-group">
                    <label for="email">E-mail</label>
                    <input id="email" name="email" type="email" value="<?= e(old('email', $client['email'] ?? '')) ?>" placeholder="contato@cliente.com">
                </div>

                <div class="field-group">
                    <label for="phone">Telefone</label>
                    <input id="phone" name="phone" type="text" value="<?= e(old('phone', $client['phone'] ?? '')) ?>" placeholder="(11) 99999-9999">
                </div>

                <div class="field-group">
                    <label for="whatsapp">WhatsApp</label>
                    <input id="whatsapp" name="whatsapp" type="text" value="<?= e(old('whatsapp', $client['whatsapp'] ?? '')) ?>" placeholder="(11) 99999-9999">
                </div>

                <div class="field-group">
                    <label for="document">CPF/CNPJ</label>
                    <input id="document" name="document" type="text" value="<?= e(old('document', $client['document'] ?? '')) ?>" placeholder="000.000.000-00">
                </div>

                <div class="field-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active" <?= (old('status', $client['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Ativo</option>
                        <option value="inactive" <?= (old('status', $client['status'] ?? 'active') === 'inactive') ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>

                <div class="field-group">
                    <label for="zip_code">CEP</label>
                    <input id="zip_code" name="zip_code" type="text" value="<?= e(old('zip_code', $client['zip_code'] ?? '')) ?>" placeholder="00000-000">
                </div>

                <div class="field-group">
                    <label for="state">Estado</label>
                    <input id="state" name="state" type="text" maxlength="2" value="<?= e(old('state', $client['state'] ?? '')) ?>" placeholder="SP">
                </div>

                <div class="field-group field-span-2">
                    <label for="street">Endereço</label>
                    <input id="street" name="street" type="text" value="<?= e(old('street', $client['street'] ?? '')) ?>" placeholder="Rua, avenida, número">
                </div>

                <div class="field-group">
                    <label for="number">Número</label>
                    <input id="number" name="number" type="text" value="<?= e(old('number', $client['number'] ?? '')) ?>" placeholder="123">
                </div>

                <div class="field-group">
                    <label for="complement">Complemento</label>
                    <input id="complement" name="complement" type="text" value="<?= e(old('complement', $client['complement'] ?? '')) ?>" placeholder="Apto, bloco, sala">
                </div>

                <div class="field-group">
                    <label for="neighborhood">Bairro</label>
                    <input id="neighborhood" name="neighborhood" type="text" value="<?= e(old('neighborhood', $client['neighborhood'] ?? '')) ?>" placeholder="Centro">
                </div>

                <div class="field-group">
                    <label for="city">Cidade</label>
                    <input id="city" name="city" type="text" value="<?= e(old('city', $client['city'] ?? '')) ?>" placeholder="São Paulo">
                </div>

                <div class="field-group field-span-2">
                    <label for="notes">Observações</label>
                    <textarea id="notes" name="notes" rows="4" placeholder="Observações internas..."><?= e(old('notes', $client['notes'] ?? '')) ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button class="button button-primary" type="submit">Salvar cliente</button>
            </div>
        </form>

        <aside class="panel client-form-aside">
            <div class="panel-head">
                <div>
                    <span class="eyebrow">Guia</span>
                    <h3>Boas práticas</h3>
                </div>
            </div>

            <div class="summary-stack">
                <div class="summary-line">
                    <span>Contato</span>
                    <strong>Telefone, WhatsApp e e-mail atualizados</strong>
                </div>

                <div class="summary-line">
                    <span>Documentação</span>
                    <strong>CPF ou CNPJ válido para identificação</strong>
                </div>

                <div class="summary-line">
                    <span>Endereço</span>
                    <strong>Dados completos ajudam em propostas e entregas</strong>
                </div>

                <div class="summary-line summary-line-total">
                    <span>Status</span>
                    <strong>Use inativo para clientes pausados</strong>
                </div>
            </div>
        </aside>
    </div>
</section>