<?php
//We've included ../Includes/FusionCharts_Gen.php, which contains FusionCharts PHP Class
//to help us easily embed the charts.
include("../Includes/FusionCharts_Gen.php");
?>
<HTML>
<HEAD>
	<TITLE>
	FusionCharts Free - Form Based Data Charting Example
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
<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> Form-Based Data Example</h2>
<h4>Restaurant Sales Chart below</h4>
<?php
	//We first request the data from the form (Default.php)
	$intSoups = $_REQUEST['Soups'];
	$intSalads = $_REQUEST['Salads'];
	$intSandwiches = $_REQUEST['Sandwiches'];
	$intBeverages = $_REQUEST['Beverages'];
	$intDesserts = $_REQUEST['Desserts'];
	//In this example, we're directly showing this data back on chart.
	//In your apps, you can do the required processing and then show the 
	//relevant data only.
	
	//Now that we've the data in variables, we need to convert this into chart data using
	//FusionCharts PHP Class
	
	# Create Pie 3d chart object 
 	$FC = new FusionCharts("Pie3D","600","300"); 

	# Set Relative Path of swf file. 
 	$FC->setSwfPath("../../FusionCharts/");
	
	
	//Store Chart attributes in a variable
	$strParam="caption=Sales by Product Category;subCaption=For this week;showPercentValues=1;  showPercentageInLabel=1;pieSliceDepth=25;showBorder=1;decimalPrecision=0;showNames=1";

 	#  Set chart attributes
 	$FC->setChartParams($strParam);
	
	//Add all data
	$FC->addChartData($intSoups,"name=Soups");
	$FC->addChartData($intSalads,"name=Salads");
	$FC->addChartData($intSandwiches,"name=Sandwitches");
	$FC->addChartData($intBeverages,"name=Beverages");
	$FC->addChartData($intDesserts,"name=Desserts");
		
	//Create the chart 
 	$FC->renderChart();
?>
<a href='javascript:history.go(-1);'>Enter data again</a>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>