<?php
/**
 * detail_produk.php
 * Halaman detail produk: varian RAM/Warna, quantity, keranjang & beli langsung
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$pdo = getDB();

$productId = (int) ($_GET['id'] ?? 0);
if ($productId <= 0) { header('Location: index.php'); exit; }

/* ── Ambil data produk ───────────────────────────────────── */
$stmtP = $pdo->prepare(
    'SELECT p.*, c.name AS category_name FROM products p
       JOIN categories c ON c.id = p.category_id
      WHERE p.id = ? LIMIT 1'
);
$stmtP->execute([$productId]);
$product = $stmtP->fetch();
if (!$product) { header('Location: index.php'); exit; }

/* ── Ambil varian produk ─────────────────────────────────── */
$stmtV = $pdo->prepare(
    'SELECT * FROM product_variants WHERE product_id = ? ORDER BY tambahan_harga ASC'
);
$stmtV->execute([$productId]);
$variants = $stmtV->fetchAll();

/* ── Kumpulkan pilihan unik ──────────────────────────────── */
$uniqueRam    = array_unique(array_column($variants, 'ram_storage'));
$uniqueWarna  = array_unique(array_column($variants, 'warna'));
$hasVariants  = !empty($variants);

/* ── Ambil kategori (untuk header) ──────────────────────── */
$categories = $pdo->query('SELECT id, name, slug FROM categories ORDER BY id')->fetchAll();

/* ── Helpers ─────────────────────────────────────────────── */
function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

$hasDiscount = !empty($product['original_price']) && $product['original_price'] > $product['price'];
$discountPct = $hasDiscount ? round((1 - $product['price'] / $product['original_price']) * 100) : 0;

