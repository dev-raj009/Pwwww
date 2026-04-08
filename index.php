<?php
session_start();
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com https://cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src \'self\' data: https:;');

// If already logged in, redirect to dashboard
if (isset($_SESSION['pw_token']) && isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PW Portal — Physics Wallah</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --primary: #ff6b35;
  --primary-dark: #e55a28;
  --secondary: #1a1a2e;
  --accent: #16213e;
  --gold: #f5a623;
  --text: #e8e8e8;
  --text-dim: #9a9ab0;
  --card-bg: rgba(255,255,255,0.04);
  --border: rgba(255,107,53,0.2);
  --success: #00d67f;
  --error: #ff4757;
  --glow: 0 0 30px rgba(255,107,53,0.3);
}
* { margin:0; padding:0; box-sizing:border-box; }
body {
  font-family: 'Exo 2', sans-serif;
  background: #0a0a14;
  color: var(--text);
  min-height: 100vh;
  overflow-x: hidden;
}
/* Background animation */
body::before {
  content:'';
  position:fixed; top:0; left:0; right:0; bottom:0;
  background: 
    radial-gradient(ellipse at 20% 20%, rgba(255,107,53,0.08) 0%, transparent 50%),
    radial-gradient(ellipse at 80% 80%, rgba(22,33,62,0.9) 0%, transparent 50%),
    linear-gradient(135deg, #0a0a14 0%, #0d0d1e 50%, #0a0a14 100%);
  z-index:-1;
}
/* Grid pattern */
body::after {
  content:'';
  position:fixed; top:0; left:0; right:0; bottom:0;
  background-image: 
    linear-gradient(rgba(255,107,53,0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,107,53,0.03) 1px, transparent 1px);
  background-size: 60px 60px;
  z-index:-1;
}
.page-wrapper {
  display:flex; min-height:100vh; align-items:center; justify-content:center;
  padding:20px;
}
.login-container {
  width:100%; max-width:480px;
  animation: slideUp 0.6s ease;
}
@keyframes slideUp {
  from { opacity:0; transform:translateY(30px); }
  to   { opacity:1; transform:translateY(0); }
}
.logo-area {
  text-align:center; margin-bottom:40px;
}
.logo-icon {
  width:72px; height:72px;
  background: linear-gradient(135deg, var(--primary), var(--gold));
  border-radius:20px;
  display:inline-flex; align-items:center; justify-content:center;
  font-size:32px; margin-bottom:16px;
  box-shadow: var(--glow);
  animation: pulse 3s ease-in-out infinite;
}
@keyframes pulse {
  0%,100% { box-shadow: 0 0 20px rgba(255,107,53,0.3); }
  50%      { box-shadow: 0 0 40px rgba(255,107,53,0.6); }
}
.logo-area h1 {
  font-family:'Rajdhani',sans-serif;
  font-size:32px; font-weight:700; letter-spacing:2px;
  background: linear-gradient(135deg, #fff, var(--gold));
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
}
.logo-area p { color:var(--text-dim); font-size:14px; margin-top:4px; }
.card {
  background: var(--card-bg);
  border:1px solid var(--border);
  border-radius:20px;
  padding:36px;
  backdrop-filter: blur(20px);
  box-shadow: 0 20px 60px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.05);
  position:relative; overflow:hidden;
}
.card::before {
  content:'';
  position:absolute; top:0; left:0; right:0; height:2px;
  background: linear-gradient(90deg, transparent, var(--primary), var(--gold), transparent);
}
/* Tab switcher */
.tab-switcher {
  display:flex; background:rgba(0,0,0,0.3);
  border-radius:12px; padding:4px; margin-bottom:32px;
  border:1px solid rgba(255,255,255,0.05);
}
.tab-btn {
  flex:1; padding:10px; border:none; background:transparent;
  color:var(--text-dim); font-family:'Exo 2',sans-serif;
  font-size:14px; font-weight:600; cursor:pointer;
  border-radius:10px; transition:all 0.3s ease;
  letter-spacing:0.5px;
}
.tab-btn.active {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color:#fff;
  box-shadow: 0 4px 15px rgba(255,107,53,0.4);
}
/* Form steps */
.form-step { display:none; }
.form-step.active { display:block; }
.step-title {
  font-family:'Rajdhani',sans-serif; font-size:22px; font-weight:600;
  color:#fff; margin-bottom:8px;
}
.step-desc { color:var(--text-dim); font-size:13px; margin-bottom:24px; line-height:1.6; }
/* Input groups */
.input-group {
  position:relative; margin-bottom:20px;
}
.input-group label {
  display:block; font-size:12px; font-weight:600; letter-spacing:1px;
  color:var(--text-dim); text-transform:uppercase; margin-bottom:8px;
}
.input-wrap {
  position:relative; display:flex; align-items:center;
}
.input-wrap i {
  position:absolute; left:16px; color:var(--text-dim); font-size:16px;
  transition: color 0.3s;
}
.input-wrap input {
  width:100%; padding:14px 16px 14px 48px;
  background:rgba(0,0,0,0.4);
  border:1px solid rgba(255,255,255,0.08);
  border-radius:12px; color:#fff;
  font-family:'Exo 2',sans-serif; font-size:15px;
  transition:all 0.3s ease; outline:none;
  letter-spacing:0.5px;
}
.input-wrap input:focus {
  border-color: var(--primary);
  background:rgba(255,107,53,0.05);
  box-shadow: 0 0 0 3px rgba(255,107,53,0.1);
}
.input-wrap input:focus + i,
.input-wrap:focus-within i { color:var(--primary); }
.input-wrap i { z-index:1; }
.input-wrap input:focus ~ i { color: var(--primary); }
/* OTP Input special styling */
.otp-inputs {
  display:flex; gap:10px; justify-content:center; margin:20px 0;
}
.otp-digit {
  width:52px; height:60px;
  background:rgba(0,0,0,0.5);
  border:2px solid rgba(255,255,255,0.1);
  border-radius:12px;
  text-align:center; color:#fff;
  font-size:24px; font-weight:700;
  font-family:'Rajdhani',sans-serif;
  outline:none; transition:all 0.3s;
}
.otp-digit:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(255,107,53,0.2);
  background:rgba(255,107,53,0.05);
}
.otp-digit.filled {
  border-color: var(--success);
  background:rgba(0,214,127,0.05);
}
/* Buttons */
.btn-primary {
  width:100%; padding:15px;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  border:none; border-radius:12px;
  color:#fff; font-family:'Rajdhani',sans-serif;
  font-size:16px; font-weight:700; letter-spacing:2px;
  text-transform:uppercase; cursor:pointer;
  transition:all 0.3s ease; position:relative; overflow:hidden;
}
.btn-primary:hover {
  transform:translateY(-2px);
  box-shadow: 0 8px 25px rgba(255,107,53,0.5);
}
.btn-primary:active { transform:translateY(0); }
.btn-primary.loading { pointer-events:none; opacity:0.7; }
.btn-primary::after {
  content:'';
  position:absolute; top:50%; left:50%;
  width:0; height:0;
  background:rgba(255,255,255,0.2);
  border-radius:50%;
  transform:translate(-50%,-50%);
  transition: width 0.4s, height 0.4s, opacity 0.4s;
}
.btn-primary:active::after { width:200%; height:200%; opacity:0; }
.btn-secondary {
  background:transparent; border:1px solid var(--border);
  color:var(--text-dim); padding:10px 20px;
  border-radius:10px; cursor:pointer; font-size:13px;
  font-family:'Exo 2',sans-serif; transition:all 0.3s;
}
.btn-secondary:hover { border-color:var(--primary); color:var(--primary); }
/* Phone prefix */
.phone-prefix {
  position:absolute; left:48px; color:var(--text-dim);
  font-size:15px; z-index:2; pointer-events:none;
}
.phone-input { padding-left:86px !important; }
/* Alert */
.alert {
  padding:12px 16px; border-radius:10px;
  font-size:13px; margin-bottom:16px; display:none;
  align-items:center; gap:8px;
}
.alert.show { display:flex; }
.alert-error { background:rgba(255,71,87,0.1); border:1px solid rgba(255,71,87,0.3); color:#ff4757; }
.alert-success { background:rgba(0,214,127,0.1); border:1px solid rgba(0,214,127,0.3); color:#00d67f; }
.alert-info { background:rgba(255,107,53,0.1); border:1px solid rgba(255,107,53,0.3); color:var(--primary); }
/* Timer */
.otp-timer {
  text-align:center; margin-top:12px; font-size:13px; color:var(--text-dim);
}
.otp-timer span { color:var(--primary); font-weight:600; }
/* Footer */
.login-footer {
  text-align:center; margin-top:24px;
  color:var(--text-dim); font-size:12px;
}
.login-footer a { color:var(--primary); text-decoration:none; }
/* Spinner */
.spinner {
  display:inline-block; width:18px; height:18px;
  border:2px solid rgba(255,255,255,0.3);
  border-top-color:#fff; border-radius:50%;
  animation:spin 0.7s linear infinite; vertical-align:middle; margin-right:8px;
}
@keyframes spin { to { transform:rotate(360deg); } }
/* Security badge */
.security-badge {
  display:flex; align-items:center; gap:8px;
  padding:10px 14px; background:rgba(0,214,127,0.05);
  border:1px solid rgba(0,214,127,0.15); border-radius:8px;
  color:var(--text-dim); font-size:12px; margin-top:20px;
}
.security-badge i { color:var(--success); }
/* Admin link */
.admin-link {
  display:flex; align-items:center; justify-content:center;
  gap:6px; margin-top:20px;
  color:var(--text-dim); text-decoration:none; font-size:12px;
  transition: color 0.3s;
}
.admin-link:hover { color:var(--primary); }
/* Responsive */
@media(max-width:480px) {
  .card { padding:24px; }
  .otp-digit { width:44px; height:54px; font-size:20px; }
}
</style>
</head>
<body>
<div class="page-wrapper">
  <div class="login-container">
    <div class="logo-area">
      <div class="logo-icon">⚡</div>
      <h1>PW PORTAL</h1>
      <p>Physics Wallah — Student Dashboard</p>
    </div>

    <div class="card">
      <!-- Tab Switcher -->
      <div class="tab-switcher">
        <button class="tab-btn active" onclick="switchTab('mobile')" id="tab-mobile">
          <i class="fa fa-mobile-alt"></i> Mobile OTP
        </button>
        <button class="tab-btn" onclick="switchTab('token')" id="tab-token">
          <i class="fa fa-key"></i> Token Login
        </button>
      </div>

      <!-- Alert Box -->
      <div class="alert" id="alertBox">
        <i class="fa fa-circle-info"></i>
        <span id="alertMsg"></span>
      </div>

      <!-- ========== MOBILE OTP SECTION ========== -->
      <div class="form-step active" id="step-mobile-phone">
        <div class="step-title">Enter Mobile Number</div>
        <div class="step-desc">We'll send you a 4-digit OTP on your registered mobile number</div>
        <div class="input-group">
          <label>Mobile Number</label>
          <div class="input-wrap">
            <i class="fa fa-phone" style="left:16px"></i>
            <span class="phone-prefix">+91</span>
            <input type="tel" id="phoneInput" class="phone-input" placeholder="10-digit mobile number"
              maxlength="10" pattern="[0-9]{10}" autocomplete="off">
          </div>
        </div>
        <button class="btn-primary" id="sendOtpBtn" onclick="sendOTP()">
          <i class="fa fa-paper-plane"></i> SEND OTP
        </button>
      </div>

      <div class="form-step" id="step-mobile-otp">
        <div class="step-title">Enter OTP</div>
        <div class="step-desc">Enter the 4-digit OTP sent to <strong id="displayPhone" style="color:var(--primary)"></strong></div>
        <div class="otp-inputs">
          <input type="tel" class="otp-digit" maxlength="1" id="otp1" oninput="otpInput(this,1)" onkeydown="otpKey(this,1)">
          <input type="tel" class="otp-digit" maxlength="1" id="otp2" oninput="otpInput(this,2)" onkeydown="otpKey(this,2)">
          <input type="tel" class="otp-digit" maxlength="1" id="otp3" oninput="otpInput(this,3)" onkeydown="otpKey(this,3)">
          <input type="tel" class="otp-digit" maxlength="1" id="otp4" oninput="otpInput(this,4)" onkeydown="otpKey(this,4)">
        </div>
        <div class="otp-timer" id="otpTimer">Resend OTP in <span id="timerCount">30</span>s</div>
        <button class="btn-primary" id="verifyOtpBtn" onclick="verifyOTP()" style="margin-top:20px">
          <i class="fa fa-check-circle"></i> VERIFY & LOGIN
        </button>
        <div style="text-align:center;margin-top:12px">
          <button class="btn-secondary" onclick="goBack('mobile')">← Change Number</button>
        </div>
      </div>

      <!-- ========== TOKEN SECTION ========== -->
      <div class="form-step" id="step-token">
        <div class="step-title">Token Login</div>
        <div class="step-desc">Enter your PW access token (JWT) to login directly</div>
        <div class="input-group">
          <label>Access Token</label>
          <div class="input-wrap">
            <i class="fa fa-key"></i>
            <input type="password" id="tokenInput" placeholder="Paste your JWT token here" autocomplete="off">
          </div>
        </div>
        <button class="btn-primary" onclick="tokenLogin()">
          <i class="fa fa-sign-in-alt"></i> LOGIN WITH TOKEN
        </button>
      </div>

      <!-- Security Badge -->
      <div class="security-badge">
        <i class="fa fa-shield-halved"></i>
        <span>256-bit encrypted · Secure session · Auto-logout on inactivity</span>
      </div>
    </div>

    <a href="admin.php" class="admin-link">
      <i class="fa fa-lock"></i> Admin Panel
    </a>

    <div class="login-footer" style="margin-top:12px">
      &copy; <?php echo date('Y'); ?> PW Portal. Unauthorized access prohibited.
    </div>
  </div>
</div>

<script>
// ============================================================
// SECURITY: Disable right-click, F12, devtools detection
// ============================================================
(function() {
  'use strict';
  // Disable right-click
  document.addEventListener('contextmenu', function(e) { e.preventDefault(); return false; });
  // Disable F12, Ctrl+Shift+I/J/C/U
  document.addEventListener('keydown', function(e) {
    if (e.key === 'F12' ||
        (e.ctrlKey && e.shiftKey && ['I','J','C'].includes(e.key.toUpperCase())) ||
        (e.ctrlKey && e.key.toUpperCase() === 'U') ||
        (e.ctrlKey && e.key.toUpperCase() === 'S')) {
      e.preventDefault(); e.stopPropagation(); return false;
    }
  });
  // Disable text selection
  document.addEventListener('selectstart', function(e) { e.preventDefault(); });
  // Devtools detection via size
  var threshold = 160;
  var devtoolsCheck = setInterval(function() {
    if ((window.outerWidth - window.innerWidth > threshold) ||
        (window.outerHeight - window.innerHeight > threshold)) {
      document.body.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100vh;background:#0a0a14;color:#ff4757;font-family:monospace;font-size:20px;text-align:center;flex-direction:column;gap:16px"><div style="font-size:60px">🚫</div><div>Developer Tools Detected</div><div style="font-size:14px;color:#666">Please close DevTools and refresh</div></div>';
    }
  }, 1000);
  // Disable console
  var consoleMethods = ['log','debug','info','warn','error','table','trace','dir'];
  consoleMethods.forEach(function(m) { console[m] = function() {}; });
})();

// ============================================================
// TAB SWITCHING
// ============================================================
var currentTab = 'mobile';
function switchTab(tab) {
  currentTab = tab;
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-'+tab).classList.add('active');
  document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
  if (tab === 'mobile') {
    document.getElementById('step-mobile-phone').classList.add('active');
  } else {
    document.getElementById('step-token').classList.add('active');
  }
  clearAlert();
}

function goBack(tab) {
  if (tab === 'mobile') {
    document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
    document.getElementById('step-mobile-phone').classList.add('active');
  }
  clearAlert();
}

// ============================================================
// ALERT
// ============================================================
function showAlert(msg, type) {
  var box = document.getElementById('alertBox');
  var msgEl = document.getElementById('alertMsg');
  box.className = 'alert show alert-' + type;
  msgEl.textContent = msg;
  var icon = box.querySelector('i');
  if (type === 'error') icon.className = 'fa fa-circle-xmark';
  else if (type === 'success') icon.className = 'fa fa-circle-check';
  else icon.className = 'fa fa-circle-info';
}
function clearAlert() {
  document.getElementById('alertBox').className = 'alert';
}

// ============================================================
// OTP INPUT HANDLING
// ============================================================
function otpInput(el, pos) {
  el.value = el.value.replace(/[^0-9]/g,'');
  if (el.value) {
    el.classList.add('filled');
    if (pos < 4) document.getElementById('otp'+(pos+1)).focus();
    else checkAutoSubmit();
  } else {
    el.classList.remove('filled');
  }
}
function otpKey(el, pos) {
  if (event.key === 'Backspace' && !el.value && pos > 1) {
    var prev = document.getElementById('otp'+(pos-1));
    prev.value = ''; prev.classList.remove('filled'); prev.focus();
  }
}
function checkAutoSubmit() {
  var otp = '';
  for (var i=1;i<=4;i++) otp += document.getElementById('otp'+i).value;
  if (otp.length === 4) { /* Optional: auto-submit */ }
}
function getOTPValue() {
  var otp = '';
  for (var i=1;i<=4;i++) otp += document.getElementById('otp'+i).value;
  return otp;
}

// ============================================================
// OTP TIMER
// ============================================================
var timerInterval = null;
function startTimer(sec) {
  clearInterval(timerInterval);
  var el = document.getElementById('timerCount');
  var container = document.getElementById('otpTimer');
  var remaining = sec;
  container.innerHTML = 'Resend OTP in <span id="timerCount">' + remaining + '</span>s';
  timerInterval = setInterval(function() {
    remaining--;
    document.getElementById('timerCount').textContent = remaining;
    if (remaining <= 0) {
      clearInterval(timerInterval);
      container.innerHTML = '<a href="#" onclick="resendOTP(); return false;" style="color:var(--primary)">Resend OTP</a>';
    }
  }, 1000);
}

// ============================================================
// SEND OTP
// ============================================================
var currentPhone = '';
function sendOTP() {
  var phone = document.getElementById('phoneInput').value.trim();
  if (!/^[6-9][0-9]{9}$/.test(phone)) {
    showAlert('Please enter a valid 10-digit Indian mobile number', 'error');
    return;
  }
  currentPhone = phone;
  var btn = document.getElementById('sendOtpBtn');
  btn.innerHTML = '<span class="spinner"></span> Sending OTP...';
  btn.classList.add('loading');
  clearAlert();

  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'api.php', true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.onload = function() {
    btn.classList.remove('loading');
    if (xhr.status === 200) {
      try {
        var resp = JSON.parse(xhr.responseText);
        if (resp.success) {
          showAlert('OTP sent to +91 ' + phone, 'success');
          document.getElementById('displayPhone').textContent = '+91 ' + phone;
          document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
          document.getElementById('step-mobile-otp').classList.add('active');
          startTimer(30);
          setTimeout(function(){ document.getElementById('otp1').focus(); }, 300);
        } else {
          showAlert(resp.message || 'Failed to send OTP', 'error');
          btn.innerHTML = '<i class="fa fa-paper-plane"></i> SEND OTP';
        }
      } catch(e) {
        showAlert('Server error. Please try again.', 'error');
        btn.innerHTML = '<i class="fa fa-paper-plane"></i> SEND OTP';
      }
    } else {
      showAlert('Network error. Check your connection.', 'error');
      btn.innerHTML = '<i class="fa fa-paper-plane"></i> SEND OTP';
    }
  };
  xhr.onerror = function() {
    btn.classList.remove('loading');
    btn.innerHTML = '<i class="fa fa-paper-plane"></i> SEND OTP';
    showAlert('Network error. Please try again.', 'error');
  };
  xhr.send(JSON.stringify({ action: 'send_otp', phone: phone }));
}

function resendOTP() {
  for (var i=1;i<=4;i++) {
    var el = document.getElementById('otp'+i);
    el.value=''; el.classList.remove('filled');
  }
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'api.php', true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.onload = function() {
    if (xhr.status === 200) {
      var resp = JSON.parse(xhr.responseText);
      if (resp.success) {
        showAlert('OTP resent successfully', 'success');
        startTimer(30);
      } else {
        showAlert(resp.message || 'Failed to resend OTP', 'error');
      }
    }
  };
  xhr.send(JSON.stringify({ action: 'send_otp', phone: currentPhone }));
}

// ============================================================
// VERIFY OTP
// ============================================================
function verifyOTP() {
  var otp = getOTPValue();
  if (otp.length !== 4) {
    showAlert('Please enter complete 4-digit OTP', 'error');
    return;
  }
  var btn = document.getElementById('verifyOtpBtn');
  btn.innerHTML = '<span class="spinner"></span> Verifying...';
  btn.classList.add('loading');
  clearAlert();

  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'api.php', true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.onload = function() {
    btn.classList.remove('loading');
    if (xhr.status === 200) {
      try {
        var resp = JSON.parse(xhr.responseText);
        if (resp.success) {
          showAlert('Login successful! Redirecting...', 'success');
          btn.innerHTML = '<i class="fa fa-check-circle"></i> SUCCESS!';
          setTimeout(function(){ window.location.href = 'dashboard.php'; }, 1200);
        } else {
          btn.innerHTML = '<i class="fa fa-check-circle"></i> VERIFY & LOGIN';
          showAlert(resp.message || 'Invalid OTP. Please try again.', 'error');
          for (var i=1;i<=4;i++) {
            var el = document.getElementById('otp'+i);
            el.value=''; el.classList.remove('filled');
          }
          document.getElementById('otp1').focus();
        }
      } catch(e) {
        btn.innerHTML = '<i class="fa fa-check-circle"></i> VERIFY & LOGIN';
        showAlert('Server error. Please try again.', 'error');
      }
    } else {
      btn.innerHTML = '<i class="fa fa-check-circle"></i> VERIFY & LOGIN';
      showAlert('Network error.', 'error');
    }
  };
  xhr.onerror = function() {
    btn.classList.remove('loading');
    btn.innerHTML = '<i class="fa fa-check-circle"></i> VERIFY & LOGIN';
    showAlert('Network error.', 'error');
  };
  xhr.send(JSON.stringify({ action: 'verify_otp', phone: currentPhone, otp: otp }));
}

// ============================================================
// TOKEN LOGIN
// ============================================================
function tokenLogin() {
  var token = document.getElementById('tokenInput').value.trim();
  if (!token || token.length < 20) {
    showAlert('Please enter a valid JWT token', 'error');
    return;
  }
  clearAlert();
  showAlert('Authenticating token...', 'info');
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'api.php', true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.onload = function() {
    if (xhr.status === 200) {
      try {
        var resp = JSON.parse(xhr.responseText);
        if (resp.success) {
          showAlert('Token verified! Redirecting...', 'success');
          setTimeout(function(){ window.location.href = 'dashboard.php'; }, 1200);
        } else {
          showAlert(resp.message || 'Invalid or expired token', 'error');
        }
      } catch(e) {
        showAlert('Server error.', 'error');
      }
    } else {
      showAlert('Network error.', 'error');
    }
  };
  xhr.send(JSON.stringify({ action: 'token_login', token: token }));
}

// Phone input - digits only
document.getElementById('phoneInput').addEventListener('input', function() {
  this.value = this.value.replace(/[^0-9]/g,'');
});
document.getElementById('phoneInput').addEventListener('keypress', function(e) {
  if (e.key === 'Enter') sendOTP();
});
document.getElementById('tokenInput').addEventListener('keypress', function(e) {
  if (e.key === 'Enter') tokenLogin();
});
</script>
</body>
</html>
