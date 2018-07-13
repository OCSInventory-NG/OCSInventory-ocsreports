DROP TABLE `plugins`;

CREATE TABLE IF NOT EXISTS `extensions` (
  `id` varchar(255) COLLATE utf8_bin NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `version` double NOT NULL,
  `licence` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `author` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `contributor` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `install_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

ALTER TABLE `plugins` ADD PRIMARY KEY (`id`);