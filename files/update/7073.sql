-- Add pretty version column for software_version
ALTER TABLE `software_version` ADD COLUMN `PRETTY_VERSION` VARCHAR(255) DEFAULT NULL;

-- Remove VULN_CVESEARCH_ALL from config
DELETE FROM `config` WHERE `NAME` = 'VULN_CVESEARCH_ALL';