DROP TABLE IF EXISTS `factory_masters`;
CREATE TABLE  `factory_masters` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `factory_output_quantities`;
CREATE TABLE  `factory_output_quantities` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `factory_master_id` int(11) unsigned NOT NULL,
  `date_pro` datetime default NULL,
  `quantity` double default NULL,
  PRIMARY KEY  (`id`),
  KEY `FK_output_master` (`factory_master_id`),
  CONSTRAINT `FK_output_master` FOREIGN KEY (`factory_master_id`) REFERENCES `factory_masters` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;