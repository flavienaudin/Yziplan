-- 1) Create Table
CREATE TABLE `utils_geo_region` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `region_name_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `utils_geo_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `region_id` int(11) DEFAULT NULL,
  `number` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_CA6A6C1B98260155` (`region_id`),
  KEY `department_name_idx` (`name`),
  CONSTRAINT `FK_CA6A6C1B98260155` FOREIGN KEY (`region_id`) REFERENCES `utils_geo_region` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `utils_geo_city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL,
  `postal_code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `insee_code` int(11) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D6C4A2FAAE80F5DF` (`department_id`),
  KEY `city_name_idx` (`name`),
  CONSTRAINT `FK_D6C4A2FAAE80F5DF` FOREIGN KEY (`department_id`) REFERENCES `utils_geo_department` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 2) Importer les données ( /resources/data/*)

-- 3) Ajouter les colonnes de date:
ALTER TABLE `utils_geo_region` ADD COLUMN `created_at` DATETIME NOT NULL default now();
ALTER TABLE `utils_geo_region` ADD COLUMN `updated_at` DATETIME NOT NULL default now();
ALTER TABLE `utils_geo_department` ADD COLUMN `created_at` DATETIME NOT NULL default now();
ALTER TABLE `utils_geo_department` ADD COLUMN `updated_at` DATETIME NOT NULL default now();
ALTER TABLE `utils_geo_city` ADD COLUMN `created_at` DATETIME NOT NULL default now();
ALTER TABLE `utils_geo_city` ADD COLUMN `updated_at` DATETIME NOT NULL default now();

-- 4) Rattrapage code postaux
UPDATE `utils_geo_city` SET postal_code = CONCAT('0', postal_code) where length(postal_code) < 5;
