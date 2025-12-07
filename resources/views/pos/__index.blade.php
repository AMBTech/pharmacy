@extends('layouts.app')

@section('content')
    <style>
        /* Simple layout styles */
        .full-width { width: 100%; }
        .products-list { max-height: 420px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px; }
        .cart-section { position: -webkit-sticky; position: sticky; top: 8px; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; background: #fff; }
        .form-row { display: flex; gap: 8px; align-items: center; }
        .form-row input, .form-row select { padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; }
        table.table { width: 100%; border-collapse: collapse; }
        table.table th, table.table td { padding: 8px; border-bottom: 1px solid #eef2f7; text-align: left; }
        .muted { color: #6b7280; font-size: 0.9rem; }
    </style>

    <div class="container mx-auto p-4">
        <!-- Search bar -->
        <div class="mb-4">
            <label for="product_search" class="block muted">Product Search</label>
            <input id="product_search" class="w-full p-2 border rounded" placeholder="Type product name or barcode and press Enter" autocomplete="off">
        </div>

        <!-- Dummy table (Lines ~65-100 in your previous file) showing how cart products will look like -->
        <div id="product-dummy-table" class="mb-4 full-width">
            <h3 class="mb-2">Products (search → qty → batch → rate → auto back to search)</h3>

            <!-- Inline form that appears when a product is selected via search -->
            <div id="product-entry" class="mb-3">
                <div class="form-row">
                    <input id="entry_qty" type="number" min="1" placeholder="Qty" style="width: 80px;" />
                    <select id="entry_batch" style="width: 180px;" hidden>
                        <!-- dynamically shown only if batches are available -->
                    </select>
                    <input id="entry_rate" type="number" step="0.01" placeholder="Rate" style="width: 120px;" />
                    <button id="entry_add" class="px-3 py-1 border rounded">Add</button>
                </div>
            </div>

            <!-- Visual dummy table for products that will appear in products-section -->
            <div class="products-list" id="products_section">
                <table class="table" id="products_table">
                    <thead>
                    <tr>
                        <th>SKU / Name</th>
                        <th>Qty</th>
                        <th>Batch</th>
                        <th>Rate</th>
                        <th>Line Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- rows will be appended here by JS -->
                    </tbody>
                </table>
                <div id="no-products" class="muted mt-2">No products added yet. Use the search box above and press Enter to start.</div>
            </div>
        </div>

        <!-- Cart section (originally at line ~196) moved below dummy table. Full width and sticky. -->
        <div id="cart_container" class="cart-section full-width mt-4">
            <h3>Cart</h3>
            <div id="cart-items">
                <div class="muted">Cart is empty</div>
            </div>
            <div class="mt-3">
                <div class="form-row">
                    <div class="muted">Subtotal:</div>
                    <div id="cart_subtotal" style="margin-left: auto; font-weight: 600">0.00</div>
                </div>
                <div class="form-row mt-2">
                    <button id="checkout_btn" class="px-3 py-1 border rounded" disabled>Proceed to Payment</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Minimal, dependency-free JS to implement the keyboard cart flow described by the user
        // Flow: Product Search → Enter → Qty → Enter → Batch (if shown) → Enter → Rate → Enter → Add → focus back to Product Search

        const productSearch = document.getElementById('product_search');
        const entryQty = document.getElementById('entry_qty');
        const entryBatch = document.getElementById('entry_batch');
        const entryRate = document.getElementById('entry_rate');
        const entryAdd = document.getElementById('entry_add');
        const productsTableBody = document.querySelector('#products_table tbody');
        const productsSection = document.getElementById('products_section');
        const noProducts = document.getElementById('no-products');
        const cartItems = document.getElementById('cart-items');
        const cartSubtotal = document.getElementById('cart_subtotal');
        const checkoutBtn = document.getElementById('checkout_btn');

        let cart = [];

        function mockSearchProduct(query) {
            // Replace this with an AJAX call to backend when available.
            // For demo purposes, return a fake product object and optionally batches.
            if (!query || query.trim().length < 1) return null;
            return {
                id: Date.now(),
                sku: 'SKU-' + Math.floor(Math.random()*9000+1000),
                name: query.trim(),
                rate: 0.00,
                batches: Math.random() > 0.6 ? [{ id: 'B1', name: 'Batch-1'}, { id: 'B2', name: 'Batch-2'}] : []
            };
        }

        let currentProduct = null;

        productSearch.addEventListener('keydown', async (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = productSearch.value;
                const p = mockSearchProduct(query);
                if (!p) return;
                currentProduct = p;
                // show entry UI and prefill
                entryQty.value = 1;
                entryRate.value = p.rate || '';
                // batches
                if (p.batches && p.batches.length) {
                    entryBatch.innerHTML = '';
                    p.batches.forEach(b => {
                        const opt = document.createElement('option'); opt.value = b.id; opt.textContent = b.name; entryBatch.appendChild(opt);
                    });
                    entryBatch.hidden = false;
                } else {
                    entryBatch.hidden = true;
                }
                // focus qty
                entryQty.focus();
            }
        });

        entryQty.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (!entryBatch.hidden) {
                    entryBatch.focus();
                } else {
                    entryRate.focus();
                }
            }
        });

        entryBatch.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                entryRate.focus();
            }
        });

        entryRate.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addCurrentProductToProductsSection();
                // simulate add button click
                entryAdd.focus();
            }
        });

        entryAdd.addEventListener('click', (e) => {
            e.preventDefault();
            addCurrentProductToProductsSection();
        });

        function addCurrentProductToProductsSection() {
            if (!currentProduct) return;
            const qty = parseFloat(entryQty.value) || 1;
            const rate = parseFloat(entryRate.value) || 0;
            const batch = entryBatch.hidden ? null : entryBatch.value;

            const line = {
                id: currentProduct.id,
                sku: currentProduct.sku,
                name: currentProduct.name,
                qty,
                rate,
                batch,
                total: +(qty * rate).toFixed(2)
            };

            cart.push(line);
            renderProducts();
            renderCart();

            // reset entry and focus back to search
            productSearch.value = '';
            currentProduct = null;
            entryQty.value = '';
            entryRate.value = '';
            entryBatch.hidden = true;

            // focus back to product search for automatic flow
            productSearch.focus();
        }

        function renderProducts() {
            productsTableBody.innerHTML = '';
            if (!cart.length) {
                noProducts.style.display = 'block';
                return;
            }
            noProducts.style.display = 'none';
            cart.forEach((line, idx) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
            <td>${line.sku} — ${line.name}</td>
            <td>${line.qty}</td>
            <td>${line.batch || '-'} </td>
            <td>${line.rate.toFixed(2)}</td>
            <td>${line.total.toFixed(2)}</td>
        `;
                productsTableBody.appendChild(tr);
            });
        }

        function renderCart() {
            cartItems.innerHTML = '';
            if (!cart.length) {
                cartItems.innerHTML = '<div class="muted">Cart is empty</div>';
                cartSubtotal.textContent = '0.00';
                checkoutBtn.disabled = true;
                return;
            }
            const ul = document.createElement('div');
            cart.forEach(it => {
                const row = document.createElement('div');
                row.className = 'form-row';
                row.style.marginBottom = '6px';
                row.innerHTML = `<div>${it.name} <span class="muted">(${it.sku})</span></div><div style="margin-left: auto">${it.total.toFixed(2)}</div>`;
                ul.appendChild(row);
            });
            cartItems.appendChild(ul);
            const subtotal = cart.reduce((s, i) => s + i.total, 0);
            cartSubtotal.textContent = subtotal.toFixed(2);
            checkoutBtn.disabled = false;
        }

        // Keyboard shortcut: Escape to clear current entry and refocus search
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                entryQty.value = '';
                entryRate.value = '';
                entryBatch.hidden = true;
                productSearch.focus();
            }
        });

        // Basic: ensure products section remains scrollable separately from page
        productsSection.addEventListener('mouseenter', () => {
            // nothing special for now; CSS handles scrolling
        });

    </script>

@endsection
