-- save current setting of sql_mode
SET @old_sql_mode := @@sql_mode ;

-- derive a new value by removing NO_ZERO_DATE and NO_ZERO_IN_DATE
SET @new_sql_mode := @old_sql_mode ;
SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_DATE,'  ,','));
SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_IN_DATE,',','));
SET @@sql_mode := @new_sql_mode ;

ALTER TABLE software MODIFY COLUMN ID BIGINT(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE software_link MODIFY COLUMN ID BIGINT(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE software_categories_link MODIFY COLUMN ID BIGINT(20) NOT NULL AUTO_INCREMENT;

-- revert back to the original sql_mode setting
SET @@sql_mode := @old_sql_mode ;