-- Add installation method column for software
ALTER TABLE `software` ADD COLUMN `INSTALLMETHOD` VARCHAR(255) DEFAULT NULL;
-- Add last update and update type column for software
ALTER TABLE `software` ADD COLUMN `LASTUPDATE` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `software` ADD COLUMN `UPDATETYPE` VARCHAR(255) DEFAULT NULL;