<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kitchen | Acie Fraiche</title>
    <link rel="icon" href="/assets/logo.png" type="image/png">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: #f8f5ef;
            color: #0f0b05;
        }
        header {
            display:flex; justify-content:space-between; align-items:center;
            padding:14px 18px; background:#fff; border-bottom:1px solid rgba(0,0,0,0.1);
        }
        main { padding:18px; max-width:960px; margin:0 auto; }
        .card { background:#fff; border:1px solid rgba(0,0,0,0.08); border-radius:14px; padding:16px; box-shadow:0 10px 26px rgba(0,0,0,0.05); }
        .muted { color: rgba(0,0,0,0.65); }
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
            <h2 style="margin-top:0;">Kitchen board</h2>
            <p class="muted">This view is reserved for kitchen staff. A fuller kitchen display (live orders, status updates) can be wired to the orders API.</p>
        </div>
    </main>
</body>
</html>
