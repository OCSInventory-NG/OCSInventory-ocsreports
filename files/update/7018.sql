-- Add SIGN column to software_category_exp table
UNLOCK TABLES;
ALTER TABLE `software_category_exp` ADD `SIGN_VERSION` VARCHAR(255) default NULL;
-- Add SIGN column to software_category_exp table
ALTER TABLE `software_category_exp` ADD `VERSION` VARCHAR(255) default NULL;
-- Add SIGN column to software_category_exp table
ALTER TABLE `software_category_exp` ADD `PUBLISHER` VARCHAR(255) default NULL;
