<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS | Acie Fraiche</title>
    <link rel="icon" href="/assets/logo.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --af-gold: #cc9933;
            --af-ink: #0f0b05;
            --af-line: rgba(15, 11, 5, 0.12);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Manrope', system-ui, -apple-system, sans-serif;
            color: var(--af-ink);
            background: #f8f5ef;
        }
        header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 18px; background: #fff; border-bottom: 1px solid var(--af-line);
            position: sticky; top:0; z-index: 10;
        }
        main { padding: 18px; display: grid; gap: 14px; max-width: 1200px; margin: 0 auto; }
        .card { background:#fff; border:1px solid var(--af-line); border-radius:16px; padding:16px; box-shadow:0 10px 28px rgba(0,0,0,0.06); }
        h1 { margin:0; font-size:22px; font-family:'Playfair Display', Georgia, serif; }
        label { font-size:13px; color:rgba(0,0,0,0.6); display:block; margin-bottom:6px; }
        input, select {
            width:100%; padding:12px; border-radius:12px; border:1px solid var(--af-line);
            font: inherit; background:#fff;
        }
        button {
            border:none; border-radius:12px; padding:12px 14px; font-weight:700; cursor:pointer;
            transition: transform 0.1s ease;
        }
        button:active { transform: translateY(1px); }
        .btn-primary { background: var(--af-ink); color:#fff; }
        .btn-ghost { background:#fff; color:var(--af-ink); border:1px solid var(--af-line); }
        .grid { display:grid; gap:12px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
        .muted { color: rgba(0,0,0,0.6); font-size:13px; }
        .status { margin-top:6px; }
        .list { display:grid; gap:10px; max-height:360px; overflow:auto; }
        .item { border:1px solid var(--af-line); border-radius:14px; padding:12px; background:#fff; display:flex; justify-content:space-between; gap:10px; align-items:center; }
        .pill { border:1px solid var(--af-line); border-radius:999px; padding:6px 10px; font-size:12px; color:rgba(0,0,0,0.7); display:inline-flex; gap:6px; align-items:center; }
        table { width:100%; border-collapse: collapse; }
        th, td { padding:6px 4px; text-align:left; font-size:13px; }
        th { border-bottom:1px solid var(--af-line); }
    </style>
</head>
<body>
    <header>
        <div style="display:flex; align-items:center; gap:12px;">
            <img src="/assets/logo.png" alt="AFC" style="width:42px; height:42px; border-radius:12px; object-fit:contain;">
            <div>
                <div style="font-weight:700;">POS · Acie Fraiche</div>
                <div class="muted">Signed in as {{ auth()->user()->name ?? 'POS User' }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-ghost">Logout</button>
        </form>
    </header>

    <main>
        <div class="card">
            <div class="grid">
                <div>
                    <label>Scan / Enter barcode</label>
                    <input id="posBarcodeInput" placeholder="Focus here and scan">
                    <div id="posScanStatus" class="muted status">Ready to scan.</div>
                    <div id="posLookupResult" style="margin-top:10px;"></div>
                </div>
                <div>
                    <h1 style="margin-bottom:6px;">POS Cart</h1>
                    <div id="posCartList" class="list"></div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; padding:10px 12px; border:1px solid var(--af-line); border-radius:12px; background:#fff;">
                        <strong>Total</strong>
                        <strong id="posCartTotal">₦0</strong>
                    </div>
                </div>
            </div>

            <div class="grid" style="margin-top:12px;">
                <div>
                    <label>Customer name (optional)</label>
                    <input id="posCustomerName" placeholder="Walk-in">
                </div>
                <div>
                    <label>Customer phone</label>
                    <input id="posCustomerPhone" placeholder="080...">
                </div>
                <div>
                    <label>Payment method</label>
                    <select id="posPaymentMethod">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="transfer">Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn-primary" id="posCheckoutBtn">Complete Sale & Print Receipt</button>
                <div class="muted">Saves order with seller info and prints a receipt.</div>
            </div>
        </div>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const posBarcodeInput = document.getElementById('posBarcodeInput');
        const posLookupResult = document.getElementById('posLookupResult');
        const posScanStatus = document.getElementById('posScanStatus');
        const posCartList = document.getElementById('posCartList');
        const posCartTotal = document.getElementById('posCartTotal');
        const posCustomerName = document.getElementById('posCustomerName');
        const posCustomerPhone = document.getElementById('posCustomerPhone');
        const posPaymentMethod = document.getElementById('posPaymentMethod');
        const posCheckoutBtn = document.getElementById('posCheckoutBtn');

        let posCart = [];
        let lastLookup = null;
        let scanDebounce = null;
        let lookupInFlight = false;

        const apiFetch = (url, options = {}) => {
            const headers = {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...(options.headers || {}),
            };
            return fetch(url, {
                credentials: 'same-origin',
                ...options,
                headers,
            });
        };

        const safeRequest = async (url, options = {}) => {
            const res = await apiFetch(url, options);
            if (!res.ok) {
                let message = `Request failed (${res.status})`;
                try {
                    const data = await res.clone().json();
                    if (data?.message) message = data.message;
                } catch (err) {
                    const text = await res.text().catch(() => '');
                    if (text) message = text;
                }
                throw new Error(message);
            }
            return res;
        };

        function setPosStatus(message, tone = 'muted') {
            if (!posScanStatus) return;
            posScanStatus.textContent = message;
            posScanStatus.style.color = tone === 'error' ? '#b91c1c' : 'rgba(0,0,0,0.7)';
        }

        function computePosTotal() {
            return posCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        }

        function renderPosCart() {
            if (!posCartList || !posCartTotal) return;
            if (!posCart.length) {
                posCartList.innerHTML = '<div class="item">Cart is empty.</div>';
                posCartTotal.textContent = '₦0';
                return;
            }
            const total = computePosTotal();
            posCartList.innerHTML = posCart.map((item, index) => {
                const line = item.price * item.qty;
                return `
                    <div class="item">
                        <div>
                            <strong>${item.name}</strong>
                            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-top:6px;">
                                <span class="pill">Barcode: ${item.barcode || 'n/a'}</span>
                                <span class="pill">₦${Number(item.price).toLocaleString()} × ${item.qty}</span>
                                <span class="pill">Line: ₦${line.toLocaleString()}</span>
                            </div>
                        </div>
                        <div style="display:flex; gap:6px;">
                            <button class="btn-ghost" onclick="updatePosQty(${index}, 'dec')">-</button>
                            <button class="btn-ghost" onclick="updatePosQty(${index}, 'inc')">+</button>
                            <button class="btn-ghost" onclick="updatePosQty(${index}, 'remove')">Remove</button>
                        </div>
                    </div>
                `;
            }).join('');
            posCartTotal.textContent = '₦' + total.toLocaleString();
        }

        function addToPosCart(item) {
            const existing = posCart.find((i) => i.id === item.id);
            if (existing) {
                existing.qty += 1;
            } else {
                posCart.push({
                    id: item.id,
                    name: item.name,
                    price: Number(item.price) || 0,
                    barcode: item.barcode,
                    qty: 1,
                });
            }
            renderPosCart();
        }

        window.updatePosQty = (index, action) => {
            const item = posCart[index];
            if (!item) return;
            if (action === 'inc') item.qty += 1;
            if (action === 'dec') item.qty = Math.max(1, item.qty - 1);
            if (action === 'remove') posCart.splice(index, 1);
            renderPosCart();
        };

        function showLookupResult(item) {
            if (!posLookupResult) return;
            lastLookup = item;
            posLookupResult.innerHTML = `
                <div class="item" style="flex-direction:column; align-items:flex-start;">
                    <strong>${item.name}</strong>
                    <div style="display:flex; gap:6px; flex-wrap:wrap; margin-top:6px;">
                        <span class="pill">Price: ₦${Number(item.price).toLocaleString()}</span>
                        <span class="pill">Barcode: ${item.barcode}</span>
                        <span class="pill">${item.category?.name || 'Uncategorized'}</span>
                    </div>
                </div>
            `;
        }

        function showLookupError(message) {
            if (!posLookupResult) return;
            lastLookup = null;
            posLookupResult.innerHTML = `<div class="muted">${message}</div>`;
        }

        async function lookupBarcode(barcode, { addToCartOnSuccess = false } = {}) {
            if (!barcode) return;
            if (lookupInFlight) return;
            lookupInFlight = true;
            setPosStatus('Looking up barcode...');
            try {
                const res = await apiFetch(`/api/menu-items/lookup?barcode=${encodeURIComponent(barcode)}`);
                if (!res.ok) {
                    const msg = res.status === 404
                        ? 'No item found for this barcode.'
                        : res.status === 409
                            ? 'Item found but currently marked sold out.'
                            : 'Could not look up this barcode.';
                    showLookupError(msg);
                    setPosStatus(msg, 'error');
                    return;
                }
                const item = await res.json();
                showLookupResult(item);
                if (addToCartOnSuccess) {
                    addToPosCart(item);
                    setPosStatus(`Added ${item.name}. Ready for next scan.`);
                    if (posBarcodeInput) {
                        posBarcodeInput.value = '';
                        posBarcodeInput.focus();
                    }
                } else {
                    setPosStatus('Found. Price pulled live; add to cart.');
                }
            } catch (e) {
                showLookupError('Lookup failed. Check connection.');
                setPosStatus('Lookup failed.', 'error');
            } finally {
                lookupInFlight = false;
                posBarcodeInput?.select();
            }
        }

        posBarcodeInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const code = e.target.value.trim();
                if (!code) return;
                lookupBarcode(code, { addToCartOnSuccess: true });
            }
        });

        posBarcodeInput?.addEventListener('input', (e) => {
            const code = e.target.value.trim();
            clearTimeout(scanDebounce);
            if (!code) {
                setPosStatus('Ready to scan.');
                return;
            }
            scanDebounce = setTimeout(() => lookupBarcode(code, { addToCartOnSuccess: true }), 180);
        });

        posCheckoutBtn?.addEventListener('click', async () => {
            if (!posCart.length) {
                alert('Cart is empty. Scan an item first.');
                return;
            }
            const payload = {
                channel: 'pos',
                customer_name: posCustomerName?.value || null,
                customer_phone: posCustomerPhone?.value || null,
                items: posCart.map(item => ({
                    menu_item_id: item.id,
                    quantity: item.qty,
                    price: item.price,
                })),
                payment: {
                    amount: computePosTotal(),
                    method: posPaymentMethod?.value || 'cash',
                    reference: `POS-${Date.now()}`,
                },
            };

            const resetBtn = () => {
                if (!posCheckoutBtn) return;
                posCheckoutBtn.disabled = false;
                posCheckoutBtn.textContent = 'Complete Sale & Print Receipt';
            };

            try {
                if (posCheckoutBtn) {
                    posCheckoutBtn.disabled = true;
                    posCheckoutBtn.textContent = 'Saving...';
                }
                const res = await safeRequest('/api/orders', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const order = await res.json();
                alert(`Sale recorded. Order code: ${order.code || 'pending'}.`);
                openPosReceipt(order);
                posCart = [];
                renderPosCart();
                if (posBarcodeInput) {
                    posBarcodeInput.value = '';
                    posBarcodeInput.focus();
                }
            } catch (e) {
                alert(e.message || 'Could not save sale.');
            } finally {
                resetBtn();
            }
        });

        function openPosReceipt(order) {
            try {
                const receiptWindow = window.open('', 'pos-receipt');
                if (!receiptWindow) return;
                const itemsHtml = (order.items || []).map(item => `
                    <tr>
                        <td>${item.name}</td>
                        <td style="text-align:center;">${item.quantity}</td>
                        <td style="text-align:right;">₦${Number(item.unit_price || item.price || 0).toLocaleString()}</td>
                        <td style="text-align:right;">₦${Number(item.total || item.unit_price * item.quantity || 0).toLocaleString()}</td>
                    </tr>
                `).join('');
                receiptWindow.document.write(`
                    <html>
                        <head><title>Receipt ${order.code || ''}</title></head>
                        <body style="font-family: Arial, sans-serif; padding:16px;">
                            <h2 style="margin:0 0 8px;">Acie Fraiche Cafe</h2>
                            <div style="margin-bottom:10px;">Order Code: <strong>${order.code || ''}</strong></div>
                            <table style="width:100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left; border-bottom:1px solid #ddd;">Item</th>
                                        <th style="text-align:center; border-bottom:1px solid #ddd;">Qty</th>
                                        <th style="text-align:right; border-bottom:1px solid #ddd;">Price</th>
                                        <th style="text-align:right; border-bottom:1px solid #ddd;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsHtml}
                                    <tr>
                                        <td colspan="3" style="text-align:right; border-top:1px solid #ddd;">Total</td>
                                        <td style="text-align:right; border-top:1px solid #ddd;">₦${Number(order.total || 0).toLocaleString()}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p style="margin-top:12px;">Sold by: {{ auth()->user()->name ?? 'POS user' }}</p>
                            <script>window.onload = function(){ window.print(); };</script>
                        </body>
                    </html>
                `);
                receiptWindow.document.close();
            } catch (e) {
                console.error('Could not open receipt', e);
            }
        }

        renderPosCart();
        posBarcodeInput?.focus();
    </script>
</body>
</html>
