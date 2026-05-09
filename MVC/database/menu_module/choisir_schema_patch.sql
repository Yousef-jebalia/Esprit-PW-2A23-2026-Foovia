USE `foovia_db`;

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `choisir` (
    `id_user` INT(10) UNSIGNED NOT NULL,
    `id_rec` INT(10) UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_user`, `id_rec`),
    KEY `idx_choisir_id_rec` (`id_rec`),
    CONSTRAINT `fk_choisir_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_choisir_recipe` FOREIGN KEY (`id_rec`) REFERENCES `recipe` (`id_rec`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;