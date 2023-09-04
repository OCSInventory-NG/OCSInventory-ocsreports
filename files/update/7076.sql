-- Adding a composite index to the table `software` for performance improvement

ALTER TABLE `software` ADD KEY `HARDWARE_ID_2` (`HARDWARE_ID`,`NAME_ID`,`VERSION_ID`) USING BTREE;