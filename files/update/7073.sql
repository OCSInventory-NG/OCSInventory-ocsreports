-- Add pretty version column for software_version
ALTER TABLE `software_version` ADD COLUMN `PRETTYVERSION` VARCHAR(255) DEFAULT NULL;
-- Add major version column for software_version
ALTER TABLE `software_version` ADD COLUMN `MAJOR` INT DEFAULT NULL;
-- Add minor version column for software_version
ALTER TABLE `software_version` ADD COLUMN `MINOR` INT DEFAULT NULL;
-- Add patch version column for software_version
ALTER TABLE `software_version` ADD COLUMN `PATCH` INT DEFAULT NULL;

-- Remove VULN_CVESEARCH_ALL from config
DELETE FROM `config` WHERE `NAME` = 'VULN_CVESEARCH_ALL';