-- If it's a new database
--
-- Table structure for table `accesslog`
--

CREATE TABLE `accesslog` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `USERID` varchar(255) DEFAULT NULL,
  `LOGDATE` datetime DEFAULT NULL,
  `PROCESSES` text,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`),
  KEY `USERID` (`USERID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `accountinfo`
--

CREATE TABLE `accountinfo` (
  `HARDWARE_ID` int(11) NOT NULL,
  `TAG` varchar(255) DEFAULT 'NA',
  PRIMARY KEY (`HARDWARE_ID`),
  KEY `TAG` (`TAG`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `accountinfo_config`
--

CREATE TABLE `accountinfo_config` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME_ACCOUNTINFO` varchar(255) DEFAULT NULL,
  `TYPE` int(11) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `ID_TAB` int(11) DEFAULT NULL,
  `COMMENT` varchar(255) DEFAULT NULL,
  `SHOW_ORDER` int(11) NOT NULL,
  `ACCOUNT_TYPE` varchar(255) DEFAULT NULL,
  `DEFAULT_VALUE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `accountinfo_config`
--

LOCK TABLES `accountinfo_config` WRITE;
INSERT INTO `accountinfo_config` VALUES (1,'TAG',0,'TAG',1,'TAG',1,'COMPUTERS',NULL),(2,'TAG',0,'TAG',1,'TAG',1,'SNMP',NULL);
UNLOCK TABLES;

--
-- Table structure for table `assets_categories`
--

CREATE TABLE `assets_categories` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CATEGORY_NAME` varchar(255) NOT NULL,
  `CATEGORY_DESC` varchar(255) NOT NULL,
  `SQL_QUERY` text NOT NULL,
  `SQL_ARGS` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `batteries`
--

CREATE TABLE `batteries` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `LOCATION` varchar(255) DEFAULT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `MANUFACTUREDATE` varchar(10) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `CHEMISTRY` varchar(20) DEFAULT NULL,
  `DESIGNCAPACITY` varchar(10) DEFAULT NULL,
  `DESIGNVOLTAGE` varchar(20) DEFAULT NULL,
  `SBDSVERSION` varchar(255) DEFAULT NULL,
  `MAXERROR` int(10) DEFAULT NULL,
  `OEMSPECIFIC` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`),
  KEY `NAME` (`NAME`),
  KEY `MANUFACTURER` (`MANUFACTURER`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `bios`
--

CREATE TABLE `bios` (
  `HARDWARE_ID` int(11) NOT NULL,
  `SMANUFACTURER` varchar(255) DEFAULT NULL,
  `SMODEL` varchar(255) DEFAULT NULL,
  `SSN` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `BMANUFACTURER` varchar(255) DEFAULT NULL,
  `BVERSION` varchar(255) DEFAULT NULL,
  `BDATE` varchar(255) DEFAULT NULL,
  `ASSETTAG` varchar(255) DEFAULT NULL,
  `MMANUFACTURER` varchar(255) DEFAULT NULL,
  `MMODEL` varchar(255) DEFAULT NULL,
  `MSN` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`HARDWARE_ID`),
  KEY `SSN` (`SSN`),
  KEY `ASSETTAG` (`ASSETTAG`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `blacklist_macaddresses`
--

CREATE TABLE `blacklist_macaddresses` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MACADDRESS` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `MACADDRESS` (`MACADDRESS`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `blacklist_macaddresses`
--

LOCK TABLES `blacklist_macaddresses` WRITE;
INSERT INTO `blacklist_macaddresses` VALUES (1,'00:00:00:00:00:00'),(2,'FF:FF:FF:FF:FF:FF'),(3,'44:45:53:54:00:00'),(4,'44:45:53:54:00:01'),(5,'00:01:02:7D:9B:1C'),(6,'00:08:A1:46:06:35'),(7,'00:08:A1:66:E2:1A'),(8,'00:09:DD:10:37:68'),(9,'00:0F:EA:9A:E2:F0'),(10,'00:10:5A:72:71:F3'),(11,'00:11:11:85:08:8B'),(12,'10:11:11:11:11:11'),(13,'44:45:53:54:61:6F'),(14,'');
UNLOCK TABLES;

--
-- Table structure for table `blacklist_serials`
--

CREATE TABLE `blacklist_serials` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SERIAL` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `SERIAL` (`SERIAL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `blacklist_serials`
--

LOCK TABLES `blacklist_serials` WRITE;
INSERT INTO `blacklist_serials` VALUES (1,'N/A'),(2,'(null string)'),(3,'INVALID'),(4,'SYS-1234567890'),(5,'SYS-9876543210'),(6,'SN-12345'),(7,'SN-1234567890'),(8,'1111111111'),(9,'1111111'),(10,'1'),(11,'0123456789'),(12,'12345'),(13,'123456'),(14,'1234567'),(15,'12345678'),(16,'123456789'),(17,'1234567890'),(18,'123456789000'),(19,'12345678901234567'),(20,'0000000000'),(21,'000000000'),(22,'00000000'),(23,'0000000'),(24,'000000'),(25,'NNNNNNN'),(26,'xxxxxxxxxxx'),(27,'EVAL'),(28,'IATPASS'),(29,'none'),(30,'To Be Filled By O.E.M.'),(31,'Tulip Computers'),(32,'Serial Number xxxxxx'),(33,'SN-123456fvgv3i0b8o5n6n7k'),(34,'');
UNLOCK TABLES;

--
-- Table structure for table `blacklist_subnet`
--

CREATE TABLE `blacklist_subnet` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SUBNET` varchar(20) NOT NULL DEFAULT '',
  `MASK` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `SUBNET` (`SUBNET`,`MASK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `NAME` varchar(50) NOT NULL,
  `IVALUE` int(11) DEFAULT NULL,
  `TVALUE` varchar(255) DEFAULT NULL,
  `COMMENTS` text,
  PRIMARY KEY (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
INSERT INTO `config` VALUES ('AUTO_DUPLICATE_LVL',7,'','Duplicates bitmap'),('DEPLOY',1,'','Activates or not the automatic deployment option'),('DOWNLOAD',0,'','Activate softwares auto deployment feature'),('DOWNLOAD_CYCLE_LATENCY',60,'','Time between two cycles (seconds)'),('DOWNLOAD_FRAG_LATENCY',10,'','Time between two downloads (seconds)'),('DOWNLOAD_GROUPS_TRACE_EVENTS',1,'','Specify if you want to track packages affected to a group on computer\'s level'),('DOWNLOAD_PACK_DIR',0,'/var/lib/ocsinventory-reports','Directory for download files'),('DOWNLOAD_PERIOD_LATENCY',1,'','Time between two periods (seconds)'),('DOWNLOAD_PERIOD_LENGTH',10,'','Number of cycles in a period'),('DOWNLOAD_REDISTRIB',0,'','Use redistribution servers'),('DOWNLOAD_SERVER_DOCROOT',0,'d:\\tele_ocs','Server directory used for group of server'),('DOWNLOAD_SERVER_URI',0,'$IP$/local','Server url used for group of server'),('DOWNLOAD_TIMEOUT',30,'','Validity of a package (in days)'),('ENABLE_GROUPS',1,'','Enable the computer\'s groups feature'),('FREQUENCY',0,'','Specify the frequency (days) of inventories. (0: inventory at each login. -1: no inventory)'),('GENERATE_OCS_FILES',0,'','Use with ocsinventory-injector, enable the multi entities feature'),('GROUPS_CACHE_OFFSET',43200,'','Random number computed in the defined range. Designed to avoid computing many groups in the same process'),('GROUPS_CACHE_REVALIDATE',43200,'','Specify the validity of computer\'s groups (default: compute it once a day - see offset)'),('GUI_REPORT_AGIN_MACH',30,'','Filter on lastdate for console page'),('GUI_REPORT_DD_MAX',4000,'','Filter on Hard Drive for console page'),('GUI_REPORT_DD_MINI',500,'','Filter on PROCESSOR for console page'),('GUI_REPORT_NOT_VIEW',3,'','Filter on DAY for console page'),('GUI_REPORT_PROC_MAX',3000,'','Filter on PROCESSOR for console page'),('GUI_REPORT_PROC_MINI',1000,'','Filter on Hard Drive for console page'),('GUI_REPORT_RAM_MAX',512,'','Filter on RAM for console page'),('GUI_REPORT_RAM_MINI',128,'','Filter on RAM for console page'),('INVENTORY_CACHE_ENABLED',1,'','Enable some stuff to improve DB queries, especially for GUI multicriteria searching system'),('INVENTORY_CACHE_REVALIDATE',7,'','the engine will clean the inventory cache structures'),('INVENTORY_DIFF',1,'','Activate/Deactivate inventory incremental writing'),('INVENTORY_FILTER_ENABLED',0,'','Enable core filter system to modify some things \"on the fly\"'),('INVENTORY_FILTER_FLOOD_IP',0,'','Enable inventory flooding filter. A dedicated ipaddress ia allowed to send a new computer only once in this period'),('INVENTORY_FILTER_FLOOD_IP_CACHE_TIME',300,'','Period definition for INVENTORY_FILTER_FLOOD_IP'),('INVENTORY_FILTER_ON',0,'','Enable inventory filter stack'),('INVENTORY_ON_STARTUP',1,'1','Launch inventory on agent service statup'),('INVENTORY_TRANSACTION',1,'','Enable/disable db commit at each inventory section'),('INVENTORY_WRITE_DIFF',0,'','Configure engine to make a differential update of inventory sections (row level). Lower DB backend load, higher frontend load'),('IPDISCOVER',2,'','Max number of computers per gateway retrieving IP on the network'),('IPDISCOVER_BETTER_THRESHOLD',1,'','Specify the minimal difference to replace an ipdiscover agent'),('IPDISCOVER_IPD_DIR',0,'/var/lib/ocsinventory-reports','Directory for Ipdiscover files'),('IPDISCOVER_LATENCY',100,'','Default latency between two arp requests'),('IPDISCOVER_MAX_ALIVE',7,'','Max number of days before an Ip Discover computer is replaced'),('IPDISCOVER_NO_POSTPONE',0,'','Disable the time before a first election (not recommended)'),('IPDISCOVER_USE_GROUPS',1,'','Enable groups for ipdiscover (for example, you might want to prevent some groups'),('LOCK_REUSE_TIME',600,'','Validity of a computer\'s lock'),('LOGLEVEL',0,'','ocs engine loglevel'),('LOG_DIR',0,'/var/lib/ocsinventory-reports','Directory for logs files'),('LOG_SCRIPT',0,'/var/lib/ocsinventory-reports','Directory for logs scripts files'),('OCS_FILES_FORMAT',0,'OCS','Generate either compressed file or clear XML text'),('OCS_FILES_OVERWRITE',0,'','Specify if you want to keep trace of all inventory between to synchronisation with the higher level server'),('OCS_FILES_PATH',0,'/tmp','Path to ocs files directory (must be writeable)'),('OCS_SERVER_ADDRESS',0,'127.0.0.1','Ocs serveur ip for plugin webservice'),('PASSWORD_VERSION',1,'PASSWORD_BCRYPT','Password encryption version'),('PROLOG_FILTER_ON',0,'','Enable prolog filter stack'),('PROLOG_FREQ',24,'','Specify the frequency (hours) of prolog, on agents'),('REGISTRY',0,'','Activates or not the registry query function'),('SESSION_VALIDITY_TIME',600,'','Validity of a session (prolog=>postinventory)'),('SNMP_INVENTORY_DIFF',1,NULL,'Configure engine to update snmp inventory regarding to snmp_laststate table (lower DB backend load)'),('TAB_ACCOUNTAG_1',1,'TAG','Default TAB on computers accountinfo'),('TAB_ACCOUNTSNMP_1',1,'TAG','Default TAB on snmp accountinfo'),('TRACE_DELETED',0,'','Trace deleted/duplicated computers (Activated by GLPI)'),('UPDATE',0,'','Activates or not the update feature'),('WARN_UPDATE',1,'1','Warn user if an update is available'),('WOL_PORT',0,'7,9','Wol ports');

INSERT INTO `config` VALUES ('GUI_VERSION',0,'7017','Version of the installed GUI and database');
UNLOCK TABLES;

--
-- Table structure for table `conntrack`
--

CREATE TABLE `conntrack` (
  `IP` varchar(255) NOT NULL DEFAULT '',
  `TIMESTAMP` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`IP`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

--
-- Table structure for table `controllers`
--

CREATE TABLE `controllers` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `CAPTION` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `VERSION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `cpus`
--

CREATE TABLE `cpus` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `SPEED` varchar(255) DEFAULT NULL,
  `CORES` int(11) DEFAULT NULL,
  `L2CACHESIZE` varchar(255) DEFAULT NULL,
  `CPUARCH` varchar(255) DEFAULT NULL,
  `DATA_WIDTH` int(11) DEFAULT NULL,
  `CURRENT_ADDRESS_WIDTH` int(11) DEFAULT NULL,
  `LOGICAL_CPUS` int(11) DEFAULT NULL,
  `VOLTAGE` varchar(255) DEFAULT NULL,
  `CURRENT_SPEED` varchar(255) DEFAULT NULL,
  `SOCKET` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `deleted_equiv`
--

CREATE TABLE `deleted_equiv` (
  `DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `DELETED` varchar(255) NOT NULL,
  `EQUIVALENT` varchar(255) DEFAULT NULL,
  KEY `DELETED` (`DELETED`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `deploy`
--

CREATE TABLE `deploy` (
  `NAME` varchar(255) NOT NULL,
  `CONTENT` longblob NOT NULL,
  PRIMARY KEY (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `HARDWARE_ID` int(11) NOT NULL,
  `NAME` varchar(50) NOT NULL,
  `IVALUE` int(11) DEFAULT NULL,
  `TVALUE` varchar(255) DEFAULT NULL,
  `COMMENTS` text,
  KEY `HARDWARE_ID` (`HARDWARE_ID`),
  KEY `TVALUE` (`TVALUE`),
  KEY `IVALUE` (`IVALUE`),
  KEY `NAME` (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `devicetype`
--

CREATE TABLE `devicetype` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `dico_ignored`
--

CREATE TABLE `dico_ignored` (
  `EXTRACTED` varchar(255) NOT NULL,
  PRIMARY KEY (`EXTRACTED`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `dico_soft`
--

CREATE TABLE `dico_soft` (
  `EXTRACTED` varchar(255) NOT NULL,
  `FORMATTED` varchar(255) NOT NULL,
  PRIMARY KEY (`EXTRACTED`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `download_affect_rules`
--

CREATE TABLE `download_affect_rules` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RULE` int(11) NOT NULL,
  `PRIORITY` int(11) NOT NULL,
  `CFIELD` varchar(20) NOT NULL,
  `OP` varchar(20) NOT NULL,
  `COMPTO` varchar(20) NOT NULL,
  `SERV_VALUE` varchar(20) DEFAULT NULL,
  `RULE_NAME` varchar(200) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `download_available`
--

CREATE TABLE `download_available` (
  `FILEID` varchar(255) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `PRIORITY` int(11) NOT NULL,
  `FRAGMENTS` int(11) NOT NULL,
  `SIZE` int(11) NOT NULL,
  `OSNAME` varchar(255) NOT NULL,
  `COMMENT` text,
  `ID_WK` int(11) DEFAULT NULL,
  `DELETED` int(1) DEFAULT '0',
  PRIMARY KEY (`FILEID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `download_enable`
--

CREATE TABLE `download_enable` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FILEID` varchar(255) NOT NULL,
  `INFO_LOC` varchar(255) NOT NULL,
  `PACK_LOC` varchar(255) NOT NULL,
  `CERT_PATH` varchar(255) DEFAULT NULL,
  `CERT_FILE` varchar(255) DEFAULT NULL,
  `SERVER_ID` int(11) DEFAULT NULL,
  `GROUP_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `FILEID` (`FILEID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `download_history`
--

CREATE TABLE `download_history` (
  `HARDWARE_ID` int(11) NOT NULL,
  `PKG_ID` int(11) NOT NULL DEFAULT '0',
  `PKG_NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`HARDWARE_ID`,`PKG_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `download_servers`
--

CREATE TABLE `download_servers` (
  `HARDWARE_ID` int(11) NOT NULL,
  `URL` varchar(250) NOT NULL,
  `ADD_PORT` int(11) NOT NULL,
  `ADD_REP` varchar(250) NOT NULL,
  `GROUP_ID` int(11) NOT NULL,
  PRIMARY KEY (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `downloadwk_conf_values`
--

CREATE TABLE `downloadwk_conf_values` (
  `FIELD` int(11) DEFAULT NULL,
  `VALUE` varchar(100) DEFAULT NULL,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DEFAULT_FIELD` int(1) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `downloadwk_fields`
--

CREATE TABLE `downloadwk_fields` (
  `TAB` varchar(100) DEFAULT NULL,
  `FIELD` varchar(100) DEFAULT NULL,
  `TYPE` int(11) DEFAULT NULL,
  `LBL` varchar(100) DEFAULT NULL,
  `MUST_COMPLETED` int(11) DEFAULT NULL,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `VALUE` varchar(255) DEFAULT NULL,
  `DEFAULT_FIELD` int(1) DEFAULT NULL,
  `RESTRICTED` int(1) DEFAULT NULL,
  `LINK_STATUS` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `downloadwk_fields`
--

LOCK TABLES `downloadwk_fields` WRITE;
INSERT INTO `downloadwk_fields` VALUES ('1','USER',3,'1038',1,1,'loggeduser',1,0,0),('2','NAME_TELEDEPLOY',0,'1037',1,2,'',1,0,0),('2','INFO_PACK',0,'53',1,3,'',1,0,0),('3','PRIORITY',2,'1039',1,4,'',1,0,0),('3','NOTIF_USER',2,'1040',1,5,'',1,0,0),('3','REPORT_USER',2,'1041',1,6,'',1,0,0),('3','REBOOT',2,'1042',1,7,'',1,0,0),('4','VALID_INSTALL',6,'1043',1,8,'',1,0,0),('4','STATUS',2,'1046',0,9,'2',1,1,0),('5','LIST_HISTO',10,'1052',0,10,'select AUTHOR,DATE,ACTION from downloadwk_history where id_dde=%s$$$$OLD_MODIF',1,0,0);
UNLOCK TABLES;

--
-- Table structure for table `downloadwk_history`
--

CREATE TABLE `downloadwk_history` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_DDE` int(11) DEFAULT NULL,
  `AUTHOR` varchar(255) DEFAULT NULL,
  `DATE` date DEFAULT NULL,
  `ACTION` longtext,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `downloadwk_pack`
--

CREATE TABLE `downloadwk_pack` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LOGIN_USER` varchar(255) DEFAULT NULL,
  `GROUP_USER` varchar(255) DEFAULT NULL,
  `Q_DATE` int(11) DEFAULT NULL,
  `fields_1` varchar(255) DEFAULT NULL,
  `fields_2` varchar(255) DEFAULT NULL,
  `fields_3` varchar(255) DEFAULT NULL,
  `fields_4` varchar(255) DEFAULT NULL,
  `fields_5` varchar(255) DEFAULT NULL,
  `fields_6` varchar(255) DEFAULT NULL,
  `fields_7` varchar(255) DEFAULT NULL,
  `fields_8` varchar(255) DEFAULT NULL,
  `fields_9` varchar(255) DEFAULT NULL,
  `fields_10` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `downloadwk_statut_request`
--

CREATE TABLE `downloadwk_statut_request` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(20) DEFAULT NULL,
  `LBL` varchar(255) DEFAULT NULL,
  `ACTIF` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `downloadwk_statut_request`
--

LOCK TABLES `downloadwk_statut_request` WRITE;
INSERT INTO `downloadwk_statut_request` VALUES (1,'NIV0','DELETE',0),(2,'NIV1','WAITING FOR INCLUSION',0),(3,'NIV2','ACKNOWLEDGEMENT',0),(4,'NIV3','REFUSAL',0),(5,'NIV4','NEED TO CHANGE',0),(6,'NIV5','CREATE PACKAGE',0),(7,'NIV6','LOCAL TEST',0),(8,'NIV7','PERIMETER LIMITED DEPLOYMENT',0),(9,'NIV8','DURING DEPLOYMENT',0);
UNLOCK TABLES;

--
-- Table structure for table `downloadwk_tab_values`
--

CREATE TABLE `downloadwk_tab_values` (
  `FIELD` varchar(100) DEFAULT NULL,
  `VALUE` varchar(100) DEFAULT NULL,
  `LBL` varchar(100) DEFAULT NULL,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DEFAULT_FIELD` int(1) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `downloadwk_tab_values`
--

LOCK TABLES `downloadwk_tab_values` WRITE;
INSERT INTO `downloadwk_tab_values` VALUES ('TAB','INFO_DEM','1033',1,1),('TAB','INFO_PAQUET','1034',2,1),('TAB','INFO_CONF','1035',3,1),('TAB','INFO_VALID','1036',4,1),('TAB','INFO_HISTO','1052',5,1);
UNLOCK TABLES;

--
-- Table structure for table `drives`
--

CREATE TABLE `drives` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `LETTER` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `FILESYSTEM` varchar(255) DEFAULT NULL,
  `TOTAL` int(11) DEFAULT NULL,
  `FREE` int(11) DEFAULT NULL,
  `NUMFILES` int(11) DEFAULT NULL,
  `VOLUMN` varchar(255) DEFAULT NULL,
  `CREATEDATE` date DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `engine_mutex`
--

CREATE TABLE `engine_mutex` (
  `NAME` varchar(255) NOT NULL DEFAULT '',
  `PID` int(11) DEFAULT NULL,
  `TAG` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`NAME`,`TAG`),
  KEY `PID` (`PID`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

--
-- Table structure for table `engine_persistent`
--

CREATE TABLE `engine_persistent` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL DEFAULT '',
  `IVALUE` int(11) DEFAULT NULL,
  `TVALUE` varchar(255) DEFAULT NULL,
  UNIQUE KEY `NAME` (`NAME`),
  KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `NAME` varchar(100) NOT NULL,
  `VERSION` varchar(50) NOT NULL,
  `OS` varchar(70) NOT NULL,
  `CONTENT` longblob NOT NULL,
  PRIMARY KEY (`NAME`,`OS`,`VERSION`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `HARDWARE_ID` int(11) NOT NULL DEFAULT '0',
  `REQUEST` longtext,
  `CREATE_TIME` int(11) DEFAULT '0',
  `REVALIDATE_FROM` int(11) DEFAULT '0',
  `XMLDEF` longtext,
  PRIMARY KEY (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `groups_cache`
--

CREATE TABLE `groups_cache` (
  `HARDWARE_ID` int(11) NOT NULL DEFAULT '0',
  `GROUP_ID` int(11) NOT NULL DEFAULT '0',
  `STATIC` int(11) DEFAULT '0',
  PRIMARY KEY (`HARDWARE_ID`,`GROUP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `hardware`
--

CREATE TABLE `hardware` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DEVICEID` varchar(255) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `WORKGROUP` varchar(255) DEFAULT NULL,
  `USERDOMAIN` varchar(255) DEFAULT NULL,
  `OSNAME` varchar(255) DEFAULT NULL,
  `OSVERSION` varchar(255) DEFAULT NULL,
  `OSCOMMENTS` varchar(255) DEFAULT NULL,
  `PROCESSORT` varchar(255) DEFAULT NULL,
  `PROCESSORS` int(11) DEFAULT '0',
  `PROCESSORN` smallint(6) DEFAULT NULL,
  `MEMORY` int(11) DEFAULT NULL,
  `SWAP` int(11) DEFAULT NULL,
  `IPADDR` varchar(255) DEFAULT NULL,
  `DNS` varchar(255) DEFAULT NULL,
  `DEFAULTGATEWAY` varchar(255) DEFAULT NULL,
  `ETIME` datetime DEFAULT NULL,
  `LASTDATE` datetime DEFAULT NULL,
  `LASTCOME` datetime DEFAULT NULL,
  `QUALITY` decimal(7,4) DEFAULT NULL,
  `FIDELITY` bigint(20) DEFAULT '1',
  `USERID` varchar(255) DEFAULT NULL,
  `TYPE` int(11) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `WINCOMPANY` varchar(255) DEFAULT NULL,
  `WINOWNER` varchar(255) DEFAULT NULL,
  `WINPRODID` varchar(255) DEFAULT NULL,
  `WINPRODKEY` varchar(255) DEFAULT NULL,
  `USERAGENT` varchar(50) DEFAULT NULL,
  `CHECKSUM` bigint(20) unsigned DEFAULT '262143',
  `SSTATE` int(11) DEFAULT '0',
  `IPSRC` varchar(255) DEFAULT NULL,
  `UUID` varchar(255) DEFAULT NULL,
  `ARCH` varchar(10) DEFAULT NULL,
  `CATEGORY_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `DEVICEID` (`DEVICEID`),
  KEY `NAME` (`NAME`),
  KEY `CHECKSUM` (`CHECKSUM`),
  KEY `USERID` (`USERID`),
  KEY `WORKGROUP` (`WORKGROUP`),
  KEY `OSNAME` (`OSNAME`),
  KEY `MEMORY` (`MEMORY`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `hardware_osname_cache`
--

CREATE TABLE `hardware_osname_cache` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `OSNAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `OSNAME` (`OSNAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `inputs`
--

CREATE TABLE `inputs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `CAPTION` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `INTERFACE` varchar(255) DEFAULT NULL,
  `POINTTYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `itmgmt_comments`
--

CREATE TABLE `itmgmt_comments` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `COMMENTS` longtext,
  `USER_INSERT` varchar(100) DEFAULT NULL,
  `DATE_INSERT` date DEFAULT NULL,
  `ACTION` varchar(255) DEFAULT NULL,
  `VISIBLE` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `javainfo`
--

CREATE TABLE `javainfo` (
  `HARDWARE_ID` int(11) NOT NULL,
  `JAVANAME` varchar(255) DEFAULT 'NONAME',
  `JAVAPATHLEVEL` int(11) DEFAULT '0',
  `JAVACOUNTRY` varchar(255) DEFAULT NULL,
  `JAVACLASSPATH` varchar(255) DEFAULT NULL,
  `JAVAHOME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `journallog`
--

CREATE TABLE `journallog` (
  `HARDWARE_ID` int(11) NOT NULL,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `JOURNALLOG` longtext,
  `LISTENERNAME` varchar(255) DEFAULT 'NONAME',
  `DATE` varchar(255) DEFAULT NULL,
  `STATUS` int(11) DEFAULT '0',
  `ERRORCODE` int(11) DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `NAME` varchar(60) NOT NULL,
  `IMG` blob,
  `JSON_VALUE` longtext,
  PRIMARY KEY (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `locks`
--

CREATE TABLE `locks` (
  `HARDWARE_ID` int(11) NOT NULL,
  `ID` int(11) DEFAULT NULL,
  `SINCE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`HARDWARE_ID`),
  KEY `SINCE` (`SINCE`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

--
-- Table structure for table `memories`
--

CREATE TABLE `memories` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `CAPTION` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `CAPACITY` varchar(255) DEFAULT NULL,
  `PURPOSE` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `SPEED` varchar(255) DEFAULT NULL,
  `NUMSLOTS` smallint(6) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `modems`
--

CREATE TABLE `modems` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `MODEL` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `monitors`
--

CREATE TABLE `monitors` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `CAPTION` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `SERIAL` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `netmap`
--

CREATE TABLE `netmap` (
  `IP` varchar(15) NOT NULL,
  `MAC` varchar(17) NOT NULL,
  `MASK` varchar(15) NOT NULL,
  `NETID` varchar(15) NOT NULL,
  `DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`MAC`),
  KEY `IP` (`IP`),
  KEY `NETID` (`NETID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `network_devices`
--

CREATE TABLE `network_devices` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `MACADDR` varchar(255) DEFAULT NULL,
  `USER` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `MACADDR` (`MACADDR`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `networks`
--

CREATE TABLE `networks` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `TYPEMIB` varchar(255) DEFAULT NULL,
  `SPEED` varchar(255) DEFAULT NULL,
  `MTU` varchar(255) DEFAULT NULL,
  `MACADDR` varchar(255) DEFAULT NULL,
  `STATUS` varchar(255) DEFAULT NULL,
  `IPADDRESS` varchar(255) DEFAULT NULL,
  `IPMASK` varchar(255) DEFAULT NULL,
  `IPGATEWAY` varchar(255) DEFAULT NULL,
  `IPSUBNET` varchar(255) DEFAULT NULL,
  `IPDHCP` varchar(255) DEFAULT NULL,
  `VIRTUALDEV` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `MACADDR` (`MACADDR`),
  KEY `IPADDRESS` (`IPADDRESS`),
  KEY `IPGATEWAY` (`IPGATEWAY`),
  KEY `IPSUBNET` (`IPSUBNET`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TYPE` varchar(255) NOT NULL,
  `FILE` varchar(255) DEFAULT NULL,
  `SUBJECT` varchar(255) DEFAULT NULL,
  `ALTBODY` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `notification`
--

LOCK TABLES `notification` WRITE;
INSERT INTO `notification` VALUES (1,'SELECTED','DEFAULT',NULL,NULL),(2,'DEFAULT','templates/OCS_template.html','Notification OCSInventory','Default report inventory'),(3,'PERSO',NULL,NULL,NULL);
UNLOCK TABLES;

--
-- Table structure for table `notification_config`
--

CREATE TABLE `notification_config` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `TVALUE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `operators`
--

CREATE TABLE `operators` (
  `ID` varchar(255) NOT NULL DEFAULT '',
  `FIRSTNAME` varchar(255) DEFAULT NULL,
  `LASTNAME` varchar(255) DEFAULT NULL,
  `PASSWD` varchar(255) DEFAULT NULL,
  `ACCESSLVL` int(11) DEFAULT NULL,
  `COMMENTS` text,
  `NEW_ACCESSLVL` varchar(255) DEFAULT NULL,
  `EMAIL` varchar(255) DEFAULT NULL,
  `USER_GROUP` varchar(255) DEFAULT NULL,
  `PASSWORD_VERSION` int(11) DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `operators`
--

LOCK TABLES `operators` WRITE;
INSERT INTO `operators` VALUES ('admin','admin','admin','21232f297a57a5a743894a0e4a801fc3',1,'Default administrator account','sadmin',NULL,NULL,0);
UNLOCK TABLES;

--
-- Table structure for table `plugins`
--

CREATE TABLE `plugins` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `version` double NOT NULL,
  `licence` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `author` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `verminocs` double NOT NULL,
  `activated` tinyint(1) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `ports`
--

CREATE TABLE `ports` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `CAPTION` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `printers`
--

CREATE TABLE `printers` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `DRIVER` varchar(255) DEFAULT NULL,
  `PORT` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `SERVERNAME` varchar(255) DEFAULT NULL,
  `SHARENAME` varchar(255) DEFAULT NULL,
  `RESOLUTION` varchar(50) DEFAULT NULL,
  `COMMENT` varchar(255) DEFAULT NULL,
  `SHARED` int(11) DEFAULT NULL,
  `NETWORK` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `prolog_conntrack`
--

CREATE TABLE `prolog_conntrack` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DEVICEID` varchar(255) DEFAULT NULL,
  `TIMESTAMP` int(11) DEFAULT NULL,
  `PID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `DEVICEID` (`DEVICEID`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

--
-- Table structure for table `regconfig`
--

CREATE TABLE `regconfig` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) DEFAULT NULL,
  `REGTREE` int(11) DEFAULT NULL,
  `REGKEY` text,
  `REGVALUE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `NAME` (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `registry`
--

CREATE TABLE `registry` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `REGVALUE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `NAME` (`NAME`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `registry_name_cache`
--

CREATE TABLE `registry_name_cache` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `NAME` (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `registry_regvalue_cache`
--

CREATE TABLE `registry_regvalue_cache` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `REGVALUE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `REGVALUE` (`REGVALUE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `repository`
--

CREATE TABLE `repository` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `BASEURL` varchar(255) DEFAULT NULL,
  `EXCLUDE` varchar(255) DEFAULT NULL,
  `EXCLUDED` varchar(255) DEFAULT NULL,
  `EXPIRE` varchar(255) DEFAULT NULL,
  `FILENAME` varchar(255) DEFAULT NULL,
  `MIRRORS` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `PKGS` varchar(255) DEFAULT NULL,
  `REVISION` varchar(255) DEFAULT NULL,
  `SIZE` varchar(255) DEFAULT NULL,
  `TAG` varchar(255) DEFAULT NULL,
  `UPDATED` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `saas`
--

CREATE TABLE `saas` (
  `SAAS_EXP_ID` int(6) NOT NULL,
  `HARDWARE_ID` int(11) NOT NULL,
  `ENTRY` varchar(255) NOT NULL,
  `DATA` varchar(255) NOT NULL,
  `TTL` int(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `saas_exp`
--

CREATE TABLE `saas_exp` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `DNS_EXP` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `sim`
--

CREATE TABLE `sim` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `OPERATOR` varchar(255) DEFAULT NULL,
  `OPNAME` varchar(255) DEFAULT NULL,
  `COUNTRY` varchar(255) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `DEVICEID` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `slots`
--

CREATE TABLE `slots` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `DESIGNATION` varchar(255) DEFAULT NULL,
  `PURPOSE` varchar(255) DEFAULT NULL,
  `STATUS` varchar(255) DEFAULT NULL,
  `PSHARE` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp`
--

CREATE TABLE `snmp` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IPADDR` varchar(255) DEFAULT NULL,
  `MACADDR` varchar(255) NOT NULL,
  `SNMPDEVICEID` varchar(255) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `CONTACT` varchar(255) DEFAULT NULL,
  `LOCATION` varchar(255) DEFAULT NULL,
  `UPTIME` varchar(255) DEFAULT NULL,
  `DOMAIN` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `LASTDATE` datetime DEFAULT NULL,
  `CHECKSUM` bigint(20) unsigned DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_accountinfo`
--

CREATE TABLE `snmp_accountinfo` (
  `SNMP_ID` int(11) NOT NULL,
  `TAG` varchar(255) DEFAULT 'NA',
  PRIMARY KEY (`SNMP_ID`),
  KEY `TAG` (`TAG`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_blades`
--

CREATE TABLE `snmp_blades` (
  `SNMP_ID` int(11) NOT NULL AUTO_INCREMENT,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `SYSTEM` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_cards`
--

CREATE TABLE `snmp_cards` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `REFERENCE` varchar(255) DEFAULT NULL,
  `FIRMWARE` varchar(255) DEFAULT NULL,
  `SOFTWARE` varchar(255) DEFAULT NULL,
  `REVISION` varchar(255) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_cartridges`
--

CREATE TABLE `snmp_cartridges` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `LEVEL` int(11) DEFAULT NULL,
  `MAXCAPACITY` int(11) DEFAULT NULL,
  `COLOR` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_communities`
--

CREATE TABLE `snmp_communities` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `VERSION` varchar(5) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `USERNAME` varchar(255) DEFAULT NULL,
  `AUTHKEY` varchar(255) DEFAULT NULL,
  `AUTHPASSWD` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_computers`
--

CREATE TABLE `snmp_computers` (
  `SNMP_ID` int(11) NOT NULL AUTO_INCREMENT,
  `SYSTEM` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_cpus`
--

CREATE TABLE `snmp_cpus` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `SPEED` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_drives`
--

CREATE TABLE `snmp_drives` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `LETTER` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `FILESYSTEM` varchar(255) DEFAULT NULL,
  `TOTAL` int(11) DEFAULT NULL,
  `FREE` int(11) DEFAULT NULL,
  `NUMFILES` int(11) DEFAULT NULL,
  `VOLUMN` varchar(255) DEFAULT NULL,
  `LABEL` varchar(255) DEFAULT NULL,
  `SERIAL` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_fans`
--

CREATE TABLE `snmp_fans` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `REFERENCE` varchar(255) DEFAULT NULL,
  `REVISION` varchar(255) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_firewalls`
--

CREATE TABLE `snmp_firewalls` (
  `SNMP_ID` int(11) NOT NULL AUTO_INCREMENT,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `SYSTEM` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_inputs`
--

CREATE TABLE `snmp_inputs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_laststate`
--

CREATE TABLE `snmp_laststate` (
  `SNMP_ID` int(11) NOT NULL,
  `COMMON` varchar(255) DEFAULT NULL,
  `PRINTERS` varchar(255) DEFAULT NULL,
  `TRAYS` varchar(255) DEFAULT NULL,
  `CARTRIDGES` varchar(255) DEFAULT NULL,
  `NETWORKS` varchar(255) DEFAULT NULL,
  `SWITCHS` varchar(255) DEFAULT NULL,
  `BLADES` varchar(255) DEFAULT NULL,
  `STORAGES` varchar(255) DEFAULT NULL,
  `DRIVES` varchar(255) DEFAULT NULL,
  `POWERSUPPLIES` varchar(255) DEFAULT NULL,
  `FANS` varchar(255) DEFAULT NULL,
  `SWITCHINFOS` varchar(255) DEFAULT NULL,
  `LOADBALANCERS` varchar(255) DEFAULT NULL,
  `CARDS` varchar(255) DEFAULT NULL,
  `COMPUTERS` varchar(255) DEFAULT NULL,
  `SOFTWARES` varchar(255) DEFAULT NULL,
  `MEMORIES` varchar(255) DEFAULT NULL,
  `CPUS` varchar(255) DEFAULT NULL,
  `INPUTS` varchar(255) DEFAULT NULL,
  `PORTS` varchar(255) DEFAULT NULL,
  `SOUNDS` varchar(255) DEFAULT NULL,
  `VIDEOS` varchar(255) DEFAULT NULL,
  `MODEMS` varchar(255) DEFAULT NULL,
  `LOCALPRINTERS` varchar(255) DEFAULT NULL,
  `VIRTUALMACHINES` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_loadbalancers`
--

CREATE TABLE `snmp_loadbalancers` (
  `SNMP_ID` int(11) NOT NULL AUTO_INCREMENT,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `SYSTEM` varchar(255) DEFAULT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_localprinters`
--

CREATE TABLE `snmp_localprinters` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_memories`
--

CREATE TABLE `snmp_memories` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `CAPACITY` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_mibs`
--

CREATE TABLE `snmp_mibs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `VENDOR` varchar(255) DEFAULT NULL,
  `URL` varchar(255) DEFAULT NULL,
  `CHECKSUM` varchar(255) DEFAULT NULL,
  `VERSION` varchar(5) DEFAULT NULL,
  `PARSER` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_modems`
--

CREATE TABLE `snmp_modems` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_networks`
--

CREATE TABLE `snmp_networks` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `MACADDR` varchar(255) DEFAULT NULL,
  `DEVICEMACADDR` varchar(255) DEFAULT NULL,
  `SLOT` varchar(255) DEFAULT NULL,
  `STATUS` varchar(255) DEFAULT NULL,
  `SPEED` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `DEVICEADDRESS` varchar(255) DEFAULT NULL,
  `DEVICENAME` varchar(255) DEFAULT NULL,
  `DEVICEPORT` varchar(255) DEFAULT NULL,
  `DEVICETYPE` varchar(255) DEFAULT NULL,
  `TYPEMIB` varchar(255) DEFAULT NULL,
  `IPADDR` varchar(255) DEFAULT NULL,
  `IPMASK` varchar(255) DEFAULT NULL,
  `IPGATEWAY` varchar(255) DEFAULT NULL,
  `IPSUBNET` varchar(255) DEFAULT NULL,
  `IPDHCP` varchar(255) DEFAULT NULL,
  `DRIVER` varchar(255) DEFAULT NULL,
  `VIRTUALDEV` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_ports`
--

CREATE TABLE `snmp_ports` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_powersupplies`
--

CREATE TABLE `snmp_powersupplies` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `REFERENCE` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `REVISION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_printers`
--

CREATE TABLE `snmp_printers` (
  `SNMP_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `COUNTER` varchar(255) DEFAULT NULL,
  `STATUS` varchar(255) DEFAULT NULL,
  `ERRORSTATE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_softwares`
--

CREATE TABLE `snmp_softwares` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `INSTALLDATE` varchar(255) DEFAULT NULL,
  `COMMENTS` text,
  `VERSION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_sounds`
--

CREATE TABLE `snmp_sounds` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_storages`
--

CREATE TABLE `snmp_storages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `MODEL` varchar(255) DEFAULT NULL,
  `DISKSIZE` int(11) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `FIRMWARE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_switchinfos`
--

CREATE TABLE `snmp_switchinfos` (
  `SNMP_ID` int(11) NOT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_switchs`
--

CREATE TABLE `snmp_switchs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `REFERENCE` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `SOFTVERSION` varchar(255) DEFAULT NULL,
  `FIRMVERSION` varchar(255) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `REVISION` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_trays`
--

CREATE TABLE `snmp_trays` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `LEVEL` varchar(255) DEFAULT NULL,
  `MAXCAPACITY` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_videos`
--

CREATE TABLE `snmp_videos` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `snmp_virtualmachines`
--

CREATE TABLE `snmp_virtualmachines` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SNMP_ID` int(11) NOT NULL,
  `CONFIG_FILE` varchar(255) DEFAULT NULL,
  `CPU` int(11) DEFAULT NULL,
  `MEMORY` int(11) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `OS` varchar(255) DEFAULT NULL,
  `POWER` varchar(255) DEFAULT NULL,
  `UUID` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SNMP_ID` (`SNMP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `software_categories`
--

CREATE TABLE `software_categories` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CATEGORY_NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `software_category_exp`
--

CREATE TABLE `software_category_exp` (
  `CATEGORY_ID` int(6) NOT NULL,
  `SOFTWARE_EXP` varchar(255) NOT NULL,
  KEY `CATEGORY_ID` (`CATEGORY_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `softwares`
--

CREATE TABLE `softwares` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `PUBLISHER` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `VERSION` varchar(255) DEFAULT NULL,
  `FOLDER` text,
  `COMMENTS` text,
  `FILENAME` varchar(255) DEFAULT NULL,
  `FILESIZE` int(11) DEFAULT '0',
  `SOURCE` int(11) DEFAULT NULL,
  `GUID` varchar(255) DEFAULT NULL,
  `LANGUAGE` varchar(255) DEFAULT NULL,
  `INSTALLDATE` datetime DEFAULT NULL,
  `BITSWIDTH` int(11) DEFAULT NULL,
  `CATEGORY` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `NAME` (`NAME`),
  KEY `VERSION` (`VERSION`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `softwares_name_cache`
--

CREATE TABLE `softwares_name_cache` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `NAME` (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `sounds`
--

CREATE TABLE `sounds` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `ssl_store`
--

CREATE TABLE `ssl_store` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FILE` longblob,
  `AUTHOR` varchar(255) DEFAULT NULL,
  `FILE_NAME` varchar(255) DEFAULT NULL,
  `FILE_TYPE` varchar(20) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `storages`
--

CREATE TABLE `storages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `MODEL` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `DISKSIZE` int(11) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `FIRMWARE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `subnet`
--

CREATE TABLE `subnet` (
  `NETID` varchar(15) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `ID` varchar(255) DEFAULT NULL,
  `MASK` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`NETID`),
  KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `Tag` varchar(100) NOT NULL DEFAULT '',
  `Login` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`Tag`,`Login`),
  KEY `Tag` (`Tag`),
  KEY `Login` (`Login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `temp_files`
--

CREATE TABLE `temp_files` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TABLE_NAME` varchar(255) DEFAULT NULL,
  `FIELDS_NAME` varchar(255) DEFAULT NULL,
  `file` longblob,
  `COMMENT` longtext,
  `AUTHOR` varchar(255) DEFAULT NULL,
  `FILE_NAME` varchar(255) DEFAULT NULL,
  `FILE_TYPE` varchar(255) DEFAULT NULL,
  `FILE_SIZE` int(11) DEFAULT NULL,
  `ID_DDE` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `usbdevices`
--

CREATE TABLE `usbdevices` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `INTERFACE` varchar(255) DEFAULT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `SERIAL` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `CHIPSET` varchar(255) DEFAULT NULL,
  `MEMORY` varchar(255) DEFAULT NULL,
  `RESOLUTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `virtualmachines`
--

CREATE TABLE `virtualmachines` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int(11) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `STATUS` varchar(255) DEFAULT NULL,
  `SUBSYSTEM` varchar(255) DEFAULT NULL,
  `VMTYPE` varchar(255) DEFAULT NULL,
  `UUID` varchar(255) DEFAULT NULL,
  `VCPU` int(11) DEFAULT NULL,
  `MEMORY` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

