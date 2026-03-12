CREATE TABLE IF NOT EXISTS `#__sitereport_reports` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `domain` VARCHAR(255) NOT NULL,
    `data` JSON NOT NULL,
    `seo_score` INT NOT NULL DEFAULT 0,
    `http_code` INT NOT NULL DEFAULT 0,
    `response_time` FLOAT NOT NULL DEFAULT 0,
    `created` DATETIME NOT NULL,
    `updated` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_domain` (`domain`),
    KEY `idx_created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
