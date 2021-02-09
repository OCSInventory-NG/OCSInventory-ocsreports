DROP TABLE IF EXISTS `auth_attempt`;

INSERT INTO `config` VALUES('SECURITY_AUTHENTICATION_BLOCK_IP',0,'','Block authentication after too many attempt');
INSERT INTO `config` VALUES('SECURITY_AUTHENTICATION_NB_ATTEMPT',1,'','Define the number of attempt to authenticate');
INSERT INTO `config` VALUES('SECURITY_AUTHENTICATION_TIME_BLOCK',1,'','Define the block timer');

INSERT INTO `config` VALUES('SECURITY_PASSWORD_ENABLED',0,'','Enable the password security');
INSERT INTO `config` VALUES('SECURITY_PASSWORD_MIN_CHAR',1,'','Set minimal characters in password');
INSERT INTO `config` VALUES('SECURITY_PASSWORD_FORCE_NB',0,'','Force number in password');
INSERT INTO `config` VALUES('SECURITY_PASSWORD_FORCE_UPPER',0,'','Force uppercase in password');
INSERT INTO `config` VALUES('SECURITY_PASSWORD_FORCE_SPE_CHAR',0,'','Force scpecial characters in password');

CREATE TABLE IF NOT EXISTS `auth_attempt` (
    `ID` INT(11) NOT NULL AUTO_INCREMENT,
    `DATETIMEATTEMPT` DATETIME NOT NULL,
    `LOGIN` VARCHAR(255) DEFAULT NULL,
    `IP` VARCHAR(255) DEFAULT NULL,
    `SUCCESS` INT(1) DEFAULT NULL,
    PRIMARY KEY  (`ID`)
) ENGINE=InnoDB;