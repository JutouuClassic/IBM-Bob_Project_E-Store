<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
$pdo = getDB();
$categories = $pdo->query('SELECT id,name,slug FROM categories ORDER BY id')->fetchAll();
$pageTitle = 'Click & Pick Up';
require_once __DIR__ . '/includes/header.php';
function e(string $s): string { return htmlspecialchars($s,ENT_QUOTES|ENT_HTML5,'UTF-8'); }
?>
<div class="max-w-screen-xl mx-auto px-4 py-4 text-sm text-gray-500 flex items-center gap-1.5">
    <a href="index.php" class="hover:text-primary transition">Beranda</a><span>/</span>
    <span class="text-gray-800 font-medium">Click &amp; Pick Up</span>
</div>
<main class="max-w-screen-xl mx-auto px-4 pb-16">
    <!-- Hero Banner -->
    <div class="bg-gradient-to-br from-blue-600 to-blue-900 rounded-2xl p-8 sm:p-12 text-white mb-10">
        <span class="inline-block bg-white/20 text-white text-xs font-bold uppercase px-3 py-1 rounded-full mb-4">Layanan</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold mb-3">Click &amp; Pick Up</h1>
        <p class="text-blue-100 text-lg max-w-xl">Pesan produk secara online, lalu ambil langsung di toko terdekat kamu. Hemat ongkos kirim, produk siap dalam 2 jam!</p>
    </div>

    <!-- Cara Kerja -->
    <h2 class="text-xl font-extrabold text-gray-900 mb-6">Cara Kerjanya</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-12">
        <?php $steps = [
            ['1','Pilih Produk','Tambahkan produk ke keranjang dan pilih metode pengambilan "Click & Pick Up" saat checkout.','from-blue-500 to-blue-700'],
            ['2','Pilih Toko','Pilih lokasi toko E-Store terdekat dari daftar toko di bawah ini.','from-blue-600 to-indigo-700'],
            ['3','Ambil Produk','Datang ke toko, tunjukkan kode konfirmasi, dan ambil produk kamu!','from-indigo-600 to-purple-700'],
        ]; foreach ($steps as $s): ?>
        <div class="bg-gradient-to-br <?= $s[3] ?> rounded-2xl p-6 text-white shadow-md">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-xl font-extrabold mb-4"><?= $s[0] ?></div>
            <h3 class="font-bold text-lg mb-1"><?= $s[1] ?></h3>
            <p class="text-white/80 text-sm"><?= $s[2] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Daftar Toko -->
    <h2 class="text-xl font-extrabold text-gray-900 mb-6">Lokasi Toko E-Store</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php $stores = [
            ['E-Store Grand Indonesia','Jl. M.H. Thamrin No.1, Jakarta Pusat','Lantai 3, Unit 321','08:00 – 22:00','✓ Siap'],
            ['E-Store Pondok Indah Mall','Jl. Metro Pondok Indah, Jakarta Selatan','Lantai 1, Unit 112','10:00 – 22:00','✓ Siap'],
            ['E-Store Summarecon Mal Serpong','Jl. Boulevard Gading Serpong, Tangerang','Lantai 2, Unit 201','10:00 – 22:00','✓ Siap'],
            ['E-Store Pakuwon Mall Surabaya','Jl. Puncak Indah Lontar, Surabaya','Lantai 1, Unit A5','09:00 – 21:00','✓ Siap'],
            ['E-Store Trans Studio Mall Bandung','Jl. Gatot Subroto No.289, Bandung','Lantai 2, Unit B3','10:00 – 22:00','✓ Siap'],
            ['E-Store Mal Bali Galeria','Jl. Bypass Ngurah Rai, Denpasar','Lantai 1, Unit C12','09:00 – 21:30','✓ Siap'],
        ]; foreach ($stores as $store): ?>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-green-700 bg-green-100 px-2 py-0.5 rounded-full"><?= $store[4] ?></span>
            </div>
            <h3 class="font-bold text-gray-900 text-sm mb-1"><?= $store[0] ?></h3>
            <p class="text-gray-500 text-xs"><?= $store[1] ?></p>
            <p class="text-gray-500 text-xs"><?= $store[2] ?></p>
            <p class="text-gray-400 text-xs mt-2">🕐 <?= $store[3] ?></p>
            <button class="mt-3 w-full border border-blue-200 text-blue-700 text-xs font-semibold py-2 rounded-xl hover:bg-blue-50 transition">
                Pilih Toko Ini
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
