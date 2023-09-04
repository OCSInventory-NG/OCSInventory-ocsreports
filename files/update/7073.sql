-- Add pretty version column for software_version
ALTER TABLE `software_version` ADD COLUMN `PRETTYVERSION` VARCHAR(255) DEFAULT NULL;
-- Add major version column for software_version
ALTER TABLE `software_version` ADD COLUMN `MAJOR` INT DEFAULT NULL;
-- Add minor version column for software_version
ALTER TABLE `software_version` ADD COLUMN `MINOR` INT DEFAULT NULL;
-- Add patch version column for software_version
ALTER TABLE `software_version` ADD COLUMN `PATCH` INT DEFAULT NULL;
-- Add index on pretty version
ALTER TABLE `software_version` ADD INDEX index_prettyversion (`PRETTYVERSION`);
-- Update unavailable version and set default pretty version
UPDATE `software_version` SET `PRETTYVERSION` = "Unavailable", `MAJOR` = 0, `MINOR` = 0, `PATCH` = 0 WHERE `ID` = 1;

-- Remove VULN_CVESEARCH_ALL from config
DELETE FROM `config` WHERE `NAME` = 'VULN_CVESEARCH_ALL';