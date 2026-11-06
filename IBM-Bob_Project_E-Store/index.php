<?php
/**
 * index.php
 * Halaman utama E-Store – katalog produk & promo
 * Menggunakan shared header/footer + RBAC
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$pdo = getDB();

/* ── Ambil semua kategori ──────────────────────────────── */
$stmtCat    = $pdo->query('SELECT id, name, slug, icon FROM categories ORDER BY id');
$categories = $stmtCat->fetchAll();

/* ── Filter kategori aktif ─────────────────────────────── */
$activeCategoryId = null;
if (!empty($_GET['category'])) {
    $slug     = trim($_GET['category']);
    $stmtSlug = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
    $stmtSlug->execute([$slug]);
    $found = $stmtSlug->fetch();
    if ($found) $activeCategoryId = (int) $found['id'];
}

/* ── Ambil produk ──────────────────────────────────────── */
if ($activeCategoryId) {
    $stmtProd = $pdo->prepare(
        'SELECT p.*, c.name AS category_name FROM products p
           JOIN categories c ON c.id = p.category_id
          WHERE p.category_id = ?
          ORDER BY p.is_featured DESC, p.created_at DESC'
    );
    $stmtProd->execute([$activeCategoryId]);
} else {
    $stmtProd = $pdo->query(
        'SELECT p.*, c.name AS category_name FROM products p
           JOIN categories c ON c.id = p.category_id
          ORDER BY p.is_featured DESC, p.created_at DESC'
    );
}
$products = $stmtProd->fetchAll();

/* ── Produk featured untuk hero carousel ──────────────── */
$stmtFeatured = $pdo->query(
    'SELECT p.*, c.name AS category_name FROM products p
       JOIN categories c ON c.id = p.category_id
      WHERE p.is_featured = 1
      ORDER BY p.created_at DESC LIMIT 5'
);
$featuredProducts = $stmtFeatured->fetchAll();

/* ── Flash messages ────────────────────────────────────── */
$flashSuccess = flashSuccess();
$flashError   = flashError();
$deletedMsg   = isset($_GET['deleted']) ? 'Produk berhasil dihapus.' : '';

/* ── Helpers ────────────────────────────────────────────── */
function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

