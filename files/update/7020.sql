-- Add ID column to software_category_exp table
UNLOCK TABLES;
ALTER TABLE `software_category_exp` ADD `ID` INTEGER NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`ID`);
