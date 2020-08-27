UNLOCK TABLES;

-- Add index to hardware_id
ALTER TABLE `software` ADD KEY `HARDWARE_ID` (`HARDWARE_ID`);