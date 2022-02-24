UNLOCK TABLES;
ALTER TABLE `registry_regvalue_cache` DROP INDEX `REGVALUE`; 
ALTER TABLE `registry_regvalue_cache` MODIFY `REGVALUE` TEXT;
