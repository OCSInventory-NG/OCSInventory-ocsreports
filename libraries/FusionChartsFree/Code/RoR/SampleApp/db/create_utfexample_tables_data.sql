USE `factorydb`;
ALTER DATABASE DEFAULT CHARACTER SET = utf8;


#
# Table structure for table 'Japanese_Factory_Masters'
#

DROP TABLE IF EXISTS `japanese_factory_masters`;
CREATE TABLE  `japanese_factory_masters` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# data for table 'japanese_factory_masters'
#

INSERT INTO `japanese_factory_masters` VALUES (1, '工場 1');
INSERT INTO `japanese_factory_masters` VALUES (2, '工場 2');
INSERT INTO `japanese_factory_masters` VALUES (3, '工場 3');
# 3 records

#
# Table structure for table 'French_Factory_Masters'
#

DROP TABLE IF EXISTS `french_factory_masters`;
CREATE TABLE  `french_factory_masters` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#
# Dumping data for table 'french_factory_masters'
#

INSERT INTO `french_factory_masters` VALUES (1, '工ndustrie 1');
INSERT INTO `french_factory_masters` VALUES (2, '工ndustrie 2');
INSERT INTO `french_factory_masters` VALUES (3, '工ndustrie 3');
# 3 records