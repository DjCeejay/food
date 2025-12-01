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
                    <h2>POS (stub)</h2>
                    <p class="muted">Quick POS entry—will be expanded with cart/payments.</p>
                    <div class="row" style="gap:10px; flex-wrap:wrap;">
                        <button class="btn-primary" onclick="alert('POS flow to be implemented')">Start Order</button>
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
                        <button class="btn-ghost" onclick="deleteCategory(${cat.id})">Delete</button>
                    </div>
                </div>
            `).join('');

            menuCategorySelect.innerHTML = `<option value="">No category</option>` + categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
            statCategories.textContent = categories.length;
        }

        function renderMenu(items) {
            menuList.innerHTML = items.map(item => `
                <div class="item">
                    <div style="display:flex; gap:10px; align-items:center;">
                        ${item.image_url ? `<img class="thumb" src="${item.image_url}" alt="${item.name}">` : ''}
                        <div>
                            <h4>${item.name}</h4>
                            <div class="row">
                                <span class="price">₦${Number(item.price).toLocaleString()}</span>
                                <span class="pill">${item.category?.name || 'Uncategorized'}</span>
                                <span class="pill" style="border-color:${item.is_sold_out ? '#fca5a5' : '#bbf7d0'};color:${item.is_sold_out ? '#b91c1c' : '#166534'}">
                                    ${item.is_sold_out ? 'Sold Out' : 'Available'}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="gap:6px;">
                        <button class="btn-ghost" onclick="toggleSoldOut(${item.id})">${item.is_sold_out ? 'Mark Available' : 'Mark Sold Out'}</button>
                        <button class="btn-ghost" onclick="editMenuItem(${item.id}, ${JSON.stringify(item).replace(/"/g, '&quot;')})">Edit</button>
                        <button class="btn-ghost" onclick="deleteMenuItem(${item.id})">Delete</button>
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
            const res = await apiFetch('/api/categories');
            const data = await res.json();
            renderCategories(data);
        }

        async function loadMenu() {
            const res = await apiFetch('/api/menu-items');
            const data = await res.json();
            renderMenu(data);
        }

        async function loadOrders() {
            const res = await apiFetch('/api/orders');
            const payload = await res.json();
            const data = payload.data || payload; // paginate or flat
            renderOrders(data);
        }

        async function loadUsers() {
            if (!usersList) return;
            const res = await apiFetch('/api/users');
            const data = await res.json();
            renderUsers(data);
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

        categoryForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = new FormData(categoryForm);
            await apiFetch('/api/categories', {
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

        menuForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = new FormData(menuForm);
            if (form.get('image')?.size === 0) {
                form.delete('image');
            }
            await apiFetch('/api/menu-items', { method: 'POST', body: form });
            menuForm.reset();
            await Promise.all([loadMenu(), loadCategories()]);
        });

        window.toggleSoldOut = async (id) => {
            await apiFetch(`/api/menu-items/${id}/toggle-sold-out`, { method: 'POST' });
            await loadMenu();
        };

        window.deleteCategory = async (id) => {
            if (!confirm('Delete this category? Items will remain uncategorized.')) return;
            await apiFetch(`/api/categories/${id}`, { method: 'DELETE' });
            await Promise.all([loadCategories(), loadMenu()]);
        };

        window.deleteMenuItem = async (id) => {
            if (!confirm('Delete this menu item?')) return;
            await apiFetch(`/api/menu-items/${id}`, { method: 'DELETE' });
            await loadMenu();
        };

        window.editMenuItem = async (id, item) => {
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
            await apiFetch(`/api/menu-items/${id}`, {
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
        };

        window.updateUserRole = async (id, role) => {
            await apiFetch(`/api/users/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ role }),
            });
            await loadUsers();
        };

        window.toggleUserActive = async (id, active) => {
            await apiFetch(`/api/users/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ is_active: active }),
            });
            await loadUsers();
        };

        window.deleteUser = async (id) => {
            if (!confirm('Delete this user account?')) return;
            await apiFetch(`/api/users/${id}`, { method: 'DELETE' });
            await loadUsers();
        };

        async function init() {
            await checkHealth();
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
