<?php
session_start();
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel — PW Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --primary: #8b5cf6;
  --primary-dark: #7c3aed;
  --accent: #ff6b35;
  --gold: #f5a623;
  --bg: #07070f;
  --bg2: #0d0d1c;
  --card: rgba(255,255,255,0.03);
  --border: rgba(139,92,246,0.15);
  --text: #e8e8e8;
  --text-dim: #6b6b85;
  --success: #00d67f;
  --error: #ff4757;
  --info: #00b4d8;
  --sidebar-w: 270px;
  --glow: 0 0 30px rgba(139,92,246,0.3);
}
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Exo 2',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; overflow-x:hidden; }
body::before {
  content:''; position:fixed; top:0; left:0; right:0; bottom:0;
  background:
    radial-gradient(ellipse at 15% 15%,rgba(139,92,246,0.07) 0%,transparent 45%),
    radial-gradient(ellipse at 85% 85%,rgba(255,107,53,0.04) 0%,transparent 45%);
  z-index:-1; pointer-events:none;
}
.grid-bg {
  position:fixed; top:0; left:0; right:0; bottom:0; z-index:-1;
  background-image:
    linear-gradient(rgba(139,92,246,0.03) 1px,transparent 1px),
    linear-gradient(90deg,rgba(139,92,246,0.03) 1px,transparent 1px);
  background-size:60px 60px;
}