$pageTitle = $product['name'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb -->
<div class="max-w-screen-xl mx-auto px-4 py-3 text-sm text-gray-500 flex items-center gap-1.5">
    <a href="index.php" class="hover:text-primary transition">Beranda</a>
    <span>/</span>
    <a href="index.php?category=<?= e($product['category_name']) ?>" class="hover:text-primary transition"><?= e($product['category_name']) ?></a>
    <span>/</span>
    <span class="text-gray-800 font-medium truncate"><?= e($product['name']) ?></span>
</div>

<main class="max-w-screen-xl mx-auto px-4 pb-16">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

        <!-- Kolom Kiri: Gambar -->
        <div>
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 aspect-square max-h-[480px]">
                <img id="mainImage"
                     src="<?= e($product['image_url']) ?>"
                     alt="<?= e($product['name']) ?>"
                     class="w-full h-full object-contain p-6">
            </div>
        </div>

        <!-- Kolom Kanan: Info & Aksi -->
        <div class="flex flex-col gap-5">

            <!-- Header produk -->
            <div>
                <span class="text-xs font-bold text-primary uppercase tracking-wide"><?= e($product['category_name']) ?></span>
                <?php if ($product['badge']): ?>
                <span class="ml-2 bg-accent text-gray-900 text-xs font-bold px-2 py-0.5 rounded-full"><?= e($product['badge']) ?></span>
                <?php endif; ?>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-1 leading-tight"><?= e($product['name']) ?></h1>
            </div>

            <!-- Harga -->
            <div class="flex items-end gap-3">
                <p id="displayPrice" class="text-3xl font-extrabold text-primary"><?= formatRupiah((float)$product['price']) ?></p>
                <?php if ($hasDiscount): ?>
                <p class="text-gray-400 text-base line-through mb-0.5"><?= formatRupiah((float)$product['original_price']) ?></p>
                <span class="bg-primary text-white text-sm font-bold px-2 py-0.5 rounded-full mb-0.5">-<?= $discountPct ?>%</span>
                <?php endif; ?>
            </div>

            <!-- Deskripsi -->
            <?php if ($product['description']): ?>
            <p class="text-gray-600 text-sm leading-relaxed"><?= e($product['description']) ?></p>
            <?php endif; ?>

            <!-- Varian -->
            <?php if ($hasVariants): ?>

            <!-- Pilih RAM/Storage -->
            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">
                    Pilih Kapasitas:
                    <span id="selectedRam" class="text-primary font-bold ml-1">—</span>
                </p>
                <div class="flex flex-wrap gap-2" id="ramOptions">
                    <?php foreach ($uniqueRam as $ram): ?>
                    <button type="button"
                            onclick="selectRam(this, '<?= e(addslashes($ram)) ?>')"
                            class="variant-btn px-4 py-2 border-2 border-gray-200 rounded-xl text-sm font-semibold text-gray-700
                                   hover:border-primary hover:text-primary transition">
                        <?= e($ram) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pilih Warna -->
            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">
                    Pilih Warna:
                    <span id="selectedColor" class="text-primary font-bold ml-1">—</span>
                </p>
                <div class="flex flex-wrap gap-2" id="colorOptions">
                    <?php
                    $colorMap = [
                        'Titanium Black'   => '#2d2d2d',
                        'Titanium Gray'    => '#8d8d8d',
                        'Titanium Violet'  => '#7c5cd8',
                        'Black Titanium'   => '#1a1a1a',
                        'Natural Titanium' => '#c4a882',
                        'White Titanium'   => '#f0f0f0',
                        'Ice Blue'         => '#a8d8f0',
                        'Phantom Silver'   => '#b0b8c4',
                    ];
                    foreach ($uniqueWarna as $w):
                        $hex = $colorMap[$w] ?? '#9ca3af';
                    ?>
                    <button type="button"
                            onclick="selectColor(this, '<?= e(addslashes($w)) ?>')"
                            class="color-btn flex items-center gap-2 px-3 py-2 border-2 border-gray-200 rounded-xl text-sm font-medium text-gray-700
                                   hover:border-primary transition">
                        <span class="w-4 h-4 rounded-full border border-gray-300 flex-shrink-0"
                              style="background-color: <?= $hex ?>;"></span>
                        <?= e($w) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php endif; /* end hasVariants */ ?>

            <!-- Pilih Jumlah -->
            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">Jumlah:</p>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="changeQtyDetail(-1)"
                            class="w-9 h-9 rounded-xl border-2 border-gray-200 flex items-center justify-center
                                   text-gray-600 hover:border-primary hover:text-primary transition font-bold text-lg">−</button>
                    <span id="detailQty" class="text-lg font-bold text-gray-900 w-8 text-center">1</span>
                    <button type="button" onclick="changeQtyDetail(1)"
                            class="w-9 h-9 rounded-xl border-2 border-gray-200 flex items-center justify-center
                                   text-gray-600 hover:border-primary hover:text-primary transition font-bold text-lg">+</button>
                    <span class="text-sm text-gray-400 ml-1">
                        Stok: <strong class="text-gray-700"><?= (int)$product['stock'] ?></strong>
                    </span>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="detailAddToCart()"
                        class="flex-1 flex items-center justify-center gap-2 border-2 border-primary text-primary
                               font-bold py-3 rounded-xl hover:bg-red-50 transition active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Masukkan Keranjang
                </button>
                <button type="button" onclick="detailBuyNow()"
                        class="flex-1 flex items-center justify-center gap-2 bg-primary hover:bg-primary-dark text-white
                               font-bold py-3 rounded-xl transition active:scale-95 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Beli Langsung
                </button>
            </div>

            <!-- Info tambahan -->
            <div class="grid grid-cols-3 gap-3 pt-2">
                <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                    <svg class="w-5 h-5 mx-auto text-primary mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <p class="text-xs text-gray-600 font-medium">Garansi Resmi</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                    <svg class="w-5 h-5 mx-auto text-primary mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="text-xs text-gray-600 font-medium">Produk Original</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                    <svg class="w-5 h-5 mx-auto text-primary mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <p class="text-xs text-gray-600 font-medium">Cicilan 0%</p>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Sidebar Keranjang -->
<div id="cartOverlay" class="fixed inset-0 bg-black/40 z-40 hidden" onclick="toggleCart()"></div>
<aside id="cartSidebar" class="fixed top-0 right-0 h-full w-full sm:w-96 bg-white shadow-2xl z-50 translate-x-full transition-transform duration-300 flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-primary">
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
/* ── Data varian dari PHP → JS ────────────────────────── */
const PRODUCT_ID    = <?= (int)$product['id'] ?>;
const PRODUCT_NAME  = <?= json_encode($product['name']) ?>;
const BASE_PRICE    = <?= (float)$product['price'] ?>;
const PRODUCT_IMG   = <?= json_encode($product['image_url']) ?>;
const HAS_VARIANTS  = <?= $hasVariants ? 'true' : 'false' ?>;
const VARIANTS      = <?= json_encode($variants) ?>;

let selectedRam   = null;
let selectedColor = null;
let detailQtyVal  = 1;
let currentPrice  = BASE_PRICE;

/* ── Pilih varian RAM ─────────────────────────────────── */
function selectRam(btn, ram) {
    document.querySelectorAll('#ramOptions .variant-btn').forEach(b => {
        b.classList.remove('border-primary', 'text-primary', 'bg-red-50');
    });
    btn.classList.add('border-primary', 'text-primary', 'bg-red-50');
    selectedRam = ram;
    document.getElementById('selectedRam').textContent = ram;
    updatePrice();
}

/* ── Pilih warna ──────────────────────────────────────── */
function selectColor(btn, color) {
    document.querySelectorAll('#colorOptions .color-btn').forEach(b => {
        b.classList.remove('border-primary', 'bg-red-50');
    });
    btn.classList.add('border-primary', 'bg-red-50');
    selectedColor = color;
    document.getElementById('selectedColor').textContent = color;
}

/* ── Update harga berdasarkan varian terpilih ─────────── */
function updatePrice() {
    if (!selectedRam) { currentPrice = BASE_PRICE; }
    else {
        const variant = VARIANTS.find(v => v.ram_storage === selectedRam);
        currentPrice = BASE_PRICE + (variant ? parseInt(variant.tambahan_harga) : 0);
    }
    document.getElementById('displayPrice').textContent =
        'Rp ' + currentPrice.toLocaleString('id-ID');
}

/* ── Ubah jumlah ──────────────────────────────────────── */
function changeQtyDetail(delta) {
    detailQtyVal = Math.max(1, detailQtyVal + delta);
    document.getElementById('detailQty').textContent = detailQtyVal;
}

/* ── Validasi pilihan varian sebelum aksi ─────────────── */
function validateVariant() {
    if (!HAS_VARIANTS) return true;
    if (!selectedRam) {
        showToast('⚠️ Pilih kapasitas RAM/Storage terlebih dahulu.');
        return false;
    }
    if (!selectedColor) {
        showToast('⚠️ Pilih warna terlebih dahulu.');
        return false;
    }
    return true;
}

/* ── Masukkan ke keranjang ────────────────────────────── */
function detailAddToCart() {
    if (!validateVariant()) return;
    const label = HAS_VARIANTS
        ? `${PRODUCT_NAME} (${selectedRam}, ${selectedColor})`
        : PRODUCT_NAME;
    for (let i = 0; i < detailQtyVal; i++) {
        Cart.addItem(PRODUCT_ID, label, currentPrice, PRODUCT_IMG);
    }
    renderCart();
    showToast(`✓ "${PRODUCT_NAME}" ditambahkan ke keranjang!`);
}

/* ── Beli langsung ────────────────────────────────────── */
function detailBuyNow() {
    if (!validateVariant()) return;
    const label = HAS_VARIANTS
        ? `${PRODUCT_NAME} (${selectedRam}, ${selectedColor})`
        : PRODUCT_NAME;
    Cart.addItem(PRODUCT_ID, label, currentPrice, PRODUCT_IMG);
    renderCart();
    showToast('🚀 Menuju checkout...');
    setTimeout(() => {
        showToast('Fitur checkout segera hadir!');
    }, 1200);
}
</script>
