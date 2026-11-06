<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
$pdo = getDB();
$categories = $pdo->query('SELECT id,name,slug FROM categories ORDER BY id')->fetchAll();

// Ambil produk yang punya diskon untuk flash sale
$products = $pdo->query(
    'SELECT p.*, c.name AS category_name FROM products p
       JOIN categories c ON c.id=p.category_id
      WHERE p.original_price IS NOT NULL AND p.original_price > p.price
      ORDER BY (1 - p.price/p.original_price) DESC LIMIT 12'
)->fetchAll();

$pageTitle = 'Flash Sale';
require_once __DIR__ . '/includes/header.php';
function e(string $s): string { return htmlspecialchars($s,ENT_QUOTES|ENT_HTML5,'UTF-8'); }
function formatRupiah(float $a): string { return 'Rp '.number_format($a,0,',','.'); }

// Waktu akhir flash sale: tengah malam hari ini
$endTime = mktime(23, 59, 59, date('n'), date('j'), date('Y'));
?>
<div class="max-w-screen-xl mx-auto px-4 py-4 text-sm text-gray-500 flex items-center gap-1.5">
    <a href="index.php" class="hover:text-primary transition">Beranda</a><span>/</span>
    <span class="text-gray-800 font-medium">Flash Sale</span>
</div>
<main class="max-w-screen-xl mx-auto px-4 pb-16">
    <!-- Banner Flash Sale -->
    <div class="bg-gradient-to-r from-orange-500 to-red-600 rounded-2xl p-8 text-white mb-8 flex flex-col sm:flex-row items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-8 h-8 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                </svg>
                <h1 class="text-3xl font-extrabold">Flash Sale</h1>
            </div>
            <p class="text-orange-100 text-lg">Diskon hingga 50% — hanya hari ini!</p>
        </div>
        <!-- Countdown Timer -->
        <div class="bg-white/20 rounded-2xl px-6 py-4 text-center flex-shrink-0">
            <p class="text-white/80 text-xs font-bold uppercase tracking-wide mb-2">Berakhir dalam</p>
            <div class="flex items-center gap-3" id="countdown">
                <div class="text-center">
                    <p class="text-3xl font-extrabold" id="ctHours">00</p>
                    <p class="text-white/70 text-xs mt-0.5">Jam</p>
                </div>
                <span class="text-2xl font-bold text-white/60">:</span>
                <div class="text-center">
                    <p class="text-3xl font-extrabold" id="ctMinutes">00</p>
                    <p class="text-white/70 text-xs mt-0.5">Menit</p>
                </div>
                <span class="text-2xl font-bold text-white/60">:</span>
                <div class="text-center">
                    <p class="text-3xl font-extrabold" id="ctSeconds">00</p>
                    <p class="text-white/70 text-xs mt-0.5">Detik</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Produk Flash Sale -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php foreach ($products as $p):
            $disc = round((1 - $p['price'] / $p['original_price']) * 100);
        ?>
        <article class="bg-white rounded-2xl shadow-sm hover:shadow-md overflow-hidden flex flex-col transition group border border-orange-100">
            <a href="detail_produk.php?id=<?= (int)$p['id'] ?>" class="block relative overflow-hidden bg-gray-50 h-48">
                <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <span class="absolute top-3 left-3 bg-primary text-white text-sm font-extrabold px-3 py-1 rounded-full shadow">-<?= $disc ?>%</span>
            </a>
            <div class="p-4 flex flex-col flex-1">
                <p class="text-xs text-gray-400 uppercase font-medium mb-1"><?= e($p['category_name']) ?></p>
                <a href="detail_produk.php?id=<?= (int)$p['id'] ?>"
                   class="text-sm font-semibold text-gray-800 line-clamp-2 flex-1 hover:text-primary transition mb-3">
                    <?= e($p['name']) ?>
                </a>
                <p class="text-primary font-extrabold text-lg"><?= formatRupiah((float)$p['price']) ?></p>
                <p class="text-gray-400 text-xs line-through"><?= formatRupiah((float)$p['original_price']) ?></p>
                <button onclick="addToCart(<?= (int)$p['id'] ?>, '<?= e(addslashes($p['name'])) ?>', <?= (float)$p['price'] ?>, '<?= e($p['image_url']) ?>')"
                        class="mt-3 w-full bg-primary hover:bg-primary-dark text-white font-semibold text-sm py-2.5 rounded-xl transition">
                    Tambah ke Keranjang
                </button>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</main>

<!-- Sidebar Keranjang -->
<div id="cartOverlay" class="fixed inset-0 bg-black/40 z-40 hidden" onclick="toggleCart()"></div>
<aside id="cartSidebar" class="fixed top-0 right-0 h-full w-full sm:w-96 bg-white shadow-2xl z-50 translate-x-full transition-transform duration-300 flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 bg-primary border-b border-gray-100">
        <h2 class="text-white font-bold text-lg">Keranjang Belanja</h2>
        <button onclick="toggleCart()" class="text-white/80 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto px-5 py-4" id="cartItems">
        <p class="text-gray-400 text-center mt-16 text-sm" id="cartEmpty">Keranjang Anda masih kosong.</p>
    </div>
    <div class="border-t border-gray-100 px-5 py-4 bg-gray-50">
        <div class="flex justify-between items-center mb-4">
            <span class="text-gray-600 font-medium">Total</span>
            <span class="text-primary font-extrabold text-xl" id="cartTotal">Rp 0</span>
        </div>
        <button onclick="checkout()" class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3 rounded-xl transition disabled:opacity-50" id="checkoutBtn" disabled>Checkout Sekarang</button>
        <button onclick="clearCart()" class="w-full mt-2 text-gray-500 hover:text-primary text-sm font-medium py-2 transition">Kosongkan Keranjang</button>
    </div>
</aside>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
/* ── Countdown Timer ─────────────────────────────────── */
(function() {
    const endTime = <?= $endTime ?> * 1000;
    function tick() {
        const diff = endTime - Date.now();
        if (diff <= 0) {
            document.getElementById('ctHours').textContent   = '00';
            document.getElementById('ctMinutes').textContent = '00';
            document.getElementById('ctSeconds').textContent = '00';
            return;
        }
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        document.getElementById('ctHours').textContent   = String(h).padStart(2,'0');
        document.getElementById('ctMinutes').textContent = String(m).padStart(2,'0');
        document.getElementById('ctSeconds').textContent = String(s).padStart(2,'0');
        setTimeout(tick, 1000);
    }
    tick();
})();
</script>
