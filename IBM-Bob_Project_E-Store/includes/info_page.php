<?php
/**
 * includes/info_page.php
 * Template halaman informasi statis.
 * Gunakan: require dengan $infoTitle, $infoColor, $infoContent sudah di-set.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
$pdo = getDB();
$categories = $pdo->query('SELECT id,name,slug FROM categories ORDER BY id')->fetchAll();
$pageTitle = $infoTitle ?? 'Informasi';
require_once __DIR__ . '/header.php';
?>
<div class="max-w-screen-xl mx-auto px-4 py-4 text-sm text-gray-500 flex items-center gap-1.5">
    <a href="index.php" class="hover:text-primary transition">Beranda</a><span>/</span>
    <span class="text-gray-800 font-medium"><?= htmlspecialchars($infoTitle ?? '', ENT_QUOTES|ENT_HTML5,'UTF-8') ?></span>
</div>
<main class="max-w-screen-xl mx-auto px-4 pb-16">
    <div class="bg-gradient-to-br <?= $infoColor ?? 'from-gray-700 to-gray-900' ?> rounded-2xl p-8 sm:p-12 text-white mb-10">
        <h1 class="text-3xl sm:text-4xl font-extrabold mb-2"><?= htmlspecialchars($infoTitle ?? '', ENT_QUOTES|ENT_HTML5,'UTF-8') ?></h1>
        <p class="text-white/80 text-lg"><?= $infoSubtitle ?? '' ?></p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-10 prose max-w-none text-gray-700 leading-relaxed">
        <?= $infoContent ?? '' ?>
    </div>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
