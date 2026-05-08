USE `foovia_db`;

START TRANSACTION;

ALTER TABLE `magasin`
    MODIFY COLUMN `id_mag` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    MODIFY COLUMN `name_mag` VARCHAR(120) NOT NULL,
    MODIFY COLUMN `email_mag` VARCHAR(191) NOT NULL,
    MODIFY COLUMN `phone_mag` VARCHAR(30) NOT NULL,
    MODIFY COLUMN `adress_mag` VARCHAR(255) NOT NULL;

ALTER TABLE `marchandise`
    MODIFY COLUMN `id_march` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    MODIFY COLUMN `name_march` VARCHAR(120) NOT NULL,
    MODIFY COLUMN `description_march` TEXT NOT NULL,
    MODIFY COLUMN `price_march` DOUBLE NOT NULL DEFAULT 0,
    MODIFY COLUMN `quantity_march` INT(10) NOT NULL DEFAULT 0,
    MODIFY COLUMN `date_expiration_march` DATE NOT NULL,
    MODIFY COLUMN `point_acces_march` VARCHAR(120) NOT NULL;

ALTER TABLE `magasin`
    ADD COLUMN IF NOT EXISTS `img_mag` LONGBLOB NULL AFTER `adress_mag`;

ALTER TABLE `marchandise`
    ADD COLUMN IF NOT EXISTS `img_march` LONGBLOB NULL AFTER `point_acces_march`,
    ADD COLUMN IF NOT EXISTS `reserved_count_march` INT NOT NULL DEFAULT 0 AFTER `img_march`;

CREATE TABLE IF NOT EXISTS `categorie` (
    `id_categ` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_categ` VARCHAR(80) NOT NULL,
    PRIMARY KEY (`id_categ`),
    UNIQUE KEY `uniq_categorie_name` (`name_categ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `vendre` (
    `id_march` INT(10) UNSIGNED NOT NULL,
    `id_mag` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_march`, `id_mag`),
    KEY `idx_vendre_mag` (`id_mag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `marchandise_categorie` (
    `id_march` INT(10) UNSIGNED NOT NULL,
    `id_categ` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_march`, `id_categ`),
    KEY `idx_marchandise_categorie_categ` (`id_categ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `marchandise_reservation` (
    `id_reservation` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_march` INT(10) UNSIGNED NOT NULL,
    `id_mag` INT(10) UNSIGNED NOT NULL,
    `quantity_reservation` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_reservation`),
    KEY `idx_reservation_product` (`id_march`),
    KEY `idx_reservation_store` (`id_mag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `categorie` (`name_categ`) VALUES
('Fruits'),
('Vegetables'),
('Leafy Greens'),
('Dairy'),
('Eggs'),
('Meat'),
('Poultry'),
('Fish and Seafood'),
('Plant-Based Protein'),
('Grains and Cereals'),
('Pasta and Rice'),
('Bakery and Baking'),
('Sweeteners'),
('Oils and Fats'),
('Spices and Condiments'),
('Legumes'),
('Nuts and Seeds'),
('Breakfast Food'),
('Sauces and Marinades'),
('Beverages'),
('Snacks'),
('Canned Food'),
('Frozen Food');

COMMIT;
