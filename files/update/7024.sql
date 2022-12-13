-- Create schedule_wol table
CREATE TABLE IF NOT EXISTS `schedule_wol` (
    `ID` INT(6) NOT NULL AUTO_INCREMENT,
    `MACHINE_ID` varchar(255) NOT NULL,
    `WOL_DATE` datetime NOT NULL,
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;