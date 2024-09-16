UNLOCK TABLES;

-- Drop the existing index on TVALUE
ALTER TABLE devices DROP INDEX TVALUE;

-- Update column to TEXT and recreate the index
ALTER TABLE devices MODIFY COLUMN TVALUE TEXT; 
ADD INDEX TVALUE (TVALUE(255));