$pageTitle = 'Toko Gadget Terlengkap';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Sub-Header Navigasi Kategori -->
<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-screen-xl mx-auto px-4">
        <div class="flex items-center gap-1 overflow-x-auto scrollbar-hide" id="categoryNav">
            <a href="index.php"
               class="nav-cat-link flex-shrink-0 px-4 py-3 text-sm font-semibold whitespace-nowrap transition border-b-2
                      <?= !$activeCategoryId ? 'border-primary text-primary' : 'border-transparent text-gray-600 hover:text-primary hover:border-primary' ?>">
                Semua
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="index.php?category=<?= e($cat['slug']) ?>"
               class="nav-cat-link flex-shrink-0 px-4 py-3 text-sm font-medium whitespace-nowrap transition border-b-2
                      <?= ($activeCategoryId === (int)$cat['id'])
                          ? 'border-primary text-primary font-semibold'
                          : 'border-transparent text-gray-600 hover:text-primary hover:border-primary' ?>">
                <?= e($cat['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>

<!-- Flash Alerts -->
<?php if ($flashSuccess): ?>
<div class="max-w-screen-xl mx-auto px-4 mt-4">
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3 text-sm font-medium flex items-center gap-2">
        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <?= e($flashSuccess) ?>
    </div>
</div>
<?php endif; ?>
<?php if ($flashError): ?>
<div class="max-w-screen-xl mx-auto px-4 mt-4">
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3 text-sm font-medium flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <?= e($flashError) ?>
    </div>
</div>
<?php endif; ?>

<!-- Hero Section -->
<section class="max-w-screen-xl mx-auto px-4 pt-6 pb-4">
    <div class="grid grid-cols-12 gap-4">

        <!-- Hero Carousel (7/12) -->
        <div class="col-span-12 lg:col-span-7 relative overflow-hidden rounded-2xl shadow-md bg-gray-900 carousel-wrapper" id="heroCarousel">
            <div class="carousel-track flex transition-transform duration-500 ease-in-out" id="carouselTrack" style="min-height:320px">
                <?php if (empty($featuredProducts)): ?>
                <div class="carousel-slide flex-shrink-0 w-full relative flex items-center justify-center bg-gradient-to-br from-primary to-red-900" style="min-height:320px">
                    <p class="text-white text-xl font-bold">Selamat Datang di E-Store</p>
                </div>
                <?php else: ?>
                <?php foreach ($featuredProducts as $idx => $fp): ?>
                <div class="carousel-slide flex-shrink-0 w-full relative overflow-hidden bg-gradient-to-br from-gray-900 to-gray-700" style="min-height:320px">
                    <img src="<?= e($fp['image_url']) ?>" alt="<?= e($fp['name']) ?>"
                         class="absolute inset-0 w-full h-full object-cover opacity-60 select-none" draggable="false">
                    <div class="relative z-10 flex flex-col justify-end h-full p-6 sm:p-8 bg-gradient-to-t from-black/70 via-transparent to-transparent" style="min-height:320px">
                        <?php if ($fp['badge']): ?>
                        <span class="inline-block mb-2 bg-accent text-gray-900 text-xs font-bold px-2.5 py-1 rounded-full w-fit"><?= e($fp['badge']) ?></span>
                        <?php endif; ?>
                        <p class="text-white/70 text-sm mb-1"><?= e($fp['category_name']) ?></p>
                        <h2 class="text-white text-xl sm:text-2xl lg:text-3xl font-extrabold leading-tight mb-2"><?= e($fp['name']) ?></h2>
                        <p class="text-white/80 text-sm hidden sm:block mb-4 line-clamp-2"><?= e($fp['description'] ?? '') ?></p>
                        <div class="flex items-center gap-4">
                            <div>
                                <p class="text-white font-extrabold text-lg sm:text-xl"><?= formatRupiah((float)$fp['price']) ?></p>
                                <?php if ($fp['original_price']): ?>
                                <p class="text-white/50 text-xs line-through"><?= formatRupiah((float)$fp['original_price']) ?></p>
                                <?php endif; ?>
                            </div>
                            <a href="detail_produk.php?id=<?= (int)$fp['id'] ?>"
                               class="bg-primary hover:bg-primary-dark text-white font-bold text-sm px-5 py-2.5 rounded-xl transition hover:scale-105 active:scale-95">
                                Beli Sekarang
                            </a>
                        </div>
                    </div>
                    <span class="absolute top-4 right-4 text-white/50 text-xs font-medium z-10"><?= $idx + 1 ?>/<?= count($featuredProducts) ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button onclick="carouselPrev()" class="carousel-nav left-3 hover:bg-white/30 focus:outline-none" aria-label="Previous">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button onclick="carouselNext()" class="carousel-nav right-3 hover:bg-white/30 focus:outline-none" aria-label="Next">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 z-10" id="carouselDots">
                <?php foreach ($featuredProducts as $idx => $_): ?>
                <button onclick="goToSlide(<?= $idx ?>)"
                        class="carousel-dot w-2 h-2 rounded-full transition-all <?= $idx === 0 ? 'bg-white w-4' : 'bg-white/40' ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Promo Grid 2x2 (5/12) -->
        <div class="col-span-12 lg:col-span-5 grid grid-cols-2 gap-4">
            <a href="layanan_pickup.php" class="promo-card bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-4 flex flex-col justify-between min-h-[130px] group shadow-md hover:shadow-lg transition">
                <div>
                    <span class="inline-block bg-white/20 text-white text-[10px] font-bold uppercase px-2 py-0.5 rounded-full mb-2">Layanan</span>
                    <p class="text-white font-extrabold text-base leading-tight">Click &amp;<br>Pick Up</p>
                    <p class="text-white/70 text-xs mt-1">Pesan online,<br>ambil di toko</p>
                </div>
                <span class="text-white/80 text-xs font-medium group-hover:underline">Selengkapnya →</span>
            </a>
            <a href="flash_sale.php" class="promo-card bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl p-4 flex flex-col justify-between min-h-[130px] group shadow-md hover:shadow-lg transition">
                <div>
                    <span class="inline-block bg-white/20 text-white text-[10px] font-bold uppercase px-2 py-0.5 rounded-full mb-2 flex items-center gap-1 w-fit">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                        Flash Sale
                    </span>
                    <p class="text-white font-extrabold text-base leading-tight">Diskon<br>Hingga 50%</p>
                    <p class="text-white/70 text-xs mt-1">Hanya hari ini!</p>
                </div>
                <span class="text-white/80 text-xs font-medium group-hover:underline">Lihat Promo →</span>
            </a>
            <a href="promo_bank.php" class="promo-card bg-gradient-to-br from-emerald-500 to-teal-700 rounded-2xl p-4 flex flex-col justify-between min-h-[130px] group shadow-md hover:shadow-lg transition">
                <div>
                    <span class="inline-block bg-white/20 text-white text-[10px] font-bold uppercase px-2 py-0.5 rounded-full mb-2">Cicilan 0%</span>
                    <p class="text-white font-extrabold text-base leading-tight">Promo Bank<br>&amp; Kartu Kredit</p>
                    <p class="text-white/70 text-xs mt-1">Cicilan hingga 24 bln</p>
                </div>
                <span class="text-white/80 text-xs font-medium group-hover:underline">Cek Bank →</span>
            </a>
            <a href="express_delivery.php" class="promo-card bg-gradient-to-br from-violet-600 to-purple-800 rounded-2xl p-4 flex flex-col justify-between min-h-[130px] group shadow-md hover:shadow-lg transition">
                <div>
                    <span class="inline-block bg-white/20 text-white text-[10px] font-bold uppercase px-2 py-0.5 rounded-full mb-2">Pengiriman</span>
                    <p class="text-white font-extrabold text-base leading-tight">Express<br>Delivery</p>
                    <p class="text-white/70 text-xs mt-1">Tiba dalam 2 jam</p>
                </div>
                <span class="text-white/80 text-xs font-medium group-hover:underline">Pesan Sekarang →</span>
            </a>
        </div>
    </div>
</section>

<!-- Katalog Produk -->
<main class="max-w-screen-xl mx-auto px-4 pb-16">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900">
                <?= $activeCategoryId
                    ? e(array_column($categories, 'name', 'id')[$activeCategoryId] ?? 'Produk')
                    : 'Semua Produk' ?>
            </h2>
            <p class="text-gray-500 text-sm" id="productCount">Menampilkan <?= count($products) ?> produk</p>
        </div>
        <!-- Tombol Tambah Produk — hanya admin -->
        <?php if (isAdmin()): ?>
        <a href="tambah_produk.php"
           class="flex items-center gap-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold
                  px-4 py-2 rounded-xl transition hover:scale-105 active:scale-95 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Produk
        </a>
        <?php endif; ?>
    </div>

    <!-- Grid produk -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5" id="productGrid">
        <?php if (empty($products)): ?>
        <div class="col-span-full text-center py-16 text-gray-400">
            <p class="text-lg font-semibold">Produk tidak ditemukan.</p>
        </div>
        <?php else: ?>
        <?php foreach ($products as $prod): ?>
        <?php
            $hasDiscount = !empty($prod['original_price']) && $prod['original_price'] > $prod['price'];
            $discountPct = $hasDiscount ? round((1 - $prod['price'] / $prod['original_price']) * 100) : 0;
        ?>
        <article class="product-card bg-white rounded-2xl shadow-sm hover:shadow-md overflow-hidden flex flex-col transition group"
                 data-name="<?= e(strtolower($prod['name'])) ?>"
                 data-category="<?= e(strtolower($prod['category_name'])) ?>">
            <!-- Gambar — klik ke detail -->
            <a href="detail_produk.php?id=<?= (int)$prod['id'] ?>" class="block relative overflow-hidden bg-gray-50 h-52">
                <img src="<?= e($prod['image_url']) ?>" alt="<?= e($prod['name']) ?>"
                     loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <?php if ($prod['badge']): ?>
                <span class="absolute top-3 left-3 bg-accent text-gray-900 text-[11px] font-bold px-2.5 py-0.5 rounded-full shadow-sm"><?= e($prod['badge']) ?></span>
                <?php endif; ?>
                <?php if ($hasDiscount): ?>
                <span class="absolute top-3 right-3 bg-primary text-white text-[11px] font-bold px-2 py-0.5 rounded-full">-<?= $discountPct ?>%</span>
                <?php endif; ?>
                <?php if ((int)$prod['stock'] === 0): ?>
                <div class="absolute inset-0 bg-white/60 flex items-center justify-center">
                    <span class="bg-gray-700 text-white text-sm font-bold px-4 py-1.5 rounded-full">Stok Habis</span>
                </div>
                <?php endif; ?>
            </a>
            <!-- Info -->
            <div class="p-4 flex flex-col flex-1">
                <p class="text-xs text-gray-400 mb-1 font-medium uppercase tracking-wide"><?= e($prod['category_name']) ?></p>
                <a href="detail_produk.php?id=<?= (int)$prod['id'] ?>"
                   class="text-sm font-semibold text-gray-800 leading-snug mb-2 line-clamp-2 flex-1 hover:text-primary transition">
                    <?= e($prod['name']) ?>
                </a>
                <div class="mb-3">
                    <p class="text-primary font-extrabold text-lg leading-none"><?= formatRupiah((float)$prod['price']) ?></p>
                    <?php if ($hasDiscount): ?>
                    <p class="text-gray-400 text-xs line-through mt-0.5"><?= formatRupiah((float)$prod['original_price']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="flex gap-2">
                    <button onclick="addToCart(<?= (int)$prod['id'] ?>, '<?= e(addslashes($prod['name'])) ?>', <?= (float)$prod['price'] ?>, '<?= e($prod['image_url']) ?>')"
                            <?= (int)$prod['stock'] === 0 ? 'disabled' : '' ?>
                            class="flex-1 flex items-center justify-center gap-1.5 bg-primary hover:bg-primary-dark
                                   disabled:bg-gray-300 disabled:cursor-not-allowed
                                   text-white font-semibold text-sm py-2.5 rounded-xl transition hover:scale-[1.02] active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Keranjang
                    </button>
                    <!-- Tombol Edit — hanya admin -->
                    <?php if (isAdmin()): ?>
                    <a href="edit_produk.php?id=<?= (int)$prod['id'] ?>"
                       class="flex items-center justify-center gap-1 border border-gray-200
                              hover:border-primary hover:text-primary text-gray-500
                              text-sm font-medium px-3 py-2.5 rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div id="noSearchResult" class="hidden text-center py-16 text-gray-400">
        <p class="text-lg font-semibold">Tidak ada produk yang cocok.</p>
    </div>
</main>

<!-- Sidebar Keranjang -->
<div id="cartOverlay" class="fixed inset-0 bg-black/40 z-40 hidden" onclick="toggleCart()"></div>
<aside id="cartSidebar"
       class="fixed top-0 right-0 h-full w-full sm:w-96 bg-white shadow-2xl z-50
              translate-x-full transition-transform duration-300 flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-primary">
        <h2 class="text-white font-bold text-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Keranjang Belanja
        </h2>
        <button onclick="toggleCart()" class="text-white/80 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
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
        <button onclick="checkout()"
                class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3 rounded-xl
                       transition hover:scale-[1.02] active:scale-95 disabled:opacity-50"
                id="checkoutBtn" disabled>
            Checkout Sekarang
        </button>
        <button onclick="clearCart()" class="w-full mt-2 text-gray-500 hover:text-primary text-sm font-medium py-2 transition">
            Kosongkan Keranjang
        </button>
    </div>
</aside>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
