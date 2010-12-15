<?php
    echo pack("CCC",0xef,0xbb,0xbf);
    //We've included  ../Includes/DBConn.php, which contains functions
    //to help us easily connect to a database.
    include("../Includes/DBConn.php");
	
	// FusionCharts Class for XML Generation
	include("../Includes/FusionCharts_Gen.php");

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

    # Create a Pie 3D chart object 
 	$FC = new FusionCharts("Pie3D","650","450"); 

	# Set Relative Path of swf file.
 	$FC->setSwfPath("../../FusionCharts/");
	
    // Store Chart attributes in a variable
	$strParam="caption=工場出力レポート;subCaption=量で;decimalPrecision=0;showNames=1;numberSuffix= Units;pieSliceDepth=30";
	
	#  Set chart attributes
 	$FC->setChartParams($strParam);
	
    // Fetch all factory records
    $strQuery = "select a.FactoryId, a.FactoryName, sum(b.Quantity) as TotOutput from Japanese_Factory_Master a,Factory_Output b where a.FactoryId=b.FactoryId group by a.FactoryId, a.FactoryName";
    $result = mysql_query($strQuery) or die(mysql_error());
    
    //Iterate through each factory
    if ($result) {
        // Convert Result set to FusionCharts Data     
        $FC->addDataFromDatabase($result, "TotOutput", "FactoryName");
    }
    //free the resultset
    mysql_free_result($result);
    mysql_close($link);

	
    //Set Proper output content-type and charset
    header('Content-type: text/xml;charset=UTF-8');
	
    //Just write out the XML data
    //NOTE THAT THIS PAGE DOESN'T CONTAIN ANY HTML TAG, WHATSOEVER
?>
<?xml version='1.0' encoding='UTF-8'?><?php  echo $FC->getXML(); ?>