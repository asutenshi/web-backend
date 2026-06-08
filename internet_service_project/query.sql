SET FOREIGN_KEY_CHECKS = 0;

-- Таблица: Клиенты
CREATE TABLE `clients` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `last_name` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(255) NOT NULL,
    `middle_name` VARCHAR(255),
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(255),
    `passport_series` VARCHAR(10) NOT NULL,
    `passport_number` VARCHAR(20) NOT NULL,
    `passport_issued_by` VARCHAR(255) NOT NULL,
    `passport_department_code` VARCHAR(7) NOT NULL,
    `passport_issued_at` DATE NOT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL, -- для soft delete

    UNIQUE KEY `unq_phone` (`phone`),
    UNIQUE KEY `unq_email` (`email`),
    UNIQUE KEY `unq_passport` (`passport_series`, `passport_number`),
    INDEX `idx_last_name` (`last_name`),
    INDEX `idx_clients_deleted_at` (`deleted_at`)
);

-- Таблица: Дома (адресный фонд)
CREATE TABLE `houses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `city` VARCHAR(255) NOT NULL,
    `street` VARCHAR(255) NOT NULL,
    `number` VARCHAR(20) NOT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,

    INDEX `idx_address` (`city`, `street`, `number`),
    INDEX `idx_houses_deleted_at` (`deleted_at`)
);

-- Таблица: Тарифы
CREATE TABLE `tarifs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `speed_mbps` SMALLINT UNSIGNED NOT NULL,
    `cost_per_month` INT UNSIGNED NOT NULL, -- храним в копейках
    -- Намеренно используем ENUM вместо справочника технологий.
    -- Если выносить все статусы и типы в отдельные таблицы, база разрастется до 19 таблиц, 
    -- что для данного проекта перебор и сильно усложнит выборки.
    `technology` ENUM('FTTB', 'GPON', 'ADSL', 'Wireless') NOT NULL, 
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,

    INDEX `idx_tarifs_deleted_at` (`deleted_at`)
);

-- Таблица: Сотрудники
CREATE TABLE `employees` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `last_name` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(255) NOT NULL,
    `middle_name` VARCHAR(255),
    `phone` VARCHAR(20) NOT NULL,
    -- Оставили обычный VARCHAR, чтобы не плодить лишние сущности (справочник должностей)
    `position` VARCHAR(100) NOT NULL, 
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,

    INDEX `idx_emp_last_name` (`last_name`),
    INDEX `idx_employees_deleted_at` (`deleted_at`)
);

-- Таблица: Модели оборудования
CREATE TABLE `device_models` (
    `id` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `manufacturer` VARCHAR(100) NOT NULL,
    `model` VARCHAR(100) NOT NULL,
    `number_of_ports` SMALLINT UNSIGNED NOT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL
);

-- Таблица: Устройства (коммутаторы и тд)
CREATE TABLE `devices` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `model_id` SMALLINT UNSIGNED NOT NULL,
    `house_id` INT UNSIGNED NOT NULL,
    `ipv4_address` INT UNSIGNED NOT NULL, -- INET_ATON для оптимизации
    `ipv6_address` VARBINARY(16),
    `serial_number` VARCHAR(50) NOT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (`model_id`) REFERENCES `device_models`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`house_id`) REFERENCES `houses`(`id`) ON DELETE RESTRICT,
    UNIQUE KEY `unq_serial` (`serial_number`),
    INDEX `idx_devices_deleted_at` (`deleted_at`)
);

