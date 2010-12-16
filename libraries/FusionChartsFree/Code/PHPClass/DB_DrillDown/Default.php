<?php
//We've included ../Includes/FusionCharts_Gen.php, which contains
//FusionCharts PHP Class to help us easily embed charts 
//We've also used ../Includes/DBConn.php to easily connect to a database
include("../Includes/FusionCharts_Gen.php");
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

     # Connect to the Database
     $link = connectToDB();

	# Create a pie 3d chart object
 	$FC = new FusionCharts("Pie3D","650","450"); 

	# Set Relative Path of swf file.
 	$FC->setSwfPath("../../FusionCharts/");
	
     #Store chart attributes in a variable
   	$strParam="caption=Factory Output report;subCaption=By Quantity;pieSliceDepth=30;showBorder=1; showNames=1;formatNumberScale=0;numberSuffix= Units";

 	#  Set chart attributes
 	$FC->setChartParams($strParam);
	
  	# Fetch all factory records creating SQL query
	$strQuery = "select a.FactoryID, b.FactoryName, sum(a.Quantity) as total from Factory_output a, Factory_Master b where a.FactoryId=b.FactoryId group by a.FactoryId,b.FactoryName";
	$result = mysql_query($strQuery) or die(mysql_error());
    
	#Pass the SQL query result and Drill-Down link format to PHP Class Function
	# this function will automatically add chart data from database
	/*
	 The last parameter passed i.e. "Detailed.php?FactoryId=##FactoryID##"
	 drill down link from the current chart
	 Here, the link redirects to another PHP file Detailed.php 
	 with a query string variable -FactoryId
	 whose value would be taken from the Query result created above.
	 Any thing placed between ## and ## will be regarded 
	 as a field/column name in the SQL query result.
	 value from that column will be assingned as the query variable's value
	 Hence, for each dataplot in the chart the resultant query variable's value
	 will be different
	*/
	if ($result) {
		$FC->addDataFromDatabase($result, "total", "FactoryName","","Detailed.php?FactoryId=##FactoryID##");
	}


    mysql_close($link);

     #Create the chart 
 	$FC->renderChart();
?>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>