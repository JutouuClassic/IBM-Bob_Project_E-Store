<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
$pdo = getDB();
$categories = $pdo->query('SELECT id,name,slug FROM categories ORDER BY id')->fetchAll();
$pageTitle = 'Express Delivery';
require_once __DIR__ . '/includes/header.php';
?>
<div class="max-w-screen-xl mx-auto px-4 py-4 text-sm text-gray-500 flex items-center gap-1.5">
    <a href="index.php" class="hover:text-primary transition">Beranda</a><span>/</span>
    <span class="text-gray-800 font-medium">Express Delivery</span>
</div>
<main class="max-w-screen-xl mx-auto px-4 pb-16">
    <!-- Banner -->
    <div class="bg-gradient-to-br from-violet-600 to-purple-800 rounded-2xl p-8 sm:p-12 text-white mb-10">
        <span class="inline-block bg-white/20 text-white text-xs font-bold uppercase px-3 py-1 rounded-full mb-4">Pengiriman</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold mb-3">Express Delivery</h1>
        <p class="text-purple-100 text-lg max-w-xl">Pesan sebelum pukul 15.00 — produk tiba di tangan kamu dalam 2 jam! Tersedia di area tertentu.</p>
    </div>

    <!-- Cek Jangkauan -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-12 max-w-xl">
        <h2 class="text-xl font-extrabold text-gray-900 mb-5">Cek Jangkauan Pengiriman</h2>
        <div class="flex gap-3">
            <input type="text" id="kodePosInput" placeholder="Masukkan kode pos atau nama kota…"
                   class="form-input flex-1">
            <button onclick="cekJangkauan()"
                    class="bg-primary hover:bg-primary-dark text-white font-bold px-5 py-2.5 rounded-xl transition flex-shrink-0">
                Cek
            </button>
        </div>
        <div id="hasilCek" class="mt-4 hidden"></div>
    </div>

    <!-- Area Terjangkau -->
    <h2 class="text-xl font-extrabold text-gray-900 mb-6">Area Yang Terjangkau</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-12">
        <?php $areas = [
            ['Jakarta Pusat','Semua kelurahan','⚡ 1-2 Jam'],
            ['Jakarta Selatan','Semua kelurahan','⚡ 1-2 Jam'],
            ['Jakarta Barat','Semua kelurahan','⚡ 2 Jam'],
            ['Jakarta Utara','Semua kelurahan','⚡ 2 Jam'],
            ['Jakarta Timur','Semua kelurahan','⚡ 2 Jam'],
            ['Tangerang Selatan','BSD, Serpong, Ciputat','⚡ 2-3 Jam'],
            ['Depok','Semua kecamatan','⚡ 2-3 Jam'],
            ['Bekasi Kota','Semua kecamatan','⚡ 2-3 Jam'],
            ['Surabaya','Area tertentu','⚡ 2-3 Jam'],
        ]; foreach ($areas as $a): ?>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 text-sm"><?= $a[0] ?></h3>
                <p class="text-gray-500 text-xs"><?= $a[1] ?></p>
                <p class="text-purple-600 text-xs font-bold mt-0.5"><?= $a[2] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Ketentuan -->
    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6">
        <h3 class="font-bold text-gray-900 mb-3">Syarat &amp; Ketentuan</h3>
        <ul class="text-sm text-gray-600 space-y-1.5 list-disc list-inside">
            <li>Pemesanan sebelum pukul 15.00 WIB untuk pengiriman hari yang sama.</li>
            <li>Biaya pengiriman express flat Rp 25.000 untuk semua area terjangkau.</li>
            <li>Ketersediaan area dapat berubah. Gunakan fitur cek di atas untuk verifikasi.</li>
            <li>Tidak tersedia untuk hari Minggu dan hari libur nasional.</li>
        </ul>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script>
const coveredAreas = ['jakarta','tangerang','depok','bekasi','surabaya','bandung'];
function cekJangkauan() {
    const q = document.getElementById('kodePosInput').value.trim().toLowerCase();
    const res = document.getElementById('hasilCek');
    if (!q) { res.classList.add('hidden'); return; }
    const covered = coveredAreas.some(a => q.includes(a)) || /^\d{5}$/.test(q);
    res.className = 'mt-4 rounded-xl px-5 py-4 flex items-center gap-3 text-sm font-medium';
    if (covered) {
        res.className += ' bg-green-50 border border-green-200 text-green-800';
        res.innerHTML = '<svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg><span>Selamat! Area <strong>' + q + '</strong> terjangkau Express Delivery. Estimasi tiba: <strong>1-3 jam</strong>.</span>';
    } else {
        res.className += ' bg-red-50 border border-red-200 text-red-800';
        res.innerHTML = '<svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span>Area <strong>' + q + '</strong> belum terjangkau Express Delivery. Gunakan layanan pengiriman reguler.</span>';
    }
    res.classList.remove('hidden');
}
document.getElementById('kodePosInput').addEventListener('keydown', e => { if(e.key==='Enter') cekJangkauan(); });
</script>