-- Таблица: Договоры (Абоненты)
CREATE TABLE `contracts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `number` VARCHAR(20) NOT NULL,
    `client_id` INT UNSIGNED NOT NULL,
    `house_id` INT UNSIGNED NOT NULL,
    `apart_num` VARCHAR(10),
    `tarif_id` INT UNSIGNED NOT NULL,
    `ipv4_address` INT UNSIGNED NOT NULL,
    `ipv6_address` VARBINARY(16),
    `device_id` INT UNSIGNED,
    `port_number` SMALLINT UNSIGNED,
    `mac_address` BINARY(6),
    `balance` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    -- Аналогично тарифам: ENUM вместо справочника для упрощения схемы БД
    `status` ENUM('Активен', 'Заблокирован (Баланс)', 'Приостановлен (Добровольно)', 'Расторгнут') NOT NULL, 
    `next_billing_at` DATE NOT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`house_id`) REFERENCES `houses`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`tarif_id`) REFERENCES `tarifs`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`id`) ON DELETE RESTRICT,

    UNIQUE KEY `unq_contract_number` (`number`),
    UNIQUE KEY `unq_ipv4` (`ipv4_address`),
    UNIQUE KEY `unq_mac` (`mac_address`),
    INDEX `idx_next_billing` (`next_billing_at`),
    INDEX `idx_contracts_deleted_at` (`deleted_at`)
);

-- Таблица: Платежи
CREATE TABLE `payments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `paid_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `contract_id` INT UNSIGNED NOT NULL,
    `amount` INT UNSIGNED NOT NULL,
    -- ENUM вместо отдельной таблицы payment_methods для защиты от избыточной нормализации
    `payment_method` ENUM('Банковская карта', 'СБП (Система быстрых платежей)', 'Наличные в офисе', 'Обещанный платеж') NOT NULL, 
    `external_transaction_id` VARCHAR(100) NOT NULL,

    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE,

    UNIQUE KEY `unq_external_tx` (`external_transaction_id`),
    INDEX `idx_paid_at` (`paid_at`)
);

-- Таблица: Тикеты (Заявки саппорта)
CREATE TABLE `tickets` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contract_id` INT UNSIGNED NOT NULL,
    `description` TEXT NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    -- ENUM для статусов тикета
    `status` ENUM('Открыт', 'В работе', 'Ожидает ответа клиента', 'Решен', 'Закрыт') NOT NULL, 
    `employee_id` INT UNSIGNED,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL,

    INDEX `idx_status` (`status`),
    INDEX `idx_tickets_deleted_at` (`deleted_at`)
);

