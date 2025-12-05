import './bootstrap';

const ordersEl = document.getElementById('kitchenOrders');
const emptyEl = document.getElementById('kitchenEmpty');
const connectionEl = document.getElementById('kitchenConnection');
const statCountEl = document.getElementById('kitchenStatCount');
const statLastEl = document.getElementById('kitchenStatLast');
const statTotalEl = document.getElementById('kitchenStatTotal');
const toastEl = document.getElementById('kitchenToast');
const soundBtn = document.getElementById('toggleSound');
const notifyBtn = document.getElementById('toggleNotify');

let soundEnabled = localStorage.getItem('kitchenSound') === '1';
let notifyEnabled = localStorage.getItem('kitchenNotify') === '1';
const chime = new Audio('data:audio/wav;base64,UklGRjQAAABXQVZFZm10IBAAAAABAAEAQB8AAIA+AAACABAAZGF0YQAAAAA=');

if (ordersEl) {
    const money = (value) => '₦' + Number(value ?? 0).toLocaleString();
    const escapeHtml = (value = '') =>
        String(value).replace(/[&<>"']/g, (char) =>
            ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char] ?? char)
        );

    let orders = (window.initialOrders ?? []).map(normalizeOrder);

    renderOrders();
    updateStats();

    if (window.Echo) {
        setConnection('Connecting…', false);
        const channel = window.Echo.private('orders');

        channel.listen('.order.created', (event) => {
            upsertOrder(normalizeOrder(event));
            showToast(`New order ${event.code ?? ''}`.trim() || 'New order received');
            notifyNewOrder(event);
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
    } else {
        setConnection('Echo not configured', false);
    }

    if (soundBtn) {
        const setSoundLabel = () => soundBtn.textContent = `Sound: ${soundEnabled ? 'On' : 'Off'}`;
        setSoundLabel();
        soundBtn.addEventListener('click', () => {
            soundEnabled = !soundEnabled;
            localStorage.setItem('kitchenSound', soundEnabled ? '1' : '0');
            setSoundLabel();
        });
    }

    if (notifyBtn) {
        const setNotifyLabel = () => notifyBtn.textContent = `Browser Alerts: ${notifyEnabled ? 'On' : 'Off'}`;
        setNotifyLabel();
        notifyBtn.addEventListener('click', async () => {
            if (!notifyEnabled && Notification?.permission === 'default') {
                await Notification.requestPermission();
            }
            notifyEnabled = Notification?.permission === 'granted';
            localStorage.setItem('kitchenNotify', notifyEnabled ? '1' : '0');
            setNotifyLabel();
        });
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
                                <div class="order-meta">
                                    <span>${formatTime(order.created_at)} · ${escapeHtml(order.channel ?? 'pos')}</span>
                                    <span class="pill warn" data-elapsed="${order.created_at}">${elapsed(order.created_at)}</span>
                                </div>
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

    function elapsed(timestamp) {
        const date = new Date(timestamp);
        if (Number.isNaN(date.getTime())) return 'Just now';
        const diff = Math.max(0, Date.now() - date.getTime());
        const mins = Math.floor(diff / 60000);
        if (mins < 1) return 'Just now';
        if (mins < 60) return `${mins}m ago`;
        const hrs = Math.floor(mins / 60);
        return `${hrs}h ${mins % 60}m`;
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

    function notifyNewOrder(event) {
        if (soundEnabled && chime?.play) {
            chime.currentTime = 0;
            chime.play().catch(() => {});
        }
        if (notifyEnabled && Notification?.permission === 'granted') {
            const title = event.code ? `New order ${event.code}` : 'New order received';
            const body = (event.items || []).map(i => `${i.quantity}× ${i.name}`).join(', ') || 'New ticket in the kitchen';
            new Notification(title, { body, icon: '/assets/logo.png' });
        }
    }

    // Refresh elapsed timers every 30s
    setInterval(() => {
        ordersEl.querySelectorAll('[data-elapsed]').forEach((pill) => {
            pill.textContent = elapsed(pill.getAttribute('data-elapsed'));
        });
    }, 30000);
}
