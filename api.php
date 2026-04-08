<?php
/**
 * PW Portal - Main API Handler
 * Handles: OTP Send, OTP Verify, Token Login, Session Management
 */

// Security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Only accept AJAX requests
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

session_start();

// Rate limiting (simple in-session)
if (!isset($_SESSION['api_calls'])) $_SESSION['api_calls'] = [];
$_SESSION['api_calls'][] = time();
$_SESSION['api_calls'] = array_filter($_SESSION['api_calls'], function($t){ return $t > time()-60; });
if (count($_SESSION['api_calls']) > 30) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please wait.']);
    exit;
}

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$action = $input['action'];

// ============================================================
// PW API Constants (from Python file)
// ============================================================
define('PW_CLIENT_ID', '5eb393ee95fab7468a79d189');
define('PW_CLIENT_SECRET', 'KjPXuAVfC5xbmgreETNMaL7z');
define('PW_ORG_ID', '5eb393ee95fab7468a79d189');
define('PW_BASE_URL', 'https://api.penpencil.co');

// ============================================================
// cURL helper
// ============================================================
function pw_curl($url, $method = 'GET', $payload = null, $token = null, $extra_headers = []) {
    $ch = curl_init();
    $headers = [
        'Content-Type: application/json',
        'Client-Id: ' . PW_CLIENT_ID,
        'Client-Type: WEB',
        'Client-Version: 2.6.12',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36 Edg/121.0.0.0',
        'Origin: https://www.pw.live',
        'Referer: https://www.pw.live/',
    ];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    $headers = array_merge($headers, $extra_headers);

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_FOLLOWLOCATION => true,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($payload) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) return ['error' => $error, 'code' => 0];
    return ['body' => $response, 'code' => $httpCode, 'data' => json_decode($response, true)];
}

// ============================================================
// Generate unique user ID
// ============================================================
function generateUserId($phone) {
    return 'USR-' . strtoupper(substr(md5($phone . PW_CLIENT_ID), 0, 8)) . '-' . strtoupper(substr(md5(time() . $phone), 0, 4));
}

// ============================================================
// Load/Save user DB (JSON file based)
// ============================================================
define('DB_FILE', __DIR__ . '/data/users.json');
define('SESSIONS_FILE', __DIR__ . '/data/sessions.json');

function loadDB($file) {
    if (!file_exists($file)) return [];
    $data = @file_get_contents($file);
    return $data ? (json_decode($data, true) ?: []) : [];
}
function saveDB($file, $data) {
    if (!is_dir(dirname($file))) mkdir(dirname($file), 0750, true);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}
function saveUserSession($phone, $token, $method = 'mobile') {
    $users  = loadDB(DB_FILE);
    $sessions = loadDB(SESSIONS_FILE);
    $userId = null;
    // Find existing user
    foreach ($users as &$u) {
        if ($u['phone'] === $phone) {
            $userId = $u['id'];
            $u['last_token'] = $token;
            $u['last_login'] = date('Y-m-d H:i:s');
            $u['login_count'] = ($u['login_count'] ?? 0) + 1;
            $u['login_method'] = $method;
            $u['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            break;
        }
    }
    if (!$userId) {
        $userId = generateUserId($phone);
        $users[] = [
            'id'           => $userId,
            'phone'        => $phone,
            'last_token'   => $token,
            'first_login'  => date('Y-m-d H:i:s'),
            'last_login'   => date('Y-m-d H:i:s'),
            'login_count'  => 1,
            'login_method' => $method,
            'ip'           => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];
    }
    // Save session
    $sessions[$token] = [
        'user_id'    => $userId,
        'phone'      => $phone,
        'token'      => $token,
        'login_time' => date('Y-m-d H:i:s'),
        'method'     => $method,
        'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'active'     => true,
        'last_active'=> date('Y-m-d H:i:s'),
    ];
    saveDB(DB_FILE, $users);
    saveDB(SESSIONS_FILE, $sessions);
    return $userId;
}

// ============================================================
// ACTION: SEND OTP
// ============================================================
if ($action === 'send_otp') {
    $phone = preg_replace('/[^0-9]/', '', $input['phone'] ?? '');
    if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number']);
        exit;
    }

    $url = PW_BASE_URL . '/v1/users/get-otp?smsType=0';
    $payload = [
        'username'       => $phone,
        'countryCode'    => '+91',
        'organizationId' => PW_ORG_ID,
    ];

    $result = pw_curl($url, 'POST', $payload);

    if ($result['code'] === 200 || $result['code'] === 201) {
        $_SESSION['otp_phone']   = $phone;
        $_SESSION['otp_sent_at'] = time();
        echo json_encode(['success' => true, 'message' => 'OTP sent successfully']);
    } else {
        $msg = 'Failed to send OTP';
        if (!empty($result['data']['message'])) $msg = $result['data']['message'];
        echo json_encode(['success' => false, 'message' => $msg]);
    }
    exit;
}

