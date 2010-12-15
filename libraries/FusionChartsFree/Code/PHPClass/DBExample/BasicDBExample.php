<?php
//We've included ../Includes/FusionCharts_Gen.php, which contains
//FusionCharts PHP Class to help us easily embed charts 
//We've also used ../Includes/DBConn.php to easily connect to a database.
include("../Includes/FusionCharts_Gen.php");
include("../Includes/DBConn.php");

?>
<HTML>
<HEAD>
	<TITLE>
	FusionCharts Free - Database Example
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
<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> -Database and Drill-Down Example</h2>


<?php
	//In this example, we show how to connect FusionCharts to a database.
	//For the sake of ease, we've used an MySQL databases containing two
	//tables.
		
	// Connect to the Database
	$link = connectToDB();

	# Create pie 3d chart object using FusionCharts PHP Class
 	$FC = new FusionCharts("Pie3D","650","450"); 

	# Set Relative Path of swf file.
 	$FC->setSwfPath("../../FusionCharts/");
	
	//Store chart attributes in a variable for ease of use
	$strParam="caption=Factory Output report;subCaption=By Quantity;pieSliceDepth=30; showBorder=1;showNames=1;formatNumberScale=0;numberSuffix= Units;decimalPrecision=0";

 	#  Set chart attributes
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

	# Render the chart
 	$FC->renderChart();
?>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>