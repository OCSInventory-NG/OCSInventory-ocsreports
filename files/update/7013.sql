-- Create software_categories table
CREATE TABLE IF NOT EXISTS `software_categories` (
    `ID` INTEGER NOT NULL AUTO_INCREMENT,
    `CATEGORY_NAME` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

-- Create software_category_exp table
CREATE TABLE IF NOT EXISTS `software_category_exp` (
    `CATEGORY_ID` INT(6) NOT NULL,
    `SOFTWARE_EXP` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

--Add CATEGORY column to software table
UNLOCK TABLES;
ALTER TABLE `softwares` ADD `CATEGORY` varchar(255) default NULL;
