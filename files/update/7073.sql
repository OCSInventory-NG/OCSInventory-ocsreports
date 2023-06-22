-- Create the new config_ldap table
CREATE TABLE `config_ldap` (
    `NAME` varchar(50) NOT NULL,
    `IVALUE` int default NULL,
    `TVALUE` text default NULL,
    `COMMENTS` text default NULL,
    PRIMARY KEY (`NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Copy all ldap config from config table
INSERT INTO config_ldap SELECT * FROM config WHERE NAME LIKE '%CONEX%';

-- Remove all ldap config from config table
DELETE FROM config WHERE NAME LIKE '%CONEX%';