-- Create cve_search table

CREATE TABLE IF NOT EXISTS `cve_search` (
  `vendor` varchar(255) COLLATE utf8_bin NOT NULL,
  `soft` varchar(255) COLLATE utf8_bin NOT NULL,
  `version` varchar(255) COLLATE utf8_bin NOT NULL,
  `cvss` DOUBLE(4,2) NOT NULL,
  `cve` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `link` varchar(255) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;