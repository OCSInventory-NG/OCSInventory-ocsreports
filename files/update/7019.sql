-- Add SIGN column to software_category_exp table
UNLOCK TABLES;
ALTER TABLE `software_categories` ADD `OS` VARCHAR(255) default NULL;
INSERT INTO `software_categories` (`CATEGORY_NAME`, `OS`) SELECT 'Default', 'ALL' FROM `software_categories` WHERE NOT EXISTS (SELECT * FROM `software_categories` WHERE `CATEGORY_NAME`='Default') LIMIT 1;
INSERT INTO `config` (`NAME`, `IVALUE`) SELECT 'DEFAULT_CATEGORY', (SELECT `ID` FROM `software_categories` WHERE `CATEGORY_NAME` = "Default") FROM `config` WHERE NOT EXISTS (SELECT * FROM `config` WHERE `NAME`='DEFAULT_CATEGORY') LIMIT 1;