-- Таблица: Списания (Начисления абонплаты)
CREATE TABLE `charges` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `charged_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `contract_id` INT UNSIGNED NOT NULL,
    `amount` INT UNSIGNED NOT NULL,

    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE,
    INDEX `idx_charged_at` (`charged_at`)
);

-- Таблица: Доп. услуги (справочник)
CREATE TABLE `services` (
    `id` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(100) NOT NULL,
    `cost` INT UNSIGNED NOT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL
);

-- Таблица: Подключенные услуги на договоре (Pivot table)
CREATE TABLE `contract_service` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contract_id` INT UNSIGNED NOT NULL,
    `service_id` SMALLINT UNSIGNED NOT NULL,
    `fixed_cost` INT UNSIGNED NOT NULL, -- фиксируем стоимость на момент подключения
    `start_date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `end_date` TIMESTAMP NULL DEFAULT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,

    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE RESTRICT,

    INDEX `idx_active` (`is_active`)
);

SET FOREIGN_KEY_CHECKS = 1;


-- Дамп тестовых данных

SET FOREIGN_KEY_CHECKS = 0;

-- Базовые справочники (Services, Device Models, Tarifs, Houses, Clients, Employees)
INSERT INTO `services` (`type`, `cost`) VALUES 
('Статический IP-адрес', 15000), 
('Антивирус Kaspersky', 9900), 
('IPTV Базовый', 25000), 
('Турбокнопка (на сутки)', 5000);

INSERT INTO `device_models` (`manufacturer`, `model`, `number_of_ports`) VALUES 
('D-Link', 'DES-3200-28', 28),
('MikroTik', 'CRS326-24G-2S+RM', 24),
('Huawei', 'SmartAX MA5608T', 128),
('Eltex', 'MES2324', 24);

INSERT INTO `tarifs` (`name`, `speed_mbps`, `cost_per_month`, `technology`, `is_active`) VALUES 
('Эконом 100', 100, 45000, 'FTTB', TRUE),
('Оптимум 300', 300, 65000, 'GPON', TRUE),
('Гигабит PRO', 1000, 120000, 'GPON', TRUE),
('Старый ADSL', 10, 30000, 'ADSL', FALSE);

INSERT INTO `houses` (`city`, `street`, `number`) VALUES 
('Москва', 'Ленина', '10'),
('Москва', 'Ленина', '12'),
('Москва', 'Пушкина', '5A'),
('Санкт-Петербург', 'Невский проспект', '45'),
('Санкт-Петербург', 'Садовая', '101');

INSERT INTO `clients` (`last_name`, `first_name`, `middle_name`, `phone`, `email`, `passport_series`, `passport_number`, `passport_issued_by`, `passport_department_code`, `passport_issued_at`) VALUES 
('Иванов', 'Иван', 'Иванович', '+79991112233', 'ivanov@example.com', '4510', '123456', 'ГУ МВД РОССИИ ПО Г. МОСКВЕ', '770-001', '2015-05-10'),
('Петрова', 'Анна', 'Сергеевна', '+79992223344', 'petrova@example.com', '4511', '654321', 'ОТДЕЛОМ УФМС РОССИИ ПО Г. МОСКВЕ', '770-045', '2016-08-21'),
('Смирнов', 'Алексей', NULL, '+79993334455', 'smirnov@example.com', '4005', '987654', 'ГУ МВД РОССИИ ПО Г. САНКТ-ПЕТЕРБУРГУ', '780-002', '2010-11-05'),
('Кузнецова', 'Елена', 'Владимировна', '+79994445566', NULL, '4012', '112233', 'ОТДЕЛОМ УФМС ПО СПБ И ЛО', '780-033', '2020-02-14'),
('Соколов', 'Дмитрий', 'Игоревич', '+79995556677', 'sokolov@example.com', '4515', '556677', 'ГУ МВД РОССИИ ПО Г. МОСКВЕ', '770-099', '2018-09-30');

INSERT INTO `employees` (`last_name`, `first_name`, `middle_name`, `phone`, `position`) VALUES 
('Сидоров', 'Петр', 'Алексеевич', '+79001001010', 'Специалист техподдержки'),
('Васильев', 'Игорь', 'Дмитриевич', '+79002002020', 'Монтажник'),
('Морозова', 'Ольга', 'Николаевна', '+79003003030', 'Бухгалтер'),
('Попов', 'Сергей', 'Андреевич', '+79004004040', 'Системный администратор');

-- Активное оборудование
INSERT INTO `devices` (`model_id`, `house_id`, `ipv4_address`, `ipv6_address`, `serial_number`) VALUES 
(1, 1, INET_ATON('10.0.1.10'), NULL, 'DLK-10001'),
(1, 2, INET_ATON('10.0.1.12'), NULL, 'DLK-10002'),
(2, 3, INET_ATON('10.0.2.5'), NULL, 'MIK-20005'),
(3, 4, INET_ATON('10.0.3.45'), INET6_ATON('2001:0db8:85a3::8a2e:0370:7334'), 'HUA-30045'),
(4, 5, INET_ATON('10.0.4.101'), NULL, 'ELT-40101');

-- Договоры абонентов
INSERT INTO `contracts` (`number`, `client_id`, `house_id`, `apart_num`, `tarif_id`, `ipv4_address`, `ipv6_address`, `device_id`, `port_number`, `mac_address`, `balance`, `created_at`, `status`, `next_billing_at`) VALUES 
('100-001', 1, 1, '45', 1, INET_ATON('100.64.1.101'), NULL, 1, 15, UNHEX('A1B2C3D4E501'), 15000, '2023-01-15 10:00:00', 'Активен', '2023-11-01'),
('100-002', 2, 2, '12', 2, INET_ATON('100.64.1.102'), NULL, 2, 8, UNHEX('A1B2C3D4E502'), -65000, '2023-02-20 14:30:00', 'Заблокирован (Баланс)', '2023-10-01'),
('100-003', 3, 4, '108', 3, INET_ATON('95.10.20.5'), INET6_ATON('2001:0db8::1'), 4, 112, UNHEX('A1B2C3D4E503'), 240000, '2023-05-10 09:15:00', 'Активен', '2023-11-01'),
('100-004', 4, 5, '3', 1, INET_ATON('100.64.4.40'), NULL, 5, 2, UNHEX('A1B2C3D4E504'), 0, '2022-11-05 11:45:00', 'Расторгнут', '2023-01-01'),
('100-005', 5, 3, '55', 2, INET_ATON('100.64.2.55'), NULL, 3, 24, UNHEX('A1B2C3D4E505'), 5000, '2023-08-01 16:20:00', 'Активен', '2023-11-01');

-- Подключенные доп услуги
INSERT INTO `contract_service` (`contract_id`, `service_id`, `fixed_cost`, `start_date`, `end_date`, `is_active`) VALUES 
(3, 1, 15000, '2023-05-10 10:00:00', NULL, TRUE),
(1, 3, 25000, '2023-03-01 12:00:00', NULL, TRUE),
(2, 2, 9900, '2023-02-20 15:00:00', '2023-09-01 00:00:00', FALSE);

-- История платежей
INSERT INTO `payments` (`paid_at`, `contract_id`, `amount`, `payment_method`, `external_transaction_id`) VALUES 
('2023-09-01 10:05:00', 1, 70000, 'Банковская карта', 'TXN-BANK-0001'),
('2023-09-02 11:12:00', 3, 135000, 'СБП (Система быстрых платежей)', 'TXN-SBP-0002'),
('2023-09-15 15:30:00', 5, 65000, 'Банковская карта', 'TXN-BANK-0003'),
('2023-10-01 09:00:00', 1, 70000, 'Банковская карта', 'TXN-BANK-0004'),
('2023-10-02 08:45:00', 3, 135000, 'СБП (Система быстрых платежей)', 'TXN-SBP-0005'),
('2023-10-15 14:20:00', 5, 65000, 'Наличные в офисе', 'CASH-OFFICE-101');

-- История списаний абонплаты
INSERT INTO `charges` (`charged_at`, `contract_id`, `amount`) VALUES 
('2023-09-01 00:00:01', 1, 70000), 
('2023-09-01 00:00:01', 2, 65000),
('2023-09-01 00:00:01', 3, 135000),
('2023-09-01 00:00:01', 5, 65000),
('2023-10-01 00:00:01', 1, 70000),
('2023-10-01 00:00:01', 3, 135000),
('2023-10-01 00:00:01', 5, 65000);

-- Обращения в саппорт
INSERT INTO `tickets` (`contract_id`, `description`, `created_at`, `status`, `employee_id`, `deleted_at`) VALUES 
(1, 'Не работает IPTV приставка. Ошибка 404 на экране.', '2023-09-15 18:20:00', 'Решен', 4, NULL),
(2, 'Вопрос: почему заблокирован интернет?', '2023-10-02 10:15:00', 'Закрыт', 1, NULL),
(5, 'Перебит кабель в подъезде после ремонта соседей.', '2023-10-25 09:00:00', 'В работе', 2, NULL),
(3, 'Настроить обратную DNS зону для статического IP.', '2023-10-26 11:30:00', 'Открыт', 4, NULL);

SET FOREIGN_KEY_CHECKS = 1;

-- VIEWS (Представления)

-- Карточка клиента (сводная инфа для CRM)
CREATE OR REPLACE VIEW `v_crm_client_profile` AS
SELECT 
    `c`.`number` AS `contract_number`,
    `c`.`status` AS `contract_status`,
    `c`.`balance`,
    CONCAT(`cl`.`last_name`, ' ', `cl`.`first_name`, IFNULL(CONCAT(' ', `cl`.`middle_name`), '')) AS `full_name`,
    `cl`.`phone`,
    CONCAT('г. ', `h`.`city`, ', ул. ', `h`.`street`, ', д. ', `h`.`number`, ', кв. ', IFNULL(`c`.`apart_num`, '-')) AS `full_address`,
    `t`.`name` AS `tarif_name`,
    `t`.`speed_mbps`,
    INET_NTOA(`c`.`ipv4_address`) AS `ipv4_address`,
    `c`.`next_billing_at`
FROM `contracts` AS `c`
JOIN `clients` AS `cl` ON `c`.`client_id` = `cl`.`id`
JOIN `houses` AS `h` ON `c`.`house_id` = `h`.`id`
JOIN `tarifs` AS `t` ON `c`.`tarif_id` = `t`.`id`
WHERE `c`.`deleted_at` IS NULL AND `cl`.`deleted_at` IS NULL
ORDER BY
    `cl`.`last_name`,
    `cl`.`first_name`,
    `cl`.`middle_name`;

SELECT * FROM `v_crm_client_profile`;

-- Отчет по должникам для биллинга
CREATE OR REPLACE VIEW `v_billing_debtors` AS
SELECT 
    `c`.`number` AS `contract_number`,
    CONCAT(`cl`.`last_name`, ' ', `cl`.`first_name`) AS `client_name`,
    `cl`.`phone`,
    `c`.`balance` AS `current_debt`,
    `t`.`cost_per_month` AS `monthly_fee`,
    IFNULL((
        SELECT SUM(`cs`.`fixed_cost`) 
        FROM `contract_service` AS `cs` 
        WHERE `cs`.`contract_id` = `c`.`id` AND `cs`.`is_active` = TRUE
    ), 0) AS `extra_services_cost`,
    `c`.`next_billing_at`
FROM `contracts` AS `c`
JOIN `clients` AS `cl` ON `c`.`client_id` = `cl`.`id`
JOIN `tarifs` AS `t` ON `c`.`tarif_id` = `t`.`id`
WHERE `c`.`status` = 'Заблокирован (Баланс)' 
  AND `c`.`balance` < 0
  AND `c`.`deleted_at` IS NULL
ORDER BY
    `c`.`balance`;

SELECT * FROM `v_billing_debtors`;

-- Утилизация портов на железе (для монтажников)
CREATE OR REPLACE VIEW `v_network_equipment_utilization` AS
SELECT 
    `d`.`id` AS `device_id`,
    CONCAT(`dm`.`manufacturer`, ' ', `dm`.`model`) AS `equipment_model`,
    `d`.`serial_number`,
    INET_NTOA(`d`.`ipv4_address`) AS `management_ip`,
    CONCAT(`h`.`city`, ', ', `h`.`street`, ', ', `h`.`number`) AS `installation_address`,
    `dm`.`number_of_ports` AS `total_ports`,
    COUNT(`c`.`id`) AS `used_ports`,
    (`dm`.`number_of_ports` - COUNT(`c`.`id`)) AS `free_ports`
FROM `devices` AS `d`
JOIN `device_models` AS `dm` ON `d`.`model_id` = `dm`.`id`
JOIN `houses` AS `h` ON `d`.`house_id` = `h`.`id`
LEFT JOIN `contracts` AS `c` ON `c`.`device_id` = `d`.`id` 
    AND `c`.`status` != 'Расторгнут' 
    AND `c`.`deleted_at` IS NULL
WHERE `d`.`deleted_at` IS NULL
GROUP BY 
    `d`.`id`, `dm`.`manufacturer`, `dm`.`model`, `d`.`serial_number`, `d`.`ipv4_address`, `h`.`city`, `h`.`street`, `h`.`number`, `dm`.`number_of_ports`
ORDER BY
    `free_ports`,
    `total_ports` DESC;

SELECT * FROM `v_network_equipment_utilization`;

-- Финансовый отчет по дням
CREATE OR REPLACE VIEW `v_daily_financial_report` AS
SELECT 
    DATE(`paid_at`) AS `payment_date`,
    `payment_method`,
    COUNT(`id`) AS `transactions_count`,
    SUM(`amount`) AS `total_revenue`
FROM `payments`
GROUP BY 
    DATE(`paid_at`), 
    `payment_method`
ORDER BY 
    `payment_date` DESC,
    `total_revenue` DESC,
    `transactions_count` DESC;

SELECT * FROM `v_daily_financial_report`;

-- Нагрузка на сотрудников ТП
CREATE OR REPLACE VIEW `v_helpdesk_workload` AS
SELECT 
    `e`.`id` AS `employee_id`,
    CONCAT(`e`.`last_name`, ' ', `e`.`first_name`) AS `employee_name`,
    `e`.`position`,
    COUNT(`t`.`id`) AS `total_assigned_tickets`,
    SUM(CASE WHEN `t`.`status` IN ('Открыт', 'В работе', 'Ожидает ответа клиента') THEN 1 ELSE 0 END) AS `active_tickets`,
    SUM(CASE WHEN `t`.`status` IN ('Решен', 'Закрыт') THEN 1 ELSE 0 END) AS `resolved_tickets`
FROM `employees` AS `e`
LEFT JOIN `tickets` AS `t` ON `e`.`id` = `t`.`employee_id` AND `t`.`deleted_at` IS NULL
WHERE `e`.`deleted_at` IS NULL
GROUP BY 
    `e`.`id`, 
    `employee_name`, 
    `e`.`position`
ORDER BY `active_tickets` DESC, `total_assigned_tickets` DESC, `resolved_tickets` DESC;

SELECT * FROM `v_helpdesk_workload`;

-- Статистика по тарифам
CREATE OR REPLACE VIEW `v_tariff_popularity` AS
SELECT 
    `t`.`name` AS `tariff_name`,
    `t`.`technology`,
    `t`.`cost_per_month`,
    COUNT(`c`.`id`) AS `active_subscribers`,
    (COUNT(`c`.`id`) * `t`.`cost_per_month`) AS `potential_monthly_revenue`
FROM `tarifs` AS `t`
LEFT JOIN `contracts` AS `c` ON `t`.`id` = `c`.`tarif_id` AND `c`.`status` = 'Активен' AND `c`.`deleted_at` IS NULL
WHERE `t`.`deleted_at` IS NULL
GROUP BY 
    `t`.`id`, 
    `t`.`name`, 
    `t`.`technology`, 
    `t`.`cost_per_month`
ORDER BY `active_subscribers` DESC, `potential_monthly_revenue` DESC;

SELECT * FROM `v_tariff_popularity`;

-- Функции и процедуры

DELIMITER //

-- Считает общую абонплату (тариф + активные доп услуги)
CREATE FUNCTION `fn_get_full_monthly_cost`(p_contract_id INT UNSIGNED) 
RETURNS INT UNSIGNED
READS SQL DATA
BEGIN
    DECLARE v_tarif_cost INT UNSIGNED DEFAULT 0;
    DECLARE v_services_cost INT UNSIGNED DEFAULT 0;

    SELECT `t`.`cost_per_month` INTO v_tarif_cost
    FROM `contracts` AS `c`
    JOIN `tarifs` AS `t` ON `c`.`tarif_id` = `t`.`id`
    WHERE `c`.`id` = p_contract_id;

    SELECT IFNULL(SUM(`fixed_cost`), 0) INTO v_services_cost
    FROM `contract_service`
    WHERE `contract_id` = p_contract_id AND `is_active` = TRUE;

    RETURN v_tarif_cost + v_services_cost;
END//
DELIMITER ;

DELIMITER //

-- Форматирует MAC из бинарного вида в читаемый (AA:BB:CC...)
CREATE FUNCTION `fn_mac_to_string`(p_mac BINARY(6)) 
RETURNS CHAR(17)
DETERMINISTIC
BEGIN
    IF p_mac IS NULL THEN
        RETURN NULL;
    END IF;

    RETURN CONCAT_WS(':', 
        LPAD(HEX(SUBSTRING(p_mac, 1, 1)), 2, '0'),
        LPAD(HEX(SUBSTRING(p_mac, 2, 1)), 2, '0'),
        LPAD(HEX(SUBSTRING(p_mac, 3, 1)), 2, '0'),
        LPAD(HEX(SUBSTRING(p_mac, 4, 1)), 2, '0'),
        LPAD(HEX(SUBSTRING(p_mac, 5, 1)), 2, '0'),
        LPAD(HEX(SUBSTRING(p_mac, 6, 1)), 2, '0')
    );
END//
DELIMITER ;

-- Триггеры

DELIMITER //

-- Автоматическое пополнение баланса и разблокировка при поступлении платежа
CREATE TRIGGER `trg_after_payment_insert`
AFTER INSERT ON `payments`
FOR EACH ROW
BEGIN
    DECLARE v_new_balance INT;
    DECLARE v_current_status VARCHAR(50);

    UPDATE `contracts`
    SET `balance` = `balance` + NEW.`amount`
    WHERE `id` = NEW.`contract_id`;

    SELECT `balance`, `status` INTO v_new_balance, v_current_status
    FROM `contracts`
    WHERE `id` = NEW.`contract_id`;

    IF v_new_balance >= 0 AND v_current_status = 'Заблокирован (Баланс)' THEN
        UPDATE `contracts`
        SET `status` = 'Активен'
        WHERE `id` = NEW.`contract_id`;
    END IF;
END//
DELIMITER ;

DELIMITER //

-- Проверка свободных портов на свитче перед заведением абонента
CREATE TRIGGER `trg_before_contract_insert`
BEFORE INSERT ON `contracts`
FOR EACH ROW
BEGIN
    DECLARE v_total_ports INT;
    DECLARE v_used_ports INT;

    SELECT `dm`.`number_of_ports` INTO v_total_ports
    FROM `devices` AS `d`
    JOIN `device_models` AS `dm` ON `d`.`model_id` = `dm`.`id`
    WHERE `d`.`id` = NEW.`device_id`;

    SELECT COUNT(*) INTO v_used_ports
    FROM `contracts`
    WHERE `device_id` = NEW.`device_id` AND `status` != 'Расторгнут';

    IF v_used_ports >= v_total_ports THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Ошибка: На данном оборудовании нет свободных портов!';
    END IF;
END//
DELIMITER ;

DELIMITER //

-- Списание абонентки (вызывается раз в месяц кроном)
CREATE PROCEDURE `sp_process_monthly_billing`(IN p_contract_id INT UNSIGNED)
BEGIN
    DECLARE v_total_charge INT UNSIGNED;
    DECLARE v_current_balance INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SET v_total_charge = fn_get_full_monthly_cost(p_contract_id);

    IF v_total_charge > 0 THEN

        INSERT INTO `charges` (`contract_id`, `amount`) 
        VALUES (p_contract_id, v_total_charge);

        UPDATE `contracts`
        SET `balance` = `balance` - CAST(v_total_charge AS SIGNED)
        WHERE `id` = p_contract_id;

        SELECT `balance` INTO v_current_balance FROM `contracts` WHERE `id` = p_contract_id;

        IF v_current_balance < 0 THEN
            UPDATE `contracts` SET `status` = 'Заблокирован (Баланс)' WHERE `id` = p_contract_id;
        END IF;

    END IF;

    UPDATE `contracts` 
    SET `next_billing_at` = DATE_ADD(`next_billing_at`, INTERVAL 1 MONTH)
    WHERE `id` = p_contract_id;

    COMMIT;
END//

DELIMITER ;
