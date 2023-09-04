-- MySQL dump 10.13  Distrib 8.0.31, for Linux (x86_64)
--
-- Host: localhost    Database: ocsweb
-- ------------------------------------------------------
-- Server version	8.0.31

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accesslog`
--

DROP TABLE IF EXISTS `accesslog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accesslog` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `USERID` varchar(255) DEFAULT NULL,
  `LOGDATE` datetime DEFAULT NULL,
  `PROCESSES` text,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`),
  KEY `USERID` (`USERID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accesslog`
--

LOCK TABLES `accesslog` WRITE;
/*!40000 ALTER TABLE `accesslog` DISABLE KEYS */;
/*!40000 ALTER TABLE `accesslog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accountinfo`
--

DROP TABLE IF EXISTS `accountinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accountinfo` (
  `HARDWARE_ID` int NOT NULL,
  `TAG` varchar(255) DEFAULT 'NA',
  PRIMARY KEY (`HARDWARE_ID`),
  KEY `TAG` (`TAG`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accountinfo`
--

LOCK TABLES `accountinfo` WRITE;
/*!40000 ALTER TABLE `accountinfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `accountinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accountinfo_config`
--

DROP TABLE IF EXISTS `accountinfo_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accountinfo_config` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME_ACCOUNTINFO` varchar(255) DEFAULT NULL,
  `TYPE` int DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `ID_TAB` int DEFAULT NULL,
  `COMMENT` varchar(255) DEFAULT NULL,
  `SHOW_ORDER` int NOT NULL,
  `ACCOUNT_TYPE` varchar(255) DEFAULT NULL,
  `DEFAULT_VALUE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accountinfo_config`
--

LOCK TABLES `accountinfo_config` WRITE;
/*!40000 ALTER TABLE `accountinfo_config` DISABLE KEYS */;
INSERT INTO `accountinfo_config` VALUES (1,'TAG',0,'TAG',1,'TAG',1,'COMPUTERS',NULL),(2,'TAG',0,'TAG',1,'TAG',1,'SNMP',NULL);
/*!40000 ALTER TABLE `accountinfo_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `archive`
--

DROP TABLE IF EXISTS `archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `archive` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `HARDWARE_ID` (`HARDWARE_ID`),
  CONSTRAINT `archive_ibfk_1` FOREIGN KEY (`HARDWARE_ID`) REFERENCES `hardware` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `archive`
--

LOCK TABLES `archive` WRITE;
/*!40000 ALTER TABLE `archive` DISABLE KEYS */;
/*!40000 ALTER TABLE `archive` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets_categories`
--

DROP TABLE IF EXISTS `assets_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets_categories` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CATEGORY_NAME` varchar(255) NOT NULL,
  `CATEGORY_DESC` varchar(255) NOT NULL,
  `SQL_QUERY` text NOT NULL,
  `SQL_ARGS` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets_categories`
--

LOCK TABLES `assets_categories` WRITE;
/*!40000 ALTER TABLE `assets_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `assets_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_attempt`
--

DROP TABLE IF EXISTS `auth_attempt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_attempt` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DATETIMEATTEMPT` datetime NOT NULL,
  `LOGIN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `IP` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `SUCCESS` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_attempt`
--

LOCK TABLES `auth_attempt` WRITE;
/*!40000 ALTER TABLE `auth_attempt` DISABLE KEYS */;
/*!40000 ALTER TABLE `auth_attempt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `batteries`
--

DROP TABLE IF EXISTS `batteries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `batteries` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `LOCATION` varchar(255) DEFAULT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `MANUFACTUREDATE` varchar(10) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `CHEMISTRY` varchar(20) DEFAULT NULL,
  `DESIGNCAPACITY` varchar(10) DEFAULT NULL,
  `DESIGNVOLTAGE` varchar(20) DEFAULT NULL,
  `SBDSVERSION` varchar(255) DEFAULT NULL,
  `MAXERROR` int DEFAULT NULL,
  `OEMSPECIFIC` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`),
  KEY `NAME` (`NAME`),
  KEY `MANUFACTURER` (`MANUFACTURER`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `batteries`
--

LOCK TABLES `batteries` WRITE;
/*!40000 ALTER TABLE `batteries` DISABLE KEYS */;
/*!40000 ALTER TABLE `batteries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bios`
--

DROP TABLE IF EXISTS `bios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bios` (
  `HARDWARE_ID` int NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bios`
--

LOCK TABLES `bios` WRITE;
/*!40000 ALTER TABLE `bios` DISABLE KEYS */;
/*!40000 ALTER TABLE `bios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blacklist_macaddresses`
--

DROP TABLE IF EXISTS `blacklist_macaddresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blacklist_macaddresses` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `MACADDRESS` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `MACADDRESS` (`MACADDRESS`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blacklist_macaddresses`
--

LOCK TABLES `blacklist_macaddresses` WRITE;
/*!40000 ALTER TABLE `blacklist_macaddresses` DISABLE KEYS */;
INSERT INTO `blacklist_macaddresses` VALUES (14,''),(1,'00:00:00:00:00:00'),(5,'00:01:02:7D:9B:1C'),(6,'00:08:A1:46:06:35'),(7,'00:08:A1:66:E2:1A'),(8,'00:09:DD:10:37:68'),(9,'00:0F:EA:9A:E2:F0'),(10,'00:10:5A:72:71:F3'),(11,'00:11:11:85:08:8B'),(12,'10:11:11:11:11:11'),(3,'44:45:53:54:00:00'),(4,'44:45:53:54:00:01'),(13,'44:45:53:54:61:6F'),(2,'FF:FF:FF:FF:FF:FF');
/*!40000 ALTER TABLE `blacklist_macaddresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blacklist_serials`
--

DROP TABLE IF EXISTS `blacklist_serials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blacklist_serials` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `SERIAL` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `SERIAL` (`SERIAL`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blacklist_serials`
--

LOCK TABLES `blacklist_serials` WRITE;
/*!40000 ALTER TABLE `blacklist_serials` DISABLE KEYS */;
INSERT INTO `blacklist_serials` VALUES (34,''),(2,'(null string)'),(24,'000000'),(23,'0000000'),(22,'00000000'),(21,'000000000'),(20,'0000000000'),(11,'0123456789'),(10,'1'),(9,'1111111'),(8,'1111111111'),(12,'12345'),(13,'123456'),(14,'1234567'),(15,'12345678'),(16,'123456789'),(17,'1234567890'),(18,'123456789000'),(19,'12345678901234567'),(27,'EVAL'),(28,'IATPASS'),(3,'INVALID'),(1,'N/A'),(25,'NNNNNNN'),(29,'none'),(32,'Serial Number xxxxxx'),(6,'SN-12345'),(7,'SN-1234567890'),(33,'SN-123456fvgv3i0b8o5n6n7k'),(4,'SYS-1234567890'),(5,'SYS-9876543210'),(30,'To Be Filled By O.E.M.'),(31,'Tulip Computers'),(26,'xxxxxxxxxxx');
/*!40000 ALTER TABLE `blacklist_serials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blacklist_subnet`
--

DROP TABLE IF EXISTS `blacklist_subnet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blacklist_subnet` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `SUBNET` varchar(20) NOT NULL DEFAULT '',
  `MASK` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `SUBNET` (`SUBNET`,`MASK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blacklist_subnet`
--

LOCK TABLES `blacklist_subnet` WRITE;
/*!40000 ALTER TABLE `blacklist_subnet` DISABLE KEYS */;
/*!40000 ALTER TABLE `blacklist_subnet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `config` (
  `NAME` varchar(50) NOT NULL,
  `IVALUE` int DEFAULT NULL,
  `TVALUE` varchar(255) DEFAULT NULL,
  `COMMENTS` text,
  PRIMARY KEY (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES ('AUTO_DUPLICATE_LVL',7,'','Duplicates bitmap'),('CONEX_LDAP_FILTER1',NULL,'',NULL),('CONEX_LDAP_FILTER1_ROLE',NULL,'',NULL),('CONEX_LDAP_FILTER2',NULL,'',NULL),('CONEX_LDAP_FILTER2_ROLE',NULL,'',NULL),('CONEX_LDAP_NB_FILTERS',0,'2',''),('DEFAULT_CATEGORY',NULL,NULL,NULL),('DEPLOY',1,'','Activates or not the automatic deployment option'),('DOWNLOAD',0,'','Activate softwares auto deployment feature'),('DOWNLOAD_CYCLE_LATENCY',60,'','Time between two cycles (seconds)'),('DOWNLOAD_FRAG_LATENCY',10,'','Time between two downloads (seconds)'),('DOWNLOAD_GROUPS_TRACE_EVENTS',1,'','Specify if you want to track packages affected to a group on computer\'s level'),('DOWNLOAD_PACK_DIR',0,'/var/lib/ocsinventory-reports','Directory for download files'),('DOWNLOAD_PERIOD_LATENCY',1,'','Time between two periods (seconds)'),('DOWNLOAD_PERIOD_LENGTH',10,'','Number of cycles in a period'),('DOWNLOAD_SERVER_DOCROOT',0,'d:\\tele_ocs','Server directory used for group of server'),('DOWNLOAD_SERVER_URI',0,'$IP$/local','Server url used for group of server'),('DOWNLOAD_TIMEOUT',30,'','Validity of a package (in days)'),('ENABLE_GROUPS',1,'','Enable the computer\'s groups feature'),('FREQUENCY',0,'','Specify the frequency (days) of inventories. (0: inventory at each login. -1: no inventory)'),('GENERATE_OCS_FILES',0,'','Use with ocsinventory-injector, enable the multi entities feature'),('GROUPS_CACHE_OFFSET',43200,'','Random number computed in the defined range. Designed to avoid computing many groups in the same process'),('GROUPS_CACHE_REVALIDATE',43200,'','Specify the validity of computer\'s groups (default: compute it once a day - see offset)'),('GUI_REPORT_AGIN_MACH',30,'','Filter on lastdate for console page'),('GUI_REPORT_DD_MAX',4000,'','Filter on Hard Drive for console page'),('GUI_REPORT_DD_MINI',500,'','Filter on PROCESSOR for console page'),('GUI_REPORT_NOT_VIEW',3,'','Filter on DAY for console page'),('GUI_REPORT_PROC_MAX',3000,'','Filter on PROCESSOR for console page'),('GUI_REPORT_PROC_MINI',1000,'','Filter on Hard Drive for console page'),('GUI_REPORT_RAM_MAX',512,'','Filter on RAM for console page'),('GUI_REPORT_RAM_MINI',128,'','Filter on RAM for console page'),('GUI_VERSION',0,'7068','Version of the installed GUI and database'),('INTERFACE_LAST_CONTACT',15,'','Custom frequency'),('INVENTORY_CACHE_ENABLED',1,'','Enable some stuff to improve DB queries, especially for GUI multicriteria searching system'),('INVENTORY_CACHE_REVALIDATE',7,'','the engine will clean the inventory cache structures'),('INVENTORY_DIFF',1,'','Activate/Deactivate inventory incremental writing'),('INVENTORY_FILTER_ENABLED',0,'','Enable core filter system to modify some things \"on the fly\"'),('INVENTORY_FILTER_FLOOD_IP',0,'','Enable inventory flooding filter. A dedicated ipaddress ia allowed to send a new computer only once in this period'),('INVENTORY_FILTER_FLOOD_IP_CACHE_TIME',300,'','Period definition for INVENTORY_FILTER_FLOOD_IP'),('INVENTORY_FILTER_ON',0,'','Enable inventory filter stack'),('INVENTORY_ON_STARTUP',1,'1','Launch inventory on agent service statup'),('INVENTORY_TRANSACTION',1,'','Enable/disable db commit at each inventory section'),('INVENTORY_WRITE_DIFF',0,'','Configure engine to make a differential update of inventory sections (row level). Lower DB backend load, higher frontend load'),('IPDISCOVER',2,'','Max number of computers per gateway retrieving IP on the network'),('IPDISCOVER_BETTER_THRESHOLD',1,'','Specify the minimal difference to replace an ipdiscover agent'),('IPDISCOVER_IPD_DIR',0,'/var/lib/ocsinventory-reports','Directory for Ipdiscover files'),('IPDISCOVER_LATENCY',100,'','Default latency between two arp requests'),('IPDISCOVER_MAX_ALIVE',7,'','Max number of days before an Ip Discover computer is replaced'),('IPDISCOVER_NO_POSTPONE',0,'','Disable the time before a first election (not recommended)'),('IPDISCOVER_PURGE_OLD',0,'','Purge of the old IPDiscover data'),('IPDISCOVER_PURGE_VALIDITY_TIME',30,'','IPDiscover data validity time'),('IPDISCOVER_USE_GROUPS',1,'','Enable groups for ipdiscover (for example, you might want to prevent some groups'),('LOCK_REUSE_TIME',600,'','Validity of a computer\'s lock'),('LOGLEVEL',0,'','ocs engine loglevel'),('LOG_DIR',0,'/var/lib/ocsinventory-reports','Directory for logs files'),('LOG_SCRIPT',0,'/var/lib/ocsinventory-reports','Directory for logs scripts files'),('OCS_FILES_FORMAT',0,'OCS','Generate either compressed file or clear XML text'),('OCS_FILES_OVERWRITE',0,'','Specify if you want to keep trace of all inventory between to synchronisation with the higher level server'),('OCS_FILES_PATH',0,'/tmp','Path to ocs files directory (must be writeable)'),('OCS_SERVER_ADDRESS',0,'127.0.0.1','Ocs serveur ip for plugin webservice'),('PASSWORD_VERSION',2,'PASSWORD_BCRYPT','Password encryption version'),('PROLOG_FILTER_ON',0,'','Enable prolog filter stack'),('PROLOG_FREQ',24,'','Specify the frequency (hours) of prolog, on agents'),('REGISTRY',0,'','Activates or not the registry query function'),('SECURITY_AUTHENTICATION_BLOCK_IP',0,'','Block authentication after too many attempt'),('SECURITY_AUTHENTICATION_NB_ATTEMPT',1,'','Define the number of attempt to authenticate'),('SECURITY_AUTHENTICATION_TIME_BLOCK',1,'','Define the block timer'),('SECURITY_PASSWORD_ENABLED',0,'','Enable the password security'),('SECURITY_PASSWORD_FORCE_NB',0,'','Force number in password'),('SECURITY_PASSWORD_FORCE_SPE_CHAR',0,'','Force scpecial characters in password'),('SECURITY_PASSWORD_FORCE_UPPER',0,'','Force uppercase in password'),('SECURITY_PASSWORD_MIN_CHAR',1,'','Set minimal characters in password'),('SESSION_VALIDITY_TIME',600,'','Validity of a session (prolog=>postinventory)'),('SNMP_INVENTORY_DIFF',1,NULL,'Configure engine to update snmp inventory regarding to snmp_laststate table (lower DB backend load)'),('TAB_ACCOUNTAG_1',1,'TAG','Default TAB on computers accountinfo'),('TAB_ACCOUNTSNMP_1',1,'TAG','Default TAB on snmp accountinfo'),('TRACE_DELETED',0,'','Trace deleted/duplicated computers (Activated by GLPI)'),('UPDATE',0,'','Activates or not the update feature'),('VULN_CVE_DELAY_TIME',2,'','Time delay between CVE scans'),('WARN_UPDATE',1,'1','Warn user if an update is available'),('WOL_PORT',0,'7,9','Wol ports');
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conntrack`
--

DROP TABLE IF EXISTS `conntrack`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conntrack` (
  `IP` varchar(255) NOT NULL DEFAULT '',
  `TIMESTAMP` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`IP`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conntrack`
--

LOCK TABLES `conntrack` WRITE;
/*!40000 ALTER TABLE `conntrack` DISABLE KEYS */;
/*!40000 ALTER TABLE `conntrack` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `controllers`
--

DROP TABLE IF EXISTS `controllers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `controllers` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `CAPTION` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `VERSION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `controllers`
--

LOCK TABLES `controllers` WRITE;
/*!40000 ALTER TABLE `controllers` DISABLE KEYS */;
/*!40000 ALTER TABLE `controllers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cpus`
--

DROP TABLE IF EXISTS `cpus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cpus` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `SPEED` varchar(255) DEFAULT NULL,
  `CORES` int DEFAULT NULL,
  `L2CACHESIZE` varchar(255) DEFAULT NULL,
  `CPUARCH` varchar(255) DEFAULT NULL,
  `DATA_WIDTH` int DEFAULT NULL,
  `CURRENT_ADDRESS_WIDTH` int DEFAULT NULL,
  `LOGICAL_CPUS` int DEFAULT NULL,
  `VOLTAGE` varchar(255) DEFAULT NULL,
  `CURRENT_SPEED` varchar(255) DEFAULT NULL,
  `SOCKET` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cpus`
--

LOCK TABLES `cpus` WRITE;
/*!40000 ALTER TABLE `cpus` DISABLE KEYS */;
/*!40000 ALTER TABLE `cpus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cve_search`
--

DROP TABLE IF EXISTS `cve_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cve_search` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `PUBLISHER_ID` int NOT NULL,
  `NAME_ID` int NOT NULL,
  `VERSION_ID` int NOT NULL,
  `CVSS` double(4,2) NOT NULL,
  `CVE` varchar(255) DEFAULT NULL,
  `LINK` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cve_search`
--

LOCK TABLES `cve_search` WRITE;
/*!40000 ALTER TABLE `cve_search` DISABLE KEYS */;
/*!40000 ALTER TABLE `cve_search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cve_search_computer`
--

DROP TABLE IF EXISTS `cve_search_computer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cve_search_computer` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `HARDWARE_NAME` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `PUBLISHER` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `VERSION` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `SOFTWARE_NAME` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `CVSS` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `CVE` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `LINK` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cve_search_computer`
--

LOCK TABLES `cve_search_computer` WRITE;
/*!40000 ALTER TABLE `cve_search_computer` DISABLE KEYS */;
/*!40000 ALTER TABLE `cve_search_computer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cve_search_correspondance`
--

DROP TABLE IF EXISTS `cve_search_correspondance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cve_search_correspondance` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME_REG` varchar(255) NOT NULL,
  `PUBLISH_RESULT` varchar(255) DEFAULT NULL,
  `NAME_RESULT` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cve_search_correspondance`
--

LOCK TABLES `cve_search_correspondance` WRITE;
/*!40000 ALTER TABLE `cve_search_correspondance` DISABLE KEYS */;
/*!40000 ALTER TABLE `cve_search_correspondance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cve_search_history`
--

DROP TABLE IF EXISTS `cve_search_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cve_search_history` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `FLAG_DATE` datetime NOT NULL,
  `CVE_NB` int DEFAULT '0',
  `PUBLISHER_ID` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cve_search_history`
--

LOCK TABLES `cve_search_history` WRITE;
/*!40000 ALTER TABLE `cve_search_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `cve_search_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deleted_equiv`
--

DROP TABLE IF EXISTS `deleted_equiv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deleted_equiv` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `DELETED` varchar(255) NOT NULL,
  `EQUIVALENT` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `DELETED` (`DELETED`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deleted_equiv`
--

LOCK TABLES `deleted_equiv` WRITE;
/*!40000 ALTER TABLE `deleted_equiv` DISABLE KEYS */;
/*!40000 ALTER TABLE `deleted_equiv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deploy`
--

DROP TABLE IF EXISTS `deploy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deploy` (
  `NAME` varchar(255) NOT NULL,
  `CONTENT` longblob NOT NULL,
  PRIMARY KEY (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deploy`
--

LOCK TABLES `deploy` WRITE;
/*!40000 ALTER TABLE `deploy` DISABLE KEYS */;
/*!40000 ALTER TABLE `deploy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `devices` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `NAME` varchar(50) NOT NULL,
  `IVALUE` int DEFAULT NULL,
  `TVALUE` varchar(255) DEFAULT NULL,
  `COMMENTS` text,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`),
  KEY `TVALUE` (`TVALUE`),
  KEY `IVALUE` (`IVALUE`),
  KEY `NAME` (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devices`
--

LOCK TABLES `devices` WRITE;
/*!40000 ALTER TABLE `devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devicetype`
--

DROP TABLE IF EXISTS `devicetype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `devicetype` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devicetype`
--

LOCK TABLES `devicetype` WRITE;
/*!40000 ALTER TABLE `devicetype` DISABLE KEYS */;
/*!40000 ALTER TABLE `devicetype` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dico_ignored`
--

DROP TABLE IF EXISTS `dico_ignored`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dico_ignored` (
  `EXTRACTED` varchar(255) NOT NULL,
  PRIMARY KEY (`EXTRACTED`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dico_ignored`
--

LOCK TABLES `dico_ignored` WRITE;
/*!40000 ALTER TABLE `dico_ignored` DISABLE KEYS */;
/*!40000 ALTER TABLE `dico_ignored` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dico_soft`
--

DROP TABLE IF EXISTS `dico_soft`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dico_soft` (
  `EXTRACTED` varchar(255) NOT NULL,
  `FORMATTED` varchar(255) NOT NULL,
  PRIMARY KEY (`EXTRACTED`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dico_soft`
--

LOCK TABLES `dico_soft` WRITE;
/*!40000 ALTER TABLE `dico_soft` DISABLE KEYS */;
/*!40000 ALTER TABLE `dico_soft` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `download_affect_rules`
--

DROP TABLE IF EXISTS `download_affect_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `download_affect_rules` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `RULE` int NOT NULL,
  `PRIORITY` int NOT NULL,
  `CFIELD` varchar(20) NOT NULL,
  `OP` varchar(20) NOT NULL,
  `COMPTO` varchar(20) NOT NULL,
  `SERV_VALUE` varchar(20) DEFAULT NULL,
  `RULE_NAME` varchar(200) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `download_affect_rules`
--

LOCK TABLES `download_affect_rules` WRITE;
/*!40000 ALTER TABLE `download_affect_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `download_affect_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `download_available`
--

DROP TABLE IF EXISTS `download_available`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `download_available` (
  `FILEID` varchar(255) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `PRIORITY` int NOT NULL,
  `FRAGMENTS` int NOT NULL,
  `SIZE` int NOT NULL,
  `OSNAME` varchar(255) NOT NULL,
  `COMMENT` text,
  `ID_WK` int DEFAULT NULL,
  `DELETED` int DEFAULT '0',
  PRIMARY KEY (`FILEID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `download_available`
--

LOCK TABLES `download_available` WRITE;
/*!40000 ALTER TABLE `download_available` DISABLE KEYS */;
/*!40000 ALTER TABLE `download_available` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `download_enable`
--

DROP TABLE IF EXISTS `download_enable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `download_enable` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `FILEID` varchar(255) NOT NULL,
  `INFO_LOC` varchar(255) NOT NULL,
  `PACK_LOC` varchar(255) NOT NULL,
  `CERT_PATH` varchar(255) DEFAULT NULL,
  `CERT_FILE` varchar(255) DEFAULT NULL,
  `SERVER_ID` int DEFAULT NULL,
  `GROUP_ID` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `FILEID` (`FILEID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `download_enable`
--

LOCK TABLES `download_enable` WRITE;
/*!40000 ALTER TABLE `download_enable` DISABLE KEYS */;
/*!40000 ALTER TABLE `download_enable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `download_history`
--

DROP TABLE IF EXISTS `download_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `download_history` (
  `HARDWARE_ID` int NOT NULL,
  `PKG_ID` int NOT NULL DEFAULT '0',
  `PKG_NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`HARDWARE_ID`,`PKG_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `download_history`
--

LOCK TABLES `download_history` WRITE;
/*!40000 ALTER TABLE `download_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `download_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `download_servers`
--

DROP TABLE IF EXISTS `download_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `download_servers` (
  `HARDWARE_ID` int NOT NULL,
  `URL` varchar(250) NOT NULL,
  `ADD_PORT` int NOT NULL,
  `ADD_REP` varchar(250) NOT NULL,
  `GROUP_ID` int NOT NULL,
  PRIMARY KEY (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `download_servers`
--

LOCK TABLES `download_servers` WRITE;
/*!40000 ALTER TABLE `download_servers` DISABLE KEYS */;
/*!40000 ALTER TABLE `download_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `downloadwk_conf_values`
--

DROP TABLE IF EXISTS `downloadwk_conf_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `downloadwk_conf_values` (
  `FIELD` int DEFAULT NULL,
  `VALUE` varchar(100) DEFAULT NULL,
  `ID` int NOT NULL AUTO_INCREMENT,
  `DEFAULT_FIELD` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `downloadwk_conf_values`
--

LOCK TABLES `downloadwk_conf_values` WRITE;
/*!40000 ALTER TABLE `downloadwk_conf_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `downloadwk_conf_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `downloadwk_fields`
--

DROP TABLE IF EXISTS `downloadwk_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `downloadwk_fields` (
  `TAB` varchar(100) DEFAULT NULL,
  `FIELD` varchar(100) DEFAULT NULL,
  `TYPE` int DEFAULT NULL,
  `LBL` varchar(100) DEFAULT NULL,
  `MUST_COMPLETED` int DEFAULT NULL,
  `ID` int NOT NULL AUTO_INCREMENT,
  `VALUE` varchar(255) DEFAULT NULL,
  `DEFAULT_FIELD` int DEFAULT NULL,
  `RESTRICTED` int DEFAULT NULL,
  `LINK_STATUS` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `downloadwk_fields`
--

LOCK TABLES `downloadwk_fields` WRITE;
/*!40000 ALTER TABLE `downloadwk_fields` DISABLE KEYS */;
INSERT INTO `downloadwk_fields` VALUES ('1','USER',3,'1038',1,1,'loggeduser',1,0,0),('2','NAME_TELEDEPLOY',0,'1037',1,2,'',1,0,0),('2','INFO_PACK',0,'53',1,3,'',1,0,0),('3','PRIORITY',2,'1039',1,4,'',1,0,0),('3','NOTIF_USER',2,'1040',1,5,'',1,0,0),('3','REPORT_USER',2,'1041',1,6,'',1,0,0),('3','REBOOT',2,'1042',1,7,'',1,0,0),('4','VALID_INSTALL',6,'1043',1,8,'',1,0,0),('4','STATUS',2,'1046',0,9,'2',1,1,0),('5','LIST_HISTO',10,'1052',0,10,'select AUTHOR,DATE,ACTION from downloadwk_history where id_dde=%s$$$$OLD_MODIF',1,0,0);
/*!40000 ALTER TABLE `downloadwk_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `downloadwk_history`
--

DROP TABLE IF EXISTS `downloadwk_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `downloadwk_history` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ID_DDE` int DEFAULT NULL,
  `AUTHOR` varchar(255) DEFAULT NULL,
  `DATE` date DEFAULT NULL,
  `ACTION` longtext,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `downloadwk_history`
--

LOCK TABLES `downloadwk_history` WRITE;
/*!40000 ALTER TABLE `downloadwk_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `downloadwk_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `downloadwk_pack`
--

DROP TABLE IF EXISTS `downloadwk_pack`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `downloadwk_pack` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `LOGIN_USER` varchar(255) DEFAULT NULL,
  `GROUP_USER` varchar(255) DEFAULT NULL,
  `Q_DATE` int DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `downloadwk_pack`
--

LOCK TABLES `downloadwk_pack` WRITE;
/*!40000 ALTER TABLE `downloadwk_pack` DISABLE KEYS */;
/*!40000 ALTER TABLE `downloadwk_pack` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `downloadwk_statut_request`
--

DROP TABLE IF EXISTS `downloadwk_statut_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `downloadwk_statut_request` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(20) DEFAULT NULL,
  `LBL` varchar(255) DEFAULT NULL,
  `ACTIF` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `downloadwk_statut_request`
--

LOCK TABLES `downloadwk_statut_request` WRITE;
/*!40000 ALTER TABLE `downloadwk_statut_request` DISABLE KEYS */;
INSERT INTO `downloadwk_statut_request` VALUES (1,'NIV0','DELETE',0),(2,'NIV1','WAITING FOR INCLUSION',0),(3,'NIV2','ACKNOWLEDGEMENT',0),(4,'NIV3','REFUSAL',0),(5,'NIV4','NEED TO CHANGE',0),(6,'NIV5','CREATE PACKAGE',0),(7,'NIV6','LOCAL TEST',0),(8,'NIV7','PERIMETER LIMITED DEPLOYMENT',0),(9,'NIV8','DURING DEPLOYMENT',0);
/*!40000 ALTER TABLE `downloadwk_statut_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `downloadwk_tab_values`
--

DROP TABLE IF EXISTS `downloadwk_tab_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `downloadwk_tab_values` (
  `FIELD` varchar(100) DEFAULT NULL,
  `VALUE` varchar(100) DEFAULT NULL,
  `LBL` varchar(100) DEFAULT NULL,
  `ID` int NOT NULL AUTO_INCREMENT,
  `DEFAULT_FIELD` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `downloadwk_tab_values`
--

LOCK TABLES `downloadwk_tab_values` WRITE;
/*!40000 ALTER TABLE `downloadwk_tab_values` DISABLE KEYS */;
INSERT INTO `downloadwk_tab_values` VALUES ('TAB','INFO_DEM','1033',1,1),('TAB','INFO_PAQUET','1034',2,1),('TAB','INFO_CONF','1035',3,1),('TAB','INFO_VALID','1036',4,1),('TAB','INFO_HISTO','1052',5,1);
/*!40000 ALTER TABLE `downloadwk_tab_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drives`
--

DROP TABLE IF EXISTS `drives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `drives` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `LETTER` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `FILESYSTEM` varchar(255) DEFAULT NULL,
  `TOTAL` int DEFAULT NULL,
  `FREE` int DEFAULT NULL,
  `NUMFILES` int DEFAULT NULL,
  `VOLUMN` varchar(255) DEFAULT NULL,
  `CREATEDATE` date DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drives`
--

LOCK TABLES `drives` WRITE;
/*!40000 ALTER TABLE `drives` DISABLE KEYS */;
/*!40000 ALTER TABLE `drives` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `engine_mutex`
--

DROP TABLE IF EXISTS `engine_mutex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `engine_mutex` (
  `NAME` varchar(255) NOT NULL DEFAULT '',
  `PID` int DEFAULT NULL,
  `TAG` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`NAME`,`TAG`),
  KEY `PID` (`PID`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `engine_mutex`
--

LOCK TABLES `engine_mutex` WRITE;
/*!40000 ALTER TABLE `engine_mutex` DISABLE KEYS */;
/*!40000 ALTER TABLE `engine_mutex` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `engine_persistent`
--

DROP TABLE IF EXISTS `engine_persistent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `engine_persistent` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL DEFAULT '',
  `IVALUE` int DEFAULT NULL,
  `TVALUE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `NAME` (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `engine_persistent`
--

LOCK TABLES `engine_persistent` WRITE;
/*!40000 ALTER TABLE `engine_persistent` DISABLE KEYS */;
/*!40000 ALTER TABLE `engine_persistent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extensions`
--

DROP TABLE IF EXISTS `extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `extensions` (
  `id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `version` double NOT NULL,
  `licence` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `author` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `contributor` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `install_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extensions`
--

LOCK TABLES `extensions` WRITE;
/*!40000 ALTER TABLE `extensions` DISABLE KEYS */;
/*!40000 ALTER TABLE `extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `files` (
  `NAME` varchar(100) NOT NULL,
  `VERSION` varchar(50) NOT NULL,
  `OS` varchar(70) NOT NULL,
  `CONTENT` longblob NOT NULL,
  PRIMARY KEY (`NAME`,`OS`,`VERSION`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups` (
  `HARDWARE_ID` int NOT NULL DEFAULT '0',
  `REQUEST` longtext,
  `CREATE_TIME` int DEFAULT '0',
  `REVALIDATE_FROM` int DEFAULT '0',
  `XMLDEF` longtext,
  PRIMARY KEY (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups_cache`
--

DROP TABLE IF EXISTS `groups_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups_cache` (
  `HARDWARE_ID` int NOT NULL DEFAULT '0',
  `GROUP_ID` int NOT NULL DEFAULT '0',
  `STATIC` int DEFAULT '0',
  PRIMARY KEY (`HARDWARE_ID`,`GROUP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups_cache`
--

LOCK TABLES `groups_cache` WRITE;
/*!40000 ALTER TABLE `groups_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `groups_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hardware`
--

DROP TABLE IF EXISTS `hardware`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hardware` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DEVICEID` varchar(255) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `WORKGROUP` varchar(255) DEFAULT NULL,
  `USERDOMAIN` varchar(255) DEFAULT NULL,
  `OSNAME` varchar(255) DEFAULT NULL,
  `OSVERSION` varchar(255) DEFAULT NULL,
  `OSCOMMENTS` varchar(255) DEFAULT NULL,
  `PROCESSORT` varchar(255) DEFAULT NULL,
  `PROCESSORS` int DEFAULT '0',
  `PROCESSORN` smallint DEFAULT NULL,
  `MEMORY` int DEFAULT NULL,
  `SWAP` int DEFAULT NULL,
  `IPADDR` varchar(255) DEFAULT NULL,
  `DNS` varchar(255) DEFAULT NULL,
  `DEFAULTGATEWAY` varchar(255) DEFAULT NULL,
  `ETIME` datetime DEFAULT NULL,
  `LASTDATE` datetime DEFAULT NULL,
  `LASTCOME` datetime DEFAULT NULL,
  `QUALITY` decimal(7,4) DEFAULT NULL,
  `FIDELITY` bigint DEFAULT '1',
  `USERID` varchar(255) DEFAULT NULL,
  `TYPE` int DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `WINCOMPANY` varchar(255) DEFAULT NULL,
  `WINOWNER` varchar(255) DEFAULT NULL,
  `WINPRODID` varchar(255) DEFAULT NULL,
  `WINPRODKEY` varchar(255) DEFAULT NULL,
  `USERAGENT` varchar(50) DEFAULT NULL,
  `CHECKSUM` bigint unsigned DEFAULT '262143',
  `SSTATE` int DEFAULT '0',
  `IPSRC` varchar(255) DEFAULT NULL,
  `UUID` varchar(255) DEFAULT NULL,
  `ARCH` varchar(10) DEFAULT NULL,
  `CATEGORY_ID` int DEFAULT NULL,
  `ARCHIVE` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `DEVICEID` (`DEVICEID`),
  KEY `NAME` (`NAME`),
  KEY `CHECKSUM` (`CHECKSUM`),
  KEY `USERID` (`USERID`),
  KEY `WORKGROUP` (`WORKGROUP`),
  KEY `OSNAME` (`OSNAME`),
  KEY `MEMORY` (`MEMORY`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hardware`
--

LOCK TABLES `hardware` WRITE;
/*!40000 ALTER TABLE `hardware` DISABLE KEYS */;
/*!40000 ALTER TABLE `hardware` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hardware_osname_cache`
--

DROP TABLE IF EXISTS `hardware_osname_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hardware_osname_cache` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `OSNAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `OSNAME` (`OSNAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hardware_osname_cache`
--

LOCK TABLES `hardware_osname_cache` WRITE;
/*!40000 ALTER TABLE `hardware_osname_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `hardware_osname_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `history` (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `USER` varchar(255) NOT NULL,
  `DATETIME_ACTION` datetime NOT NULL,
  `ACTION` varchar(255) NOT NULL,
  `TARGET` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history`
--

LOCK TABLES `history` WRITE;
/*!40000 ALTER TABLE `history` DISABLE KEYS */;
/*!40000 ALTER TABLE `history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inputs`
--

DROP TABLE IF EXISTS `inputs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inputs` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `CAPTION` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `INTERFACE` varchar(255) DEFAULT NULL,
  `POINTTYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inputs`
--

LOCK TABLES `inputs` WRITE;
/*!40000 ALTER TABLE `inputs` DISABLE KEYS */;
/*!40000 ALTER TABLE `inputs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itmgmt_comments`
--

DROP TABLE IF EXISTS `itmgmt_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itmgmt_comments` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `COMMENTS` longtext,
  `USER_INSERT` varchar(100) DEFAULT NULL,
  `DATE_INSERT` date DEFAULT NULL,
  `ACTION` varchar(255) DEFAULT NULL,
  `VISIBLE` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `itmgmt_comments`
--

LOCK TABLES `itmgmt_comments` WRITE;
/*!40000 ALTER TABLE `itmgmt_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `itmgmt_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `javainfo`
--

DROP TABLE IF EXISTS `javainfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `javainfo` (
  `HARDWARE_ID` int NOT NULL,
  `JAVANAME` varchar(255) DEFAULT 'NONAME',
  `JAVAPATHLEVEL` int DEFAULT '0',
  `JAVACOUNTRY` varchar(255) DEFAULT NULL,
  `JAVACLASSPATH` varchar(255) DEFAULT NULL,
  `JAVAHOME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `javainfo`
--

LOCK TABLES `javainfo` WRITE;
/*!40000 ALTER TABLE `javainfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `javainfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journallog`
--

DROP TABLE IF EXISTS `journallog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journallog` (
  `HARDWARE_ID` int NOT NULL,
  `ID` int NOT NULL AUTO_INCREMENT,
  `JOURNALLOG` longtext,
  `LISTENERNAME` varchar(255) DEFAULT 'NONAME',
  `DATE` varchar(255) DEFAULT NULL,
  `STATUS` int DEFAULT '0',
  `ERRORCODE` int DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journallog`
--

LOCK TABLES `journallog` WRITE;
/*!40000 ALTER TABLE `journallog` DISABLE KEYS */;
/*!40000 ALTER TABLE `journallog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `NAME` varchar(60) NOT NULL,
  `IMG` blob,
  `JSON_VALUE` longtext,
  PRIMARY KEY (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `layouts`
--

DROP TABLE IF EXISTS `layouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `layouts` (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `LAYOUT_NAME` varchar(255) NOT NULL,
  `CREATOR` varchar(255) NOT NULL,
  `TABLE_NAME` varchar(255) NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `VISIBLE_COL` text NOT NULL,
  `VISIBILITY_SCOPE` varchar(255) DEFAULT 'USER',
  `GROUP_ID` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `layouts`
--

LOCK TABLES `layouts` WRITE;
/*!40000 ALTER TABLE `layouts` DISABLE KEYS */;
/*!40000 ALTER TABLE `layouts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `local_groups`
--

DROP TABLE IF EXISTS `local_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local_groups` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `ID_GROUP` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `NAME` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `MEMBER` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`,`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `local_groups`
--

LOCK TABLES `local_groups` WRITE;
/*!40000 ALTER TABLE `local_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `local_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `local_users`
--

DROP TABLE IF EXISTS `local_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local_users` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `ID_USER` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `GID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `NAME` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `HOME` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `SHELL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `LOGIN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `MEMBER` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`,`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `local_users`
--

LOCK TABLES `local_users` WRITE;
/*!40000 ALTER TABLE `local_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `local_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locks`
--

DROP TABLE IF EXISTS `locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `locks` (
  `HARDWARE_ID` int NOT NULL,
  `ID` int DEFAULT NULL,
  `SINCE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`HARDWARE_ID`),
  KEY `SINCE` (`SINCE`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locks`
--

LOCK TABLES `locks` WRITE;
/*!40000 ALTER TABLE `locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `memories`
--

DROP TABLE IF EXISTS `memories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `memories` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `CAPTION` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `CAPACITY` int DEFAULT NULL,
  `PURPOSE` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `SPEED` varchar(255) DEFAULT NULL,
  `NUMSLOTS` smallint DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `memories`
--

LOCK TABLES `memories` WRITE;
/*!40000 ALTER TABLE `memories` DISABLE KEYS */;
/*!40000 ALTER TABLE `memories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modems`
--

DROP TABLE IF EXISTS `modems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modems` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `MODEL` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modems`
--

LOCK TABLES `modems` WRITE;
/*!40000 ALTER TABLE `modems` DISABLE KEYS */;
/*!40000 ALTER TABLE `modems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `monitors`
--

DROP TABLE IF EXISTS `monitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitors` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `CAPTION` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `SERIAL` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monitors`
--

LOCK TABLES `monitors` WRITE;
/*!40000 ALTER TABLE `monitors` DISABLE KEYS */;
/*!40000 ALTER TABLE `monitors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `netmap`
--

DROP TABLE IF EXISTS `netmap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `netmap` (
  `IP` varchar(15) NOT NULL,
  `MAC` varchar(17) NOT NULL,
  `MASK` varchar(15) NOT NULL,
  `NETID` varchar(15) NOT NULL,
  `DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `NAME` varchar(255) DEFAULT NULL,
  `TAG` varchar(255) DEFAULT NULL,
  `HARDWARE_ID` int DEFAULT NULL,
  PRIMARY KEY (`MAC`),
  KEY `IP` (`IP`),
  KEY `NETID` (`NETID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `netmap`
--

LOCK TABLES `netmap` WRITE;
/*!40000 ALTER TABLE `netmap` DISABLE KEYS */;
/*!40000 ALTER TABLE `netmap` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `network_devices`
--

DROP TABLE IF EXISTS `network_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_devices` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `MACADDR` varchar(255) DEFAULT NULL,
  `USER` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `MACADDR` (`MACADDR`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `network_devices`
--

LOCK TABLES `network_devices` WRITE;
/*!40000 ALTER TABLE `network_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `network_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `networks`
--

DROP TABLE IF EXISTS `networks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `networks` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `networks`
--

LOCK TABLES `networks` WRITE;
/*!40000 ALTER TABLE `networks` DISABLE KEYS */;
/*!40000 ALTER TABLE `networks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `TYPE` varchar(255) NOT NULL,
  `FILE` varchar(255) DEFAULT NULL,
  `SUBJECT` varchar(255) DEFAULT NULL,
  `ALTBODY` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification`
--

LOCK TABLES `notification` WRITE;
/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
INSERT INTO `notification` VALUES (1,'SELECTED','DEFAULT',NULL,NULL),(2,'DEFAULT','templates/OCS_template.html','Notification OCSInventory','Default report inventory'),(3,'PERSO',NULL,NULL,NULL);
/*!40000 ALTER TABLE `notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_config`
--

DROP TABLE IF EXISTS `notification_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_config` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `TVALUE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_config`
--

LOCK TABLES `notification_config` WRITE;
/*!40000 ALTER TABLE `notification_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operators`
--

DROP TABLE IF EXISTS `operators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operators` (
  `ID` varchar(255) NOT NULL DEFAULT '',
  `FIRSTNAME` varchar(255) DEFAULT NULL,
  `LASTNAME` varchar(255) DEFAULT NULL,
  `PASSWD` varchar(255) DEFAULT NULL,
  `ACCESSLVL` int DEFAULT NULL,
  `COMMENTS` text,
  `NEW_ACCESSLVL` varchar(255) DEFAULT NULL,
  `EMAIL` varchar(255) DEFAULT NULL,
  `USER_GROUP` varchar(255) DEFAULT NULL,
  `PASSWORD_VERSION` int DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operators`
--

LOCK TABLES `operators` WRITE;
/*!40000 ALTER TABLE `operators` DISABLE KEYS */;
INSERT INTO `operators` VALUES ('admin','admin','admin','8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918',1,'Default administrator account','sadmin',NULL,NULL,2);
/*!40000 ALTER TABLE `operators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ports`
--

DROP TABLE IF EXISTS `ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ports` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `CAPTION` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ports`
--

LOCK TABLES `ports` WRITE;
/*!40000 ALTER TABLE `ports` DISABLE KEYS */;
/*!40000 ALTER TABLE `ports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `printers`
--

DROP TABLE IF EXISTS `printers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `printers` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `DRIVER` varchar(255) DEFAULT NULL,
  `PORT` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `SERVERNAME` varchar(255) DEFAULT NULL,
  `SHARENAME` varchar(255) DEFAULT NULL,
  `RESOLUTION` varchar(50) DEFAULT NULL,
  `COMMENT` varchar(255) DEFAULT NULL,
  `SHARED` int DEFAULT NULL,
  `NETWORK` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `printers`
--

LOCK TABLES `printers` WRITE;
/*!40000 ALTER TABLE `printers` DISABLE KEYS */;
/*!40000 ALTER TABLE `printers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prolog_conntrack`
--

DROP TABLE IF EXISTS `prolog_conntrack`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prolog_conntrack` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DEVICEID` varchar(255) DEFAULT NULL,
  `TIMESTAMP` int DEFAULT NULL,
  `PID` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `DEVICEID` (`DEVICEID`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prolog_conntrack`
--

LOCK TABLES `prolog_conntrack` WRITE;
/*!40000 ALTER TABLE `prolog_conntrack` DISABLE KEYS */;
/*!40000 ALTER TABLE `prolog_conntrack` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regconfig`
--

DROP TABLE IF EXISTS `regconfig`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regconfig` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) DEFAULT NULL,
  `REGTREE` int DEFAULT NULL,
  `REGKEY` text,
  `REGVALUE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `NAME` (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regconfig`
--

LOCK TABLES `regconfig` WRITE;
/*!40000 ALTER TABLE `regconfig` DISABLE KEYS */;
/*!40000 ALTER TABLE `regconfig` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registry`
--

DROP TABLE IF EXISTS `registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registry` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `REGVALUE` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  PRIMARY KEY (`ID`),
  KEY `NAME` (`NAME`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registry`
--

LOCK TABLES `registry` WRITE;
/*!40000 ALTER TABLE `registry` DISABLE KEYS */;
/*!40000 ALTER TABLE `registry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registry_name_cache`
--

DROP TABLE IF EXISTS `registry_name_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registry_name_cache` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `NAME` (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registry_name_cache`
--

LOCK TABLES `registry_name_cache` WRITE;
/*!40000 ALTER TABLE `registry_name_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `registry_name_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registry_regvalue_cache`
--

DROP TABLE IF EXISTS `registry_regvalue_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registry_regvalue_cache` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `REGVALUE` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registry_regvalue_cache`
--

LOCK TABLES `registry_regvalue_cache` WRITE;
/*!40000 ALTER TABLE `registry_regvalue_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `registry_regvalue_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports_notifications`
--

DROP TABLE IF EXISTS `reports_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reports_notifications` (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `GROUP_ID` int NOT NULL,
  `RECURRENCE` varchar(255) NOT NULL,
  `END_DATE` datetime DEFAULT NULL,
  `DATE_CREATED` datetime DEFAULT NULL,
  `WEEKDAY` varchar(255) DEFAULT NULL,
  `LAST_EXEC` datetime DEFAULT NULL,
  `MAIL` varchar(255) NOT NULL,
  `STATUS` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports_notifications`
--

LOCK TABLES `reports_notifications` WRITE;
/*!40000 ALTER TABLE `reports_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repository`
--

DROP TABLE IF EXISTS `repository`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `repository` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repository`
--

LOCK TABLES `repository` WRITE;
/*!40000 ALTER TABLE `repository` DISABLE KEYS */;
/*!40000 ALTER TABLE `repository` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saas`
--

DROP TABLE IF EXISTS `saas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saas` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `SAAS_EXP_ID` int NOT NULL,
  `HARDWARE_ID` int NOT NULL,
  `ENTRY` varchar(255) NOT NULL,
  `DATA` varchar(255) NOT NULL,
  `TTL` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saas`
--

LOCK TABLES `saas` WRITE;
/*!40000 ALTER TABLE `saas` DISABLE KEYS */;
/*!40000 ALTER TABLE `saas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saas_exp`
--

DROP TABLE IF EXISTS `saas_exp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saas_exp` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `DNS_EXP` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saas_exp`
--

LOCK TABLES `saas_exp` WRITE;
/*!40000 ALTER TABLE `saas_exp` DISABLE KEYS */;
/*!40000 ALTER TABLE `saas_exp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `save_query`
--

DROP TABLE IF EXISTS `save_query`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `save_query` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `QUERY_NAME` varchar(255) NOT NULL,
  `DESCRIPTION` text,
  `PARAMETERS` text NOT NULL,
  `WHO_CAN_SEE` varchar(255) DEFAULT 'ALL',
  `USER_ID` varchar(255) DEFAULT NULL,
  `GROUP_ID` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `save_query`
--

LOCK TABLES `save_query` WRITE;
/*!40000 ALTER TABLE `save_query` DISABLE KEYS */;
/*!40000 ALTER TABLE `save_query` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_wol`
--

DROP TABLE IF EXISTS `schedule_wol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedule_wol` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `MACHINE_ID` varchar(255) NOT NULL,
  `WOL_DATE` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_wol`
--

LOCK TABLES `schedule_wol` WRITE;
/*!40000 ALTER TABLE `schedule_wol` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_wol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sim`
--

DROP TABLE IF EXISTS `sim`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sim` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `OPERATOR` varchar(255) DEFAULT NULL,
  `OPNAME` varchar(255) DEFAULT NULL,
  `COUNTRY` varchar(255) DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `DEVICEID` varchar(255) DEFAULT NULL,
  `PHONENUMBER` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sim`
--

LOCK TABLES `sim` WRITE;
/*!40000 ALTER TABLE `sim` DISABLE KEYS */;
/*!40000 ALTER TABLE `sim` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slots`
--

DROP TABLE IF EXISTS `slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slots` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `DESIGNATION` varchar(255) DEFAULT NULL,
  `PURPOSE` varchar(255) DEFAULT NULL,
  `STATUS` varchar(255) DEFAULT NULL,
  `PSHARE` tinyint DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slots`
--

LOCK TABLES `slots` WRITE;
/*!40000 ALTER TABLE `slots` DISABLE KEYS */;
/*!40000 ALTER TABLE `slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_accountinfo`
--

DROP TABLE IF EXISTS `snmp_accountinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snmp_accountinfo` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `SNMP_TYPE` varchar(255) NOT NULL,
  `SNMP_RECONCILIATION_FIELD` varchar(255) NOT NULL,
  `SNMP_RECONCILIATION_VALUE` varchar(255) NOT NULL,
  `TAG` varchar(255) DEFAULT 'NA',
  PRIMARY KEY (`ID`),
  KEY `TAG` (`TAG`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_accountinfo`
--

LOCK TABLES `snmp_accountinfo` WRITE;
/*!40000 ALTER TABLE `snmp_accountinfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `snmp_accountinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_communities`
--

DROP TABLE IF EXISTS `snmp_communities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snmp_communities` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `VERSION` varchar(5) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `USERNAME` varchar(255) DEFAULT NULL,
  `AUTHPASSWD` varchar(255) DEFAULT NULL,
  `AUTHPROTO` varchar(255) DEFAULT NULL,
  `PRIVPROTO` varchar(255) DEFAULT NULL,
  `PRIVPASSWD` varchar(255) DEFAULT NULL,
  `LEVEL` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_communities`
--

LOCK TABLES `snmp_communities` WRITE;
/*!40000 ALTER TABLE `snmp_communities` DISABLE KEYS */;
/*!40000 ALTER TABLE `snmp_communities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_configs`
--

DROP TABLE IF EXISTS `snmp_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snmp_configs` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `TYPE_ID` int NOT NULL,
  `LABEL_ID` int NOT NULL,
  `OID` varchar(255) NOT NULL,
  `RECONCILIATION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_configs`
--

LOCK TABLES `snmp_configs` WRITE;
/*!40000 ALTER TABLE `snmp_configs` DISABLE KEYS */;
INSERT INTO `snmp_configs` VALUES (1,1,2,'1.3.6.1.2.1.1.5.0','Yes'),(2,1,1,'1.3.6.1.2.1.1.1.0',NULL),(3,1,4,'1.3.6.1.2.1.1.6.0',NULL),(4,1,3,'1.3.6.1.2.1.1.3.0',NULL),(5,1,5,'1.3.6.1.2.1.4.20.1.1',NULL),(6,1,6,'1.3.6.1.2.1.4.20.1.3',NULL);
/*!40000 ALTER TABLE `snmp_configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_default`
--

DROP TABLE IF EXISTS `snmp_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snmp_default` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DefaultName` varchar(255) DEFAULT NULL,
  `DefaultDescription` varchar(255) DEFAULT NULL,
  `DefaultLocation` varchar(255) DEFAULT NULL,
  `DefaultUptime` varchar(255) DEFAULT NULL,
  `DefaultAddressIP` varchar(255) DEFAULT NULL,
  `DefaultGateway` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `DefaultName` (`DefaultName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_default`
--

LOCK TABLES `snmp_default` WRITE;
/*!40000 ALTER TABLE `snmp_default` DISABLE KEYS */;
/*!40000 ALTER TABLE `snmp_default` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_labels`
--

DROP TABLE IF EXISTS `snmp_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snmp_labels` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `LABEL_NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_labels`
--

LOCK TABLES `snmp_labels` WRITE;
/*!40000 ALTER TABLE `snmp_labels` DISABLE KEYS */;
INSERT INTO `snmp_labels` VALUES (1,'DefaultDescription'),(2,'DefaultName'),(3,'DefaultUptime'),(4,'DefaultLocation'),(5,'DefaultAddressIP'),(6,'DefaultGateway');
/*!40000 ALTER TABLE `snmp_labels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_mibs`
--

DROP TABLE IF EXISTS `snmp_mibs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snmp_mibs` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `VENDOR` varchar(255) DEFAULT NULL,
  `URL` varchar(255) DEFAULT NULL,
  `CHECKSUM` varchar(255) DEFAULT NULL,
  `VERSION` varchar(5) DEFAULT NULL,
  `PARSER` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_mibs`
--

LOCK TABLES `snmp_mibs` WRITE;
/*!40000 ALTER TABLE `snmp_mibs` DISABLE KEYS */;
/*!40000 ALTER TABLE `snmp_mibs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_types`
--

DROP TABLE IF EXISTS `snmp_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snmp_types` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `TYPE_NAME` varchar(255) NOT NULL,
  `TABLE_TYPE_NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_types`
--

LOCK TABLES `snmp_types` WRITE;
/*!40000 ALTER TABLE `snmp_types` DISABLE KEYS */;
INSERT INTO `snmp_types` VALUES (1,'Default','snmp_default');
/*!40000 ALTER TABLE `snmp_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snmp_types_conditions`
--

DROP TABLE IF EXISTS `snmp_types_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snmp_types_conditions` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `TYPE_ID` int NOT NULL,
  `CONDITION_OID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `CONDITION_VALUE` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_types_conditions`
--

LOCK TABLES `snmp_types_conditions` WRITE;
/*!40000 ALTER TABLE `snmp_types_conditions` DISABLE KEYS */;
INSERT INTO `snmp_types_conditions` VALUES (1,1,'1.3.6.1.2.1.1.1.0',NULL);
/*!40000 ALTER TABLE `snmp_types_conditions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software`
--

DROP TABLE IF EXISTS `software`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `software` (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `NAME_ID` int NOT NULL,
  `PUBLISHER_ID` int NOT NULL,
  `VERSION_ID` int NOT NULL,
  `FOLDER` text,
  `COMMENTS` text,
  `FILENAME` varchar(255) DEFAULT NULL,
  `FILESIZE` int DEFAULT '0',
  `SOURCE` int DEFAULT NULL,
  `GUID` varchar(255) DEFAULT NULL,
  `LANGUAGE` varchar(255) DEFAULT NULL,
  `INSTALLDATE` datetime DEFAULT NULL,
  `BITSWIDTH` int DEFAULT NULL,
  `ARCHITECTURE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`),
  KEY `NAME_ID` (`NAME_ID`),
  KEY `PUBLISHER_ID` (`PUBLISHER_ID`),
  KEY `VERSION_ID` (`VERSION_ID`)
  KEY `HARDWARE_ID_2` (`HARDWARE_ID`, `NAME_ID`, `VERSION_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software`
--

LOCK TABLES `software` WRITE;
/*!40000 ALTER TABLE `software` DISABLE KEYS */;
/*!40000 ALTER TABLE `software` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_categories`
--

DROP TABLE IF EXISTS `software_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `software_categories` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CATEGORY_NAME` varchar(255) NOT NULL,
  `OS` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_categories`
--

LOCK TABLES `software_categories` WRITE;
/*!40000 ALTER TABLE `software_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_categories_link`
--

DROP TABLE IF EXISTS `software_categories_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `software_categories_link` (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `NAME_ID` int NOT NULL,
  `PUBLISHER_ID` int NOT NULL,
  `VERSION_ID` int NOT NULL,
  `CATEGORY_ID` int NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `NAME_ID` (`NAME_ID`),
  KEY `PUBLISHER_ID` (`PUBLISHER_ID`),
  KEY `VERSION_ID` (`VERSION_ID`),
  KEY `CATEGORY_ID` (`CATEGORY_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_categories_link`
--

LOCK TABLES `software_categories_link` WRITE;
/*!40000 ALTER TABLE `software_categories_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_categories_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_category_exp`
--

DROP TABLE IF EXISTS `software_category_exp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `software_category_exp` (
  `CATEGORY_ID` int NOT NULL,
  `SOFTWARE_EXP` varchar(255) NOT NULL,
  `SIGN_VERSION` varchar(255) DEFAULT NULL,
  `VERSION` varchar(255) DEFAULT NULL,
  `PUBLISHER` varchar(255) DEFAULT NULL,
  `ID` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`ID`),
  KEY `CATEGORY_ID` (`CATEGORY_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_category_exp`
--

LOCK TABLES `software_category_exp` WRITE;
/*!40000 ALTER TABLE `software_category_exp` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_category_exp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_link`
--

DROP TABLE IF EXISTS `software_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `software_link` (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `NAME_ID` int NOT NULL,
  `PUBLISHER_ID` int NOT NULL,
  `VERSION_ID` int NOT NULL,
  `CATEGORY_ID` int DEFAULT NULL,
  `IDENTIFIER` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `COUNT` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `NAME_ID` (`NAME_ID`),
  KEY `PUBLISHER_ID` (`PUBLISHER_ID`),
  KEY `VERSION_ID` (`VERSION_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_link`
--

LOCK TABLES `software_link` WRITE;
/*!40000 ALTER TABLE `software_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_name`
--

DROP TABLE IF EXISTS `software_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `software_name` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `NAME` (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_name`
--

LOCK TABLES `software_name` WRITE;
/*!40000 ALTER TABLE `software_name` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_name` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_publisher`
--

DROP TABLE IF EXISTS `software_publisher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `software_publisher` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `PUBLISHER` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `PUBLISHER` (`PUBLISHER`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_publisher`
--

LOCK TABLES `software_publisher` WRITE;
/*!40000 ALTER TABLE `software_publisher` DISABLE KEYS */;
INSERT INTO `software_publisher` VALUES (1,'Unavailable');
/*!40000 ALTER TABLE `software_publisher` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_version`
--

DROP TABLE IF EXISTS `software_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `software_version` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `VERSION` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `VERSION` (`VERSION`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_version`
--

LOCK TABLES `software_version` WRITE;
/*!40000 ALTER TABLE `software_version` DISABLE KEYS */;
INSERT INTO `software_version` VALUES (1,'Unavailable');
/*!40000 ALTER TABLE `software_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `softwares_name_cache`
--

DROP TABLE IF EXISTS `softwares_name_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `softwares_name_cache` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `NAME` (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `softwares_name_cache`
--

LOCK TABLES `softwares_name_cache` WRITE;
/*!40000 ALTER TABLE `softwares_name_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `softwares_name_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sounds`
--

DROP TABLE IF EXISTS `sounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sounds` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sounds`
--

LOCK TABLES `sounds` WRITE;
/*!40000 ALTER TABLE `sounds` DISABLE KEYS */;
/*!40000 ALTER TABLE `sounds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ssl_store`
--

DROP TABLE IF EXISTS `ssl_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ssl_store` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `FILE` longblob,
  `AUTHOR` varchar(255) DEFAULT NULL,
  `FILE_NAME` varchar(255) DEFAULT NULL,
  `FILE_TYPE` varchar(20) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ssl_store`
--

LOCK TABLES `ssl_store` WRITE;
/*!40000 ALTER TABLE `ssl_store` DISABLE KEYS */;
/*!40000 ALTER TABLE `ssl_store` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `storages`
--

DROP TABLE IF EXISTS `storages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `storages` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `MODEL` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `DISKSIZE` int DEFAULT NULL,
  `SERIALNUMBER` varchar(255) DEFAULT NULL,
  `FIRMWARE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `storages`
--

LOCK TABLES `storages` WRITE;
/*!40000 ALTER TABLE `storages` DISABLE KEYS */;
/*!40000 ALTER TABLE `storages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subnet`
--

DROP TABLE IF EXISTS `subnet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subnet` (
  `PK` int NOT NULL AUTO_INCREMENT,
  `NETID` varchar(15) NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `ID` varchar(255) DEFAULT NULL,
  `MASK` varchar(255) DEFAULT NULL,
  `TAG` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`PK`),
  KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subnet`
--

LOCK TABLES `subnet` WRITE;
/*!40000 ALTER TABLE `subnet` DISABLE KEYS */;
/*!40000 ALTER TABLE `subnet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `Tag` varchar(100) NOT NULL DEFAULT '',
  `Login` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`Tag`,`Login`),
  KEY `Tag` (`Tag`),
  KEY `Login` (`Login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temp_files`
--

DROP TABLE IF EXISTS `temp_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `temp_files` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `TABLE_NAME` varchar(255) DEFAULT NULL,
  `FIELDS_NAME` varchar(255) DEFAULT NULL,
  `file` longblob,
  `COMMENT` longtext,
  `AUTHOR` varchar(255) DEFAULT NULL,
  `FILE_NAME` varchar(255) DEFAULT NULL,
  `FILE_TYPE` varchar(255) DEFAULT NULL,
  `FILE_SIZE` int DEFAULT NULL,
  `ID_DDE` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temp_files`
--

LOCK TABLES `temp_files` WRITE;
/*!40000 ALTER TABLE `temp_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `temp_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usbdevices`
--

DROP TABLE IF EXISTS `usbdevices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usbdevices` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `INTERFACE` varchar(255) DEFAULT NULL,
  `MANUFACTURER` varchar(255) DEFAULT NULL,
  `SERIAL` varchar(255) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usbdevices`
--

LOCK TABLES `usbdevices` WRITE;
/*!40000 ALTER TABLE `usbdevices` DISABLE KEYS */;
/*!40000 ALTER TABLE `usbdevices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `videos`
--

DROP TABLE IF EXISTS `videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `videos` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `CHIPSET` varchar(255) DEFAULT NULL,
  `MEMORY` varchar(255) DEFAULT NULL,
  `RESOLUTION` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `videos`
--

LOCK TABLES `videos` WRITE;
/*!40000 ALTER TABLE `videos` DISABLE KEYS */;
/*!40000 ALTER TABLE `videos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `virtualmachines`
--

DROP TABLE IF EXISTS `virtualmachines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `virtualmachines` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `HARDWARE_ID` int NOT NULL,
  `NAME` varchar(255) DEFAULT NULL,
  `STATUS` varchar(255) DEFAULT NULL,
  `SUBSYSTEM` varchar(255) DEFAULT NULL,
  `VMTYPE` varchar(255) DEFAULT NULL,
  `UUID` varchar(255) DEFAULT NULL,
  `VCPU` int DEFAULT NULL,
  `MEMORY` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `HARDWARE_ID` (`HARDWARE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `virtualmachines`
--

LOCK TABLES `virtualmachines` WRITE;
/*!40000 ALTER TABLE `virtualmachines` DISABLE KEYS */;
/*!40000 ALTER TABLE `virtualmachines` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-10-28  8:12:15
