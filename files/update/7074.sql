-- Drop primary index on MAC to accept multiple same macaddress for different ipaddress
DROP INDEX `PRIMARY` ON `netmap`;
-- Add primary id column
ALTER TABLE `netmap` ADD COLUMN `ID` bigint NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;
-- Add a new classic index on MAC
ALTER TABLE `netmap` ADD INDEX MAC (`MAC`);
-- Add IP column for network_devices
ALTER TABLE `network_devices` ADD COLUMN `IP` varchar(255) DEFAULT NULL AFTER `MACADDR`;
-- Add index for IP
ALTER TABLE `network_devices` ADD INDEX IP (`IP`);