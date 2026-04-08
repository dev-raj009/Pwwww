<?php
session_start();
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (!isset($_SESSION['pw_token']) || !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userPhone  = $_SESSION['user_phone'] ?? 'Unknown';
$userId     = $_SESSION['user_id'] ?? '';
$loginMethod= $_SESSION['login_method'] ?? 'mobile';
$loginTime  = $_SESSION['login_time'] ?? time();
$token      = $_SESSION['pw_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Dashboard — PW Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --primary: #ff6b35;
  --primary-dark: #e55a28;
  --gold: #f5a623;
  --bg: #0a0a14;
  --bg2: #0d0d1e;
  --card: rgba(255,255,255,0.04);
  --border: rgba(255,107,53,0.15);
  --text: #e8e8e8;
  --text-dim: #7a7a90;
  --success: #00d67f;
  --error: #ff4757;
  --info: #00b4d8;
  --sidebar-w: 260px;
}
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Exo 2',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; overflow-x:hidden; }
body::before {
  content:'';position:fixed;top:0;left:0;right:0;bottom:0;
  background:radial-gradient(ellipse at 10% 20%,rgba(255,107,53,0.06) 0%,transparent 50%);
  z-index:-1; pointer-events:none;
}
/* ====== SIDEBAR ====== */
.sidebar {
  position:fixed; left:0; top:0; bottom:0; width:var(--sidebar-w);
  background:rgba(10,10,20,0.95);
  border-right:1px solid var(--border);
  display:flex; flex-direction:column;
  z-index:100; transition:transform 0.3s ease;
  backdrop-filter:blur(20px);
}
.sidebar-logo {
  padding:28px 24px;
  border-bottom:1px solid var(--border);
  display:flex; align-items:center; gap:12px;
}
.sidebar-logo .logo-icon {
  width:44px; height:44px;
  background:linear-gradient(135deg,var(--primary),var(--gold));
  border-radius:12px; display:flex; align-items:center;
  justify-content:center; font-size:20px;
  box-shadow:0 0 20px rgba(255,107,53,0.4);
}
.sidebar-logo h2 {
  font-family:'Rajdhani',sans-serif; font-size:22px; font-weight:700;
  background:linear-gradient(135deg,#fff,var(--gold));
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
}
.sidebar-logo p { font-size:11px; color:var(--text-dim); margin-top:2px; }
.sidebar-nav { flex:1; padding:20px 0; overflow-y:auto; }
.nav-section-label {
  font-size:10px; font-weight:700; letter-spacing:2px;
  color:var(--text-dim); text-transform:uppercase;
  padding:0 24px; margin:16px 0 8px;
}
.nav-item {
  display:flex; align-items:center; gap:12px;
  padding:12px 24px; color:var(--text-dim);
  text-decoration:none; font-size:14px; font-weight:500;
  transition:all 0.3s; cursor:pointer; border:none;
  background:transparent; width:100%; text-align:left;
  position:relative;
}
.nav-item:hover, .nav-item.active {
  color:#fff; background:rgba(255,107,53,0.08);
}
.nav-item.active::before {
  content:''; position:absolute; left:0; top:50%;
  transform:translateY(-50%);
  width:3px; height:60%; background:var(--primary);
  border-radius:0 3px 3px 0;
}
.nav-item i { width:18px; font-size:16px; }
.sidebar-footer { padding:20px 24px; border-top:1px solid var(--border); }
.user-card {
  display:flex; align-items:center; gap:10px;
  padding:12px; background:var(--card);
  border:1px solid var(--border); border-radius:12px; margin-bottom:12px;
}
.user-avatar {
  width:38px; height:38px; border-radius:10px;
  background:linear-gradient(135deg,var(--primary),var(--gold));
  display:flex; align-items:center; justify-content:center;
  font-size:16px; font-weight:700; flex-shrink:0;
}
.user-info { min-width:0; }
.user-info .phone { font-size:13px; font-weight:600; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.user-info .uid { font-size:10px; color:var(--text-dim); font-family:monospace; }
.logout-btn {
  width:100%; padding:10px; background:rgba(255,71,87,0.1);
  border:1px solid rgba(255,71,87,0.2); border-radius:10px;
  color:#ff4757; font-family:'Exo 2',sans-serif; font-size:13px;
  font-weight:600; cursor:pointer; transition:all 0.3s;
  display:flex; align-items:center; justify-content:center; gap:8px;
}
.logout-btn:hover { background:rgba(255,71,87,0.2); }

/* ====== MAIN CONTENT ====== */
.main-content {
  margin-left:var(--sidebar-w);
  min-height:100vh;
  padding:32px;
}
.top-bar {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:32px;
}
.page-title { font-family:'Rajdhani',sans-serif; font-size:28px; font-weight:700; color:#fff; }
.page-title span { color:var(--primary); }
.top-badge {
  display:flex; align-items:center; gap:8px;
  padding:8px 16px; background:var(--card);
  border:1px solid var(--border); border-radius:30px; font-size:13px;
}
.badge-dot { width:8px; height:8px; border-radius:50%; background:var(--success); animation:blink 1.5s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.3;} }

/* ====== INFO CARDS ====== */
.info-grid {
  display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
  gap:16px; margin-bottom:32px;
}
.info-card {
  background:var(--card); border:1px solid var(--border);
  border-radius:16px; padding:20px; position:relative; overflow:hidden;
  transition:all 0.3s; cursor:default;
}
.info-card::before {
  content:''; position:absolute; top:0; left:0; right:0; height:2px;
  background:linear-gradient(90deg,transparent,var(--primary),transparent);
  opacity:0; transition:opacity 0.3s;
}
.info-card:hover { transform:translateY(-3px); border-color:rgba(255,107,53,0.3); }
.info-card:hover::before { opacity:1; }
.info-card .ic-icon {
  width:42px; height:42px; border-radius:12px; margin-bottom:14px;
  display:flex; align-items:center; justify-content:center; font-size:18px;
}
.info-card .ic-label { font-size:11px; color:var(--text-dim); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; }
.info-card .ic-value { font-size:18px; font-weight:700; color:#fff; font-family:'Rajdhani',sans-serif; word-break:break-all; }
.ic-orange { background:rgba(255,107,53,0.15); color:var(--primary); }
.ic-green  { background:rgba(0,214,127,0.15); color:var(--success); }
.ic-blue   { background:rgba(0,180,216,0.15); color:var(--info); }
.ic-gold   { background:rgba(245,166,35,0.15); color:var(--gold); }

/* ====== TOKEN BOX ====== */
.token-box {
  background:var(--card); border:1px solid var(--border);
  border-radius:16px; padding:24px; margin-bottom:28px;
  position:relative;
}
.token-box-title {
  display:flex; align-items:center; gap:10px;
  font-family:'Rajdhani',sans-serif; font-size:18px; font-weight:600;
  color:#fff; margin-bottom:16px;
}
.token-display {
  background:rgba(0,0,0,0.4); border:1px solid rgba(255,255,255,0.08);
  border-radius:10px; padding:14px 16px;
  font-family:monospace; font-size:12px; color:var(--text-dim);
  word-break:break-all; line-height:1.6;
  position:relative; max-height:80px; overflow:hidden;
  transition:max-height 0.4s ease;
}
.token-display.expanded { max-height:200px; }
.token-actions { display:flex; gap:10px; margin-top:12px; flex-wrap:wrap; }
.action-btn {
  padding:8px 16px; border-radius:8px; font-size:12px; font-weight:600;
  cursor:pointer; transition:all 0.3s; display:flex; align-items:center; gap:6px;
  font-family:'Exo 2',sans-serif;
}
.btn-copy { background:rgba(0,180,216,0.1); border:1px solid rgba(0,180,216,0.3); color:var(--info); }
.btn-copy:hover { background:rgba(0,180,216,0.2); }
.btn-expand { background:rgba(255,107,53,0.1); border:1px solid var(--border); color:var(--primary); }
.btn-expand:hover { background:rgba(255,107,53,0.2); }

/* ====== COURSES ====== */
.section-header {
  display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;
}
.section-title {
  font-family:'Rajdhani',sans-serif; font-size:22px; font-weight:700;
  color:#fff; display:flex; align-items:center; gap:10px;
}
.section-title i { color:var(--primary); }
.courses-grid {
  display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:20px;
}
.course-card {
  background:var(--card); border:1px solid var(--border);
  border-radius:16px; padding:22px; transition:all 0.3s;
  position:relative; overflow:hidden;
}
.course-card::after {
  content:''; position:absolute; top:0; right:0;
  width:80px; height:80px;
  background:radial-gradient(circle,rgba(255,107,53,0.08),transparent);
  pointer-events:none;
}
.course-card:hover { transform:translateY(-4px); border-color:rgba(255,107,53,0.35); box-shadow:0 12px 35px rgba(0,0,0,0.4); }
.course-badge {
  display:inline-flex; align-items:center; gap:5px;
  padding:4px 10px; background:rgba(255,107,53,0.1);
  border:1px solid rgba(255,107,53,0.2); border-radius:20px;
  font-size:11px; color:var(--primary); margin-bottom:14px;
}
.course-name { font-family:'Rajdhani',sans-serif; font-size:18px; font-weight:600; color:#fff; margin-bottom:8px; line-height:1.3; }
.course-id { font-size:11px; color:var(--text-dim); font-family:monospace; }
.course-meta { display:flex; gap:12px; margin-top:14px; flex-wrap:wrap; }
.course-meta-item { font-size:11px; color:var(--text-dim); display:flex; align-items:center; gap:4px; }
.course-meta-item i { color:var(--primary); font-size:12px; }

/* Empty state */
.empty-state {
  text-align:center; padding:60px 20px;
  background:var(--card); border:1px solid var(--border); border-radius:16px;
}
.empty-state .empty-icon { font-size:60px; margin-bottom:16px; opacity:0.4; }
.empty-state h3 { font-family:'Rajdhani',sans-serif; font-size:22px; color:var(--text-dim); margin-bottom:8px; }
.empty-state p { color:var(--text-dim); font-size:14px; }

/* Loading */
.loading-state { text-align:center; padding:50px; }
.loading-ring {
  width:50px; height:50px; margin:0 auto 16px;
  border:3px solid rgba(255,107,53,0.15);
  border-top-color:var(--primary); border-radius:50%;
  animation:spin 0.8s linear infinite;
}
@keyframes spin { to{transform:rotate(360deg);} }

/* Toast */
.toast {
  position:fixed; bottom:24px; right:24px;
  padding:12px 20px; border-radius:10px;
  font-size:13px; font-weight:600; z-index:9999;
  display:flex; align-items:center; gap:8px;
  transform:translateY(100px); opacity:0;
  transition:all 0.3s ease; pointer-events:none;
}
.toast.show { transform:translateY(0); opacity:1; }
.toast-success { background:#00d67f; color:#000; }
.toast-error   { background:#ff4757; color:#fff; }
.toast-info    { background:var(--primary); color:#fff; }

/* Mobile sidebar toggle */
.sidebar-toggle {
  display:none; position:fixed; top:16px; left:16px;
  z-index:200; background:var(--primary); border:none;
  color:#fff; width:40px; height:40px; border-radius:10px;
  font-size:18px; cursor:pointer;
}
@media(max-width:900px) {
  .sidebar { transform:translateX(-100%); }
  .sidebar.open { transform:translateX(0); }
  .main-content { margin-left:0; padding:20px 16px; }
  .sidebar-toggle { display:flex; align-items:center; justify-content:center; }
  .top-bar { padding-left:50px; }
  .courses-grid { grid-template-columns:1fr; }
  .info-grid { grid-template-columns:repeat(2,1fr); }
}
</style>
</head>
<body>
<button class="sidebar-toggle" onclick="toggleSidebar()" id="sidebarToggle">
  <i class="fa fa-bars"></i>
</button>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">⚡</div>
    <div>
      <h2>PW PORTAL</h2>
      <p>Student Dashboard</p>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a class="nav-item active" onclick="loadSection('courses')">
      <i class="fa fa-graduation-cap"></i> My Courses
    </a>
    <a class="nav-item" onclick="loadSection('token')">
      <i class="fa fa-key"></i> My Token
    </a>
    <a class="nav-item" onclick="loadSection('profile')">
      <i class="fa fa-user"></i> Profile
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar"><?php echo strtoupper(substr($userPhone, -2)); ?></div>
      <div class="user-info">
        <div class="phone"><?php echo htmlspecialchars($userPhone); ?></div>
        <div class="uid"><?php echo htmlspecialchars($userId); ?></div>
      </div>
    </div>
    <button class="logout-btn" onclick="doLogout()">
      <i class="fa fa-right-from-bracket"></i> Logout
    </button>
  </div>
</aside>

<!-- MAIN CONTENT -->
<main class="main-content">
  <div class="top-bar">
    <div class="page-title">My <span>Dashboard</span></div>
    <div class="top-badge">
      <div class="badge-dot"></div>
      <span><?php echo $loginMethod === 'mobile' ? 'Mobile Login' : 'Token Login'; ?></span>
    </div>
  </div>

  <!-- INFO CARDS -->
  <div class="info-grid">
    <div class="info-card">
      <div class="ic-icon ic-orange"><i class="fa fa-phone"></i></div>
      <div class="ic-label">Mobile Number</div>
      <div class="ic-value"><?php echo htmlspecialchars($userPhone); ?></div>
    </div>
    <div class="info-card">
      <div class="ic-icon ic-gold"><i class="fa fa-id-badge"></i></div>
      <div class="ic-label">User ID</div>
      <div class="ic-value" style="font-size:13px;font-family:monospace"><?php echo htmlspecialchars($userId); ?></div>
    </div>
    <div class="info-card">
      <div class="ic-icon ic-green"><i class="fa fa-clock"></i></div>
      <div class="ic-label">Login Time</div>
      <div class="ic-value" style="font-size:14px"><?php echo date('d M Y, h:i A', $loginTime); ?></div>
    </div>
    <div class="info-card">
      <div class="ic-icon ic-blue"><i class="fa fa-shield-halved"></i></div>
      <div class="ic-label">Auth Method</div>
      <div class="ic-value"><?php echo strtoupper($loginMethod); ?></div>
    </div>
  </div>

  <!-- TOKEN SECTION -->
  <div class="token-box" id="tokenSection">
    <div class="token-box-title"><i class="fa fa-key" style="color:var(--gold)"></i> Your JWT Access Token</div>
    <div class="token-display" id="tokenDisplay"><?php echo htmlspecialchars($token); ?></div>
    <div class="token-actions">
      <button class="action-btn btn-copy" onclick="copyToken()"><i class="fa fa-copy"></i> Copy Token</button>
      <button class="action-btn btn-expand" onclick="toggleToken()"><i class="fa fa-expand-alt" id="expandIcon"></i> <span id="expandTxt">Show Full</span></button>
    </div>
  </div>

  <!-- COURSES SECTION -->
  <div>
    <div class="section-header">
      <div class="section-title"><i class="fa fa-books"></i> Enrolled Courses</div>
      <button class="action-btn btn-expand" onclick="fetchCourses()"><i class="fa fa-rotate"></i> Refresh</button>
    </div>
    <div id="coursesContainer">
      <div class="loading-state">
        <div class="loading-ring"></div>
        <p style="color:var(--text-dim)">Fetching your courses...</p>
      </div>
    </div>
  </div>
</main>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
// ======= SECURITY =======
(function(){
  'use strict';
  document.addEventListener('contextmenu',function(e){e.preventDefault();return false;});
  document.addEventListener('keydown',function(e){
    if(e.key==='F12'||(e.ctrlKey&&e.shiftKey&&['I','J','C'].includes(e.key.toUpperCase()))||(e.ctrlKey&&e.key.toUpperCase()==='U')){
      e.preventDefault();return false;
    }
  });
  var consoleMethods=['log','debug','info','warn','error','table','trace','dir'];
  consoleMethods.forEach(function(m){console[m]=function(){};});
})();

// ======= TOAST =======
function showToast(msg, type) {
  var t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast show toast-' + type;
  clearTimeout(t._timer);
  t._timer = setTimeout(function(){ t.classList.remove('show'); }, 2800);
}

// ======= COPY TOKEN =======
function copyToken() {
  var token = document.getElementById('tokenDisplay').textContent.trim();
  if (navigator.clipboard) {
    navigator.clipboard.writeText(token).then(function(){ showToast('Token copied!','success'); });
  } else {
    var el = document.createElement('textarea');
    el.value = token; document.body.appendChild(el);
    el.select(); document.execCommand('copy'); document.body.removeChild(el);
    showToast('Token copied!','success');
  }
}

function toggleToken() {
  var td = document.getElementById('tokenDisplay');
  var icon = document.getElementById('expandIcon');
  var txt  = document.getElementById('expandTxt');
  if (td.classList.toggle('expanded')) {
    icon.className = 'fa fa-compress-alt'; txt.textContent = 'Collapse';
  } else {
    icon.className = 'fa fa-expand-alt'; txt.textContent = 'Show Full';
  }
}

// ======= FETCH COURSES =======
function fetchCourses() {
  document.getElementById('coursesContainer').innerHTML = '<div class="loading-state"><div class="loading-ring"></div><p style="color:var(--text-dim)">Fetching your courses...</p></div>';

  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'api.php', true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.onload = function() {
    try {
      var resp = JSON.parse(xhr.responseText);
      if (resp.success && resp.data) {
        renderCourses(resp.data);
      } else if (resp.redirect) {
        window.location.href = 'index.php';
      } else {
        document.getElementById('coursesContainer').innerHTML = renderEmpty('Failed to load courses: ' + (resp.message||'Unknown error'));
      }
    } catch(e) {
      document.getElementById('coursesContainer').innerHTML = renderEmpty('Server error. Please try again.');
    }
  };
  xhr.onerror = function() {
    document.getElementById('coursesContainer').innerHTML = renderEmpty('Network error. Check your connection.');
  };
  xhr.send(JSON.stringify({ action: 'get_batches' }));
}

function renderCourses(data) {
  if (!data || data.length === 0) {
    document.getElementById('coursesContainer').innerHTML = '<div class="empty-state"><div class="empty-icon">📚</div><h3>No Courses Found</h3><p>You have not purchased any courses yet.<br>Visit pw.live to enroll.</p></div>';
    return;
  }
  var html = '<div class="courses-grid">';
  data.forEach(function(course) {
    var name    = course.name || 'Unnamed Course';
    var id      = course._id || 'N/A';
    var lang    = course.language || 'N/A';
    var exam    = course.exam || '';
    var startDate = course.startDate ? new Date(course.startDate).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}) : '';
    var subjects  = course.subjects ? course.subjects.length : 0;
    html += '<div class="course-card">';
    html += '<div class="course-badge"><i class="fa fa-bolt"></i>' + (exam || 'Course') + '</div>';
    html += '<div class="course-name">' + escHTML(name) + '</div>';
    html += '<div class="course-id">ID: ' + escHTML(id) + '</div>';
    html += '<div class="course-meta">';
    if (lang) html += '<div class="course-meta-item"><i class="fa fa-language"></i>' + escHTML(lang) + '</div>';
    if (startDate) html += '<div class="course-meta-item"><i class="fa fa-calendar"></i>' + startDate + '</div>';
    if (subjects) html += '<div class="course-meta-item"><i class="fa fa-book"></i>' + subjects + ' Subjects</div>';
    html += '</div></div>';
  });
  html += '</div>';
  document.getElementById('coursesContainer').innerHTML = html;
}

function renderEmpty(msg) {
  return '<div class="empty-state"><div class="empty-icon">⚠️</div><h3>Oops!</h3><p>' + escHTML(msg) + '</p></div>';
}

function escHTML(str) {
  var d = document.createElement('div');
  d.textContent = str; return d.innerHTML;
}

// ======= LOGOUT =======
function doLogout() {
  if (!confirm('Are you sure you want to logout?')) return;
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'api.php', true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.onload = function() { window.location.href = 'index.php'; };
  xhr.onerror = function() { window.location.href = 'index.php'; };
  xhr.send(JSON.stringify({ action: 'logout' }));
}

// ======= SIDEBAR MOBILE =======
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
}

// ======= SESSION WATCHDOG =======
setInterval(function() {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'api.php', true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.onload = function() {
    try {
      var r = JSON.parse(xhr.responseText);
      if (r.success && !r.logged_in) window.location.href = 'index.php';
    } catch(e) {}
  };
  xhr.send(JSON.stringify({ action: 'check_session' }));
}, 60000);

// ======= INIT =======
fetchCourses();
</script>
</body>
</html>
