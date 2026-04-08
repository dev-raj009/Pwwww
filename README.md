# PW Portal — Setup Instructions

## Files
- `index.php`      — User login page (Mobile OTP + Token)
- `api.php`        — User API handler
- `dashboard.php`  — User dashboard (courses)
- `admin.php`      — Admin login page
- `admin_api.php`  — Admin API handler  
- `admin_panel.php`— Full admin dashboard
- `.htaccess`      — Apache security rules
- `data/`          — Auto-created JSON database folder

## Requirements
- PHP 7.4+ with cURL enabled
- Apache with mod_rewrite
- Internet access (to reach api.penpencil.co)

## Admin Credentials
- Username: rajdev
- Password: PWtoken

## How It Works

### User Login Flow
1. User opens index.php
2. Chooses: Mobile OTP or Token
3. Mobile OTP: Enters 10-digit number → OTP sent via PW API → Enter OTP → JWT token generated → Session saved → Redirected to dashboard
4. Token: Pastes JWT token → Validated against PW API → Session saved → Dashboard

### Dashboard
- Shows user's JWT token (copyable)
- Lists all purchased courses/batches from PW
- Persists via PHP session (no re-login until logout)

### Admin Panel
- Login at admin.php (rajdev/PWtoken)
- Dashboard: Live stats, today's logins, active users
- Users: All registered users, their tokens, login history
- Sessions: Live session tracking, online/offline status
- Auto-refreshes every 30 seconds

## Security Features
- Right-click disabled
- F12 / DevTools blocked
- Console methods disabled  
- DevTools size-detection
- Rate limiting on OTP
- Admin lockout after 5 failed attempts (5 min)
- Session-based auth (not URL params)
- AJAX-only API endpoints (rejects direct browser access)
- .htaccess blocks data/ directory
- Security headers set

## Database
Uses simple JSON files in /data/:
- users.json    — User records
- sessions.json — Session/token records
- admin_log.json— Admin login attempts

## API Source
Based on PW (Physics Wallah / penpencil.co) API:
- OTP: POST /v1/users/get-otp
- Token: POST /v3/oauth/token
- Batches: GET /v3/batches/my-batches
