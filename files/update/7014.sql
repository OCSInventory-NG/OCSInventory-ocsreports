-- Create assets_categories table
CREATE TABLE IF NOT EXISTS `assets_categories` (
    `ID` INTEGER NOT NULL AUTO_INCREMENT,
    `CATEGORY_NAME` VARCHAR(255) NOT NULL,
    `SQL_QUERY` TEXT NOT NULL,
    `SQL_ARGS` TEXT NOT NULL,
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

--Add CATEGORY column to hardware table
ALTER TABLE `hardware` ADD `CATEGORY` int(11) default NULL;