/* ====== SIDEBAR ====== */
.sidebar {
  position:fixed; left:0; top:0; bottom:0; width:var(--sidebar-w);
  background:rgba(7,7,15,0.98);
  border-right:1px solid var(--border);
  display:flex; flex-direction:column; z-index:100;
  backdrop-filter:blur(20px);
}
.s-logo {
  padding:24px 22px; border-bottom:1px solid var(--border);
  display:flex; align-items:center; gap:12px;
}
.s-logo-icon {
  width:46px; height:46px; border-radius:14px;
  background:linear-gradient(135deg,var(--primary),#3b82f6);
  display:flex; align-items:center; justify-content:center;
  font-size:20px; box-shadow:var(--glow);
}
.s-logo h2 {
  font-family:'Rajdhani',sans-serif; font-size:20px; font-weight:700;
  background:linear-gradient(135deg,#fff,var(--primary));
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
}
.s-logo p { font-size:10px; color:var(--text-dim); margin-top:2px; letter-spacing:1px; text-transform:uppercase; }
.s-nav { flex:1; padding:16px 0; overflow-y:auto; }
.s-nav-label {
  font-size:10px; font-weight:700; letter-spacing:2px; color:var(--text-dim);
  text-transform:uppercase; padding:0 22px; margin:14px 0 6px;
}
.s-nav-item {
  display:flex; align-items:center; gap:10px;
  padding:12px 22px; color:var(--text-dim); text-decoration:none;
  font-size:13px; font-weight:500; cursor:pointer; border:none;
  background:transparent; width:100%; text-align:left; transition:all 0.3s;
  position:relative;
}
.s-nav-item:hover, .s-nav-item.active { color:#fff; background:rgba(139,92,246,0.08); }
.s-nav-item.active::before {
  content:''; position:absolute; left:0; top:50%; transform:translateY(-50%);
  width:3px; height:60%; background:var(--primary); border-radius:0 3px 3px 0;
}
.s-nav-item i { width:18px; font-size:15px; }
.s-footer { padding:18px 22px; border-top:1px solid var(--border); }
.admin-badge {
  display:flex; align-items:center; gap:10px;
  padding:12px; background:var(--card); border:1px solid var(--border);
  border-radius:12px; margin-bottom:12px;
}
.admin-avatar {
  width:38px; height:38px; border-radius:10px;
  background:linear-gradient(135deg,var(--primary),#3b82f6);
  display:flex; align-items:center; justify-content:center;
  font-size:18px;
}
.admin-info .name { font-size:14px; font-weight:700; color:#fff; }
.admin-info .role { font-size:10px; color:var(--primary); letter-spacing:1px; text-transform:uppercase; }
.logout-btn {
  width:100%; padding:10px; background:rgba(255,71,87,0.08);
  border:1px solid rgba(255,71,87,0.2); border-radius:10px;
  color:#ff4757; font-family:'Exo 2',sans-serif; font-size:12px;
  font-weight:600; cursor:pointer; transition:all 0.3s;
  display:flex; align-items:center; justify-content:center; gap:8px;
}
.logout-btn:hover { background:rgba(255,71,87,0.15); }

/* ====== MAIN ====== */
.main-area {
  margin-left:var(--sidebar-w);
  min-height:100vh; padding:28px 28px 40px;
}
.topbar {
  display:flex; align-items:center; justify-content:space-between; margin-bottom:28px;
}
.topbar-left h1 {
  font-family:'Rajdhani',sans-serif; font-size:26px; font-weight:700;
  color:#fff;
}
.topbar-left h1 span { color:var(--primary); }
.topbar-left p { color:var(--text-dim); font-size:12px; margin-top:2px; }
.topbar-right { display:flex; align-items:center; gap:12px; }
.live-indicator {
  display:flex; align-items:center; gap:6px;
  padding:6px 14px; background:rgba(0,214,127,0.08);
  border:1px solid rgba(0,214,127,0.2); border-radius:20px;
  font-size:12px; color:var(--success);
}
.live-dot { width:6px; height:6px; border-radius:50%; background:var(--success); animation:blink 1.5s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.3;} }
.time-display { color:var(--text-dim); font-size:13px; font-family:monospace; }

/* ====== SECTION ====== */
.section { display:none; }
.section.active { display:block; animation:fadeIn 0.3s ease; }
@keyframes fadeIn { from{opacity:0;transform:translateY(8px);} to{opacity:1;transform:translateY(0);} }

/* ====== STAT CARDS ====== */
.stats-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(190px,1fr)); gap:16px; margin-bottom:28px; }
.stat-card {
  background:var(--card); border:1px solid var(--border);
  border-radius:16px; padding:22px;
  transition:all 0.3s; position:relative; overflow:hidden;
}
.stat-card::after {
  content:''; position:absolute; bottom:-20px; right:-20px;
  width:80px; height:80px; border-radius:50%;
  opacity:0.08;
}
.stat-card:hover { border-color:rgba(139,92,246,0.35); transform:translateY(-3px); }
.stat-icon {
  width:44px; height:44px; border-radius:12px;
  display:flex; align-items:center; justify-content:center;
  font-size:18px; margin-bottom:16px;
}
.ic-purple { background:rgba(139,92,246,0.15); color:var(--primary); }
.ic-orange { background:rgba(255,107,53,0.15); color:var(--accent); }
.ic-green  { background:rgba(0,214,127,0.15);  color:var(--success); }
.ic-blue   { background:rgba(0,180,216,0.15);  color:var(--info); }
.ic-gold   { background:rgba(245,166,35,0.15); color:var(--gold); }
.ic-red    { background:rgba(255,71,87,0.15);  color:var(--error); }
.stat-label { font-size:11px; color:var(--text-dim); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; }
.stat-value {
  font-family:'Rajdhani',sans-serif; font-size:28px; font-weight:700;
  color:#fff; line-height:1;
}
.stat-sub { font-size:11px; color:var(--text-dim); margin-top:6px; }

/* ====== TABLES ====== */
.table-card {
  background:var(--card); border:1px solid var(--border);
  border-radius:16px; overflow:hidden; margin-bottom:24px;
}
.table-header {
  padding:18px 22px; border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between;
}
.table-title { font-family:'Rajdhani',sans-serif; font-size:18px; font-weight:600; color:#fff; display:flex; align-items:center; gap:10px; }
.table-title i { color:var(--primary); }
.btn-refresh {
  padding:7px 14px; background:rgba(139,92,246,0.1); border:1px solid var(--border);
  color:var(--primary); border-radius:8px; cursor:pointer; font-size:12px;
  font-family:'Exo 2',sans-serif; transition:all 0.3s;
  display:flex; align-items:center; gap:6px;
}
.btn-refresh:hover { background:rgba(139,92,246,0.2); }
.table-wrap { overflow-x:auto; }
table { width:100%; border-collapse:collapse; font-size:13px; }
th {
  padding:12px 18px; text-align:left;
  background:rgba(0,0,0,0.3); color:var(--text-dim);
  font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase;
  border-bottom:1px solid var(--border);
}
td {
  padding:14px 18px; border-bottom:1px solid rgba(255,255,255,0.04);
  vertical-align:middle;
}
tr:last-child td { border-bottom:none; }
tr:hover td { background:rgba(139,92,246,0.04); }
.badge {
  display:inline-flex; align-items:center; gap:4px;
  padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;
}
.badge-mobile { background:rgba(255,107,53,0.12); color:var(--accent); border:1px solid rgba(255,107,53,0.2); }
.badge-token  { background:rgba(0,180,216,0.12); color:var(--info); border:1px solid rgba(0,180,216,0.2); }
.badge-online { background:rgba(0,214,127,0.12); color:var(--success); border:1px solid rgba(0,214,127,0.2); }
.badge-offline{ background:rgba(255,255,255,0.06); color:var(--text-dim); border:1px solid rgba(255,255,255,0.08); }
.mono { font-family:monospace; font-size:11px; color:var(--text-dim); }
.btn-view {
  padding:6px 12px; background:rgba(139,92,246,0.1); border:1px solid var(--border);
  color:var(--primary); border-radius:6px; cursor:pointer; font-size:11px;
  font-family:'Exo 2',sans-serif; transition:all 0.3s;
}
.btn-view:hover { background:rgba(139,92,246,0.2); }

/* ====== JSON Box ====== */
.json-box {
  background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.07);
  border-radius:10px; padding:16px; font-family:monospace; font-size:11px;
  line-height:1.7; overflow-x:auto; color:#a8b4c8;
  max-height:300px; overflow-y:auto;
}
.json-key   { color:#8b5cf6; }
.json-str   { color:#00d67f; }
.json-num   { color:#f5a623; }
.json-bool  { color:#ff6b35; }
.json-null  { color:#ff4757; }

/* ====== MODAL ====== */
.modal-overlay {
  position:fixed; top:0; left:0; right:0; bottom:0;
  background:rgba(0,0,0,0.85); z-index:999;
  display:none; align-items:center; justify-content:center; padding:20px;
  backdrop-filter:blur(8px);
}
.modal-overlay.show { display:flex; }
.modal {
  background:#0d0d1c; border:1px solid var(--border);
  border-radius:20px; width:100%; max-width:720px; max-height:88vh;
  overflow:hidden; display:flex; flex-direction:column;
  animation:modalIn 0.3s ease;
}
@keyframes modalIn { from{transform:scale(0.95);opacity:0;} to{transform:scale(1);opacity:1;} }
.modal-header {
  padding:20px 24px; border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between;
}
.modal-title { font-family:'Rajdhani',sans-serif; font-size:20px; font-weight:700; color:#fff; }
.modal-close {
  background:rgba(255,71,87,0.1); border:1px solid rgba(255,71,87,0.2);
  color:#ff4757; width:36px; height:36px; border-radius:8px;
  cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center;
  transition:all 0.3s;
}
.modal-close:hover { background:rgba(255,71,87,0.2); }
.modal-body { padding:24px; overflow-y:auto; flex:1; }
.detail-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; margin-bottom:20px; }
.detail-item { background:rgba(0,0,0,0.3); border:1px solid var(--border); border-radius:10px; padding:14px; }
.detail-label { font-size:10px; color:var(--text-dim); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; }
.detail-value { font-size:14px; color:#fff; word-break:break-all; font-family:monospace; }

/* ====== Loading ====== */
.loading-row td { text-align:center; padding:30px; color:var(--text-dim); }
.spin-icon { animation:spin 0.8s linear infinite; display:inline-block; }
@keyframes spin { to{transform:rotate(360deg);} }

/* ====== Toast ====== */
.toast {
  position:fixed; bottom:20px; right:20px;
  padding:12px 18px; border-radius:10px; font-size:13px; font-weight:600;
  z-index:9999; transform:translateY(80px); opacity:0; transition:all 0.3s;
  display:flex; align-items:center; gap:8px;
}
.toast.show { transform:translateY(0); opacity:1; }
.toast-success { background:#00d67f; color:#000; }
.toast-error   { background:#ff4757; color:#fff; }
.toast-info    { background:var(--primary); color:#fff; }

/* Mobile */
@media(max-width:900px) {
  .sidebar { transform:translateX(-100%); transition:transform 0.3s; }
  .sidebar.open { transform:translateX(0); }
  .main-area { margin-left:0; padding:16px; }
  .stats-grid { grid-template-columns:repeat(2,1fr); }
  .detail-grid { grid-template-columns:1fr; }
}
.mob-toggle {
  display:none; position:fixed; top:14px; left:14px;
  z-index:200; background:var(--primary); border:none;
  color:#fff; width:40px; height:40px; border-radius:10px;
  font-size:18px; cursor:pointer;
}
@media(max-width:900px) { .mob-toggle { display:flex; align-items:center; justify-content:center; } }
</style>
</head>
<body>
<div class="grid-bg"></div>
<button class="mob-toggle" onclick="toggleSidebar()"><i class="fa fa-bars"></i></button>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="s-logo">
    <div class="s-logo-icon"><i class="fa fa-shield-halved"></i></div>
    <div>
      <h2>ADMIN PANEL</h2>
      <p>PW Portal Control</p>
    </div>
  </div>
  <nav class="s-nav">
    <div class="s-nav-label">Overview</div>
    <button class="s-nav-item active" onclick="showSection('dashboard')" id="nav-dashboard">
      <i class="fa fa-chart-line"></i> Dashboard
    </button>
    <div class="s-nav-label">Management</div>
    <button class="s-nav-item" onclick="showSection('users')" id="nav-users">
      <i class="fa fa-users"></i> All Users
    </button>
    <button class="s-nav-item" onclick="showSection('sessions')" id="nav-sessions">
      <i class="fa fa-clock-rotate-left"></i> Live Sessions
    </button>
  </nav>
  <div class="s-footer">
    <div class="admin-badge">
      <div class="admin-avatar">🛡️</div>
      <div class="admin-info">
        <div class="name">rajdev</div>
        <div class="role">Super Admin</div>
      </div>
    </div>
    <button class="logout-btn" onclick="adminLogout()">
      <i class="fa fa-right-from-bracket"></i> Logout
    </button>
  </div>
</aside>

<!-- MAIN AREA -->
<div class="main-area">
  <!-- Top Bar -->
  <div class="topbar">
    <div class="topbar-left" style="padding-left:0" id="topbarLeft">
      <h1>Admin <span>Dashboard</span></h1>
      <p>Welcome back, rajdev — System monitoring active</p>
    </div>
    <div class="topbar-right">
      <div class="live-indicator"><div class="live-dot"></div> LIVE</div>
      <div class="time-display" id="clockDisplay"></div>
    </div>
  </div>

  <!-- ============ DASHBOARD SECTION ============ -->
  <div class="section active" id="sec-dashboard">
    <div class="stats-grid" id="statsGrid">
      <div class="stat-card"><div class="stat-icon ic-purple"><i class="fa fa-users"></i></div><div class="stat-label">Total Users</div><div class="stat-value" id="stat-total">—</div><div class="stat-sub">Registered accounts</div></div>
      <div class="stat-card"><div class="stat-icon ic-green"><i class="fa fa-circle-dot"></i></div><div class="stat-label">Active Now</div><div class="stat-value" id="stat-active">—</div><div class="stat-sub">Last 10 minutes</div></div>
      <div class="stat-card"><div class="stat-icon ic-orange"><i class="fa fa-calendar-day"></i></div><div class="stat-label">Today's Logins</div><div class="stat-value" id="stat-today">—</div><div class="stat-sub">Last 24 hours</div></div>
      <div class="stat-card"><div class="stat-icon ic-blue"><i class="fa fa-list"></i></div><div class="stat-label">Total Sessions</div><div class="stat-value" id="stat-sessions">—</div><div class="stat-sub">All time</div></div>
      <div class="stat-card"><div class="stat-icon ic-orange"><i class="fa fa-mobile-alt"></i></div><div class="stat-label">Mobile Logins</div><div class="stat-value" id="stat-mobile">—</div><div class="stat-sub">OTP authenticated</div></div>
      <div class="stat-card"><div class="stat-icon ic-blue"><i class="fa fa-key"></i></div><div class="stat-label">Token Logins</div><div class="stat-value" id="stat-token">—</div><div class="stat-sub">Direct JWT</div></div>
    </div>

    <!-- Recent Sessions table -->
    <div class="table-card">
      <div class="table-header">
        <div class="table-title"><i class="fa fa-clock-rotate-left"></i> Recent Login Activity</div>
        <button class="btn-refresh" onclick="loadStats(); loadSessions()"><i class="fa fa-rotate"></i> Refresh</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>User</th><th>Method</th><th>Login Time</th><th>Status</th><th>Token</th>
            </tr>
          </thead>
          <tbody id="recentTbody">
            <tr class="loading-row"><td colspan="5"><i class="fa fa-rotate spin-icon"></i> Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ============ USERS SECTION ============ -->
  <div class="section" id="sec-users">
    <div class="table-card">
      <div class="table-header">
        <div class="table-title"><i class="fa fa-users"></i> Registered Users</div>
        <button class="btn-refresh" onclick="loadUsers()"><i class="fa fa-rotate"></i> Refresh</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>User ID</th><th>Phone</th><th>Method</th><th>First Login</th><th>Last Login</th><th>Total Logins</th><th>IP</th><th>Actions</th>
            </tr>
          </thead>
          <tbody id="usersTbody">
            <tr class="loading-row"><td colspan="8"><i class="fa fa-rotate spin-icon"></i> Loading users...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ============ SESSIONS SECTION ============ -->
  <div class="section" id="sec-sessions">
    <div class="table-card">
      <div class="table-header">
        <div class="table-title"><i class="fa fa-satellite-dish"></i> Live Sessions</div>
        <button class="btn-refresh" onclick="loadSessions()"><i class="fa fa-rotate"></i> Refresh</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>User ID</th><th>Phone</th><th>Method</th><th>Login Time</th><th>Last Active</th><th>Status</th><th>IP</th><th>Token Preview</th>
            </tr>
          </thead>
          <tbody id="sessionsTbody">
            <tr class="loading-row"><td colspan="8"><i class="fa fa-rotate spin-icon"></i> Loading sessions...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ====== USER DETAIL MODAL ====== -->
<div class="modal-overlay" id="userModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modalTitle">User Details</div>
      <button class="modal-close" onclick="closeModal()"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="modalBody">Loading...</div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
// ======= SECURITY =======
(function(){
  document.addEventListener('contextmenu',function(e){e.preventDefault();});
  document.addEventListener('keydown',function(e){
    if(e.key==='F12'||(e.ctrlKey&&e.shiftKey&&['I','J','C'].includes(e.key.toUpperCase()))||(e.ctrlKey&&e.key.toUpperCase()==='U')){e.preventDefault();}
  });
  var c=['log','debug','info','warn','error','table','trace'];
  c.forEach(function(m){console[m]=function(){};});
})();

// ======= CLOCK =======
function updateClock() {
  var now = new Date();
  document.getElementById('clockDisplay').textContent =
    now.toLocaleDateString('en-IN',{day:'2-digit',month:'short'}) + ' ' +
    now.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
setInterval(updateClock, 1000);
updateClock();

// ======= TOAST =======
function showToast(msg,type) {
  var t=document.getElementById('toast');
  t.textContent=msg; t.className='toast show toast-'+type;
  clearTimeout(t._t); t._t=setTimeout(function(){t.classList.remove('show');},2500);
}

// ======= SECTION NAV =======
function showSection(id) {
  document.querySelectorAll('.section').forEach(function(s){s.classList.remove('active');});
  document.querySelectorAll('.s-nav-item').forEach(function(n){n.classList.remove('active');});
  document.getElementById('sec-'+id).classList.add('active');
  document.getElementById('nav-'+id).classList.add('active');
  var titles = { dashboard:'Admin <span style="color:var(--primary)">Dashboard</span>', users:'All <span style="color:var(--primary)">Users</span>', sessions:'Live <span style="color:var(--primary)">Sessions</span>' };
  document.getElementById('topbarLeft').querySelector('h1').innerHTML = titles[id]||id;
  if (id==='users') loadUsers();
  else if (id==='sessions') loadSessions();
}

// ======= API CALL =======
function apiCall(payload, cb) {
  var xhr = new XMLHttpRequest();
  xhr.open('POST','admin_api.php',true);
  xhr.setRequestHeader('Content-Type','application/json');
  xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
  xhr.onload = function() {
    try { cb(null, JSON.parse(xhr.responseText)); }
    catch(e) { cb('Parse error'); }
  };
  xhr.onerror = function() { cb('Network error'); };
  xhr.send(JSON.stringify(payload));
}

// ======= LOAD STATS =======
function loadStats() {
  apiCall({action:'get_stats'}, function(err, resp) {
    if (err || !resp.success) return;
    document.getElementById('stat-total').textContent   = resp.total_users    || 0;
    document.getElementById('stat-active').textContent  = resp.active_now     || 0;
    document.getElementById('stat-today').textContent   = resp.today_logins   || 0;
    document.getElementById('stat-sessions').textContent= resp.total_sessions || 0;
    document.getElementById('stat-mobile').textContent  = resp.mobile_logins  || 0;
    document.getElementById('stat-token').textContent   = resp.token_logins   || 0;
  });
}

// ======= LOAD RECENT SESSIONS (dashboard) =======
function loadSessions() {
  document.getElementById('recentTbody').innerHTML = '<tr class="loading-row"><td colspan="5"><i class="fa fa-rotate spin-icon"></i> Loading...</td></tr>';
  document.getElementById('sessionsTbody').innerHTML = '<tr class="loading-row"><td colspan="8"><i class="fa fa-rotate spin-icon"></i> Loading...</td></tr>';
  apiCall({action:'get_sessions'}, function(err,resp) {
    if (err || !resp.success) {
      document.getElementById('recentTbody').innerHTML = '<tr class="loading-row"><td colspan="5">Failed to load</td></tr>';
      return;
    }
    var sessions = resp.sessions || [];
    // Recent table (dashboard) - top 10
    var html = '';
    var recent = sessions.slice(0,10);
    if (!recent.length) { html = '<tr class="loading-row"><td colspan="5">No sessions yet</td></tr>'; }
    else {
      recent.forEach(function(s) {
        var statusBadge = s.is_online
          ? '<span class="badge badge-online"><i class="fa fa-circle" style="font-size:8px"></i> Online</span>'
          : '<span class="badge badge-offline">Offline</span>';
        var methodBadge = s.method==='mobile'
          ? '<span class="badge badge-mobile"><i class="fa fa-mobile-alt"></i> Mobile</span>'
          : '<span class="badge badge-token"><i class="fa fa-key"></i> Token</span>';
        html += '<tr>';
        html += '<td><span class="mono">'+ esc(s.phone||'—') +'</span></td>';
        html += '<td>'+methodBadge+'</td>';
        html += '<td style="font-size:12px;color:var(--text-dim)">'+ esc(s.login_time||'—') +'</td>';
        html += '<td>'+statusBadge+'</td>';
        html += '<td><span class="mono">'+ esc(s.token_preview||'—') +'</span></td>';
        html += '</tr>';
      });
    }
    document.getElementById('recentTbody').innerHTML = html;

    // Full sessions table
    var html2 = '';
    if (!sessions.length) { html2 = '<tr class="loading-row"><td colspan="8">No sessions yet</td></tr>'; }
    else {
      sessions.forEach(function(s) {
        var statusBadge = s.is_online
          ? '<span class="badge badge-online"><i class="fa fa-circle" style="font-size:8px"></i> Online</span>'
          : '<span class="badge badge-offline">Offline</span>';
        var methodBadge = s.method==='mobile'
          ? '<span class="badge badge-mobile"><i class="fa fa-mobile-alt"></i> Mobile</span>'
          : '<span class="badge badge-token"><i class="fa fa-key"></i> Token</span>';
        html2 += '<tr>';
        html2 += '<td><span class="mono" style="font-size:11px">'+ esc(s.user_id||'—') +'</span></td>';
        html2 += '<td>'+ esc(s.phone||'—') +'</td>';
        html2 += '<td>'+methodBadge+'</td>';
        html2 += '<td style="font-size:12px;color:var(--text-dim)">'+ esc(s.login_time||'—') +'</td>';
        html2 += '<td style="font-size:12px;color:var(--text-dim)">'+ esc(s.last_active||'—') +'</td>';
        html2 += '<td>'+statusBadge+'</td>';
        html2 += '<td><span class="mono" style="font-size:11px">'+ esc(s.ip||'—') +'</span></td>';
        html2 += '<td><span class="mono" style="font-size:11px;color:var(--text-dim)">'+ esc(s.token_preview||'—') +'</span></td>';
        html2 += '</tr>';
      });
    }
    document.getElementById('sessionsTbody').innerHTML = html2;
  });
}

// ======= LOAD USERS =======
function loadUsers() {
  document.getElementById('usersTbody').innerHTML = '<tr class="loading-row"><td colspan="8"><i class="fa fa-rotate spin-icon"></i> Loading users...</td></tr>';
  apiCall({action:'get_users'}, function(err,resp) {
    if (err || !resp.success) {
      document.getElementById('usersTbody').innerHTML = '<tr class="loading-row"><td colspan="8">Failed to load</td></tr>';
      return;
    }
    var users = resp.users || [];
    var html = '';
    if (!users.length) { html = '<tr class="loading-row"><td colspan="8">No users registered yet</td></tr>'; }
    else {
      users.forEach(function(u) {
        var methodBadge = u.login_method==='mobile'
          ? '<span class="badge badge-mobile"><i class="fa fa-mobile-alt"></i> Mobile</span>'
          : '<span class="badge badge-token"><i class="fa fa-key"></i> Token</span>';
        html += '<tr>';
        html += '<td><span class="mono" style="font-size:11px">'+ esc(u.id||'—') +'</span></td>';
        html += '<td><strong>'+ esc(u.phone||'—') +'</strong></td>';
        html += '<td>'+methodBadge+'</td>';
        html += '<td style="font-size:12px;color:var(--text-dim)">'+ esc(u.first_login||'—') +'</td>';
        html += '<td style="font-size:12px;color:var(--text-dim)">'+ esc(u.last_login||'—') +'</td>';
        html += '<td style="text-align:center"><strong style="color:var(--primary)">'+ (u.login_count||1) +'</strong></td>';
        html += '<td><span class="mono" style="font-size:11px">'+ esc(u.ip||'—') +'</span></td>';
        html += '<td><button class="btn-view" onclick="viewUser(\''+ esc(u.id) +'\')"><i class="fa fa-eye"></i> View</button></td>';
        html += '</tr>';
      });
    }
    document.getElementById('usersTbody').innerHTML = html;
  });
}

// ======= USER DETAIL MODAL =======
function viewUser(userId) {
  document.getElementById('userModal').classList.add('show');
  document.getElementById('modalTitle').textContent = 'User Details';
  document.getElementById('modalBody').innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-dim)"><i class="fa fa-rotate spin-icon fa-2x"></i></div>';

  apiCall({action:'get_user_detail', user_id: userId}, function(err, resp) {
    if (err || !resp.success) {
      document.getElementById('modalBody').innerHTML = '<p style="color:var(--error)">Failed to load user details</p>';
      return;
    }
    var u = resp.user;
    var sessions = resp.sessions || [];
    document.getElementById('modalTitle').textContent = u.phone + ' — Details';

    var html = '';
    html += '<div class="detail-grid">';
    html += detailItem('User ID', u.id);
    html += detailItem('Phone', u.phone);
    html += detailItem('Login Method', u.login_method||'mobile');
    html += detailItem('Total Logins', u.login_count||1);
    html += detailItem('First Login', u.first_login||'—');
    html += detailItem('Last Login', u.last_login||'—');
    html += detailItem('IP Address', u.ip||'—');
    html += '</div>';

    // JWT Token
    if (u.full_token) {
      html += '<div style="margin-bottom:18px">';
      html += '<div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px">JWT Access Token</div>';
      html += '<div class="json-box" style="word-break:break-all;">'+ esc(u.full_token) +'</div>';
      html += '<button style="margin-top:8px;padding:6px 14px;background:rgba(0,180,216,0.1);border:1px solid rgba(0,180,216,0.3);color:var(--info);border-radius:6px;cursor:pointer;font-size:12px;" onclick="copyText(\''+ esc(u.full_token) +'\')"><i class="fa fa-copy"></i> Copy Token</button>';
      html += '</div>';
    }

    // Sessions
    html += '<div style="font-family:Rajdhani,sans-serif;font-size:16px;font-weight:600;color:#fff;margin-bottom:12px">Login History ('+sessions.length+')</div>';
    if (sessions.length) {
      html += '<div class="json-box">';
      sessions.slice(0,5).forEach(function(s,i) {
        var obj = {
          session: i+1,
          login_time: s.login_time,
          method: s.method,
          ip: s.ip,
          active: s.active,
          last_active: s.last_active,
        };
        html += syntaxHighlight(JSON.stringify(obj,null,2)) + (i<sessions.length-1?'\n---\n':'');
      });
      html += '</div>';
    } else {
      html += '<p style="color:var(--text-dim);font-size:13px">No session history</p>';
    }
    document.getElementById('modalBody').innerHTML = html;
  });
}
function detailItem(label,value) {
  return '<div class="detail-item"><div class="detail-label">'+esc(label)+'</div><div class="detail-value">'+esc(String(value||'—'))+'</div></div>';
}
function closeModal() {
  document.getElementById('userModal').classList.remove('show');
}
document.getElementById('userModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// ======= JSON SYNTAX HIGHLIGHT =======
function syntaxHighlight(json) {
  return json
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g,
      function(match) {
        var cls='json-num';
        if(/^"/.test(match)) { cls=/: $/.test(match)||/:$/.test(match.replace(/\s/g,''))?'json-key':'json-str'; }
        else if(/true|false/.test(match)) cls='json-bool';
        else if(/null/.test(match)) cls='json-null';
        return '<span class="'+cls+'">'+match+'</span>';
      }
    );
}

// ======= COPY =======
function copyText(text) {
  if (navigator.clipboard) navigator.clipboard.writeText(text).then(function(){ showToast('Copied!','success'); });
  else {
    var el=document.createElement('textarea');
    el.value=text; document.body.appendChild(el); el.select(); document.execCommand('copy'); document.body.removeChild(el);
    showToast('Copied!','success');
  }
}

// ======= LOGOUT =======
function adminLogout() {
  if (!confirm('Logout from admin panel?')) return;
  apiCall({action:'admin_logout'}, function(){ window.location.href='admin.php'; });
}

// ======= SIDEBAR =======
function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }

// ======= ESC HTML =======
function esc(str) {
  var d=document.createElement('div'); d.textContent=String(str||''); return d.innerHTML;
}

// ======= AUTO REFRESH =======
setInterval(function(){ loadStats(); loadSessions(); }, 30000);

// ======= INIT =======
loadStats();
loadSessions();
</script>
</body>
</html>
