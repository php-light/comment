CREATE TABLE IF NOT EXISTS `comment` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentTable` VARCHAR(191) NOT NULL DEFAULT '',
  `parent` VARCHAR(191) NOT NULL DEFAULT '',
  `comment` TEXT,
  `createdAt` VARCHAR(191) NOT NULL DEFAULT '',
  `createdBy` VARCHAR(191) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
