<?php
    echo pack("CCC",0xef,0xbb,0xbf);
    //We've included  ../Includes/DBConn.php, which contains functions
    //to help us easily connect to a database.
    include("../Includes/DBConn.php");

	/*
	This page generates the XML data for the Pie Chart contained in JapaneseDBExample.php. 	
	
	For the sake of ease, we've used the same database as used by other examples. 
	We have added one more table Japanese_Factory_Master with stores the names of the factory in Japanese language.
	
	Steps to ensure UTF8 xml output for FusionCharts:
		1. Output the BOM bytes 0xef 0xbb 0xbf as shown above in the first few lines
		2. Put the xml declaration <?xml version='1.0' encoding='UTF-8'?> immediately after the output from previous step.
		3. Declare contentType to be text/xml, charSet.
		4. Use getBytes to get the data from UTF field in the database and to convert it into String, use new String(bytes,"UTF-8")
	Do not output anything other than the BOM, xml declaration and the xml itself. (no empty lines too!)
	*/ 
		
    //Connect to the DB
    $link = connectToDB();

    $useUTFQuery = "SET NAMES 'utf8'";
    $utfQueryResult = mysql_query($useUTFQuery);

    //$strXML will be used to store the entire XML document generated
    //Generate the graph element
    $strXML = "<graph caption='工場出力レポート' subCaption='量で' decimalPrecision='0' showNames='1' numberSuffix=' Units' decimalPrecision='0' pieSliceDepth='30' >";
	
    // Fetch all factory records
    $strQuery = "select * from Japanese_Factory_Master";
    $result = mysql_query($strQuery) or die(mysql_error());
    
    //Iterate through each factory
    if ($result) {
        while($ors = mysql_fetch_array($result)) {
            //Now create a second query to get details for this factory
            $strQuery = "select sum(Quantity) as TotOutput from Factory_Output where FactoryId=" . $ors['FactoryId'];
            $result2 = mysql_query($strQuery) or die(mysql_error()); 
            $ors2 = mysql_fetch_array($result2);
            //Generate <set name='..' value='..'/>     
            $strXML .= "<set name='" . $ors['FactoryName'] . "' value='" . $ors2['TotOutput'] . "' />";
            //free the resultset
            mysql_free_result($result2);
        }
    }
    mysql_close($link);

    //Finally, close <graph> element
    $strXML .= "</graph>";
		
    //Set Proper output content-type and charset
    header('Content-type: text/xml;charset=UTF-8');
	
    //Just write out the XML data
    //NOTE THAT THIS PAGE DOESN'T CONTAIN ANY HTML TAG, WHATSOEVER
?>
<?xml version='1.0' encoding='UTF-8'?><?php  echo $strXML; ?>