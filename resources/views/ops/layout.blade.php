<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? "Operations" }} — Global Trading</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#0B1220;
    --ink-2:#131C2E;
    --ink-line:rgba(255,255,255,.09);
    --canvas:#F5F6F8;
    --card:#FFFFFF;
    --line:#E4E7EC;
    --text-900:#0F172A;
    --text-600:#475569;
    --text-500:#64748B;
    --gold:#C99A3D;
    --gold-ink:#7A5A1E;
    --gold-soft:#FBF1DD;
    --ok:#15803D; --ok-soft:#E7F6EC;
    --draft:#B45309; --draft-soft:#FEF3C7;
    --danger:#B91C1C; --danger-soft:#FDECEC;
    --radius:10px;
    font-family:'Inter',system-ui,sans-serif;
  }
  *{box-sizing:border-box;}
  html,body{margin:0;padding:0;background:var(--canvas);color:var(--text-900);}
  body{-webkit-font-smoothing:antialiased;}
  h1,h2,h3{margin:0;font-weight:700;letter-spacing:-0.01em;}
  p{margin:0;}
  button{font-family:inherit;cursor:pointer;}
  input{font-family:inherit;}
  a{color:inherit;text-decoration:none;}

  .shell{ display:flex; width:100%; min-height:100vh; }
  .side{
    width:248px; flex:0 0 248px; background:var(--ink); color:#fff;
    display:flex; flex-direction:column; min-height:100vh;
  }
  .side .brand{ padding:22px 20px 18px; border-bottom:1px solid var(--ink-line); }
  .side .brand .mark{ font-size:15px; font-weight:800; letter-spacing:.02em; }
  .side .brand .mark span{ color:var(--gold); }
  .side .brand .sub{ font-size:11px; color:#7C8598; margin-top:2px; letter-spacing:.03em; }
  .nav{ padding:14px 12px; display:flex; flex-direction:column; gap:2px; flex:1; }
  .nav a{
    display:flex; align-items:center; gap:10px;
    padding:9px 12px; border-radius:8px; font-size:13.5px; font-weight:500;
    color:#AEB6C7; border-left:2px solid transparent;
  }
  .nav a:hover{ background:rgba(255,255,255,.05); color:#fff; }
  .nav a.active{ background:rgba(201,154,61,.12); color:#fff; border-left:2px solid var(--gold); }
  .side .foot{ padding:16px 20px; border-top:1px solid var(--ink-line); font-size:11.5px; color:#6C7488; }

  .main{ flex:1; min-width:0; display:flex; flex-direction:column; }
  .topbar{
    height:60px; flex:0 0 60px; background:var(--card); border-bottom:1px solid var(--line);
    display:flex; align-items:center; justify-content:space-between; padding:0 22px;
  }
  .search{
    display:flex; align-items:center; gap:8px; background:var(--canvas);
    border:1px solid var(--line); border-radius:8px; padding:7px 12px; width:300px; color:var(--text-500);
  }
  .search input{ border:0; background:transparent; outline:0; font-size:13.5px; width:100%; color:var(--text-900); }
  .content{ padding:26px 28px 60px; flex:1; }

  .page-head{ display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:20px; }
  .page-head h1{ font-size:21px; }
  .page-head .desc{ font-size:13px; color:var(--text-500); margin-top:4px; }
  .btn{
    display:inline-flex; align-items:center; gap:7px;
    padding:9px 16px; border-radius:8px; font-size:13.5px; font-weight:600; border:1px solid transparent;
  }
  .btn-gold{ background:var(--gold); color:#241A05; }
  .btn-gold:hover{ background:#B98A2E; }
  .btn-line{ background:#fff; color:var(--text-900); border-color:var(--line); }
  .btn-line:hover{ background:var(--canvas); }
  .btn-ghost{ background:transparent; color:var(--text-600); }
  .btn-ghost:hover{ color:var(--text-900); }
  .btn-sm{ padding:6px 12px; font-size:12.5px; }
  .btn svg{ width:15px; height:15px; }

  .stats{ display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
  .stat{ background:var(--card); border:1px solid var(--line); border-radius:var(--radius); padding:16px 18px; }
  .stat .n{ font-size:24px; font-weight:800; letter-spacing:-0.02em; }
  .stat .l{ font-size:12.5px; color:var(--text-500); margin-top:3px; }
  .stat .n.gold{ color:var(--gold-ink); }

  .card{ background:var(--card); border:1px solid var(--line); border-radius:var(--radius); overflow:hidden; }
  table{ width:100%; border-collapse:collapse; font-size:13.5px; }
  thead th{
    text-align:left; padding:11px 18px; font-size:11.5px; font-weight:600; color:var(--text-500);
    text-transform:uppercase; letter-spacing:.04em; background:#FAFBFC; border-bottom:1px solid var(--line);
  }
  tbody td{ padding:13px 18px; border-bottom:1px solid var(--line); vertical-align:middle; }
  tbody tr:last-child td{ border-bottom:none; }
  tbody tr:hover{ background:#FAFBFC; }
  .cust-id{ font-family:'IBM Plex Mono',monospace; font-size:12px; color:var(--text-500); }
  .cust-name{ font-weight:600; }
  .cust-sub{ font-size:12px; color:var(--text-500); margin-top:1px; }

  .pill{ display:inline-flex; align-items:center; gap:5px; padding:4px 10px 4px 8px; border-radius:999px; font-size:12px; font-weight:600; }
  .pill-draft{ background:var(--draft-soft); color:var(--draft); }
  .pill-active{ background:var(--ok-soft); color:var(--ok); }
  .pill-blocked{ background:var(--danger-soft); color:var(--danger); }

  .row-actions{ display:flex; gap:6px; justify-content:flex-end; }
  .icon-btn{
    width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center;
    border-radius:7px; border:1px solid var(--line); background:#fff; color:var(--text-600);
  }
  .icon-btn:hover{ background:var(--canvas); color:var(--text-900); }
  .icon-btn svg{ width:15px; height:15px; }

  .panel-head{
    display:flex; align-items:center; justify-content:space-between;
    padding:16px 20px; border-bottom:1px solid var(--line);
  }
  .panel-head h2{ font-size:15px; }
  .panel-body{ padding:20px; display:flex; flex-direction:column; gap:18px; }
  .field-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
  .field{ display:flex; flex-direction:column; gap:6px; }
  .field label{ font-size:12.5px; font-weight:600; color:var(--text-600); }
  .field input, .field select{
    padding:9px 11px; border:1px solid var(--line); border-radius:8px; font-size:13.5px; color:var(--text-900); outline:0;
  }
  .field input:focus, .field select:focus{ border-color:var(--gold); box-shadow:0 0 0 3px rgba(201,154,61,.15); }
  .subhead{ font-size:12.5px; font-weight:700; color:var(--text-900); text-transform:uppercase; letter-spacing:.04em; margin-bottom:2px; }

  .contact-row{
    display:grid; grid-template-columns:110px 1fr 1fr 1fr 32px; gap:10px; align-items:end;
    padding-bottom:12px; border-bottom:1px dashed var(--line); margin-bottom:12px;
  }
  .contact-row:last-of-type{ border-bottom:none; margin-bottom:0; padding-bottom:0; }
  .role-tag{
    align-self:center; font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:.03em;
    color:var(--gold-ink); background:var(--gold-soft); padding:5px 8px; border-radius:6px; text-align:center;
  }

  .panel-foot{ display:flex; justify-content:flex-end; gap:10px; padding:16px 20px; border-top:1px solid var(--line); background:#FAFBFC; }
  .hint-strip{
    display:flex; gap:8px; align-items:flex-start; background:var(--gold-soft); border:1px solid #EBDBB2;
    border-radius:8px; padding:10px 12px; font-size:12.5px; color:#5A4212; line-height:1.5;
  }
  .hint-strip svg{ width:15px; height:15px; flex:0 0 auto; margin-top:1px; }

  .subtabs{ display:flex; gap:6px; border-bottom:1px solid var(--line); margin-bottom:4px; }
  .subtab{
    background:none; border:0; padding:9px 4px; margin-right:18px; font-size:13.5px; font-weight:600;
    color:var(--text-500); border-bottom:2px solid transparent; margin-bottom:-1px;
  }
  .subtab.active{ color:var(--text-900); border-bottom-color:var(--gold); }

  .chk-chip{
    display:flex; align-items:center; gap:7px; font-size:13px; padding:7px 12px;
    border:1px solid var(--line); border-radius:999px; background:#fff; color:var(--text-600);
  }
  .chk-chip input{ accent-color:var(--gold); width:14px; height:14px; }

  .timeline-card{ background:var(--card); border:1px solid var(--line); border-radius:var(--radius); padding:22px 26px 6px; }
  .timeline-card h2{ font-size:15px; margin-bottom:20px; }
  .tl{ position:relative; padding-left:6px; }
  .tl-item{ position:relative; padding:0 0 26px 34px; }
  .tl-item::before{
    content:""; position:absolute; left:9px; top:22px; bottom:-4px; width:2px; background:var(--line);
  }
  .tl-item:last-child::before{ display:none; }
  .tl-item.done::before{ background:var(--gold); }
  .tl-dot{
    position:absolute; left:0; top:1px; width:20px; height:20px; border-radius:50%;
    display:flex; align-items:center; justify-content:center; border:2px solid var(--line); background:#fff;
  }
  .tl-item.done .tl-dot{ background:var(--gold); border-color:var(--gold); }
  .tl-item.done .tl-dot svg{ width:11px; height:11px; color:#241A05; }
  .tl-item.current .tl-dot{ border-color:var(--gold); box-shadow:0 0 0 4px rgba(201,154,61,.18); }
  .tl-item.current .tl-dot::after{ content:""; width:8px; height:8px; border-radius:50%; background:var(--gold); }
  .tl-label{ font-size:13.5px; font-weight:600; color:var(--text-900); }
  .tl-date{ font-size:12px; color:var(--text-500); margin-top:2px; }
  .tl-item.current .tl-label{ color:var(--gold-ink); }

  @media (max-width: 980px){
    .field-grid{ grid-template-columns:1fr 1fr; }
    .stats{ grid-template-columns:1fr 1fr; }
  }
</style>
</head>
<body>
<div class="shell">
  <aside class="side">
    <div class="brand"><div class="mark">GLOBAL <span>TRADING</span></div><div class="sub">Operations dashboard</div></div>
    <nav class="nav">
      <a href="/adminhmt">Overview</a>
      <a href="{{ route('ops.customers.index') }}" class="{{ request()->routeIs('ops.customers.*') ? 'active' : '' }}">Customer registration</a>
      <a href="{{ route('ops.orders.companies') }}" class="{{ request()->routeIs('ops.orders.*') ? 'active' : '' }}">Orders</a>
    </nav>
    <div class="foot">Signed in as {{ auth()->user()->name }}</div>
  </aside>
  <div class="main">
    <div class="topbar">
      <div class="search"><input placeholder="Search customers, orders, invoice #..."></div>
      <div style="width:34px;height:34px;border-radius:8px;background:var(--ink);color:var(--gold);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
    </div>
    <div class="content">
      @if (session('status'))
        <div style="background:#E7F6EC;color:#15803D;border:1px solid #bbe6c8;border-radius:8px;padding:10px 14px;font-size:13.5px;margin-bottom:16px;">{{ session('status') }}</div>
      @endif
      {{ $slot }}
    </div>
  </div>
</div>
</body>
</html>