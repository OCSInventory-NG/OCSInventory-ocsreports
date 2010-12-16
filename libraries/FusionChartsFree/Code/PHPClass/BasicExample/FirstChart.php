<?php
//We've included ../Includes/FusionCharts_Gen.php, which contains FusionCharts PHP Class
//to help us easily embed the charts.
include("../Includes/FusionCharts_Gen.php");
?>
<HTML>
<HEAD>
	<TITLE>
	FusionCharts Free - Simple Column 3D Chart 
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
	-->
	</style>
</HEAD>


<BODY>

<CENTER>
<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> Examples</h2>
<h4>Simple Column 3D Chart</h4>
<p>&nbsp;</p>
<?php
	//This page demonstrates the ease of generating charts using FusionCharts PHPClass.
	//For this chart, we've cread a chart  object used FusionCharts PHP Class
	//supply chart data and configurations to it and render chart using the instance
	
	//Here, we've kept this example very simple.
	
	# Create column 3d chart object 
 	$FC = new FusionCharts("Column3D","600","300"); 

	# Set Relative Path of swf file.
 	$FC->setSwfPath("../../FusionCharts/");
		
	# Store Chart attributes in a variable
	$strParam="caption=Monthly Unit Sales;xAxisName=Month;yAxisName=Units;decimalPrecision=0; formatNumberScale=0";

 	#  Set Chart attributes
 	$FC->setChartParams($strParam);
	
	#add chart data values and category names
	$FC->addChartData("462","name=Jan");
	$FC->addChartData("857","name=Feb");
	$FC->addChartData("671","name=Mar");
	$FC->addChartData("494","name=Apr");
	$FC->addChartData("761","name=May");
	$FC->addChartData("960","name=Jun");
	$FC->addChartData("629","name=Jul");
	$FC->addChartData("622","name=Aug");
	$FC->addChartData("376","name=Sep");
	$FC->addChartData("494","name=Oct");
	$FC->addChartData("761","name=Nov");
	$FC->addChartData("960","name=Dec");
	

	# Render  Chart 
 	$FC->renderChart();

?>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>