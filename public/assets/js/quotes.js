(function () {
    const builder = document.querySelector('[data-quote-builder]');

    if (!builder) {
        return;
    }

    const data = window.orcaproQuoteData || { services: [], products: [] };
    const itemsContainer = builder.querySelector('[data-items-container]');
    const subtotalEl = builder.querySelector('[data-summary-subtotal]');
    const shippingEl = builder.querySelector('[data-summary-shipping]');
    const discountEl = builder.querySelector('[data-summary-discount]');
    const totalEl = builder.querySelector('[data-summary-total]');
    const shippingInput = builder.querySelector('[data-shipping-total]');
    const globalDiscountInput = builder.querySelector('[data-global-discount]');
    const templateSelect = builder.querySelector('#template_key');

    const parseNumber = (value) => {
        const normalized = String(value || '0').replace(/\./g, '').replace(',', '.');
        const parsed = Number.parseFloat(normalized);
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const formatCurrency = (value) => `R$ ${Number(value || 0).toFixed(2).replace('.', ',')}`;

    const getCatalog = (rowType) => {
        if (rowType === 'service') {
            return data.services || [];
        }

        if (rowType === 'product') {
            return data.products || [];
        }

        return [];
    };

    const updateSummary = () => {
        let subtotal = 0;

        builder.querySelectorAll('[data-item-row]').forEach((row) => {
            const quantity = parseNumber(row.querySelector('[data-quantity]')?.value);
            const unitPrice = parseNumber(row.querySelector('[data-unit-price]')?.value);
            const discount = parseNumber(row.querySelector('[data-discount]')?.value);
            const rowTotal = Math.max(0, (quantity * unitPrice) - discount);

            const totalField = row.querySelector('[data-row-total]');
            if (totalField) {
                totalField.value = formatCurrency(rowTotal);
            }

            subtotal += rowTotal;
        });

        const shipping = parseNumber(shippingInput?.value);
        const discount = parseNumber(globalDiscountInput?.value);
        const total = Math.max(0, subtotal + shipping - discount);

        if (subtotalEl) subtotalEl.textContent = formatCurrency(subtotal);
        if (shippingEl) shippingEl.textContent = formatCurrency(shipping);
        if (discountEl) discountEl.textContent = formatCurrency(discount);
        if (totalEl) totalEl.textContent = formatCurrency(total);
    };

    const wireRow = (row) => {
        const typeSelect = row.querySelector('[data-item-type]');
        const catalogSelect = row.querySelector('[data-catalog-select]');
        const itemRefId = row.querySelector('[data-item-ref-id]');
        const descriptionInput = row.querySelector('[data-description]');
        const unitLabelInput = row.querySelector('[data-unit-label]');
        const quantityInput = row.querySelector('[data-quantity]');
        const unitPriceInput = row.querySelector('[data-unit-price]');
        const discountInput = row.querySelector('[data-discount]');

        const fillCatalog = () => {
            const currentType = typeSelect?.value || 'custom';
            const catalog = getCatalog(currentType);

            if (!catalogSelect) {
                return;
            }

            catalogSelect.innerHTML = '<option value="">Escolher item</option>';

            catalog.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.name} - ${formatCurrency(item.price)}`;
                option.dataset.name = item.name;
                option.dataset.unit = item.unit || 'un';
                option.dataset.price = item.price;
                catalogSelect.appendChild(option);
            });
        };

        if (typeSelect) {
            typeSelect.addEventListener('change', () => {
                fillCatalog();
                updateSummary();
            });
        }

        if (catalogSelect) {
            catalogSelect.addEventListener('change', () => {
                const selectedOption = catalogSelect.selectedOptions[0];

                if (!selectedOption || !selectedOption.value) {
                    return;
                }

                if (itemRefId) {
                    itemRefId.value = selectedOption.value;
                }

                if (descriptionInput) {
                    descriptionInput.value = selectedOption.dataset.name || descriptionInput.value;
                }

                if (unitLabelInput) {
                    unitLabelInput.value = selectedOption.dataset.unit || unitLabelInput.value;
                }

                if (unitPriceInput) {
                    unitPriceInput.value = Number(selectedOption.dataset.price || 0).toFixed(2).replace('.', ',');
                }

                updateSummary();
            });
        }

        [quantityInput, unitPriceInput, discountInput].forEach((input) => {
            if (input) {
                input.addEventListener('input', updateSummary);
            }
        });

        fillCatalog();
    };

    const addRow = (presetType = 'custom') => {
        const rowIndex = itemsContainer.querySelectorAll('[data-item-row]').length;
        const row = document.createElement('div');
        row.className = 'quote-item';
        row.setAttribute('data-item-row', '');

        row.innerHTML = `
            <div class="quote-item-grid">
                <div class="field-group">
                    <label>Tipo</label>
                    <select name="items[${rowIndex}][item_type]" data-item-type>
                        <option value="custom" ${presetType === 'custom' ? 'selected' : ''}>Livre</option>
                        <option value="service" ${presetType === 'service' ? 'selected' : ''}>Serviço</option>
                        <option value="product" ${presetType === 'product' ? 'selected' : ''}>Produto</option>
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
                    <input type="text" name="items[${rowIndex}][description]" data-description placeholder="Descrição do item">
                </div>

                <div class="field-group">
                    <label>Quantidade</label>
                    <input type="number" step="0.01" min="0" name="items[${rowIndex}][quantity]" value="1" data-quantity>
                </div>

                <div class="field-group">
                    <label>Unidade</label>
                    <input type="text" name="items[${rowIndex}][unit_label]" value="un" data-unit-label placeholder="un">
                </div>

                <div class="field-group">
                    <label>Valor unitário</label>
                    <input type="text" name="items[${rowIndex}][unit_price]" value="0,00" data-unit-price placeholder="0,00">
                </div>

                <div class="field-group">
                    <label>Desconto</label>
                    <input type="text" name="items[${rowIndex}][discount]" value="0,00" data-discount placeholder="0,00">
                </div>

                <div class="field-group field-span-2">
                    <label>Total</label>
                    <input type="text" value="R$ 0,00" data-row-total readonly>
                </div>

                <input type="hidden" name="items[${rowIndex}][item_ref_id]" value="" data-item-ref-id>
            </div>
        `;

        itemsContainer.appendChild(row);
        wireRow(row);
        updateSummary();
    };

    itemsContainer.querySelectorAll('[data-item-row]').forEach((row) => wireRow(row));

    builder.querySelectorAll('[data-add-item]').forEach((button) => {
        button.addEventListener('click', () => addRow(button.dataset.itemType || 'custom'));
    });

    [shippingInput, globalDiscountInput].forEach((input) => {
        if (input) {
            input.addEventListener('input', updateSummary);
        }
    });

    if (templateSelect) {
        templateSelect.addEventListener('change', () => {
            builder.querySelectorAll('.template-card').forEach((card) => card.classList.remove('template-card-active'));
            const activeIndex = templateSelect.selectedIndex;
            const cards = builder.querySelectorAll('.template-card');

            if (cards[activeIndex]) {
                cards[activeIndex].classList.add('template-card-active');
            }
        });
    }

    updateSummary();
})();
