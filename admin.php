<?php
session_start();
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Already logged in as admin
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: admin_panel.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — PW Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Exo+2:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --admin-primary: #8b5cf6;
  --admin-dark: #7c3aed;
  --admin-glow: rgba(139,92,246,0.4);
  --bg: #070710;
  --card: rgba(255,255,255,0.03);
  --border: rgba(139,92,246,0.2);
  --text: #e8e8e8;
  --text-dim: #6b6b85;
}
* { margin:0; padding:0; box-sizing:border-box; }
body {
  font-family:'Exo 2',sans-serif;
  background:var(--bg);
  color:var(--text); min-height:100vh;
  display:flex; align-items:center; justify-content:center;
  overflow:hidden;
}
body::before {
  content:''; position:fixed; top:0; left:0; right:0; bottom:0;
  background:
    radial-gradient(ellipse at 30% 30%, rgba(139,92,246,0.07) 0%, transparent 50%),
    radial-gradient(ellipse at 70% 70%, rgba(59,130,246,0.05) 0%, transparent 50%);
  z-index:-1;
}
/* Animated grid */
.grid-bg {
  position:fixed; top:0; left:0; right:0; bottom:0; z-index:-1;
  background-image:
    linear-gradient(rgba(139,92,246,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(139,92,246,0.04) 1px, transparent 1px);
  background-size:50px 50px;
  animation:gridMove 20s linear infinite;
}
@keyframes gridMove {
  0%{background-position:0 0;}
  100%{background-position:50px 50px;}
}
.admin-wrapper {
  width:100%; max-width:420px; padding:20px;
  animation:fadeIn 0.5s ease;
}
@keyframes fadeIn {
  from{opacity:0;transform:scale(0.97);}
  to{opacity:1;transform:scale(1);}
}
.admin-logo {
  text-align:center; margin-bottom:36px;
}
.shield-icon {
  width:70px; height:70px; margin:0 auto 16px;
  background:linear-gradient(135deg,var(--admin-primary),#3b82f6);
  border-radius:20px; display:flex; align-items:center; justify-content:center;
  font-size:30px; box-shadow:0 0 30px var(--admin-glow);
  animation:shieldPulse 3s ease-in-out infinite;
}
@keyframes shieldPulse {
  0%,100%{box-shadow:0 0 20px var(--admin-glow);}
  50%{box-shadow:0 0 50px var(--admin-glow);}
}
.admin-logo h1 {
  font-family:'Rajdhani',sans-serif; font-size:28px; font-weight:700;
  background:linear-gradient(135deg,#fff,var(--admin-primary));
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
  letter-spacing:3px;
}
.admin-logo p { color:var(--text-dim); font-size:13px; margin-top:4px; }
.card {
  background:var(--card); border:1px solid var(--border);
  border-radius:20px; padding:36px;
  backdrop-filter:blur(20px);
  box-shadow:0 20px 60px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.03);
  position:relative; overflow:hidden;
}
.card::before {
  content:''; position:absolute; top:0; left:0; right:0; height:2px;
  background:linear-gradient(90deg,transparent,var(--admin-primary),#3b82f6,transparent);
}
.warning-badge {
  display:flex; align-items:center; gap:10px;
  padding:10px 14px; background:rgba(251,191,36,0.05);
  border:1px solid rgba(251,191,36,0.2); border-radius:10px; margin-bottom:28px;
  color:#fbbf24; font-size:12px;
}
.form-title { font-family:'Rajdhani',sans-serif; font-size:20px; font-weight:600; color:#fff; margin-bottom:24px; }
.input-group { margin-bottom:20px; }
.input-group label {
  display:block; font-size:11px; font-weight:700; letter-spacing:1.5px;
  color:var(--text-dim); text-transform:uppercase; margin-bottom:8px;
}
.input-wrap { position:relative; }
.input-wrap i {
  position:absolute; left:16px; top:50%; transform:translateY(-50%);
  color:var(--text-dim); font-size:15px; transition:color 0.3s;
}
.input-wrap input {
  width:100%; padding:14px 14px 14px 46px;
  background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.07);
  border-radius:12px; color:#fff; font-family:'Exo 2',sans-serif; font-size:15px;
  outline:none; transition:all 0.3s;
}
.input-wrap input:focus {
  border-color:var(--admin-primary);
  background:rgba(139,92,246,0.05);
  box-shadow:0 0 0 3px rgba(139,92,246,0.1);
}
.input-wrap input:focus ~ i { color:var(--admin-primary); }
.input-wrap .toggle-pw {
  position:absolute; right:14px; top:50%; transform:translateY(-50%);
  background:transparent; border:none; color:var(--text-dim);
  cursor:pointer; font-size:14px; padding:4px; transition:color 0.3s;
}
.input-wrap .toggle-pw:hover { color:var(--admin-primary); }
.btn-admin {
  width:100%; padding:15px;
  background:linear-gradient(135deg,var(--admin-primary),var(--admin-dark));
  border:none; border-radius:12px; color:#fff;
  font-family:'Rajdhani',sans-serif; font-size:16px; font-weight:700;
  letter-spacing:2px; text-transform:uppercase; cursor:pointer;
  transition:all 0.3s; position:relative; overflow:hidden;
}
.btn-admin:hover { transform:translateY(-2px); box-shadow:0 8px 25px var(--admin-glow); }
.btn-admin.loading { pointer-events:none; opacity:0.7; }
.alert {
  padding:12px 16px; border-radius:10px; font-size:13px;
  margin-bottom:16px; display:none; align-items:center; gap:8px;
}
.alert.show { display:flex; }
.alert-error { background:rgba(255,71,87,0.1); border:1px solid rgba(255,71,87,0.3); color:#ff4757; }
.spinner {
  display:inline-block; width:16px; height:16px;
  border:2px solid rgba(255,255,255,0.3);
  border-top-color:#fff; border-radius:50%;
  animation:spin 0.7s linear infinite; vertical-align:middle; margin-right:8px;
}
@keyframes spin { to{transform:rotate(360deg);} }
.attempts-info { text-align:center; margin-top:16px; font-size:12px; color:var(--text-dim); }
.back-link {
  display:block; text-align:center; margin-top:20px;
  color:var(--text-dim); font-size:12px; text-decoration:none;
  transition:color 0.3s;
}
.back-link:hover { color:var(--admin-primary); }
</style>
</head>
<body>
<div class="grid-bg"></div>
<div class="admin-wrapper">
  <div class="admin-logo">
    <div class="shield-icon"><i class="fa fa-shield-halved"></i></div>
    <h1>ADMIN PANEL</h1>
    <p>Restricted Access — Authorized Personnel Only</p>
  </div>

  <div class="card">
    <div class="warning-badge">
      <i class="fa fa-triangle-exclamation"></i>
      <span>This area is monitored. Unauthorized access attempts are logged.</span>
    </div>

    <div class="form-title">Administrator Login</div>

    <div class="alert alert-error" id="alertBox">
      <i class="fa fa-circle-xmark"></i>
      <span id="alertMsg">Invalid credentials</span>
    </div>

    <div class="input-group">
      <label>Username</label>
      <div class="input-wrap">
        <i class="fa fa-user"></i>
        <input type="text" id="adminUser" placeholder="Enter admin username" autocomplete="off" spellcheck="false">
      </div>
    </div>

    <div class="input-group">
      <label>Password</label>
      <div class="input-wrap">
        <i class="fa fa-lock"></i>
        <input type="password" id="adminPass" placeholder="Enter admin password" autocomplete="off" id="adminPassInput">
        <button class="toggle-pw" type="button" onclick="togglePw()" id="toggleBtn">
          <i class="fa fa-eye" id="eyeIcon"></i>
        </button>
      </div>
    </div>

    <button class="btn-admin" id="loginBtn" onclick="adminLogin()">
      <i class="fa fa-right-to-bracket"></i> AUTHENTICATE
    </button>
    <div class="attempts-info" id="attemptsInfo"></div>
  </div>

  <a href="index.php" class="back-link">← Back to User Portal</a>
</div>

<script>
(function(){
  document.addEventListener('contextmenu',function(e){e.preventDefault();});
  document.addEventListener('keydown',function(e){
    if(e.key==='F12'||(e.ctrlKey&&e.shiftKey&&['I','J','C'].includes(e.key.toUpperCase()))||(e.ctrlKey&&e.key.toUpperCase()==='U')){e.preventDefault();}
  });
})();

var failCount = parseInt(localStorage.getItem('admin_fails')||'0');
var lockUntil  = parseInt(localStorage.getItem('admin_lock_until')||'0');
var MAX_ATTEMPTS = 5;

function togglePw() {
  var inp = document.getElementById('adminPass');
  var icon = document.getElementById('eyeIcon');
  if (inp.type === 'password') {
    inp.type = 'text'; icon.className = 'fa fa-eye-slash';
  } else {
    inp.type = 'password'; icon.className = 'fa fa-eye';
  }
}

function showAlert(msg) {
  var box = document.getElementById('alertBox');
  var msgEl = document.getElementById('alertMsg');
  msgEl.textContent = msg;
  box.classList.add('show');
}
function clearAlert() { document.getElementById('alertBox').classList.remove('show'); }

function adminLogin() {
  // Check lockout
  if (Date.now() < lockUntil) {
    var sec = Math.ceil((lockUntil - Date.now()) / 1000);
    showAlert('Too many failed attempts. Try again in ' + sec + 's');
    return;
  }

  var user = document.getElementById('adminUser').value.trim();
  var pass = document.getElementById('adminPass').value.trim();
  if (!user || !pass) { showAlert('Please enter username and password'); return; }

  clearAlert();
  var btn = document.getElementById('loginBtn');
  btn.innerHTML = '<span class="spinner"></span> AUTHENTICATING...';
  btn.classList.add('loading');

  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'admin_api.php', true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.onload = function() {
    btn.classList.remove('loading');
    try {
      var resp = JSON.parse(xhr.responseText);
      if (resp.success) {
        btn.innerHTML = '<i class="fa fa-check-circle"></i> ACCESS GRANTED';
        localStorage.removeItem('admin_fails');
        localStorage.removeItem('admin_lock_until');
        setTimeout(function(){ window.location.href = 'admin_panel.php'; }, 800);
      } else {
        failCount++;
        localStorage.setItem('admin_fails', failCount);
        var remaining = MAX_ATTEMPTS - failCount;
        if (remaining <= 0) {
          lockUntil = Date.now() + 300000; // 5 min
          localStorage.setItem('admin_lock_until', lockUntil);
          showAlert('Account locked for 5 minutes after too many failures');
        } else {
          showAlert((resp.message || 'Invalid credentials') + ' — ' + remaining + ' attempts remaining');
        }
        btn.innerHTML = '<i class="fa fa-right-to-bracket"></i> AUTHENTICATE';
      }
    } catch(e) {
      btn.innerHTML = '<i class="fa fa-right-to-bracket"></i> AUTHENTICATE';
      showAlert('Server error. Try again.');
    }
  };
  xhr.onerror = function() {
    btn.classList.remove('loading');
    btn.innerHTML = '<i class="fa fa-right-to-bracket"></i> AUTHENTICATE';
    showAlert('Network error.');
  };
  xhr.send(JSON.stringify({ action: 'admin_login', username: user, password: pass }));
}

document.addEventListener('keypress', function(e) {
  if (e.key === 'Enter') adminLogin();
});
</script>
</body>
</html>
