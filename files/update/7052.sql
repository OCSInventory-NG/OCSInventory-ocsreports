UNLOCK TABLES;

-- Change snmp structure type tables
CREATE TABLE IF NOT EXISTS `snmp_types_conditions` (
    `ID` INTEGER NOT NULL AUTO_INCREMENT,
    `TYPE_ID` INTEGER NOT NULL,
    `CONDITION_OID` VARCHAR(255) NOT NULL,
    `CONDITION_VALUE` VARCHAR(255) NOT NULL,
    PRIMARY KEY  (`ID`)
) ENGINE=InnoDB;

-- Migrate snmp type condition to new table
INSERT IGNORE INTO `snmp_types_conditions` (`TYPE_ID`, `CONDITION_OID`, `CONDITION_VALUE`)
SELECT DISTINCT `ID`, `CONDITION_OID`, `CONDITION_VALUE`
FROM `snmp_types`;

-- Drop useless columns from snmp_types
ALTER TABLE `snmp_types` DROP COLUMN `CONDITION_OID`;
ALTER TABLE `snmp_types` DROP COLUMN `CONDITION_VALUE`;