CREATE DATABASE IF NOT EXISTS `calendar_app`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `calendar_app`;

CREATE TABLE IF NOT EXISTS `task_types` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `task_types` (`name`) VALUES
('Встреча'),
('Звонок'),
('Совещание'),
('Дело');

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `type_id` INT(10) UNSIGNED NOT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `task_datetime` DATETIME NOT NULL,
  `duration` INT(10) UNSIGNED NOT NULL DEFAULT 60,
  `comment` TEXT,
  `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `type_id` (`type_id`),
  INDEX `task_datetime` (`task_datetime`),
  FOREIGN KEY (`type_id`) REFERENCES `task_types`(`id`) ON DELETE RESTRICT
);
