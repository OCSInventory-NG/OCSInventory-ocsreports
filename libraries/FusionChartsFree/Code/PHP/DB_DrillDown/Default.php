<?php
//We've included ../Includes/FusionCharts.php and ../Includes/DBConn.php, which contains
//functions to help us easily embed the charts and connect to a database.
include("../Includes/FusionCharts.php");
include("../Includes/DBConn.php");
?>
<HTML>
<HEAD>
	<TITLE>
	FusionCharts Free - Database and Drill-Down Example
	</TITLE>
	<?php
	//You need to include the following JS file, if you intend to embed the chart using JavaScript.
	//Embedding using JavaScripts avoids the "Click to Activate..." issue in Internet Explorer
	//When you make your own charts, make sure that the path to this JS file is correct. Else, you would get JavaScript errors.
	?>	
	<SCRIPT LANGUAGE="Javascript" SRC="../../FusionCharts/FusionCharts.js"></SCRIPT>
	<style type="text/css">
	<!--
	body {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
	}
	.text{
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
	}
	-->
	</style>
</HEAD>
<BODY>

<CENTER>
<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> - Database and Drill-Down Example</h2>
<h4>Click on any pie slice to see detailed data.</h4>

<?php
    //In this example, we show how to connect FusionCharts to a database.
    //For the sake of ease, we've used an MySQL databases containing two
    //tables.

    // Connect to the DB
    $link = connectToDB();

    //$strXML will be used to store the entire XML document generated
    //Generate the graph element
    $strXML = "<graph caption='Factory Output report' subCaption='By Quantity' pieSliceDepth='30' showBorder='1' showNames='1' formatNumberScale='0' numberSuffix=' Units' >";

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
            //Note that we're setting link as Detailed.php?FactoryId=<<FactoryId>>
            $strXML .= "<set name='" . $ors['FactoryName'] . "' value='" . $ors2['TotOutput'] . "' link='" . urlencode("Detailed.php?FactoryId=" . $ors['FactoryId']) . "'/>";
            //free the resultset
            mysql_free_result($result2);
        }
    }
    mysql_close($link);


    //Finally, close <graph> element
    $strXML .= "</graph>";

    //Create the chart - Pie 3D Chart with data from strXML
    echo renderChart("../../FusionCharts/FCF_Pie3D.swf", "", $strXML, "FactorySum", 650, 450);
?>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>