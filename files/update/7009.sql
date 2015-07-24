ALTER TABLE `bios` ADD COLUMN `MMANUFACTURER` varchar(255) default NULL;
ALTER TABLE `bios` ADD COLUMN `MMODEL` varchar(255) default NULL;
ALTER TABLE `bios` ADD COLUMN `MSN` varchar(255) default NULL;

CREATE TABLE IF NOT EXISTS `plugins` (
  `id` int(6) unsigned NOT NULL,
  `name` varchar(30) COLLATE utf8_bin NOT NULL,
  `version` double NOT NULL,
  `licence` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `author` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `verminocs` double NOT NULL,
  `activated` tinyint(1) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

ALTER TABLE `plugins` ADD PRIMARY KEY (`id`);
ALTER TABLE `plugins` MODIFY `id` int(6) unsigned NOT NULL AUTO_INCREMENT;
