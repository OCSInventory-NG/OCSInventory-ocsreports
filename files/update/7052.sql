UNLOCK TABLES;

-- Change snmp structure type tables
CREATE TABLE IF NOT EXISTS `snmp_types_conditions` (
    `ID` INTEGER NOT NULL AUTO_INCREMENT,
    `TYPE_ID` INTEGER NOT NULL,
    `CONDITION_OID` VARCHAR(255) NOT NULL,
    `CONDITION_VALUE` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY  (`ID`)
) ENGINE=InnoDB;

-- Migrate snmp type condition to new table
INSERT IGNORE INTO `snmp_types_conditions` (`TYPE_ID`, `CONDITION_OID`, `CONDITION_VALUE`)
SELECT DISTINCT `ID`, `CONDITION_OID`, `CONDITION_VALUE`
FROM `snmp_types`;

-- Drop useless columns from snmp_types
ALTER TABLE `snmp_types` DROP COLUMN `CONDITION_OID`;
ALTER TABLE `snmp_types` DROP COLUMN `CONDITION_VALUE`;

-- Add default SNMP type, label and OID
-- Type
INSERT IGNORE INTO `snmp_types` (`TYPE_NAME`, `TABLE_TYPE_NAME`) VALUES ('Default', 'snmp_default');

-- Condition on description OID
INSERT IGNORE INTO `snmp_types_conditions` (`TYPE_ID`, `CONDITION_OID`) 
SELECT DISTINCT `ID`, '1.3.6.1.2.1.1.1.0'
FROM `snmp_types`
WHERE `TYPE_NAME` = 'Default';

-- Label
INSERT IGNORE INTO `snmp_labels` (`LABEL_NAME`) VALUES ('DefaultDescription');
INSERT IGNORE INTO `snmp_labels` (`LABEL_NAME`) VALUES ('DefaultName');
INSERT IGNORE INTO `snmp_labels` (`LABEL_NAME`) VALUES ('DefaultUptime');
INSERT IGNORE INTO `snmp_labels` (`LABEL_NAME`) VALUES ('DefaultLocation');
INSERT IGNORE INTO `snmp_labels` (`LABEL_NAME`) VALUES ('DefaultAddressIP');
INSERT IGNORE INTO `snmp_labels` (`LABEL_NAME`) VALUES ('DefaultGateway');

-- Configuration

-- Name
INSERT IGNORE INTO `snmp_configs` (`TYPE_ID`, `LABEL_ID`, `OID`, `RECONCILIATION`) 
SELECT `snmp_types`.`ID`, `snmp_labels`.`ID`, '1.3.6.1.2.1.1.5.0', 'Yes'
FROM `snmp_types`, `snmp_labels`
WHERE `LABEL_NAME` = 'DefaultName' AND `TYPE_NAME` = 'Default' GROUP BY `snmp_types`.`ID`;

-- Description
INSERT IGNORE INTO `snmp_configs` (`TYPE_ID`, `LABEL_ID`, `OID`) 
SELECT `snmp_types`.`ID`, `snmp_labels`.`ID`, '1.3.6.1.2.1.1.1.0'
FROM `snmp_types`, `snmp_labels`
WHERE `LABEL_NAME` = 'DefaultDescription' AND `TYPE_NAME` = 'Default' GROUP BY `snmp_types`.`ID`;

-- Location
INSERT IGNORE INTO `snmp_configs` (`TYPE_ID`, `LABEL_ID`, `OID`) 
SELECT `snmp_types`.`ID`, `snmp_labels`.`ID`, '1.3.6.1.2.1.1.6.0'
FROM `snmp_types`, `snmp_labels`
WHERE `LABEL_NAME` = 'DefaultLocation' AND `TYPE_NAME` = 'Default' GROUP BY `snmp_types`.`ID`;

-- Uptime
INSERT IGNORE INTO `snmp_configs` (`TYPE_ID`, `LABEL_ID`, `OID`) 
SELECT `snmp_types`.`ID`, `snmp_labels`.`ID`, '1.3.6.1.2.1.1.3.0'
FROM `snmp_types`, `snmp_labels`
WHERE `LABEL_NAME` = 'DefaultUptime' AND `TYPE_NAME` = 'Default' GROUP BY `snmp_types`.`ID`;

-- Address IP
INSERT IGNORE INTO `snmp_configs` (`TYPE_ID`, `LABEL_ID`, `OID`) 
SELECT `snmp_types`.`ID`, `snmp_labels`.`ID`, '1.3.6.1.2.1.4.20.1.1'
FROM `snmp_types`, `snmp_labels`
WHERE `LABEL_NAME` = 'DefaultAddressIP' AND `TYPE_NAME` = 'Default' GROUP BY `snmp_types`.`ID`;

-- Gateway
INSERT IGNORE INTO `snmp_configs` (`TYPE_ID`, `LABEL_ID`, `OID`) 
SELECT `snmp_types`.`ID`, `snmp_labels`.`ID`, '1.3.6.1.2.1.4.20.1.3'
FROM `snmp_types`, `snmp_labels`
WHERE `LABEL_NAME` = 'DefaultGateway' AND `TYPE_NAME` = 'Default' GROUP BY `snmp_types`.`ID`;

-- Create default snmp table
CREATE TABLE IF NOT EXISTS `snmp_default` (
    `ID` INT(6) NOT NULL AUTO_INCREMENT,
    `DefaultName` VARCHAR(255) DEFAULT NULL,
    `DefaultDescription` VARCHAR(255) DEFAULT NULL,
    `DefaultLocation` VARCHAR(255) DEFAULT NULL,
    `DefaultUptime` VARCHAR(255) DEFAULT NULL,
    `DefaultAddressIP` VARCHAR(255) DEFAULT NULL,
    `DefaultGateway` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

-- Add AUTHPROTO and PRIVPROTO to SNMP v3
ALTER TABLE `snmp_communities` ADD COLUMN `AUTHPROTO` varchar(255) DEFAULT NULL;
ALTER TABLE `snmp_communities` ADD COLUMN `PRIVPROTO` varchar(255) DEFAULT NULL;