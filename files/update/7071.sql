-- Create show methods table
CREATE TABLE IF NOT EXISTS `show_methods` (
    `ID` BIGINT NOT NULL AUTO_INCREMENT,
    `METHOD` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;
INSERT INTO `show_methods` (METHOD) VALUES('Count');
INSERT INTO `show_methods` (METHOD) VALUES('By computer');