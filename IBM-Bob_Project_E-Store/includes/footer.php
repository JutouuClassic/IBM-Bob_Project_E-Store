<?php
/**
 * includes/footer.php
 * Footer dengan semua link navigasi aktif.
 * Sertakan di setiap halaman dengan: require_once __DIR__ . '/../includes/footer.php';
 */
?>
<!-- ════════════════════════════════════════════════════════
     FOOTER
════════════════════════════════════════════════════════ -->
<footer class="bg-gray-900 text-gray-300 mt-8">
    <div class="max-w-screen-xl mx-auto px-4 py-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

        <!-- Brand -->
        <div>
            <div class="flex items-center gap-2 mb-3">
                <div class="bg-primary text-white font-extrabold text-lg px-2.5 py-1 rounded-lg">E</div>
                <span class="text-white font-bold text-lg">E-Store</span>
            </div>
            <p class="text-sm leading-relaxed text-gray-400">
                Toko gadget terlengkap dengan produk original bergaransi resmi dan layanan terbaik.
            </p>
        </div>

        <!-- Layanan -->
        <div>
            <h4 class="text-white font-semibold mb-3">Layanan</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="layanan_pickup.php"    class="hover:text-primary transition">Click &amp; Pick Up</a></li>
                <li><a href="express_delivery.php"  class="hover:text-primary transition">Express Delivery</a></li>
                <li><a href="trade_in.php"          class="hover:text-primary transition">Trade-In</a></li>
                <li><a href="service_center.php"    class="hover:text-primary transition">Service Center</a></li>
            </ul>
        </div>

        <!-- Tentang Kami -->
        <div>
            <h4 class="text-white font-semibold mb-3">Tentang Kami</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="about.php"   class="hover:text-primary transition">Profil Perusahaan</a></li>
                <li><a href="career.php"  class="hover:text-primary transition">Karier</a></li>
                <li><a href="blog.php"    class="hover:text-primary transition">Blog &amp; Berita</a></li>
                <li><a href="contact.php" class="hover:text-primary transition">Hubungi Kami</a></li>
            </ul>
        </div>

        <!-- Bantuan -->
        <div>
            <h4 class="text-white font-semibold mb-3">Bantuan</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="faq.php"           class="hover:text-primary transition">FAQ</a></li>
                <li><a href="privacy.php"        class="hover:text-primary transition">Kebijakan Privasi</a></li>
                <li><a href="terms.php"          class="hover:text-primary transition">Syarat &amp; Ketentuan</a></li>
                <li><a href="help_shopping.php"  class="hover:text-primary transition">Cara Belanja</a></li>
            </ul>
        </div>
    </div>

    <div class="border-t border-gray-800 text-center py-4 text-gray-500 text-xs">
        &copy; <?= date('Y') ?> E-Store. Semua Hak Dilindungi.
    </div>
</footer>

<!-- Toast Notification -->
<div id="toast"
     class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[60]
            bg-gray-900 text-white text-sm font-medium px-5 py-3 rounded-xl shadow-lg
            opacity-0 pointer-events-none transition-opacity duration-300">
</div>

<?php if (isset($deletedMsg) && $deletedMsg): ?>
<script>window.__deletedMsg = <?= json_encode($deletedMsg) ?>;</script>
<?php endif; ?>
<?php if (isset($flashSuccess) && $flashSuccess): ?>
<script>window.__flashSuccess = <?= json_encode($flashSuccess) ?>;</script>
<?php endif; ?>

<script src="<?= isset($rootPath) ? $rootPath : '' ?>script.js"></script>
</body>
</html>
