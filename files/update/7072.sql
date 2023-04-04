-- write default values for SCAN_TYPE_IPDISCOVER SCAN_TYPE_SNMP and SCAN_ARP_BANDWIDTH config options
INSERT INTO `config` VALUES('SCAN_TYPE_IPDISCOVER',0,'ICMP','IPD scan type');
INSERT INTO `config` VALUES('SCAN_TYPE_SNMP',0,'ICMP','SNMP scan type');
INSERT INTO `config` VALUES('SCAN_ARP_BANDWIDTH',256,'','Arp scan bandwidth in Kb/s');