-- Drop old snmp_accountinfo table
DROP TABLE IF EXISTS `snmp_accountinfo`;

-- Create the new snmp_accountinfo table
CREATE TABLE `snmp_accountinfo` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `SNMP_TYPE` varchar(255) NOT NULL,
    `SNMP_RECONCILIATION_FIELD` varchar(255) NOT NULL,
    `SNMP_RECONCILIATION_VALUE` varchar(255) NOT NULL,
    `TAG` varchar(255) DEFAULT 'NA',
    PRIMARY KEY (`ID`),
    KEY `TAG` (`TAG`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;