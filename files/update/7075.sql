-- Add installation method column for software
ALTER TABLE `software` ADD COLUMN `INSTALLMETHOD` VARCHAR(255) DEFAULT NULL;