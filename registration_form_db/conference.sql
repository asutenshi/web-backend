CREATE DATABASE IF NOT EXISTS `conference`
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;

USE `conference`;

CREATE TABLE IF NOT EXISTS `subjects` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `subjects` (`name`) VALUES
('Бизнес и коммуникации'),
('Технологии'),
('Реклама'),
('Маркетинг'),
('Проектирование');

CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `payments` (`name`) VALUES
('WebMoney'),
('Яндекс.Деньги'),
('PayPal'),
('Кредитная карта'),
('Робокасса');

CREATE TABLE IF NOT EXISTS `participants` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `lastname` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `tel` VARCHAR(255) NOT NULL,
  `subject_id` INT(10) UNSIGNED NOT NULL,
  `payment_id` INT(10) UNSIGNED NOT NULL,
  `mailing` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  `ip` VARCHAR(45) NOT NULL DEFAULT '',
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `subject_id` (`subject_id`),
  INDEX `payment_id` (`payment_id`),
  INDEX `deleted_at` (`deleted_at`),
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE RESTRICT
);

GRANT ALL PRIVILEGES ON `conference`.* TO 'root'@'%';
FLUSH PRIVILEGES;