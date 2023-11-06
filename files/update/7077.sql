-- Add IPD reconciliation field to snmp_configs table
UNLOCK TABLES;
ALTER TABLE `snmp_configs` ADD COLUMN `IPD_RECONCILIATION` VARCHAR(255) DEFAULT NULL;