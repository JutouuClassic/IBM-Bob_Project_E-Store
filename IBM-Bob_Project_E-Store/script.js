/**
 * script.js
 * E-Store – Frontend Interactivity
 * Fitur: Hero Carousel, Search Filter, Shopping Cart (localStorage)
 * Tidak memerlukan library/framework JS eksternal
 */

'use strict';

/* ════════════════════════════════════════════════════════
   1. HERO CAROUSEL
════════════════════════════════════════════════════════ */
(function initCarousel() {
    const track  = document.getElementById('carouselTrack');
    const dots   = document.querySelectorAll('.carousel-dot');

    if (!track) return; // Tidak ada carousel di halaman ini

    const slides     = track.querySelectorAll('.carousel-slide');
    const totalSlides = slides.length;
    let   current    = 0;
    let   autoTimer  = null;

    /** Pergi ke slide tertentu */
    function goTo(index) {
        current = (index + totalSlides) % totalSlides;
        track.style.transform = `translateX(-${current * 100}%)`;

        // Update dots
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === current);
            // Lebar dot aktif diatur via CSS class .active di style.css
        });
    }

    /** Ekspose fungsi ke scope global agar bisa dipanggil dari inline HTML */
    window.carouselPrev = function () {
        resetAutoplay();
        goTo(current - 1);
    };
    window.carouselNext = function () {
        resetAutoplay();
        goTo(current + 1);
    };
    window.goToSlide = function (index) {
        resetAutoplay();
        goTo(index);
    };

    /** Autoplay setiap 5 detik */
    function startAutoplay() {
        autoTimer = setInterval(() => goTo(current + 1), 5000);
    }
    function resetAutoplay() {
        clearInterval(autoTimer);
        startAutoplay();
    }

    // Inisialisasi
    if (totalSlides > 0) {
        goTo(0);
        startAutoplay();
    }

    // Pause autoplay saat hover
    const wrapper = document.getElementById('heroCarousel');
    if (wrapper) {
        wrapper.addEventListener('mouseenter', () => clearInterval(autoTimer));
        wrapper.addEventListener('mouseleave', startAutoplay);
    }

    // Swipe support di layar sentuh
    let touchStartX = 0;
    track.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    track.addEventListener('touchend', (e) => {
        const diff = touchStartX - e.changedTouches[0].screenX;
        if (Math.abs(diff) > 40) {
            diff > 0 ? carouselNext() : carouselPrev();
        }
    }, { passive: true });
})();


/* ════════════════════════════════════════════════════════
   2. DROPDOWN KATEGORI (HEADER)
════════════════════════════════════════════════════════ */
window.toggleCategoryMenu = function () {
    const menu = document.getElementById('categoryMenu');
    if (!menu) return;
    menu.classList.toggle('hidden');
};

// Tutup dropdown saat klik di luar
document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('categoryDropdown');
    const menu     = document.getElementById('categoryMenu');
    if (menu && dropdown && !dropdown.contains(e.target)) {
        menu.classList.add('hidden');
    }
});


/* ════════════════════════════════════════════════════════
   3. LIVE SEARCH / FILTER PRODUK
════════════════════════════════════════════════════════ */
(function initSearch() {
    const input     = document.getElementById('searchInput');
    const grid      = document.getElementById('productGrid');
    const noResult  = document.getElementById('noSearchResult');
    const countEl   = document.getElementById('productCount');

    if (!input || !grid) return;

    input.addEventListener('input', debounce(filterProducts, 200));

    function filterProducts() {
        const query   = input.value.trim().toLowerCase();
        const cards   = grid.querySelectorAll('.product-card');
        let   visible = 0;

        cards.forEach((card) => {
            const name     = card.dataset.name     || '';
            const category = card.dataset.category || '';
            const match    = name.includes(query) || category.includes(query);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        // Tampilkan pesan kosong jika tidak ada yang cocok
        if (noResult) {
            noResult.classList.toggle('hidden', visible > 0 || query === '');
        }
        if (countEl) {
            countEl.textContent = query
                ? `Menampilkan ${visible} produk untuk "${input.value.trim()}"`
                : `Menampilkan ${cards.length} produk`;
        }
    }

    /** Debounce helper – mencegah pemrosesan terlalu sering saat mengetik */
    function debounce(fn, delay) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }
})();


/* ════════════════════════════════════════════════════════
   4. SHOPPING CART (localStorage)
════════════════════════════════════════════════════════ */
const Cart = (function () {
    const STORAGE_KEY = 'estore_cart';

    /* ── Operasi data ─────────────────────────────────── */

    function getItems() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
        } catch {
            return [];
        }
    }

    function saveItems(items) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    }

    function addItem(id, name, price, imageUrl) {
        const items = getItems();
        const idx   = items.findIndex((i) => i.id === id);

        if (idx > -1) {
            items[idx].qty += 1;
        } else {
            items.push({ id, name, price, imageUrl, qty: 1 });
        }

        saveItems(items);
        return items;
    }

    function removeItem(id) {
        const items = getItems().filter((i) => i.id !== id);
        saveItems(items);
        return items;
    }

    function updateQty(id, delta) {
        const items = getItems();
        const idx   = items.findIndex((i) => i.id === id);
        if (idx === -1) return items;

        items[idx].qty = Math.max(1, items[idx].qty + delta);
        saveItems(items);
        return items;
    }

    function clearAll() {
        localStorage.removeItem(STORAGE_KEY);
        return [];
    }

    function getTotalCount() {
        return getItems().reduce((sum, i) => sum + i.qty, 0);
    }

    function getTotalPrice() {
        return getItems().reduce((sum, i) => sum + i.price * i.qty, 0);
    }

    return { getItems, addItem, removeItem, updateQty, clearAll, getTotalCount, getTotalPrice };
})();


