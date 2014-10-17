CREATE TABLE batteries (
   `ID` int(11) not null AUTO_INCREMENT,
   `HARDWARE_ID` int(11), 
   `LOCATION` varchar(255) default null, 
   `MANUFACTURER` varchar(255) default null, 
   `MANUFACTURER_DATE` varchar(10) default null, 
   `SERIALNUMBER` varchar(255) default null, 
   `NAME` varchar(255) default null, 
   `CHEMISTRY` varchar(20) default null, 
   `DESIGNCAPACITY` varchar(10) default null, 
   `DESIGNVOLTAGE` varchar(20) default null, 
   `SBDSVERSION` varchar(255) default null, 
   `MAXERROR` int(10) default null, 
   `OEMSPECIFIC` varchar(255) default null, 
   PRIMARY KEY (`ID`,`HARDWARE_ID`),
   key `NAME` (`NAME`),
   key `MANUFACTURER` (`MANUFACTURER`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;
