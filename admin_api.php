<?php
/**
 * Admin API Handler
 * Handles admin authentication and data queries
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Forbidden']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method Not Allowed']);
    exit;
}

session_start();

// ============================================================
// ADMIN CREDENTIALS
// ============================================================
define('ADMIN_USERNAME', 'rajdev');
define('ADMIN_PASSWORD', 'PWtoken');

// DB files
define('DB_FILE',       __DIR__ . '/data/users.json');
define('SESSIONS_FILE', __DIR__ . '/data/sessions.json');
define('ADMIN_LOG_FILE',__DIR__ . '/data/admin_log.json');

function loadDB($file) {
    if (!file_exists($file)) return [];
    $d = @file_get_contents($file);
    return $d ? (json_decode($d,true) ?: []) : [];
}
function saveDB($file, $data) {
    if (!is_dir(dirname($file))) mkdir(dirname($file),0750,true);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action'])) {
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}

$action = $input['action'];

// ============================================================
// ADMIN AUTH CHECK (for protected actions)
// ============================================================
function requireAdmin() {
    if (empty($_SESSION['admin_logged_in'])) {
        echo json_encode(['success'=>false,'message'=>'Unauthorized','redirect'=>true]);
        exit;
    }
}

// ============================================================
// ACTION: ADMIN LOGIN
// ============================================================
if ($action === 'admin_login') {
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');

    // Rate limit in session
    if (!isset($_SESSION['admin_attempts'])) $_SESSION['admin_attempts'] = 0;
    if (!isset($_SESSION['admin_lock_until'])) $_SESSION['admin_lock_until'] = 0;

    if (time() < $_SESSION['admin_lock_until']) {
        echo json_encode(['success'=>false,'message'=>'Too many attempts. Please wait.']);
        exit;
    }

    // Log the attempt
    $log = loadDB(ADMIN_LOG_FILE);
    $log[] = [
        'time'     => date('Y-m-d H:i:s'),
        'ip'       => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'username' => $username,
        'success'  => false,
    ];

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time']= time();
        $_SESSION['admin_ip']        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $_SESSION['admin_attempts']  = 0;
        // Mark success
        $log[count($log)-1]['success'] = true;
        saveDB(ADMIN_LOG_FILE, array_slice($log, -500)); // Keep last 500
        echo json_encode(['success'=>true]);
    } else {
        $_SESSION['admin_attempts']++;
        if ($_SESSION['admin_attempts'] >= 5) {
            $_SESSION['admin_lock_until'] = time() + 300;
            $_SESSION['admin_attempts']   = 0;
        }
        saveDB(ADMIN_LOG_FILE, array_slice($log, -500));
        echo json_encode(['success'=>false,'message'=>'Invalid credentials']);
    }
    exit;
}

// ============================================================
// ACTION: ADMIN LOGOUT
// ============================================================
if ($action === 'admin_logout') {
    requireAdmin();
    session_destroy();
    echo json_encode(['success'=>true]);
    exit;
}

// ============================================================
// ACTION: GET DASHBOARD STATS
// ============================================================
if ($action === 'get_stats') {
    requireAdmin();
    $users    = loadDB(DB_FILE);
    $sessions = loadDB(SESSIONS_FILE);

    $now        = time();
    $today      = date('Y-m-d');
    $todayLogins= 0;
    $activeNow  = 0;

    foreach ($sessions as $s) {
        if (strpos($s['login_time'], $today) === 0) $todayLogins++;
        if (!empty($s['active'])) {
            $lastActive = strtotime($s['last_active'] ?? $s['login_time']);
            if ($now - $lastActive < 600) $activeNow++; // active in last 10 min
        }
    }

    // Method breakdown
    $mobileCount = 0; $tokenCount = 0;
    foreach ($users as $u) {
        if (($u['login_method']??'') === 'mobile') $mobileCount++;
        else $tokenCount++;
    }

    echo json_encode([
        'success'       => true,
        'total_users'   => count($users),
        'total_sessions'=> count($sessions),
        'today_logins'  => $todayLogins,
        'active_now'    => $activeNow,
        'mobile_logins' => $mobileCount,
        'token_logins'  => $tokenCount,
    ]);
    exit;
}

// ============================================================
// ACTION: GET ALL USERS
// ============================================================
if ($action === 'get_users') {
    requireAdmin();
    $users    = loadDB(DB_FILE);
    $sessions = loadDB(SESSIONS_FILE);

    // Attach latest session to each user
    foreach ($users as &$u) {
        $u['token_preview'] = '';
        if (!empty($u['last_token'])) {
            $u['token_preview'] = substr($u['last_token'],0,30) . '...' . substr($u['last_token'],-10);
            $u['full_token']    = $u['last_token'];
        }
    }

    echo json_encode(['success'=>true, 'users'=>array_values($users)]);
    exit;
}

// ============================================================
// ACTION: GET ALL SESSIONS
// ============================================================
if ($action === 'get_sessions') {
    requireAdmin();
    $sessions = loadDB(SESSIONS_FILE);
    $now      = time();

    $list = [];
    foreach ($sessions as $token => $s) {
        $lastActive  = strtotime($s['last_active'] ?? $s['login_time']);
        $s['is_online'] = (!empty($s['active']) && ($now - $lastActive) < 600);
        $s['token_preview'] = substr($token,0,20).'...'.substr($token,-8);
        $list[] = $s;
    }
    // Sort by login_time DESC
    usort($list, function($a,$b){ return strtotime($b['login_time']) - strtotime($a['login_time']); });

    echo json_encode(['success'=>true, 'sessions'=>array_slice($list,0,100)]);
    exit;
}

// ============================================================
// ACTION: GET USER DETAIL
// ============================================================
if ($action === 'get_user_detail') {
    requireAdmin();
    $userId   = $input['user_id'] ?? '';
    $users    = loadDB(DB_FILE);
    $sessions = loadDB(SESSIONS_FILE);

    $user = null;
    foreach ($users as $u) {
        if ($u['id'] === $userId) { $user = $u; break; }
    }
    if (!$user) {
        echo json_encode(['success'=>false,'message'=>'User not found']);
        exit;
    }

    // Get user sessions
    $userSessions = [];
    foreach ($sessions as $token => $s) {
        if ($s['user_id'] === $userId) {
            $s['token'] = $token;
            $userSessions[] = $s;
        }
    }
    usort($userSessions, function($a,$b){ return strtotime($b['login_time']) - strtotime($a['login_time']); });

    echo json_encode([
        'success'  => true,
        'user'     => $user,
        'sessions' => $userSessions,
    ]);
    exit;
}

// ============================================================
// ACTION: CHECK ADMIN SESSION
// ============================================================
if ($action === 'check_admin') {
    echo json_encode(['success'=>true,'logged_in'=>!empty($_SESSION['admin_logged_in'])]);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Unknown action']);
