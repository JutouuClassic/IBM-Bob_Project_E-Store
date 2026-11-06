<?php
/**
 * config/auth.php
 * Helper autentikasi: session, cek login, cek role
 * Sertakan di setiap halaman yang memerlukan akses terlindungi.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ── Cek apakah pengguna sudah login ─────────────────────── */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/* ── Ambil role pengguna saat ini ────────────────────────── */
function currentRole(): string {
    return $_SESSION['role'] ?? 'guest';
}

/* ── Cek apakah pengguna adalah admin ────────────────────── */
function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

/* ── Paksa halaman hanya bisa diakses jika sudah login ────── */
function requireLogin(string $redirect = 'login.php'): void {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect . '?pesan=login_required');
        exit;
    }
}

/* ── Paksa halaman hanya bisa diakses oleh admin ─────────── */
function requireAdmin(string $redirect = 'index.php'): void {
    if (!isAdmin()) {
        // Simpan pesan peringatan ke session agar bisa ditampilkan
        $_SESSION['flash_error'] = 'Akses ditolak. Halaman ini khusus Admin.';
        header('Location: ' . $redirect);
        exit;
    }
}

/* ── Ambil & hapus flash message (one-time) ──────────────── */
function flashError(): string {
    $msg = $_SESSION['flash_error'] ?? '';
    unset($_SESSION['flash_error']);
    return $msg;
}
function flashSuccess(): string {
    $msg = $_SESSION['flash_success'] ?? '';
    unset($_SESSION['flash_success']);
    return $msg;
}