// ============================================================
// ACTION: VERIFY OTP & GET TOKEN
// ============================================================
if ($action === 'verify_otp') {
    $phone = preg_replace('/[^0-9]/', '', $input['phone'] ?? '');
    $otp   = preg_replace('/[^0-9]/', '', $input['otp']   ?? '');

    if (!$phone || !$otp) {
        echo json_encode(['success' => false, 'message' => 'Missing phone or OTP']);
        exit;
    }
    if (!isset($_SESSION['otp_phone']) || $_SESSION['otp_phone'] !== $phone) {
        echo json_encode(['success' => false, 'message' => 'Session expired. Please request OTP again.']);
        exit;
    }
    if (time() - ($_SESSION['otp_sent_at'] ?? 0) > 300) {
        echo json_encode(['success' => false, 'message' => 'OTP expired. Please request a new one.']);
        exit;
    }

    $url = PW_BASE_URL . '/v3/oauth/token';
    $payload = [
        'username'       => $phone,
        'otp'            => $otp,
        'client_id'      => 'system-admin',
        'client_secret'  => PW_CLIENT_SECRET,
        'grant_type'     => 'password',
        'organizationId' => PW_ORG_ID,
        'latitude'       => 0,
        'longitude'      => 0,
    ];

    $extra = [
        'Integration-With: ',
        'Randomid: ' . sprintf('%s-%s-%s-%s', bin2hex(random_bytes(4)), bin2hex(random_bytes(2)), bin2hex(random_bytes(2)), bin2hex(random_bytes(4))),
        'Sec-Ch-Ua: "Not A(Brand";v="99", "Microsoft Edge";v="121", "Chromium";v="121"',
        'Sec-Ch-Ua-Mobile: ?0',
    ];

    $result = pw_curl($url, 'POST', $payload, null, $extra);

    if ($result['code'] === 200 && !empty($result['data']['data']['access_token'])) {
        $token  = $result['data']['data']['access_token'];
        $userId = saveUserSession($phone, $token, 'mobile');

        // Set session
        $_SESSION['pw_token']    = $token;
        $_SESSION['user_phone']  = $phone;
        $_SESSION['user_id']     = $userId;
        $_SESSION['login_time']  = time();
        $_SESSION['login_method']= 'mobile';
        unset($_SESSION['otp_phone'], $_SESSION['otp_sent_at']);

        echo json_encode([
            'success' => true,
            'token'   => $token,
            'user_id' => $userId,
        ]);
    } else {
        $msg = 'Invalid OTP or authentication failed';
        if (!empty($result['data']['message'])) $msg = $result['data']['message'];
        if (!empty($result['data']['error_description'])) $msg = $result['data']['error_description'];
        echo json_encode(['success' => false, 'message' => $msg]);
    }
    exit;
}

