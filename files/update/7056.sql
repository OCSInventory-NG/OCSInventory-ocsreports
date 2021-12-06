-- Add missing primary keys

-- cve_search
ALTER TABLE `cve_search` 
ADD COLUMN `ID` int NOT NULL AUTO_INCREMENT FIRST, 
ADD PRIMARY KEY (`ID`);

-- cve_search_computer
ALTER TABLE `cve_search_computer` 
ADD COLUMN `ID` int NOT NULL AUTO_INCREMENT FIRST, 
ADD PRIMARY KEY (`ID`);

-- saas
ALTER TABLE `saas` 
ADD COLUMN `ID` int NOT NULL AUTO_INCREMENT FIRST, 
ADD PRIMARY KEY (`ID`);

-- extensions
ALTER TABLE `extensions` ADD PRIMARY KEY (`id`);

-- subnet
ALTER TABLE `subnet` 
ADD COLUMN `PK` int NOT NULL AUTO_INCREMENT FIRST, 
ADD PRIMARY KEY (`PK`);

-- engine_persistent
ALTER TABLE `engine_persistent` ADD PRIMARY KEY (`ID`), 
DROP KEY `ID`;