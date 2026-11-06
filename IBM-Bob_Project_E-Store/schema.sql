-- ============================================================
-- schema.sql
-- Database: toko_gadget
-- Buat database, tabel, dan isi data sampel
-- ============================================================

CREATE DATABASE IF NOT EXISTS toko_gadget
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE toko_gadget;

-- --------------------------------------------------------
-- Tabel: categories
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    slug       VARCHAR(110)  NOT NULL UNIQUE,
    icon       VARCHAR(50)   NOT NULL DEFAULT 'device-phone-mobile',
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabel: products
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id  INT UNSIGNED     NOT NULL,
    name         VARCHAR(200)     NOT NULL,
    slug         VARCHAR(220)     NOT NULL UNIQUE,
    description  TEXT,
    price        DECIMAL(15,2)    NOT NULL,
    original_price DECIMAL(15,2)  DEFAULT NULL COMMENT 'Harga sebelum diskon',
    stock        INT UNSIGNED     NOT NULL DEFAULT 0,
    image_url    VARCHAR(500)     NOT NULL DEFAULT 'https://placehold.co/400x400/f3f4f6/9ca3af?text=No+Image',
    badge        VARCHAR(50)      DEFAULT NULL COMMENT 'e.g. NEW, HOT, -20%',
    is_featured  TINYINT(1)       NOT NULL DEFAULT 0,
    created_at   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Data sampel: categories
-- --------------------------------------------------------
INSERT INTO categories (name, slug, icon) VALUES
('Gadget',     'gadget',     'device-phone-mobile'),
('Tablet',     'tablet',     'device-tablet'),
('Laptop',     'laptop',     'computer-desktop'),
('Aksesoris',  'aksesoris',  'puzzle-piece'),
('TV',         'tv',         'tv'),
('Audio',      'audio',      'speaker-wave');

-- --------------------------------------------------------
-- Data sampel: products
-- --------------------------------------------------------
INSERT INTO products (category_id, name, slug, description, price, original_price, stock, image_url, badge, is_featured) VALUES

-- Gadget
(1, 'Samsung Galaxy S24 Ultra',   'samsung-galaxy-s24-ultra',   'Flagship Android terbaru dengan kamera 200MP dan S Pen terintegrasi.',   19999000, 22999000, 15, 'https://placehold.co/400x400/1a1a2e/e94560?text=S24+Ultra',   '-13%', 1),
(1, 'iPhone 15 Pro Max',           'iphone-15-pro-max',           'Chip A17 Pro, kamera 48MP dengan Tetraprism zoom 5x.',                   21999000, 23999000, 10, 'https://placehold.co/400x400/1c1c1e/ffffff?text=iPhone+15PM', 'HOT',  1),
(1, 'Xiaomi 14 Ultra',             'xiaomi-14-ultra',             'Leica Summilux optics, Snapdragon 8 Gen 3, pengisian 90W.',               14999000, 16499000, 20, 'https://placehold.co/400x400/ff6900/ffffff?text=Xiaomi+14U',  '-9%',  0),
(1, 'OPPO Find X7 Pro',            'oppo-find-x7-pro',            'Hasselblad camera system, Dimensity 9300.',                               12499000, 13999000, 18, 'https://placehold.co/400x400/1b4332/95d5b2?text=FindX7Pro',   'NEW',  0),

-- Tablet
(2, 'iPad Pro M4 13 inch',         'ipad-pro-m4-13',              'Layar Ultra Retina XDR OLED, chip M4, Apple Pencil Pro support.',         22499000, 24999000, 8,  'https://placehold.co/400x400/e5e7eb/374151?text=iPad+Pro+M4', '-10%', 1),
(2, 'Samsung Galaxy Tab S9 Ultra', 'samsung-galaxy-tab-s9-ultra', 'Layar AMOLED 14.6 inci, S Pen included, RAM 12GB.',                       15999000, 17999000, 12, 'https://placehold.co/400x400/0f172a/38bdf8?text=Tab+S9+Ultra','HOT',  0),
(2, 'Xiaomi Pad 6 Pro',            'xiaomi-pad-6-pro',            'Snapdragon 8+ Gen 1, refresh rate 144Hz, baterai 8600mAh.',               6499000,  7499000,  25, 'https://placehold.co/400x400/fef9c3/713f12?text=Pad+6+Pro',   '-13%', 0),

-- Laptop
(3, 'MacBook Pro M3 Pro 14"',      'macbook-pro-m3-pro-14',       'Chip M3 Pro, layar Liquid Retina XDR, baterai hingga 18 jam.',            31999000, 34999000, 5,  'https://placehold.co/400x400/d1d5db/111827?text=MBP+M3+Pro',  'HOT',  1),
(3, 'ASUS ROG Zephyrus G14',       'asus-rog-zephyrus-g14',       'AMD Ryzen 9, RTX 4070, layar 14" 165Hz OLED.',                            22999000, 24999000, 7,  'https://placehold.co/400x400/1a1a2e/e94560?text=ROG+G14',     '-8%',  0),
(3, 'Lenovo ThinkPad X1 Carbon',   'lenovo-thinkpad-x1-carbon',   'Intel Core Ultra 7, 32GB RAM, SSD 1TB, ringan 1.12 kg.',                  26999000, 28999000, 6,  'https://placehold.co/400x400/1e3a5f/93c5fd?text=X1+Carbon',   'NEW',  0),