// ============================================================
// ACTION: TOKEN LOGIN
// ============================================================
if ($action === 'token_login') {
    $token = trim($input['token'] ?? '');
    if (strlen($token) < 20) {
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
        exit;
    }

    // Validate token by fetching batches
    $url = PW_BASE_URL . '/v3/batches/my-batches?' . http_build_query([
        'mode' => '1', 'filter' => 'false', 'exam' => '', 'amount' => '',
        'organisationId' => PW_ORG_ID, 'classes' => '', 'limit' => '5',
        'page' => '1', 'programId' => '', 'ut' => '1652675230446',
    ]);

    $mobileHeaders = [
        'Host: api.penpencil.co',
        'client-version: 12.84',
        'user-agent: Android',
        'randomid: e4307177362e86f1',
        'client-type: MOBILE',
        'device-meta: {APP_VERSION:12.84,DEVICE_MAKE:Asus,DEVICE_MODEL:ASUS_X00TD,OS_VERSION:6,PACKAGE_NAME:xyz.penpencil.physicswalb}',
    ];

    $result = pw_curl($url, 'GET', null, $token, $mobileHeaders);

    if ($result['code'] === 200 && isset($result['data']['data'])) {
        // Extract phone from token if possible, else use hash
        $phone = 'token_user_' . substr(md5($token), 0, 8);
        // Try to get user profile
        $profileResult = pw_curl(PW_BASE_URL . '/v1/users/me', 'GET', null, $token, $mobileHeaders);
        if (!empty($profileResult['data']['data']['username'])) {
            $phone = $profileResult['data']['data']['username'];
        }

        $userId = saveUserSession($phone, $token, 'token');
        $_SESSION['pw_token']    = $token;
        $_SESSION['user_phone']  = $phone;
        $_SESSION['user_id']     = $userId;
        $_SESSION['login_time']  = time();
        $_SESSION['login_method']= 'token';

        echo json_encode(['success' => true, 'user_id' => $userId]);
    } else {
        $msg = 'Token is invalid or expired';
        if (!empty($result['data']['message'])) $msg = $result['data']['message'];
        echo json_encode(['success' => false, 'message' => $msg]);
    }
    exit;
}

// ============================================================
// ACTION: LOGOUT
// ============================================================
if ($action === 'logout') {
    // Mark session inactive
    if (!empty($_SESSION['pw_token'])) {
        $sessions = loadDB(SESSIONS_FILE);
        if (isset($sessions[$_SESSION['pw_token']])) {
            $sessions[$_SESSION['pw_token']]['active'] = false;
            $sessions[$_SESSION['pw_token']]['logout_time'] = date('Y-m-d H:i:s');
            saveDB(SESSIONS_FILE, $sessions);
        }
    }
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// ============================================================
// ACTION: GET COURSES (batches)
// ============================================================
if ($action === 'get_batches') {
    if (empty($_SESSION['pw_token'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated', 'redirect' => true]);
        exit;
    }
    $token = $_SESSION['pw_token'];

    // Update last active
    $sessions = loadDB(SESSIONS_FILE);
    if (isset($sessions[$token])) {
        $sessions[$token]['last_active'] = date('Y-m-d H:i:s');
        saveDB(SESSIONS_FILE, $sessions);
    }

    $url = PW_BASE_URL . '/v3/batches/my-batches?' . http_build_query([
        'mode' => '1', 'filter' => 'false', 'exam' => '', 'amount' => '',
        'organisationId' => PW_ORG_ID, 'classes' => '', 'limit' => '20',
        'page' => '1', 'programId' => '', 'ut' => '1652675230446',
    ]);

    $mobileHeaders = [
        'Host: api.penpencil.co',
        'client-version: 12.84',
        'user-agent: Android',
        'randomid: e4307177362e86f1',
        'client-type: MOBILE',
        'device-meta: {APP_VERSION:12.84,DEVICE_MAKE:Asus,DEVICE_MODEL:ASUS_X00TD,OS_VERSION:6,PACKAGE_NAME:xyz.penpencil.physicswalb}',
    ];

    $result = pw_curl($url, 'GET', null, $token, $mobileHeaders);

    if ($result['code'] === 200 && isset($result['data']['data'])) {
        echo json_encode(['success' => true, 'data' => $result['data']['data']]);
    } elseif ($result['code'] === 401) {
        session_destroy();
        echo json_encode(['success' => false, 'message' => 'Session expired', 'redirect' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch courses']);
    }
    exit;
}

// ============================================================
// ACTION: CHECK SESSION
// ============================================================
if ($action === 'check_session') {
    if (!empty($_SESSION['pw_token']) && !empty($_SESSION['user_id'])) {
        echo json_encode(['success' => true, 'logged_in' => true]);
    } else {
        echo json_encode(['success' => true, 'logged_in' => false]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
