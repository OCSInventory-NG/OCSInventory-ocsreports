<?php
    //We've included  ../Includes/DBConn.php, which contains functions
    //to help us easily connect to a database.
    include("../Includes/DBConn.php");
    //We've included ../Includes/FusionCharts_Gen.php, which FusionCharts PHP Class
    //to help us easily embed the charts.
    include("../Includes/FusionCharts_Gen.php");

    //This page generates the XML data for the Pie Chart contained in
    //Default.php. 	
	
    //For the sake of ease, we've used an MySQL databases containing two
    //tables.. 
		
    //Connect to the Database
    $link = connectToDB();

	# Create a pie 3d chart object 
 	$FC = new FusionCharts("Pie3D","650","450"); 

	# Set Relative Path of swf file.
 	$FC->setSwfPath("../../FusionCharts/");
	
	#store chart attributes in a variable
  	$strParam="caption=Factory Output report;subCaption=By Quantity;pieSliceDepth=30;showBorder=1;showNames=1;formatNumberScale=0;numberSuffix= Units;decimalPrecision=0";
 	#Set chart attributes
 	$FC->setChartParams($strParam);
	
    
  	// Fetch all factory records usins SQL Query
	//Store chart data values in 'total' column/field and category names in 'FactoryName'
	$strQuery = "select a.FactoryID, b.FactoryName, sum(a.Quantity) as total from Factory_output a, Factory_Master b where a.FactoryId=b.FactoryId group by a.FactoryId,b.FactoryName";
	$result = mysql_query($strQuery) or die(mysql_error());
    
	//Pass the SQL Query result to the FusionCharts PHP Class function 
	//along with field/column names that are storing chart values and corresponding category names
	//to set chart data from database
	if ($result) {
		$FC->addDataFromDatabase($result, "total", "FactoryName");
	}
    mysql_close($link);
		
    //Set Proper output content-type
    header('Content-type: text/xml');
	
    //Just write out the XML data
    //NOTE THAT THIS PAGE DOESN'T CONTAIN ANY HTML TAG, WHATSOEVER
    print  $FC->getXML();
?>
