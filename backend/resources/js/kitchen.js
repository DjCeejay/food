import './bootstrap';

const ordersEl = document.getElementById('kitchenOrders');
const emptyEl = document.getElementById('kitchenEmpty');
const connectionEl = document.getElementById('kitchenConnection');
const statCountEl = document.getElementById('kitchenStatCount');
const statLastEl = document.getElementById('kitchenStatLast');
const statTotalEl = document.getElementById('kitchenStatTotal');
const toastEl = document.getElementById('kitchenToast');

if (ordersEl) {
    const money = (value) => '₦' + Number(value ?? 0).toLocaleString();
    const escapeHtml = (value = '') =>
        String(value).replace(/[&<>"']/g, (char) =>
            ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char] ?? char)
        );

    let orders = (window.initialOrders ?? []).map(normalizeOrder);

    renderOrders();
    updateStats();
    setConnection('Echo not configured', false);

    if (window.Echo) {
        const channel = window.Echo.private('orders');

        channel.listen('.order.created', (event) => {
            upsertOrder(normalizeOrder(event));
            showToast(`New order ${event.code ?? ''}`.trim() || 'New order received');
        });

        if (typeof channel.error === 'function') {
            channel.error(() => setConnection('Channel error', false));
        }

        const connector = window.Echo.connector?.pusher ?? window.Echo.connector;
        const connection = connector?.connection;
        if (connection) {
            connection.bind('connected', () => setConnection('Live', true));
            connection.bind('disconnected', () => setConnection('Disconnected', false));
            connection.bind('error', () => setConnection('Connection error', false));
        } else {
            setConnection('Live', true);
        }
    }

    function normalizeOrder(order) {
        return {
            ...order,
            total: Number(order.total ?? 0),
            created_at: order.created_at ?? order.createdAt ?? new Date().toISOString(),
            customer_name: order.customer_name ?? order.customerName ?? '',
            customer_phone: order.customer_phone ?? order.customerPhone ?? '',
            items: (order.items ?? []).map((item) => ({
                ...item,
                quantity: Number(item.quantity ?? 0),
                total: Number(item.total ?? item.unit_price ?? 0),
                name: item.name ?? '',
            })),
        };
    }

    function upsertOrder(order) {
        orders = [
            order,
            ...orders.filter((existing) => existing.id !== order.id),
        ].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        renderOrders();
        updateStats();
    }

    function renderOrders() {
        if (!orders.length) {
            ordersEl.innerHTML = '';
            emptyEl.style.display = 'block';
            return;
        }

        emptyEl.style.display = 'none';
        ordersEl.innerHTML = orders
            .map((order) => {
                const items = order.items
                    .map((item) => `<li><span>${escapeHtml(item.name)}</span><span class="small">x${item.quantity} • ${money(item.total ?? item.unit_price ?? 0)}</span></li>`)
                    .join('');
                const customer = order.customer_name || order.customer_phone
                    ? `${escapeHtml(order.customer_name || 'Guest')} ${order.customer_phone ? ' · ' + escapeHtml(order.customer_phone) : ''}`
                    : 'Walk-in';

                return `
                    <div class="order" data-order-id="${order.id}">
                        <div class="order-header">
                            <div>
                                <strong>${escapeHtml(order.code ?? 'New order')}</strong>
                                <div class="small">${formatTime(order.created_at)} · ${escapeHtml(order.channel ?? 'pos')}</div>
                            </div>
                            <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                                <span class="badge">${escapeHtml(order.status ?? 'pending')}</span>
                                <span class="pill">${money(order.total)}</span>
                            </div>
                        </div>
                        <div class="small" style="margin-top:6px;">Customer: ${customer}</div>
                        <ul class="items">${items}</ul>
                    </div>
                `;
            })
            .join('');
    }

    function updateStats() {
        statCountEl.textContent = orders.length;
        statTotalEl.textContent = money(orders.reduce((sum, order) => sum + (order.total ?? 0), 0));
        statLastEl.textContent = orders[0] ? formatTime(orders[0].created_at) : '—';
    }

    function formatTime(timestamp) {
        const date = new Date(timestamp);
        if (Number.isNaN(date.getTime())) {
            return 'Just now';
        }
        return date.toLocaleString(undefined, {
            hour: '2-digit',
            minute: '2-digit',
            day: '2-digit',
            month: 'short',
        });
    }

    function setConnection(label, ok) {
        if (!connectionEl) return;
        connectionEl.textContent = label;
        connectionEl.style.background = ok ? 'rgba(0,128,0,0.08)' : 'rgba(255,165,0,0.12)';
        connectionEl.style.borderColor = ok ? 'rgba(0,128,0,0.35)' : 'rgba(255,165,0,0.35)';
        connectionEl.style.color = ok ? '#0f5132' : '#7a4a00';
    }

    function showToast(message) {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.style.display = 'block';
        setTimeout(() => {
            toastEl.style.display = 'none';
        }, 2600);
    }
}
