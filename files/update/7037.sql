-- migrate software name
INSERT IGNORE INTO `software_name` (`NAME`)
SELECT DISTINCT `NAME`
FROM `softwares`;

-- migrate software publisher
INSERT IGNORE INTO `software_publisher` (`PUBLISHER`)
SELECT DISTINCT `PUBLISHER`
FROM `softwares`;

-- migrate software version
INSERT IGNORE INTO `software_version` (`VERSION`)
SELECT DISTINCT `VERSION`
FROM `softwares`;

-- migrate softwares
INSERT IGNORE INTO `software` (
  `ID`, `HARDWARE_ID`, `NAME_ID`, `PUBLISHER_ID`, `VERSION_ID`,
  `FOLDER`, `COMMENTS`, `FILENAME`, `FILESIZE`,
  `SOURCE`, `GUID`, `LANGUAGE`, `INSTALLDATE`, `BITSWIDTH`)
SELECT s.`ID`, s.`HARDWARE_ID`, n.`ID`, p.`ID`, v.`ID`,
  s.`FOLDER`, s.`COMMENTS`, s.`FILENAME`, s.`FILESIZE`,
  s.`SOURCE`, s.`GUID`, s.`LANGUAGE`, s.`INSTALLDATE`, s.`BITSWIDTH`
FROM `softwares` s
INNER JOIN `software_name` n ON (n.`NAME` = s.`NAME`)
INNER JOIN `software_publisher` p ON (p.`PUBLISHER` = s.`PUBLISHER`)
INNER JOIN `software_version` v ON (v.`VERSION` = s.`VERSION`);

-- add missing indexes
ALTER TABLE `software` ADD KEY `NAME_ID` (`NAME_ID`);
ALTER TABLE `software` ADD KEY `PUBLISHER_ID` (`PUBLISHER_ID`);
ALTER TABLE `software` ADD KEY `VERSION_ID` (`VERSION_ID`);

-- drop old softwares
DROP TABLE `softwares`;


-- add missing primary keys
ALTER TABLE `deleted_equiv` ADD `ID` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`ID`);
ALTER TABLE `devices` ADD `ID` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`ID`);
