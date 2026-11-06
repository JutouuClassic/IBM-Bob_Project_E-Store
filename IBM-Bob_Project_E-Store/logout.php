<?php
/**
 * logout.php
 * Hancurkan session dan redirect ke halaman utama
 */

require_once __DIR__ . '/config/auth.php';

// Hapus semua data session
$_SESSION = [];

// Hancurkan cookie session
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

header('Location: index.php');
exit;
