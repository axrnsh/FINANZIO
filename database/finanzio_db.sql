CREATE DATABASE finanzio_db;

USE finanzio_db;

CREATE TABLE users (
    id_users INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    email VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE income (
    income_id INT AUTO_INCREMENT PRIMARY KEY,
    id_users INT(30) NOT NULL,
    income_amount INT(12) NOT NULL,
    income_date DATE NOT NULL,
    income_category VARCHAR(50) NOT NULL,
    income_assets VARCHAR(30) NOT NULL
);

CREATE TABLE expense (
    expense_id INT AUTO_INCREMENT PRIMARY KEY,
    id_users INT(30) NOT NULL,
    expense_amount INT(12) NOT NULL,
    expense_date DATE NOT NULL,
    expense_category VARCHAR(50) NOT NULL,
    expense_assets VARCHAR(30) NOT NULL
);

CREATE TABLE assets (
	assets_id INT AUTO_INCREMENT PRIMARY KEY,
    id_users INT(30) NOT NULL,
    assets_name VARCHAR(50) NOT NULL,
    assets_amount INT(12) NOT NULL
);

CREATE TABLE upcoming_bills (
    upcoming_bills_id INT AUTO_INCREMENT PRIMARY KEY,
    id_users INT(30) NOT NULL,
    upcoming_bills_name VARCHAR(50) NOT NULL,
    upcoming_bills_due_date DATE NOT NULL,
    upcoming_bills_status VARCHAR(50) NOT NULL,
    upcoming_bills_amount INT(12) NOT NULL,
    upcoming_bills_assets VARCHAR(30) NOT NULL
);

DELIMITER $$
CREATE TRIGGER update_assets_after_income_insert
AFTER INSERT ON income
FOR EACH ROW
BEGIN
    UPDATE assets
    SET assets_amount = assets_amount + NEW.income_amount
    WHERE id_users = NEW.id_users AND assets_name = NEW.income_assets;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER update_assets_after_income_update
AFTER UPDATE ON income
FOR EACH ROW
BEGIN
    UPDATE assets
    SET assets_amount = assets_amount + (NEW.income_amount - OLD.income_amount)
    WHERE id_users = NEW.id_users AND assets_name = NEW.income_assets;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER update_assets_after_expense_insert
AFTER INSERT ON expense
FOR EACH ROW
BEGIN
    UPDATE assets
    SET assets_amount = assets_amount - NEW.expense_amount
    WHERE id_users = NEW.id_users AND assets_name = NEW.expense_assets;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER update_assets_after_expense_update
AFTER UPDATE ON expense
FOR EACH ROW
BEGIN
    UPDATE assets
    SET assets_amount = assets_amount + (OLD.expense_amount - NEW.expense_amount)
    WHERE id_users = NEW.id_users AND assets_name = NEW.expense_assets;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER update_assets_after_upcoming_bills_insert
AFTER INSERT ON upcoming_bills
FOR EACH ROW
BEGIN
    IF NEW.upcoming_bills_status = 'Paid' THEN
        UPDATE assets
        SET assets_amount = assets_amount - NEW.upcoming_bills_amount
        WHERE id_users = NEW.id_users AND assets_name = NEW.upcoming_bills_assets;
    END IF;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER update_assets_after_upcoming_bills_update
AFTER UPDATE ON upcoming_bills
FOR EACH ROW
BEGIN
    IF OLD.upcoming_bills_status = 'Paid' AND NEW.upcoming_bills_status = 'Paid' THEN
        UPDATE assets
        SET assets_amount = assets_amount + OLD.upcoming_bills_amount - NEW.upcoming_bills_amount
        WHERE id_users = OLD.id_users AND assets_name = OLD.upcoming_bills_assets;
    ELSEIF OLD.upcoming_bills_status = 'Paid' THEN
        UPDATE assets
        SET assets_amount = assets_amount + OLD.upcoming_bills_amount
        WHERE id_users = OLD.id_users AND assets_name = OLD.upcoming_bills_assets;
    ELSEIF NEW.upcoming_bills_status = 'Paid' THEN
        UPDATE assets
        SET assets_amount = assets_amount - NEW.upcoming_bills_amount
        WHERE id_users = NEW.id_users AND assets_name = NEW.upcoming_bills_assets;
    END IF;
END$$
DELIMITER ;