<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
$pdo = getDB();
$categories = $pdo->query('SELECT id,name,slug FROM categories ORDER BY id')->fetchAll();
$pageTitle = 'Promo Bank & Cicilan 0%';
require_once __DIR__ . '/includes/header.php';
?>
<div class="max-w-screen-xl mx-auto px-4 py-4 text-sm text-gray-500 flex items-center gap-1.5">
    <a href="index.php" class="hover:text-primary transition">Beranda</a><span>/</span>
    <span class="text-gray-800 font-medium">Promo Bank &amp; Cicilan 0%</span>
</div>
<main class="max-w-screen-xl mx-auto px-4 pb-16">
    <!-- Banner -->
    <div class="bg-gradient-to-br from-emerald-500 to-teal-700 rounded-2xl p-8 sm:p-12 text-white mb-10">
        <span class="inline-block bg-white/20 text-white text-xs font-bold uppercase px-3 py-1 rounded-full mb-4">Cicilan 0%</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold mb-3">Promo Bank &amp; Kartu Kredit</h1>
        <p class="text-emerald-100 text-lg max-w-xl">Beli sekarang, bayar nanti tanpa bunga. Cicilan 0% tersedia untuk berbagai bank partner E-Store.</p>
    </div>

    <!-- Partner Bank -->
    <h2 class="text-xl font-extrabold text-gray-900 mb-6">Bank &amp; Kartu Partner</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-12">
        <?php $banks = [
            ['BCA','Cicilan 0% 3, 6, 12, 24 bulan','Min. transaksi Rp 1.000.000','Berlaku setiap hari','#003087'],
            ['Mandiri','Cicilan 0% 3, 6, 12 bulan','Min. transaksi Rp 500.000','Berlaku setiap hari','#003087'],
            ['CIMB Niaga','Cicilan 0% 3, 6, 12 bulan','Min. transaksi Rp 750.000','Sabtu & Minggu','#b91c1c'],
            ['BNI','Cicilan 0% 3, 6, 12 bulan','Min. transaksi Rp 1.000.000','Berlaku setiap hari','#1d4ed8'],
            ['BRI','Cicilan 0% 3, 6, 12 bulan','Min. transaksi Rp 1.000.000','Berlaku setiap hari','#15803d'],
            ['OVO / GoPay','Cicilan PayLater 3 & 6 bulan','Min. transaksi Rp 300.000','Berlaku setiap hari','#7c3aed'],
        ]; foreach ($banks as $b): ?>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-extrabold text-sm mb-4"
                 style="background-color:<?= $b[4] ?>">
                <?= substr($b[0], 0, 3) ?>
            </div>
            <h3 class="font-extrabold text-gray-900 text-lg mb-1"><?= $b[0] ?></h3>
            <p class="text-emerald-600 font-semibold text-sm"><?= $b[1] ?></p>
            <p class="text-gray-500 text-xs mt-1"><?= $b[2] ?></p>
            <p class="text-gray-400 text-xs">📅 <?= $b[3] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Kalkulator Cicilan -->
    <h2 class="text-xl font-extrabold text-gray-900 mb-6">Simulasi Cicilan</h2>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 max-w-lg">
        <div class="space-y-4">
            <div>
                <label class="form-label">Harga Produk (Rp)</label>
                <input type="number" id="simHarga" value="10000000" min="0" step="100000"
                       oninput="hitungCicilan()" class="form-input">
            </div>
            <div>
                <label class="form-label">Tenor Cicilan</label>
                <select id="simTenor" onchange="hitungCicilan()" class="form-input">
                    <option value="3">3 bulan</option>
                    <option value="6" selected>6 bulan</option>
                    <option value="12">12 bulan</option>
                    <option value="24">24 bulan</option>
                </select>
            </div>
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center">
                <p class="text-emerald-600 text-sm font-medium mb-1">Cicilan per bulan</p>
                <p id="hasilCicilan" class="text-3xl font-extrabold text-emerald-700">Rp 0</p>
                <p class="text-xs text-gray-400 mt-1">*Cicilan 0% bunga, tanpa biaya tambahan</p>
            </div>
        </div>
    </div>

    <!-- S&K -->
    <div class="mt-10 bg-gray-50 border border-gray-200 rounded-2xl p-6">
        <h3 class="font-bold text-gray-900 mb-3">Syarat &amp; Ketentuan</h3>
        <ul class="text-sm text-gray-600 space-y-1.5 list-disc list-inside">
            <li>Promo berlaku untuk kartu kredit yang diterbitkan di Indonesia.</li>
            <li>Minimum transaksi sesuai dengan ketentuan masing-masing bank.</li>
            <li>Cicilan 0% tidak dapat digabungkan dengan promo lainnya.</li>
            <li>E-Store berhak mengubah ketentuan promo sewaktu-waktu tanpa pemberitahuan sebelumnya.</li>
            <li>Untuk informasi lebih lanjut, hubungi customer service kami.</li>
        </ul>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script>
function hitungCicilan() {
    const harga = parseFloat(document.getElementById('simHarga').value) || 0;
    const tenor = parseInt(document.getElementById('simTenor').value) || 1;
    const cicilan = Math.ceil(harga / tenor);
    document.getElementById('hasilCicilan').textContent =
        'Rp ' + cicilan.toLocaleString('id-ID');
}
hitungCicilan();
</script>