/* ── Render keranjang ke sidebar ───────────────────────── */
function renderCart() {
    const container   = document.getElementById('cartItems');
    const emptyMsg    = document.getElementById('cartEmpty');
    const totalEl     = document.getElementById('cartTotal');
    const badgeEl     = document.getElementById('cartBadge');
    const checkoutBtn = document.getElementById('checkoutBtn');

    if (!container) return;

    const items = Cart.getItems();
    const count = Cart.getTotalCount();
    const total = Cart.getTotalPrice();

    // Badge jumlah item
    if (badgeEl) {
        if (count > 0) {
            badgeEl.textContent = count > 99 ? '99+' : count;
            badgeEl.classList.remove('hidden');
        } else {
            badgeEl.classList.add('hidden');
        }
    }

    // Total harga
    if (totalEl) totalEl.textContent = formatRupiahJS(total);

    // Tombol checkout
    if (checkoutBtn) checkoutBtn.disabled = items.length === 0;

    // Kosongkan container dulu
    // Hapus semua child kecuali teks kosong
    while (container.firstChild) container.removeChild(container.firstChild);

    if (items.length === 0) {
        const msg = document.createElement('p');
        msg.id = 'cartEmpty';
        msg.className = 'text-gray-400 text-center mt-16 text-sm';
        msg.textContent = 'Keranjang Anda masih kosong.';
        container.appendChild(msg);
        return;
    }

    // Render setiap item
    items.forEach((item) => {
        const el = document.createElement('div');
        el.className = 'flex items-center gap-3 py-3 border-b border-gray-100 last:border-0';
        el.innerHTML = `
            <img src="${escapeHtml(item.imageUrl)}"
                 alt="${escapeHtml(item.name)}"
                 class="cart-item-img">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 leading-snug truncate">
                    ${escapeHtml(item.name)}
                </p>
                <p class="text-primary font-bold text-sm mt-0.5">
                    ${formatRupiahJS(item.price)}
                </p>
                <div class="flex items-center gap-2 mt-2">
                    <button onclick="changeQty(${item.id}, -1)"
                            class="w-6 h-6 rounded-full border border-gray-300 text-gray-600
                                   hover:bg-gray-100 transition text-sm font-bold flex items-center justify-center">
                        −
                    </button>
                    <span class="text-sm font-semibold text-gray-800 min-w-[20px] text-center">
                        ${item.qty}
                    </span>
                    <button onclick="changeQty(${item.id}, 1)"
                            class="w-6 h-6 rounded-full border border-gray-300 text-gray-600
                                   hover:bg-gray-100 transition text-sm font-bold flex items-center justify-center">
                        +
                    </button>
                </div>
            </div>
            <button onclick="removeFromCart(${item.id})"
                    class="p-1.5 text-gray-300 hover:text-primary transition flex-shrink-0"
                    title="Hapus item">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>`;
        container.appendChild(el);
    });
}

/* ── Aksi keranjang (dipanggil dari HTML) ──────────────── */

/** Tambah produk ke keranjang */
window.addToCart = function (id, name, price, imageUrl) {
    Cart.addItem(id, name, price, imageUrl);
    renderCart();
    showToast(`"${name}" ditambahkan ke keranjang`);
};

/** Ubah kuantitas item */
window.changeQty = function (id, delta) {
    Cart.updateQty(id, delta);
    renderCart();
};

/** Hapus item dari keranjang */
window.removeFromCart = function (id) {
    Cart.removeItem(id);
    renderCart();
};

/** Kosongkan seluruh keranjang */
window.clearCart = function () {
    Cart.clearAll();
    renderCart();
};

/** Simulasi checkout */
window.checkout = function () {
    showToast('Fitur checkout segera hadir! 🚀');
};


/* ── Toggle sidebar keranjang ──────────────────────────── */
window.toggleCart = function () {
    const sidebar = document.getElementById('cartSidebar');
    const overlay = document.getElementById('cartOverlay');

    if (!sidebar) return;

    const isOpen = sidebar.classList.contains('open');
    sidebar.classList.toggle('open', !isOpen);
    if (overlay) overlay.classList.toggle('hidden', isOpen);

    // Cegah scroll body saat keranjang terbuka
    document.body.style.overflow = isOpen ? '' : 'hidden';
};


/* ════════════════════════════════════════════════════════
   5. TOAST NOTIFICATION
════════════════════════════════════════════════════════ */
let toastTimer = null;

function showToast(message, duration = 2800) {
    const toast = document.getElementById('toast');
    if (!toast) return;

    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), duration);
}


/* ════════════════════════════════════════════════════════
   6. UTILITAS
════════════════════════════════════════════════════════ */

/** Format angka ke format Rupiah di JavaScript */
function formatRupiahJS(amount) {
    return 'Rp ' + Number(amount).toLocaleString('id-ID');
}

/** Escape HTML untuk mencegah XSS pada konten dinamis JS */
function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(String(str)));
    return div.innerHTML;
}


/* ════════════════════════════════════════════════════════
   INISIALISASI SAAT DOM SIAP
════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {
    // Render status keranjang dari localStorage
    renderCart();

    // Toast dari redirect hapus produk
    if (window.__deletedMsg) {
        showToast('🗑️ ' + window.__deletedMsg, 3500);
    }
    // Toast flash success dari session (login, dll.)
    if (window.__flashSuccess) {
        showToast('✓ ' + window.__flashSuccess, 3500);
    }
});
