<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kitchen | Acie Fraiche</title>
    <link rel="icon" href="/assets/logo.png" type="image/png">
    @vite(['resources/js/kitchen.js'])
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: #f8f5ef;
            color: #0f0b05;
        }
        header, .board-header {
            display:flex; justify-content:space-between; align-items:center;
            padding:14px 18px; background:#fff; border-bottom:1px solid rgba(0,0,0,0.1);
        }
        main { padding:18px; max-width:960px; margin:0 auto; }
        .card { background:#fff; border:1px solid rgba(0,0,0,0.08); border-radius:14px; padding:16px; box-shadow:0 10px 26px rgba(0,0,0,0.05); }
        .muted { color: rgba(0,0,0,0.65); }
        .pill { border:1px solid rgba(0,0,0,0.12); border-radius:999px; padding:8px 12px; font-size:12px; display:inline-flex; align-items:center; gap:6px; }
        .stat-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:10px; margin:14px 0; }
        .stat { border:1px dashed rgba(0,0,0,0.08); border-radius:12px; padding:10px; background:#fcfbf9; }
        .orders { display:grid; gap:10px; }
        .order { border:1px solid rgba(0,0,0,0.08); border-radius:12px; padding:12px; background:#fff; box-shadow:0 8px 18px rgba(0,0,0,0.04); }
        .order-header { display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; }
        .badge { padding:6px 8px; border-radius:10px; font-size:12px; background: rgba(255,165,0,0.14); color:#7a4a00; }
        .items { list-style:none; padding:0; margin:8px 0 0; display:grid; gap:6px; }
        .items li { display:flex; justify-content:space-between; }
        .small { font-size:13px; color: rgba(0,0,0,0.65); }
        .highlight { color:#523700; font-weight:700; }
        .empty { border:1px dashed rgba(0,0,0,0.12); border-radius:12px; padding:16px; text-align:center; color:rgba(0,0,0,0.6); }
        .toast { position:fixed; right:18px; bottom:18px; background:#0f0b05; color:#fff; padding:12px 14px; border-radius:12px; box-shadow:0 18px 36px rgba(0,0,0,0.16); display:none; }
    </style>
</head>
<body>
    <header>
        <div>
            <strong>Kitchen · Acie Fraiche</strong>
            <div class="muted">Signed in as {{ auth()->user()->name ?? 'Kitchen User' }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="border:1px solid rgba(0,0,0,0.12); border-radius:10px; padding:10px 12px; background:#fff; cursor:pointer;">Logout</button>
        </form>
    </header>
    <main>
        <div class="card">
            <div class="board-header" style="padding:0 0 10px 0;">
                <div>
                    <h2 style="margin-top:0; margin-bottom:6px;">Kitchen board</h2>
                    <p class="muted" style="margin:0;">Live feed from POS and online orders.</p>
                </div>
                <div id="kitchenConnection" class="pill">Connecting…</div>
            </div>

            <div class="stat-grid">
                <div class="stat">
                    <div class="small">Orders today</div>
                    <div class="highlight" id="kitchenStatCount">0</div>
                </div>
                <div class="stat">
                    <div class="small">Last order</div>
                    <div class="highlight" id="kitchenStatLast">—</div>
                </div>
                <div class="stat">
                    <div class="small">Total value</div>
                    <div class="highlight" id="kitchenStatTotal">₦0</div>
                </div>
            </div>

            <div id="kitchenOrders" class="orders"></div>
            <div id="kitchenEmpty" class="empty" style="display:none;">No orders yet. They will appear here in real time.</div>
        </div>
    </main>
    <div id="kitchenToast" class="toast"></div>
    <script>
        window.initialOrders = @json($initialOrders ?? []);
    </script>
</body>
</html>