-- Aksesoris
(4, 'Apple AirPods Pro 2',         'apple-airpods-pro-2',         'Active Noise Cancellation, Adaptive Transparency, MagSafe charging.',     4299000,  4799000,  30, 'https://placehold.co/400x400/f9fafb/6b7280?text=AirPods+Pro', '-10%', 0),
(4, 'Samsung Galaxy Watch 6 Classic','samsung-galaxy-watch-6-classic','Bezel putar ikonik, ECG, pemantauan kesehatan lengkap.',              4999000,  5499000,  22, 'https://placehold.co/400x400/1c1917/f5f5f4?text=Watch+6C',    '-9%',  0),
(4, 'Logitech MX Master 3S',       'logitech-mx-master-3s',       'Mouse ergonomis ultra-presisi, MagSpeed scroll, Bluetooth.',              1299000,  1499000,  40, 'https://placehold.co/400x400/18181b/e4e4e7?text=MX+Master+3', 'HOT',  0),

-- TV
(5, 'Samsung Neo QLED 4K 65"',     'samsung-neo-qled-65',         'Mini LED, Quantum HDR 32x, Gaming Hub, Motion Xcelerator 144Hz.',         22999000, 25999000, 4,  'https://placehold.co/400x400/0c0c0c/00d2ff?text=Neo+QLED+65', '-12%', 1),
(5, 'LG OLED C3 55"',              'lg-oled-c3-55',               'OLED evo panel, α9 AI Processor 4K Gen6, Dolby Vision IQ.',               17999000, 19999000, 6,  'https://placehold.co/400x400/111827/6366f1?text=OLED+C3+55',  'HOT',  0),

-- Audio
(6, 'Sony WH-1000XM5',             'sony-wh-1000xm5',             'Industry-leading noise cancelling, 30 jam playtime, LDAC support.',       4799000,  5299000,  35, 'https://placehold.co/400x400/18181b/f4f4f5?text=WH-1000XM5',  '-9%',  0),
(6, 'JBL Charge 5',                'jbl-charge-5',                'Speaker portabel tahan air IP67, Power Bank built-in, 20 jam playtime.',   1899000,  2199000,  50, 'https://placehold.co/400x400/dc2626/ffffff?text=JBL+Charge+5', '-14%', 0);

-- --------------------------------------------------------
-- Tabel: users (autentikasi & role)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100)            NOT NULL,
    email      VARCHAR(100)            NOT NULL UNIQUE,
    password   VARCHAR(255)            NOT NULL,
    role       ENUM('admin','user')    NOT NULL DEFAULT 'user',
    created_at TIMESTAMP               NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed: admin@estore.com / admin123
-- Seed: budi@example.com / user123
INSERT INTO users (nama, email, password, role) VALUES
('Administrator', 'admin@estore.com',  '$2y$10$TKh8H1.PfY5IfBMEkMWEBO3HaX9JFu3pNi4u6YBFNlVkXbm9QFKGS', 'admin'),
('Budi Santoso',  'budi@example.com',  '$2y$10$sGmLwc6JnFLdjlFdJEZE.OD8MlPovVG7cSbGdcXy4Y4bk9B6TmOKG', 'user');

-- --------------------------------------------------------
-- Tabel: product_variants (varian RAM/Warna)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_variants (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      INT UNSIGNED    NOT NULL,
    ram_storage     VARCHAR(50)     NOT NULL,
    warna           VARCHAR(50)     NOT NULL,
    stok            INT             NOT NULL DEFAULT 10,
    tambahan_harga  INT             NOT NULL DEFAULT 0,
    CONSTRAINT fk_variants_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Varian: Samsung Galaxy S24 Ultra (id=1)
INSERT INTO product_variants (product_id, ram_storage, warna, stok, tambahan_harga) VALUES
(1, '8GB/256GB',  'Titanium Black',  15, 0),
(1, '12GB/512GB', 'Titanium Gray',   10, 2000000),
(1, '12GB/512GB', 'Titanium Violet',  8, 2000000);

-- Varian: iPhone 15 Pro Max (id=2)
INSERT INTO product_variants (product_id, ram_storage, warna, stok, tambahan_harga) VALUES
(2, '256GB', 'Black Titanium',    10, 0),
(2, '512GB', 'Natural Titanium',   7, 3000000),
(2, '1TB',   'White Titanium',     5, 6000000);
