INSERT INTO `config` VALUES('WARN_UPDATE',1,'1','Warn user if an update is available');
INSERT INTO `config` VALUES('INVENTORY_ON_STARTUP',1,'1','Launch inventory on agent service statup');
ALTER TABLE `networks` ADD `MTU` VARCHAR(255) NULL DEFAULT NULL AFTER `SPEED`;
ALTER TABLE `download_available` ADD `DELETED` INT(1) NOT NULL DEFAULT '0' AFTER `ID_WK`;