# MySQL dump
#
#--------------------------------------------------------
# Program Version 2.0.46

CREATE DATABASE IF NOT EXISTS `factorydb`;
USE `factorydb`;

#
# Table structure for table 'Factory_Master'
#

DROP TABLE IF EXISTS `Factory_Master`;

CREATE TABLE `Factory_Master` (
  `FactoryId` INTEGER NOT NULL AUTO_INCREMENT, 
  `FactoryName` VARCHAR(50), 
  INDEX (`FactoryName`), 
  PRIMARY KEY (`FactoryId`)
) TYPE=MyISAM;

#
# Dumping data for table 'Factory_Master'
#

INSERT INTO `Factory_Master` VALUES (1, 'Factory 1');
INSERT INTO `Factory_Master` VALUES (2, 'Factory 2');
INSERT INTO `Factory_Master` VALUES (3, 'Factory 3');
# 3 records

#
# Table structure for table 'Factory_Output'
#

DROP TABLE IF EXISTS `Factory_Output`;

CREATE TABLE `Factory_Output` (
  `FactoryID` INTEGER DEFAULT 0, 
  `DatePro` DATETIME DEFAULT 'Now()', 
  `Quantity` DOUBLE NULL, 
  INDEX (`FactoryID`)
) TYPE=MyISAM;

#
# Dumping data for table 'Factory_Output'
#

INSERT INTO `Factory_Output` VALUES (1, '2003-01-01 17:53:26', 21);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-02 17:54:13', 23);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-03 17:54:14', 22);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-04 17:54:21', 24);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-05 17:54:45', 32);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-06 17:54:53', 21);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-07 17:54:58', 34);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-08 17:55:04', 32);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-09 17:55:15', 32);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-10 17:55:20', 23);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-11 17:55:26', 23);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-12 17:55:35', 32);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-13 17:55:40', 53);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-14 17:55:44', 23);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-15 17:55:51', 26);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-16 17:55:58', 43);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-17 17:56:04', 16);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-18 17:56:09', 45);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-19 17:56:15', 65);
INSERT INTO `Factory_Output` VALUES (1, '2003-01-20 17:56:22', 54);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-01 17:53:26', 121);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-02 17:54:13', 123);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-03 17:54:14', 122);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-04 17:54:21', 124);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-05 17:54:45', 132);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-06 17:54:53', 121);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-07 17:54:58', 134);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-08 17:55:04', 132);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-09 17:55:15', 132);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-10 17:55:20', 123);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-11 17:55:26', 123);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-12 17:55:35', 132);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-13 17:55:40', 153);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-14 17:55:44', 123);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-15 17:55:51', 126);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-16 17:55:58', 143);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-17 17:56:04', 116);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-18 17:56:09', 145);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-19 17:56:15', 165);
INSERT INTO `Factory_Output` VALUES (2, '2003-01-20 17:56:22', 154);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-01 17:53:26', 54);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-02 17:54:13', 56);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-03 17:54:14', 89);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-04 17:54:21', 56);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-05 17:54:45', 98);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-06 17:54:53', 76);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-07 17:54:58', 65);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-08 17:55:04', 45);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-09 17:55:15', 75);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-10 17:55:20', 54);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-11 17:55:26', 75);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-12 17:55:35', 76);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-13 17:55:40', 34);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-14 17:55:44', 97);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-15 17:55:51', 55);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-16 17:55:58', 43);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-17 17:56:04', 16);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-18 17:56:09', 35);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-19 17:56:15', 78);
INSERT INTO `Factory_Output` VALUES (3, '2003-01-20 17:56:22', 75);
# 60 records

