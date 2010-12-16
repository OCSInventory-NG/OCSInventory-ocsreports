<?php
    //We've included  ../Includes/DBConn.php, which contains functions
    //to help us easily connect to a database.
    include("../Includes/DBConn.php");

    //This page generates the XML data for the Pie Chart contained in
    //Default.php. 	
	
    //For the sake of ease, we've used an MySQL databases containing two
    //tables.. 
		
    //Connect to the DB
    $link = connectToDB();

    //$strXML will be used to store the entire XML document generated
    //Generate the graph element
    $strXML = "<graph caption='Factory Output report' subCaption='By Quantity' decimalPrecision='0' showNames='1' numberSuffix=' Units' decimalPrecision='0' pieSliceDepth='30' >";
	
    // Fetch all factory records
    $strQuery = "select * from Factory_Master";
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
		
    //Set Proper output content-type
    header('Content-type: text/xml');
	
    //Just write out the XML data
    //NOTE THAT THIS PAGE DOESN'T CONTAIN ANY HTML TAG, WHATSOEVER
    echo $strXML;
?>
