<?php
/**
 * tambah_produk.php
 * Halaman admin untuk menambah produk baru ke database
 * Mendukung upload foto lokal ATAU URL gambar eksternal
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

/* ── Hanya admin yang boleh akses halaman ini ─────────── */
requireAdmin();

$pdo = getDB();

/* ── Konfigurasi upload ───────────────────────────────────── */
define('UPLOAD_DIR',      __DIR__ . '/images/products/');
define('UPLOAD_URL_PATH', 'images/products/');
define('MAX_FILE_SIZE',   3 * 1024 * 1024); // 3 MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_EXTS',  ['jpg', 'jpeg', 'png', 'webp', 'gif']);

/* ── Pastikan folder upload ada ──────────────────────────── */
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

/* ── Ambil kategori untuk dropdown ───────────────────────── */
$stmtCat    = $pdo->query('SELECT id, name FROM categories ORDER BY name');
$categories = $stmtCat->fetchAll();

/* ── Inisialisasi variabel feedback ──────────────────────── */
$success = '';
$errors  = [];
$old     = [];

/* ── Proses form POST ────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old = $_POST;

    // Ambil & sanitasi input teks
    $name           = trim($_POST['name']           ?? '');
    $category_id    = (int) ($_POST['category_id']  ?? 0);
    $price          = trim($_POST['price']           ?? '');
    $original_price = trim($_POST['original_price']  ?? '');
    $stock          = (int) ($_POST['stock']         ?? 0);
    $description    = trim($_POST['description']     ?? '');
    $image_url      = trim($_POST['image_url']       ?? '');
    $badge          = trim($_POST['badge']           ?? '');
    $is_featured    = isset($_POST['is_featured']) ? 1 : 0;

    /* ── Validasi teks ────────────────────────────────────── */
    if ($name === '')       $errors['name']        = 'Nama produk wajib diisi.';
    if ($category_id === 0) $errors['category_id'] = 'Pilih kategori produk.';
    if ($price === '' || !is_numeric($price) || (float)$price <= 0)
                            $errors['price']       = 'Harga harus berupa angka positif.';
    if ($stock < 0)         $errors['stock']       = 'Stok tidak boleh negatif.';

    if ($original_price !== '' && (!is_numeric($original_price) || (float)$original_price <= 0)) {
        $errors['original_price'] = 'Harga asli harus berupa angka positif.';
    }

    /* ── Proses gambar: prioritaskan file upload ──────────── */
    $finalImageUrl = '';
    $uploadMode    = $_POST['image_mode'] ?? 'url'; // 'upload' atau 'url'

    if ($uploadMode === 'upload' && !empty($_FILES['image_file']['name'])) {
        // ── Jalur Upload File ───────────────────────────────
        $file     = $_FILES['image_file'];
        $fileSize = $file['size'];
        $fileTmp  = $file['tmp_name'];
        $fileExt  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Deteksi MIME type yang sebenarnya (bukan hanya dari nama file)
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileTmp);
        finfo_close($finfo);

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['image_file'] = 'Terjadi kesalahan saat upload. Coba lagi.';
        } elseif ($fileSize > MAX_FILE_SIZE) {
            $errors['image_file'] = 'Ukuran file maksimal 3 MB.';
        } elseif (!in_array($mimeType, ALLOWED_TYPES, true) || !in_array($fileExt, ALLOWED_EXTS, true)) {
            $errors['image_file'] = 'Format file tidak didukung. Gunakan JPG, PNG, WebP, atau GIF.';
        } else {
            // Nama file unik: timestamp + random + ekstensi asli
            $newFilename   = time() . '_' . bin2hex(random_bytes(6)) . '.' . $fileExt;
            $destination   = UPLOAD_DIR . $newFilename;

            if (move_uploaded_file($fileTmp, $destination)) {
                $finalImageUrl = UPLOAD_URL_PATH . $newFilename;
            } else {
                $errors['image_file'] = 'Gagal menyimpan file. Periksa izin folder.';
            }
        }
    } else {
        // ── Jalur URL Eksternal ─────────────────────────────
        if ($image_url !== '' && !filter_var($image_url, FILTER_VALIDATE_URL)) {
            $errors['image_url'] = 'URL gambar tidak valid.';
        }
        if (empty($errors['image_url'])) {
            $finalImageUrl = $image_url;
        }
    }

    // Fallback ke placeholder jika tidak ada gambar sama sekali
    if ($finalImageUrl === '' && empty($errors['image_file']) && empty($errors['image_url'])) {
        $finalImageUrl = 'https://placehold.co/400x400/f3f4f6/9ca3af?text=No+Image';
    }

    /* ── Simpan ke database jika tidak ada error ─────────── */
    if (empty($errors)) {
        $slug     = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $slug     = trim($slug, '-') . '-' . time();
        $priceVal = (float) $price;
        $origVal  = ($original_price !== '') ? (float) $original_price : null;
        $badgeVal = $badge !== '' ? $badge : null;
        $descVal  = $description !== '' ? $description : null;

        $stmt = $pdo->prepare(
            'INSERT INTO products
                (category_id, name, slug, description, price, original_price, stock, image_url, badge, is_featured)
             VALUES
                (:category_id, :name, :slug, :description, :price, :original_price, :stock, :image_url, :badge, :is_featured)'
        );
        $stmt->execute([
            ':category_id'    => $category_id,
            ':name'           => $name,
            ':slug'           => $slug,
            ':description'    => $descVal,
            ':price'          => $priceVal,
            ':original_price' => $origVal,
            ':stock'          => $stock,
            ':image_url'      => $finalImageUrl,
            ':badge'          => $badgeVal,
            ':is_featured'    => $is_featured,
        ]);

        $success = 'Produk <strong>' . htmlspecialchars($name, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</strong> berhasil ditambahkan!';
        $old = [];
    }
}

