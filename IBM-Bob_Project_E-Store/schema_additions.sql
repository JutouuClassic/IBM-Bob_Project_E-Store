-- ============================================================
-- schema_additions.sql
-- Additional schema alterations and seed data for eStore
-- ============================================================


-- ------------------------------------------------------------
-- 1. CREATE TABLE users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `nama`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(100) NOT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('admin','user') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ------------------------------------------------------------
-- 2. Seed data for users
--    Passwords are bcrypt hashes generated with PHP password_hash()
--    Admin  password : 'admin123'  → hash below (PASSWORD_BCRYPT, cost 10)
--    User   password : 'user123'   → hash below (PASSWORD_BCRYPT, cost 10)
-- ------------------------------------------------------------
INSERT INTO `users` (`nama`, `email`, `password`, `role`) VALUES
(
    'Administrator',
    'admin@estore.com',
    -- bcrypt hash of 'admin123' (PASSWORD_BCRYPT, cost 10)
    '$2y$10$TKh8H1.PfY5IfBMEkMWEBO3HaX9JFu3pNi4u6YBFNlVkXbm9QFKGS',
    'admin'
),
(
    'Budi Santoso',
    'budi@example.com',
    -- bcrypt hash of 'user123' (PASSWORD_BCRYPT, cost 10)
    '$2y$10$sGmLwc6JnFLdjlFdJEZE.OD8MlPovVG7cSbGdcXy4Y4bk9B6TmOKG',
    'user'
);


-- ------------------------------------------------------------
-- 3. CREATE TABLE product_variants
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id`              INT          NOT NULL AUTO_INCREMENT,
    `product_id`      INT UNSIGNED NOT NULL,
    `ram_storage`     VARCHAR(50)  NOT NULL,
    `warna`           VARCHAR(50)  NOT NULL,
    `stok`            INT          NOT NULL DEFAULT 10,
    `tambahan_harga`  INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_variants_product`
        FOREIGN KEY (`product_id`)
        REFERENCES `products` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ------------------------------------------------------------
-- 4. Seed data for product_variants
-- ------------------------------------------------------------

-- product_id = 1 : Samsung Galaxy S24 Ultra
INSERT INTO `product_variants` (`product_id`, `ram_storage`, `warna`, `stok`, `tambahan_harga`) VALUES
(1, '8GB/256GB',  'Titanium Black',  15,  0),
(1, '12GB/512GB', 'Titanium Gray',   10,  2000000),
(1, '12GB/512GB', 'Titanium Violet',  8,  2000000);

-- product_id = 2 : iPhone 15 Pro Max
INSERT INTO `product_variants` (`product_id`, `ram_storage`, `warna`, `stok`, `tambahan_harga`) VALUES
(2, '256GB', 'Black Titanium',   10,       0),
(2, '512GB', 'Natural Titanium',  7, 3000000),
(2, '1TB',   'White Titanium',    5, 6000000);
