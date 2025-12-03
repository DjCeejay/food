<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Acie Fraiche Admin</title>
    <link rel="icon" href="/assets/logo.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --af-gold: #cc9933;
            --af-brown: #523700;
            --af-ink: #0f0b05;
            --af-cream: #f7f1e7;
            --af-card: #fbf7f0;
            --af-line: rgba(82, 55, 0, 0.14);
            --af-shadow: 0 18px 48px rgba(0, 0, 0, 0.12);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Manrope', system-ui, -apple-system, sans-serif;
            color: var(--af-ink);
            background:
                radial-gradient(circle at 14% 18%, rgba(204,153,51,0.12), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(82,55,0,0.09), transparent 22%),
                var(--af-cream);
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
            transition: grid-template-columns 0.2s ease;
        }
        body.collapsed { grid-template-columns: 72px 1fr; }
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 18px;
            border-right: 1px solid var(--af-line);
            background: rgba(255,255,255,0.94);
            backdrop-filter: blur(10px);
            box-shadow: 6px 0 30px rgba(0,0,0,0.05);
            display: grid;
            align-content: start;
            gap: 12px;
            transition: width 0.2s ease, transform 0.2s ease, padding 0.2s ease;
        }
        .sidebar.collapsed { width: 72px; padding: 12px; }
        .brand {
            display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 12px;
            border: 1px solid var(--af-line); background: #fff; box-shadow: 0 10px 24px rgba(0,0,0,0.06);
            transition: opacity 0.2s ease;
        }
        .sidebar.collapsed .brand { opacity: 0; pointer-events: none; height: 0; padding: 0; margin: 0; }
        .brand img { width: 48px; height: 48px; border-radius: 12px; object-fit: contain; }
        .brand-title { margin: 0; font-family: 'Playfair Display', Georgia, serif; font-size: 20px; }
        .muted { color: rgba(0,0,0,0.64); font-size: 14px; margin: 2px 0 0; }
        .hamburger {
            display: block;
            position: fixed;
            top: 16px;
            left: 16px;
            background: #fff;
            border: 1px solid var(--af-line);
            border-radius: 12px;
            padding: 10px 12px;
            box-shadow: var(--af-shadow);
            cursor: pointer;
            z-index: 30;
        }
        nav { display: grid; gap: 6px; margin-top: 8px; }
        .nav-btn {
            border: 1px solid var(--af-line);
            background: #fff;
            color: var(--af-brown);
            padding: 11px 12px;
            border-radius: 12px;
            font-weight: 700;
            text-align: left;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .nav-btn.active { background: var(--af-brown); color: #fff; box-shadow: var(--af-shadow); }
        .nav-label { white-space: nowrap; }
        .sidebar.collapsed .nav-label { display: none; }
        main { padding: 22px; display: grid; gap: 18px; }
        .hero {
            background: linear-gradient(135deg, rgba(204,153,51,0.2), rgba(255,255,255,0.9));
            border: 1px solid var(--af-line);
            border-radius: 18px;
            padding: 18px;
            box-shadow: var(--af-shadow);
        }
        .hero-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        button { border: none; border-radius: 12px; padding: 10px 14px; font-weight: 700; cursor: pointer; transition: transform 0.1s ease; }
        button:active { transform: translateY(1px); }
        .btn-primary { background: var(--af-brown); color: #fff; }
        .btn-ghost { background: #fff; color: var(--af-brown); border: 1px solid var(--af-line); }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-top: 12px; }
        .stat { background: #fff; border: 1px solid var(--af-line); border-radius: 14px; padding: 14px; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .stat h3 { margin: 0; font-size: 26px; }
        .stat span { color: rgba(0,0,0,0.6); font-size: 13px; }
        .panels { display: grid; gap: 16px; }
        .panel { display: none; }
        .panel.active { display: block; }
        .card { background: var(--af-card); border: 1px solid var(--af-line); border-radius: 16px; padding: 16px; box-shadow: var(--af-shadow); }
        .card h2 { margin: 0 0 8px; font-size: 18px; font-family: 'Playfair Display', Georgia, serif; }
        form { display: grid; gap: 8px; margin-top: 10px; }
        label { font-size: 13px; color: rgba(0,0,0,0.6); }
        input, textarea, select {
            width: 100%; padding: 10px 12px;
            border: 1px solid var(--af-line); border-radius: 12px;
            font: inherit; background: #fff;
        }
        .list { display: grid; gap: 8px; max-height: 380px; overflow: auto; }
        .item { border: 1px solid var(--af-line); border-radius: 14px; padding: 12px; background: #fff; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .item h4 { margin: 0 0 4px; font-size: 15px; }
        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; border: 1px solid var(--af-line); font-size: 12px; color: rgba(0,0,0,0.65); }
        .price { font-weight: 700; color: var(--af-brown); }
        .row { display: flex; gap: 8px; flex-wrap: wrap; }
        .thumb { width: 64px; height: 64px; object-fit: cover; border-radius: 12px; border: 1px solid var(--af-line); background: #fff; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; background: #fff; border: 1px solid var(--af-line); border-radius: 14px; overflow: hidden; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid var(--af-line); }
        th { color: rgba(0,0,0,0.6); font-weight: 700; background: #fdf8ef; }
        tr:last-child td { border-bottom: none; }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 14px; }
        .frame-wrap { border: 1px solid var(--af-line); border-radius: 14px; overflow: hidden; box-shadow: var(--af-shadow); background: #fff; }
        iframe { width: 100%; height: 70vh; border: none; }
        /* Admin menu cards */
        .menu-card { border: 1px solid var(--af-line); border-radius: 16px; padding: 12px; background: #fff; box-shadow: 0 10px 24px rgba(0,0,0,0.04); display: grid; gap: 8px; }
        .menu-card-head { display:flex; justify-content:space-between; gap:8px; align-items:flex-start; flex-wrap:wrap; }
        .menu-card-title { margin:0; font-size:16px; }
        .menu-tags { display:flex; gap:6px; flex-wrap:wrap; }
        .menu-pill { border: 1px solid var(--af-line); border-radius: 999px; padding: 4px 10px; font-size:12px; color: rgba(0,0,0,0.7); background:#fff; }
        .menu-pill.sold { border-color:#fca5a5; color:#b91c1c; background:#fef2f2; }
        .menu-pill.active { border-color:#bbf7d0; color:#166534; background:#f0fdf4; }
        .menu-meta { font-size:13px; color: rgba(0,0,0,0.7); }
        .menu-actions { display:flex; gap:6px; flex-wrap:wrap; }
        @media (max-width: 960px) {
            body { grid-template-columns: 1fr; }
            .sidebar { position: fixed; left: 0; top: 0; width: 260px; transform: translateX(-110%); z-index: 20; }
            .sidebar.open { transform: translateX(0); }
            .sidebar.collapsed { width: 260px; padding: 18px; }
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <button class="hamburger" id="toggleSidebar">☰</button>
        <div class="brand">
            <img src="/assets/logo.png" alt="Acie Fraiche Logo">
            <div>
                <p class="brand-title">Acie Fraiche</p>
                <p class="muted">Admin & POS</p>
            </div>
        </div>
        <nav>
            <button class="nav-btn active" data-tab="overview"><span class="nav-label">Overview</span></button>
            <button class="nav-btn" data-tab="categories"><span class="nav-label">Categories</span></button>
            <button class="nav-btn" data-tab="menu"><span class="nav-label">Menu</span></button>
            <button class="nav-btn" data-tab="users"><span class="nav-label">Users</span></button>
            <button class="nav-btn" data-tab="orders"><span class="nav-label">Orders</span></button>
            <button class="nav-btn" data-tab="pos"><span class="nav-label">POS</span></button>
            <button class="nav-btn" data-tab="health"><span class="nav-label">Health</span></button>
            <button class="nav-btn" data-tab="site"><span class="nav-label">Public Site</span></button>
        </nav>
        <div class="muted" style="margin-top:12px;">
            Signed in as {{ auth()->user()->name ?? 'User' }}
        </div>
    </aside>

    <main>
        <div class="hero">
            <div class="hero-top">
                <div>
                    <p class="muted" style="margin:0 0 4px;">Dashboard</p>
                    <h1 style="margin:0; font-family:'Playfair Display', Georgia, serif;">Today at a glance</h1>
                </div>
                <div class="actions">
                    <button class="btn-ghost" onclick="switchTab('pos')">Open POS</button>
                    <button class="btn-primary" onclick="switchTab('menu')">Add Menu Item</button>
                </div>
            </div>
            <div class="stats">
                <div class="stat"><h3 id="statCategories">0</h3><span>Categories</span></div>
                <div class="stat"><h3 id="statItems">0</h3><span>Menu items</span></div>
                <div class="stat"><h3 id="statOrders">0</h3><span>Orders</span></div>
                <div class="stat"><h3 id="statRevenue">₦0</h3><span>Revenue</span></div>
            </div>
        </div>

        <div class="panels">
            <section class="panel active" data-section="overview">
                <div class="card">
                    <h2>Overview</h2>
                    <p class="muted">Quick links to start.</p>
                    <div class="row" style="gap:10px; flex-wrap:wrap;">
                        <button class="btn-primary" onclick="switchTab('menu')">Manage Menu</button>
                        <button class="btn-ghost" onclick="switchTab('categories')">Manage Categories</button>
                        <button class="btn-ghost" onclick="switchTab('orders')">View Orders</button>
                        <button class="btn-ghost" onclick="switchTab('pos')">Open POS</button>
                    </div>
                </div>
            </section>

            <section class="panel" data-section="categories">
                <div class="card">
                    <h2>Categories</h2>
                    <p class="muted">Create and manage categories.</p>
                    <div class="grid-2">
                        <div class="list" id="categoryList"></div>
                        <form id="categoryForm">
                            <label>Name</label>
                            <input name="name" placeholder="e.g. Mains" required />
                            <label>Description</label>
                            <input name="description" placeholder="Optional" />
                            <button class="btn-primary" type="submit">Add Category</button>
                        </form>
                    </div>
                </div>
            </section>

            <section class="panel" data-section="menu">
                <div class="card">
                    <h2>Menu Items</h2>
                    <p class="muted">Add items and toggle sold-out.</p>
                    <div class="grid-2">
                        <div class="list" id="menuList"></div>
                        <form id="menuForm">
                            <label>Name</label>
                            <input name="name" placeholder="Item name" required />
                            <label>Description</label>
                            <textarea name="description" rows="2" placeholder="Optional"></textarea>
                            <label>Price (NGN)</label>
                            <input name="price" type="number" step="0.01" min="0" required />
                            <label>Category</label>
                            <select name="category_id" id="menuCategorySelect">
                                <option value="">No category</option>
                            </select>
                            <label>Image</label>
                            <input name="image" type="file" accept="image/*" />
                            <button class="btn-primary" type="submit">Add Menu Item</button>
                        </form>
                    </div>
                </div>
            </section>

            <section class="panel" data-section="users">
                <div class="card">
                    <h2>Users</h2>
                    <p class="muted">Approve accounts and assign roles.</p>
                    <div class="list" id="usersList"></div>
                </div>
            </section>

            <section class="panel" data-section="orders">
                <div class="card">
                    <h2>Orders</h2>
                    <p class="muted">Recent orders (sample data seeded).</p>
                    <table id="ordersTable">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Channel</th>
                                <th>When</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>

            <section class="panel" data-section="pos">
                <div class="card">
                    <h2>POS</h2>
                    <p class="muted">Scan or type a barcode to add the latest-priced item to the order.</p>
                    <div class="row" style="gap:10px; flex-wrap:wrap; align-items:flex-start;">
                        <div style="flex:1; min-width:260px;">
                            <label style="display:block; font-size:13px; color:rgba(0,0,0,0.6); margin-bottom:6px;">Scan / Enter barcode</label>
                            <input id="posBarcodeInput" placeholder="Focus here and scan" style="width:100%; padding:12px; border-radius:12px; border:1px solid var(--af-line); font-size:16px;" />
                            <small class="muted" id="posScanStatus" style="display:block; margin-top:6px;">Ready to scan.</small>
                        </div>
                        <div style="flex:1; min-width:280px;">
                            <div id="posLookupResult" class="item" style="display:none; flex-direction:column; align-items:flex-start;"></div>
                        </div>
                    </div>
                    <div style="margin-top:14px;">
                        <h3 style="margin:0 0 8px;">POS Cart</h3>
                        <div id="posCartList" class="list"></div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px; padding:10px 12px; border:1px solid var(--af-line); border-radius:12px; background:#fff;">
                            <strong>Total</strong>
                            <strong id="posCartTotal">₦0</strong>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel" data-section="health">
                <div class="card">
                    <h2>API Health</h2>
                    <p class="muted">Live check of the backend.</p>
                    <p id="healthDetail" class="muted">Checking...</p>
                </div>
            </section>

            <section class="panel" data-section="site">
                <div class="card">
                    <h2>Public Site Preview</h2>
                    <p class="muted">This loads the current landing page so you can keep visuals aligned.</p>
                    <div class="frame-wrap">
                        <iframe src="/live.html" title="Public site preview"></iframe>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleSidebar = document.getElementById('toggleSidebar');
        const navBtns = document.querySelectorAll('.nav-btn');
        const panels = document.querySelectorAll('.panel');

        const categoryList = document.getElementById('categoryList');
        const categoryForm = document.getElementById('categoryForm');
        const menuList = document.getElementById('menuList');
        const menuForm = document.getElementById('menuForm');
        const menuCategorySelect = document.getElementById('menuCategorySelect');
        const usersList = document.getElementById('usersList');
        const ordersTableBody = document.querySelector('#ordersTable tbody');
        const statCategories = document.getElementById('statCategories');
        const statItems = document.getElementById('statItems');
        const statOrders = document.getElementById('statOrders');
        const statRevenue = document.getElementById('statRevenue');
        const healthDetail = document.getElementById('healthDetail');
        const roles = ['admin', 'staff', 'pos', 'kitchen', 'desk'];
        const posBarcodeInput = document.getElementById('posBarcodeInput');
        const posLookupResult = document.getElementById('posLookupResult');
        const posScanStatus = document.getElementById('posScanStatus');
        const posCartList = document.getElementById('posCartList');
        const posCartTotal = document.getElementById('posCartTotal');
        let posCart = [];
        let lastLookup = null;
        let isInteracting = false;
        let interactionTimeout;

        const markInteracting = () => {
            isInteracting = true;
            clearTimeout(interactionTimeout);
            interactionTimeout = setTimeout(() => { isInteracting = false; }, 2000);
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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

        const setBusy = (btn, busy) => {
            if (!btn) return;
            btn.disabled = busy;
            if (busy) {
                btn.dataset.originalText = btn.dataset.originalText || btn.textContent;
                btn.textContent = 'Working...';
            } else if (btn.dataset.originalText) {
                btn.textContent = btn.dataset.originalText;
            }
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

        const runAction = async (btn, fn) => {
            setBusy(btn, true);
            try {
                await fn();
            } catch (e) {
                alert(e.message || 'Could not complete that action.');
                console.error(e);
            } finally {
                setBusy(btn, false);
            }
        };

        toggleSidebar?.addEventListener('click', () => {
            if (window.innerWidth <= 960) {
                sidebar.classList.toggle('open');
            } else {
                document.body.classList.toggle('collapsed');
                sidebar.classList.toggle('collapsed');
            }
        });

        function switchTab(tab) {
            navBtns.forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
            panels.forEach(p => p.classList.toggle('active', p.dataset.section === tab));
            if (window.innerWidth <= 960) sidebar.classList.remove('open');
        }
        navBtns.forEach(btn => btn.addEventListener('click', () => switchTab(btn.dataset.tab)));

        async function checkHealth() {
            try {
                const res = await apiFetch('/api/health');
                if (!res.ok) throw new Error('bad status');
                healthDetail.textContent = 'API responding normally.';
            } catch (e) {
                healthDetail.textContent = 'API not reachable. Check server.';
            }
        }

        function renderCategories(categories) {
            categoryList.innerHTML = categories.map(cat => `
                <div class="item">
                    <div>
                        <h4>${cat.name}</h4>
                        <small class="muted">${cat.description || ''}</small>
                    </div>
                    <span class="pill" style="border-color:${cat.is_active ? '#bbf7d0' : '#fca5a5'};color:${cat.is_active ? '#166534' : '#b91c1c'}">
                        ${cat.is_active ? 'Active' : 'Inactive'}
                    </span>
                    <div class="row" style="gap:6px;">
                        <button class="btn-ghost" onclick="deleteCategory(${cat.id}, this)">Delete</button>
                    </div>
                </div>
            `).join('');

            menuCategorySelect.innerHTML = `<option value="">No category</option>` + categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
            statCategories.textContent = categories.length;
        }

        function renderMenu(items) {
            menuList.innerHTML = items.map(item => `
                <div class="menu-card">
                    <div class="menu-card-head">
                        <div style="display:flex; gap:10px; align-items:center;">
                            ${item.image_url ? `<img class="thumb" src="${item.image_url}" alt="${item.name}">` : ''}
                            <div>
                                <p class="menu-card-title">${item.name}</p>
                                <div class="menu-tags">
                                    <span class="menu-pill">₦${Number(item.price).toLocaleString()}</span>
                                    <span class="menu-pill">${item.category?.name || 'Uncategorized'}</span>
                                    <span class="menu-pill ${item.is_sold_out ? 'sold' : 'active'}">${item.is_sold_out ? 'Sold Out' : 'Available'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="menu-tags">
                            <span class="menu-pill">Barcode: ${item.barcode || 'Not set'}</span>
                            <button class="btn-ghost" ${item.barcode ? '' : 'disabled'} onclick="copyBarcode(${JSON.stringify(item.barcode || '')}, this)">Copy</button>
                            <button class="btn-ghost" ${item.barcode ? '' : 'disabled'} onclick="printBarcode(${JSON.stringify(item.barcode || '')}, ${JSON.stringify(item.name || '')})">Print</button>
                            <button class="btn-ghost" onclick="regenBarcode(${item.id}, this)">Regenerate</button>
                        </div>
                    </div>
                    <p class="menu-meta">${item.description || 'No description yet.'}</p>
                    <div class="menu-actions">
                        <button class="btn-ghost" onclick="toggleSoldOut(${item.id}, this)">${item.is_sold_out ? 'Mark Available' : 'Mark Sold Out'}</button>
                        <button class="btn-ghost" onclick="editMenuItem(${item.id}, ${JSON.stringify(item).replace(/"/g, '&quot;')}, this)">Edit</button>
                        <button class="btn-ghost" onclick="deleteMenuItem(${item.id}, this)">Delete</button>
                    </div>
                </div>
            `).join('');
            statItems.textContent = items.length;
        }

        function renderOrders(orders) {
            let revenue = 0;
            ordersTableBody.innerHTML = orders.map(o => {
                revenue += Number(o.total || 0);
                return `
                    <tr>
                        <td>${o.code}</td>
                        <td>${o.status}</td>
                        <td>₦${Number(o.total).toLocaleString()}</td>
                        <td>${o.channel}</td>
                        <td>${new Date(o.created_at).toLocaleString()}</td>
                    </tr>
                `;
            }).join('');
            statOrders.textContent = orders.length;
            statRevenue.textContent = '₦' + revenue.toLocaleString();
        }

        async function loadCategories() {
            try {
                const res = await safeRequest('/api/categories');
                const data = await res.json();
                renderCategories(data);
            } catch (e) {
                console.error(e);
                categoryList.innerHTML = '<div class="muted">Could not load categories.</div>';
            }
        }

        async function loadMenu() {
            try {
                const res = await safeRequest('/api/menu-items');
                const data = await res.json();
                renderMenu(data);
            } catch (e) {
                console.error(e);
                menuList.innerHTML = '<div class="muted">Could not load menu items.</div>';
            }
        }

        async function loadOrders() {
            try {
                const res = await safeRequest('/api/orders');
                const payload = await res.json();
                const data = payload.data || payload; // paginate or flat
                renderOrders(data);
            } catch (e) {
                console.error(e);
                ordersTableBody.innerHTML = '<tr><td colspan="5">Could not load orders.</td></tr>';
            }
        }

        async function loadUsers() {
            if (!usersList) return;
            try {
                const res = await safeRequest('/api/users');
                const data = await res.json();
                renderUsers(data);
            } catch (e) {
                console.error(e);
                usersList.innerHTML = '<div class="muted">Could not load users.</div>';
            }
        }

        function renderUsers(users) {
            if (!usersList) return;
            usersList.innerHTML = users.map(u => `
                <div class="item" style="align-items:flex-start;">
                    <div>
                        <h4>${u.name}</h4>
                        <div class="muted">${u.email}</div>
                        <div class="row" style="gap:6px;margin-top:6px;">
                            <span class="pill" style="border-color:${u.is_active ? '#bbf7d0' : '#fca5a5'};color:${u.is_active ? '#166534' : '#b91c1c'}">
                                ${u.is_active ? 'Active' : 'Pending'}
                            </span>
                            <span class="pill">Role: ${u.role || 'staff'}</span>
                        </div>
                    </div>
                    <div class="row" style="gap:6px;">
                        <select onchange="updateUserRole(${u.id}, this.value)" value="${u.role || 'staff'}">
                            ${roles.map(r => `<option value="${r}" ${r === (u.role || 'staff') ? 'selected' : ''}>${r}</option>`).join('')}
                        </select>
                        <button class="btn-ghost" onclick="toggleUserActive(${u.id}, ${u.is_active ? 'false' : 'true'})">
                            ${u.is_active ? 'Deactivate' : 'Approve'}
                        </button>
                        <button class="btn-ghost" onclick="deleteUser(${u.id})">Delete</button>
                    </div>
                </div>
            `).join('');
        }

        function setPosStatus(message, tone = 'muted') {
            if (!posScanStatus) return;
            posScanStatus.textContent = message;
            posScanStatus.style.color = tone === 'error' ? '#b91c1c' : 'rgba(0,0,0,0.6)';
        }

        function renderPosCart() {
            if (!posCartList || !posCartTotal) return;
            if (!posCart.length) {
                posCartList.innerHTML = '<div class="item">Cart is empty.</div>';
                posCartTotal.textContent = '₦0';
                return;
            }

            let total = 0;
            posCartList.innerHTML = posCart.map((item, index) => {
                const line = item.price * item.qty;
                total += line;
                return `
                    <div class="item" style="align-items:center;">
                        <div>
                            <h4>${item.name}</h4>
                            <div class="row" style="gap:6px;">
                                <span class="pill">Barcode: ${item.barcode || 'n/a'}</span>
                                <span class="pill">₦${Number(item.price).toLocaleString()} × ${item.qty}</span>
                            </div>
                        </div>
                        <div class="row" style="gap:6px;">
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
            posLookupResult.style.display = 'flex';
            posLookupResult.innerHTML = `
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <h4 style="margin:0;">${item.name}</h4>
                    <div class="row" style="gap:6px; flex-wrap:wrap;">
                        <span class="pill">Price: ₦${Number(item.price).toLocaleString()}</span>
                        <span class="pill">Barcode: ${item.barcode}</span>
                        <span class="pill">${item.category?.name || 'Uncategorized'}</span>
                    </div>
                    <button class="btn-primary" data-add-pos-item>Add to cart</button>
                </div>
            `;
        }

        function showLookupError(message) {
            if (!posLookupResult) return;
            lastLookup = null;
            posLookupResult.style.display = 'flex';
            posLookupResult.innerHTML = `<div class="muted">${message}</div>`;
        }

        async function lookupBarcode(barcode) {
            if (!barcode) return;
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
                setPosStatus('Found. Price pulled live; add to cart.');
            } catch (e) {
                showLookupError('Lookup failed. Check connection.');
                setPosStatus('Lookup failed.', 'error');
            } finally {
                posBarcodeInput?.select();
            }
        }

        categoryForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = new FormData(categoryForm);
            const submitBtn = categoryForm.querySelector('button[type="submit"]');
            await runAction(submitBtn, async () => {
                await safeRequest('/api/categories', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name: form.get('name'),
                        description: form.get('description') || null,
                        is_active: true,
                    }),
                });
                categoryForm.reset();
                await loadCategories();
            });
        });

        menuForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = new FormData(menuForm);
            if (form.get('image')?.size === 0) {
                form.delete('image');
            }
            const submitBtn = menuForm.querySelector('button[type="submit"]');
            await runAction(submitBtn, async () => {
                await safeRequest('/api/menu-items', { method: 'POST', body: form });
                menuForm.reset();
                await Promise.all([loadMenu(), loadCategories()]);
            });
        });

        posLookupResult?.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-add-pos-item]');
            if (!btn || !lastLookup) return;
            addToPosCart(lastLookup);
        });

        posBarcodeInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const code = e.target.value.trim();
                if (!code) return;
                lookupBarcode(code);
            }
        });

        window.toggleSoldOut = async (id, btn) => {
            await runAction(btn, async () => {
                await safeRequest(`/api/menu-items/${id}/toggle-sold-out`, { method: 'POST' });
                await loadMenu();
            });
        };

        window.deleteCategory = async (id, btn) => {
            if (!confirm('Delete this category? Items will remain uncategorized.')) return;
            await runAction(btn, async () => {
                await safeRequest(`/api/categories/${id}`, { method: 'DELETE' });
                await Promise.all([loadCategories(), loadMenu()]);
            });
        };

        window.deleteMenuItem = async (id, btn) => {
            if (!confirm('Delete this menu item?')) return;
            await runAction(btn, async () => {
                await safeRequest(`/api/menu-items/${id}`, { method: 'DELETE' });
                await loadMenu();
            });
        };

        window.copyBarcode = async (code) => {
            if (!code) {
                alert('This item does not have a barcode yet.');
                return;
            }
            try {
                if (navigator?.clipboard?.writeText) {
                    await navigator.clipboard.writeText(code);
                    alert('Barcode copied to clipboard.');
                } else {
                    throw new Error('Clipboard unavailable');
                }
            } catch (e) {
                alert(`Barcode: ${code}`);
            }
        };

        window.printBarcode = (code, name = '') => {
            if (!code) {
                alert('This item does not have a barcode yet.');
                return;
            }
            const safeCode = String(code);
            const safeName = String(name || '');
            const popup = window.open('', '_blank', 'width=520,height=420');
            if (!popup) {
                alert('Please allow pop-ups to print the barcode.');
                return;
            }
            const printable = `
<!doctype html>
<html>
<head>
    <title>Barcode ${safeCode}</title>
    <style>
        body { margin: 0; font-family: 'Manrope', system-ui, -apple-system, sans-serif; display:flex; align-items:center; justify-content:center; height:100vh; background:#f7f1e7; }
        .sheet { background:#fff; padding:28px; border:1px solid #e5e7eb; border-radius:16px; box-shadow:0 18px 48px rgba(0,0,0,0.12); text-align:center; width:340px; }
        h2 { margin:0 0 8px; font-size:18px; }
        .meta { color:#6b7280; margin:0 0 12px; }
        svg { width:100%; }
        @media print { body { background:#fff; } .sheet { box-shadow:none; border:1px solid #000; } }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\\/script>
</head>
<body>
    <div class="sheet">
        <h2 id="printName"></h2>
        <p class="meta">Barcode: ${safeCode}</p>
        <svg id="printBarcode"></svg>
    </div>
    <script>
        const code = ${JSON.stringify(safeCode)};
        const name = ${JSON.stringify(safeName)} || 'Menu Item';
        window.onload = () => {
            document.getElementById('printName').textContent = name;
            JsBarcode('#printBarcode', code, { format: 'code128', width: 2, height: 80, displayValue: true, fontSize: 14 });
            setTimeout(() => { window.focus(); window.print(); }, 200);
        };
    <\\/script>
</body>
</html>`;
            popup.document.write(printable);
            popup.document.close();
        };

        window.regenBarcode = async (id, btn) => {
            if (!confirm('Regenerate barcode? Printed labels with the old code will stop working.')) return;
            await runAction(btn, async () => {
                await safeRequest(`/api/menu-items/${id}/regenerate-barcode`, { method: 'POST' });
                await loadMenu();
            });
        };

        window.editMenuItem = async (id, item, btn) => {
            const name = prompt('Name', item.name);
            if (name === null || name.trim() === '') return;
            const priceInput = prompt('Price (NGN)', item.price);
            const price = Number(priceInput);
            if (Number.isNaN(price)) {
                alert('Invalid price');
                return;
            }
            const description = prompt('Description', item.description || '') ?? '';
            const category_id = prompt('Category ID (leave blank to unset)', item.category_id || '') || null;
            await runAction(btn, async () => {
                await safeRequest(`/api/menu-items/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name,
                        price,
                        description: description || null,
                        category_id: category_id || null,
                    }),
                });
                await loadMenu();
            });
        };

        window.updateUserRole = async (id, role) => {
            await runAction(null, async () => {
                await safeRequest(`/api/users/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ role }),
                });
                await loadUsers();
            });
        };

        window.toggleUserActive = async (id, active) => {
            await runAction(null, async () => {
                await safeRequest(`/api/users/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ is_active: active }),
                });
                await loadUsers();
            });
        };

        window.deleteUser = async (id) => {
            if (!confirm('Delete this user account?')) return;
            await runAction(null, async () => {
                await safeRequest(`/api/users/${id}`, { method: 'DELETE' });
                await loadUsers();
            });
        };

        async function init() {
            await checkHealth();
            renderPosCart();
            posBarcodeInput?.focus();
            await Promise.all([loadCategories(), loadMenu(), loadOrders(), loadUsers()]);

            // Live refresh every 8 seconds
            const refresh = async () => {
                if (isInteracting) return;
                await Promise.all([loadCategories(), loadMenu(), loadOrders(), loadUsers()]);
            };
            setInterval(refresh, 8000);

            // Pause refresh while typing or focusing inputs
            document.addEventListener('focusin', markInteracting);
            document.addEventListener('input', markInteracting);
            document.addEventListener('mousedown', markInteracting);
        }

        init();
    </script>
</body>
</html>