/* ── Helper functions ────────────────────────────────────── */
function oldValue(string $key, string $default = ''): string {
    global $old;
    return htmlspecialchars($old[$key] ?? $default, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
function hasError(string $key): bool {
    global $errors;
    return isset($errors[$key]);
}
function errorMsg(string $key): string {
    global $errors;
    return htmlspecialchars($errors[$key] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk – E-Store Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#E50914',
                        'primary-dark': '#b5070f',
                        accent: '#F59E0B',
                        'accent-light': '#FEF3C7',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 min-h-screen font-sans antialiased">

<!-- HEADER ADMIN -->
<header class="bg-primary shadow-md sticky top-0 z-30">
    <div class="max-w-screen-xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="index.php" class="flex items-center gap-2">
            <div class="bg-white text-primary font-extrabold text-xl px-3 py-1 rounded-lg leading-none">E</div>
            <span class="text-white font-bold text-xl tracking-wide">E-Store</span>
            <span class="text-white/60 text-sm font-medium ml-1">| Admin</span>
        </a>
        <a href="index.php"
           class="flex items-center gap-1.5 text-white/80 hover:text-white text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Toko
        </a>
    </div>
</header>

<!-- KONTEN UTAMA -->
<main class="max-w-3xl mx-auto px-4 py-10">

    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Tambah Produk Baru</h1>
        <p class="text-gray-500 text-sm mt-1">Isi formulir di bawah untuk menambahkan produk ke katalog E-Store.</p>
    </div>

    <!-- Alert Sukses -->
    <?php if ($success): ?>
    <div class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-800
                rounded-xl px-5 py-4 mb-6" role="alert">
        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <div class="text-sm">
            <p class="font-medium"><?= $success ?></p>
            <a href="index.php" class="text-green-700 underline font-medium mt-1 inline-block">
                Lihat di katalog →
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Alert Error -->
    <?php if (!empty($errors)): ?>
    <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800
                rounded-xl px-5 py-4 mb-6" role="alert">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div class="text-sm">
            <p class="font-semibold mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside space-y-0.5">
                <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form — enctype multipart untuk file upload -->
    <form method="POST"
          action="tambah_produk.php"
          enctype="multipart/form-data"
          novalidate
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6">

        <!-- Nama Produk -->
        <div>
            <label for="name" class="form-label">
                Nama Produk <span class="text-primary">*</span>
            </label>
            <input type="text" id="name" name="name"
                   value="<?= oldValue('name') ?>"
                   placeholder="contoh: Samsung Galaxy S24 Ultra"
                   maxlength="200" required
                   class="form-input <?= hasError('name') ? 'border-red-400' : '' ?>">
            <?php if (hasError('name')): ?>
            <p class="form-error"><?= errorMsg('name') ?></p>
            <?php endif; ?>
        </div>

        <!-- Kategori -->
        <div>
            <label for="category_id" class="form-label">
                Kategori <span class="text-primary">*</span>
            </label>
            <select id="category_id" name="category_id" required
                    class="form-input <?= hasError('category_id') ? 'border-red-400' : '' ?>">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"
                    <?= (oldValue('category_id') == $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if (hasError('category_id')): ?>
            <p class="form-error"><?= errorMsg('category_id') ?></p>
            <?php endif; ?>
        </div>

        <!-- Harga & Harga Asli -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="price" class="form-label">
                    Harga Jual (Rp) <span class="text-primary">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium select-none">Rp</span>
                    <input type="number" id="price" name="price"
                           value="<?= oldValue('price') ?>"
                           placeholder="0" min="0" step="1000" required
                           class="form-input pl-10 <?= hasError('price') ? 'border-red-400' : '' ?>">
                </div>
                <?php if (hasError('price')): ?>
                <p class="form-error"><?= errorMsg('price') ?></p>
                <?php endif; ?>
            </div>
            <div>
                <label for="original_price" class="form-label">
                    Harga Sebelum Diskon (Rp)
                    <span class="text-gray-400 font-normal text-xs ml-1">opsional</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium select-none">Rp</span>
                    <input type="number" id="original_price" name="original_price"
                           value="<?= oldValue('original_price') ?>"
                           placeholder="0" min="0" step="1000"
                           class="form-input pl-10 <?= hasError('original_price') ? 'border-red-400' : '' ?>">
                </div>
                <?php if (hasError('original_price')): ?>
                <p class="form-error"><?= errorMsg('original_price') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stok & Badge -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="stock" class="form-label">
                    Stok <span class="text-primary">*</span>
                </label>
                <input type="number" id="stock" name="stock"
                       value="<?= oldValue('stock', '0') ?>"
                       min="0" required
                       class="form-input <?= hasError('stock') ? 'border-red-400' : '' ?>">
                <?php if (hasError('stock')): ?>
                <p class="form-error"><?= errorMsg('stock') ?></p>
                <?php endif; ?>
            </div>
            <div>
                <label for="badge" class="form-label">
                    Badge / Label
                    <span class="text-gray-400 font-normal text-xs ml-1">opsional</span>
                </label>
                <input type="text" id="badge" name="badge"
                       value="<?= oldValue('badge') ?>"
                       placeholder="contoh: NEW, HOT, -20%"
                       maxlength="50" class="form-input">
                <p class="text-xs text-gray-400 mt-1">Teks pendek yang muncul di kartu produk.</p>
            </div>
        </div>

        <!-- ════ SEKSI GAMBAR PRODUK ════ -->
        <div class="border border-gray-200 rounded-xl p-5 space-y-4">
            <p class="form-label mb-0">Gambar Produk
                <span class="text-gray-400 font-normal text-xs ml-1">opsional</span>
            </p>

            <!-- Toggle mode: Upload vs URL -->
            <div class="flex gap-3" id="imageModeToggle">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="image_mode" value="upload" id="modeUpload"
                           class="accent-primary"
                           <?= (($old['image_mode'] ?? 'upload') === 'upload') ? 'checked' : '' ?>>
                    <span class="text-sm font-medium text-gray-700">Upload dari komputer</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="image_mode" value="url" id="modeUrl"
                           class="accent-primary"
                           <?= (($old['image_mode'] ?? 'upload') === 'url') ? 'checked' : '' ?>>
                    <span class="text-sm font-medium text-gray-700">Pakai URL eksternal</span>
                </label>
            </div>

            <!-- Panel Upload File -->
            <div id="panelUpload">
                <!-- Drop zone -->
                <label for="image_file"
                       id="dropZone"
                       class="flex flex-col items-center justify-center gap-2 w-full
                              border-2 border-dashed border-gray-300 rounded-xl
                              bg-gray-50 hover:bg-red-50 hover:border-primary
                              cursor-pointer transition p-8 text-center">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-gray-600">
                            Klik untuk pilih foto, atau drag &amp; drop
                        </p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP, GIF — Maks. 3 MB</p>
                    </div>
                    <input type="file" id="image_file" name="image_file"
                           accept=".jpg,.jpeg,.png,.webp,.gif"
                           class="hidden">
                </label>

                <!-- Preview foto yang dipilih -->
                <div id="uploadPreviewWrap" class="hidden mt-3 flex items-center gap-4">
                    <img id="uploadPreview" src="" alt="Preview"
                         class="w-24 h-24 object-cover rounded-xl border border-gray-200 shadow-sm flex-shrink-0">
                    <div>
                        <p id="uploadFileName" class="text-sm font-semibold text-gray-700 truncate max-w-[200px]"></p>
                        <p id="uploadFileSize" class="text-xs text-gray-400 mt-0.5"></p>
                        <button type="button" onclick="clearUpload()"
                                class="mt-2 text-xs text-primary hover:underline font-medium">
                            Ganti foto
                        </button>
                    </div>
                </div>

                <?php if (hasError('image_file')): ?>
                <p class="form-error mt-1"><?= errorMsg('image_file') ?></p>
                <?php endif; ?>
            </div>

            <!-- Panel URL Eksternal -->
            <div id="panelUrl" class="hidden">
                <input type="url" id="image_url" name="image_url"
                       value="<?= oldValue('image_url') ?>"
                       placeholder="https://example.com/foto-produk.jpg"
                       class="form-input <?= hasError('image_url') ? 'border-red-400' : '' ?>">
                <?php if (hasError('image_url')): ?>
                <p class="form-error mt-1"><?= errorMsg('image_url') ?></p>
                <?php endif; ?>
                <!-- Preview URL -->
                <div id="urlPreviewWrap" class="hidden mt-3">
                    <img id="urlPreview" src="" alt="Preview"
                         class="w-24 h-24 object-cover rounded-xl border border-gray-200 shadow-sm">
                </div>
            </div>
        </div>
        <!-- ════ END SEKSI GAMBAR ════ -->

        <!-- Deskripsi -->
        <div>
            <label for="description" class="form-label">
                Deskripsi Produk
                <span class="text-gray-400 font-normal text-xs ml-1">opsional</span>
            </label>
            <textarea id="description" name="description" rows="4"
                      placeholder="Tuliskan deskripsi singkat produk..."
                      class="form-input resize-y"><?= oldValue('description') ?></textarea>
        </div>

        <!-- Featured -->
        <div class="flex items-start gap-3 p-4 bg-accent-light rounded-xl border border-amber-200">
            <input type="checkbox" id="is_featured" name="is_featured" value="1"
                   <?= (oldValue('is_featured') === '1') ? 'checked' : '' ?>
                   class="mt-0.5 w-4 h-4 accent-primary cursor-pointer flex-shrink-0">
            <div>
                <label for="is_featured" class="text-sm font-semibold text-gray-800 cursor-pointer">
                    Tampilkan di Hero Carousel
                </label>
                <p class="text-xs text-gray-500 mt-0.5">
                    Produk akan muncul di slide utama halaman depan (max 5 produk).
                </p>
            </div>
        </div>

        <!-- Tombol Submit -->
        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="flex items-center gap-2 bg-primary hover:bg-primary-dark text-white
                           font-bold px-6 py-3 rounded-xl transition hover:scale-[1.02] active:scale-95 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Simpan Produk
            </button>
            <a href="index.php" class="text-gray-500 hover:text-gray-700 font-medium text-sm transition">
                Batal
            </a>
        </div>
    </form>
</main>

<footer class="border-t border-gray-200 text-center py-5 text-gray-400 text-xs mt-8">
    &copy; <?= date('Y') ?> E-Store Admin Panel
</footer>

<script>
/* ════════════════════════════════════════════════════════
   Toggle panel: Upload vs URL
════════════════════════════════════════════════════════ */
(function () {
    const radios     = document.querySelectorAll('input[name="image_mode"]');
    const panelUp    = document.getElementById('panelUpload');
    const panelUrl   = document.getElementById('panelUrl');

    function switchPanel() {
        const mode = document.querySelector('input[name="image_mode"]:checked').value;
        panelUp .classList.toggle('hidden', mode !== 'upload');
        panelUrl.classList.toggle('hidden', mode !== 'url');
    }

    radios.forEach(r => r.addEventListener('change', switchPanel));
    switchPanel(); // init
})();

/* ════════════════════════════════════════════════════════
   Upload preview & drag-and-drop
════════════════════════════════════════════════════════ */
(function () {
    const input       = document.getElementById('image_file');
    const dropZone    = document.getElementById('dropZone');
    const previewWrap = document.getElementById('uploadPreviewWrap');
    const previewImg  = document.getElementById('uploadPreview');
    const nameEl      = document.getElementById('uploadFileName');
    const sizeEl      = document.getElementById('uploadFileSize');

    function showPreview(file) {
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            nameEl.textContent = file.name;
            sizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
            previewWrap.classList.remove('hidden');
            dropZone.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    input.addEventListener('change', () => {
        if (input.files[0]) showPreview(input.files[0]);
    });

    // Drag & drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-primary', 'bg-red-50');
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-primary', 'bg-red-50');
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-red-50');
        const file = e.dataTransfer.files[0];
        if (file) {
            // Assign file ke input agar ikut ter-submit
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            showPreview(file);
        }
    });
})();

/* ════════════════════════════════════════════════════════
   Reset upload (ganti foto)
════════════════════════════════════════════════════════ */
window.clearUpload = function () {
    const input       = document.getElementById('image_file');
    const previewWrap = document.getElementById('uploadPreviewWrap');
    const dropZone    = document.getElementById('dropZone');
    input.value       = '';
    previewWrap.classList.add('hidden');
    dropZone.classList.remove('hidden');
};

/* ════════════════════════════════════════════════════════
   URL Preview
════════════════════════════════════════════════════════ */
(function () {
    const input = document.getElementById('image_url');
    const wrap  = document.getElementById('urlPreviewWrap');
    const img   = document.getElementById('urlPreview');

    if (!input) return;

    function update() {
        const url = input.value.trim();
        if (url) {
            img.src = url;
            wrap.classList.remove('hidden');
            img.onerror = () => wrap.classList.add('hidden');
        } else {
            wrap.classList.add('hidden');
        }
    }

    input.addEventListener('input', update);
    update();
})();
</script>
</body>
</html>
