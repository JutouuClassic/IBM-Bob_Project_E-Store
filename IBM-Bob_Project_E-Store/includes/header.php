<?php
/**
 * includes/header.php
 * Header dinamis: tampilkan info user jika login, tombol masuk/daftar jika belum.
 * Sertakan di setiap halaman dengan: require_once __DIR__ . '/../includes/header.php';
 *
 * Variabel yang dibutuhkan dari halaman pemanggil (opsional):
 *   $pageTitle  — judul tab browser
 *   $activeCategoryId — ID kategori aktif untuk highlight nav
 *   $categories — array kategori dari DB
 */

if (session_status() === PHP_SESSION_NONE) session_start();

// Pastikan helper auth tersedia
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/../config/auth.php';
}

$_headerTitle = $pageTitle ?? 'E-Store';

function _e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= _e($_headerTitle) ?> – E-Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#E50914', 'primary-dark': '#b5070f',
                        accent: '#F59E0B', 'accent-light': '#FEF3C7',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="<?= isset($rootPath) ? $rootPath : '' ?>style.css">
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased">

<!-- ════════════════════════════════════════════════════════
     HEADER UTAMA
════════════════════════════════════════════════════════ -->
<header class="bg-primary shadow-md sticky top-0 z-50">
    <div class="max-w-screen-xl mx-auto px-4 py-3 flex items-center gap-3">

        <!-- Logo -->
        <a href="index.php" class="flex items-center gap-2 flex-shrink-0">
            <div class="bg-white text-primary font-extrabold text-xl px-3 py-1 rounded-lg leading-none">E</div>
            <span class="text-white font-bold text-xl tracking-wide hidden sm:inline">E-Store</span>
        </a>

        <!-- Dropdown Kategori -->
        <div class="relative flex-shrink-0" id="categoryDropdown">
            <button onclick="toggleCategoryMenu()"
                    class="flex items-center gap-1 bg-primary-dark hover:bg-red-900 text-white text-sm font-medium px-3 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span class="hidden md:inline">Kategori</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div id="categoryMenu"
                 class="hidden absolute left-0 top-full mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
                <a href="index.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-primary transition font-medium">
                    Semua Produk
                </a>
                <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                <a href="index.php?category=<?= _e($cat['slug']) ?>"
                   class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-primary transition
                          <?= (isset($activeCategoryId) && $activeCategoryId === (int)$cat['id']) ? 'bg-red-50 text-primary font-semibold' : '' ?>">
                    <?= _e($cat['name']) ?>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="flex-1 max-w-xl">
            <div class="relative">
                <input type="text" id="searchInput"
                       placeholder="Cari produk, merek, atau kategori..."
                       class="w-full pl-4 pr-10 py-2 rounded-xl text-sm text-gray-800 bg-white
                              border border-transparent focus:outline-none focus:ring-2 focus:ring-white/50
                              placeholder-gray-400">
                <button class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Ikon Aksi -->
        <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
            <!-- Notifikasi -->
            <button class="relative p-2 text-white hover:bg-primary-dark rounded-lg transition" title="Notifikasi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute top-1 right-1 w-2 h-2 bg-accent rounded-full"></span>
            </button>
            <!-- Wishlist -->
            <button class="relative p-2 text-white hover:bg-primary-dark rounded-lg transition hidden sm:block" title="Wishlist">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
            <!-- Keranjang -->
            <button id="cartBtn" onclick="toggleCart()"
                    class="relative p-2 text-white hover:bg-primary-dark rounded-lg transition" title="Keranjang">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span id="cartBadge"
                      class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-accent text-gray-900
                             text-[10px] font-bold rounded-full flex items-center justify-center px-1">0</span>
            </button>

            <!-- ── AUTH SECTION ── -->
            <?php if (isLoggedIn()): ?>
            <!-- Pengguna sudah login: tampilkan nama, role badge, dan logout -->
            <div class="hidden sm:flex items-center gap-2 ml-1">
                <div class="text-right">
                    <p class="text-white text-xs font-semibold leading-none">
                        <?= _e($_SESSION['nama']) ?>
                    </p>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full
                                 <?= $_SESSION['role'] === 'admin'
                                     ? 'bg-accent text-gray-900'
                                     : 'bg-white/20 text-white' ?>">
                        <?= $_SESSION['role'] === 'admin' ? 'Admin' : 'User' ?>
                    </span>
                </div>
                <a href="logout.php"
                   class="bg-white/20 hover:bg-white/30 text-white text-xs font-semibold
                          px-3 py-1.5 rounded-lg transition">
                    Logout
                </a>
            </div>
            <!-- Versi mobile: hanya tombol logout -->
            <a href="logout.php" class="sm:hidden p-2 text-white hover:bg-primary-dark rounded-lg transition" title="Logout">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </a>
            <?php else: ?>
            <!-- Belum login: tombol Masuk & Daftar -->
            <div class="flex items-center gap-1 ml-1">
                <a href="login.php" class="text-white text-sm font-medium hover:underline px-2 py-1">Masuk</a>
                <span class="text-white/50 hidden sm:inline">|</span>
                <a href="register.php"
                   class="hidden sm:inline-block bg-white text-primary text-sm font-bold
                          px-3 py-1 rounded-lg hover:bg-gray-100 transition">
                    Daftar
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</header>
