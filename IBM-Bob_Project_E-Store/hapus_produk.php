<?php
/**
 * hapus_produk.php
 * Endpoint untuk menghapus satu produk (POST only)
 * Dipanggil dari modal konfirmasi di edit_produk.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

/* ── Hanya admin yang boleh menghapus produk ──────────── */
requireAdmin();

// Hanya izinkan method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$pdo       = getDB();
$productId = (int) ($_POST['id'] ?? 0);

if ($productId <= 0) {
    header('Location: index.php');
    exit;
}

// Ambil data produk dulu (untuk hapus file gambar lokal)
$stmt = $pdo->prepare('SELECT image_url FROM products WHERE id = ? LIMIT 1');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if ($product) {
    // Hapus file gambar lokal jika ada
    $imagePath = __DIR__ . '/' . $product['image_url'];
    if (
        strpos($product['image_url'], 'images/products/') === 0 &&
        is_file($imagePath)
    ) {
        @unlink($imagePath);
    }

    // Hapus record dari database
    $delStmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $delStmt->execute([$productId]);
}

// Redirect ke halaman utama setelah hapus
header('Location: index.php?deleted=1');
exit;
