-- Esquema minimo, compatible con el layout real de TrinityCore/AzerothCore
-- (base de datos `auth`), solo para poder probar el flujo de aprovisionamiento
-- de cuentas de juego sin depender de un core real corriendo.
-- NO es una copia completa del esquema del core, solo lo necesario para
-- que el gateway de creacion/edicion de cuentas funcione en pruebas locales.

CREATE TABLE IF NOT EXISTS `account` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(32) NOT NULL,
    `salt` BINARY(32) NOT NULL,
    `verifier` BINARY(32) NOT NULL,
    `email` VARCHAR(255) NOT NULL DEFAULT '',
    `reg_mail` VARCHAR(255) NOT NULL DEFAULT '',
    `joindate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_ip` VARCHAR(15) NOT NULL DEFAULT '127.0.0.1',
    `locked` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `expansion` TINYINT UNSIGNED NOT NULL DEFAULT 2,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `account_access` (
    `id` INT UNSIGNED NOT NULL,
    `gmlevel` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `RealmID` INT NOT NULL DEFAULT -1,
    PRIMARY KEY (`id`, `RealmID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `account_banned` (
    `id` INT UNSIGNED NOT NULL,
    `bandate` INT UNSIGNED NOT NULL DEFAULT 0,
    `unbandate` INT UNSIGNED NOT NULL DEFAULT 0,
    `bannedby` VARCHAR(50) NOT NULL DEFAULT '',
    `banreason` VARCHAR(255) NOT NULL DEFAULT '',
    `active` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`, `bandate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
