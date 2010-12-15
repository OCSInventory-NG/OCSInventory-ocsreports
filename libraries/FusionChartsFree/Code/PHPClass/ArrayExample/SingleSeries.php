<?php
//We've included ../Includes/FusionCharts_Gen.php, which contains FusionCharts PHP Class
//to help us easily embed the charts.
include("../Includes/FusionCharts_Gen.php");
?>
<HTML>
<HEAD>
	<TITLE>
	FusionCharts Free - Array Example using Single Series Column 3D Chart
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
<h4>Plotting single series chart from data contained in Array.</h4>
<?php
	//In this example, using FusionCharts PHP Class we plot a single series chart 
	//from data contained in an array. 
	
	//The array needs to have two columns - first one for data labels/names
	//and the next one for data values.
	
	//Let's store the sales data for 6 products in our array). We also store
	//the name of products. 
	//Store Name of Products
	$arrData[0][0] = "Product A";
	$arrData[1][0] = "Product B";
	$arrData[2][0] = "Product C";
	$arrData[3][0] = "Product D";
	$arrData[4][0] = "Product E";
	$arrData[5][0] = "Product F";
	//Store sales data
	$arrData[0][1] = 567500;
	$arrData[1][1] = 815300;
	$arrData[2][1] = 556800;
	$arrData[3][1] = 734500;
	$arrData[4][1] = 676800;
	$arrData[5][1] = 648500;

	# Create FusionCharts PHP Class object for single series column3d chart
 	$FC = new FusionCharts("Column3D","600","300"); 

	# Set Relative Path of swf file.
 	$FC->setSwfPath("../../FusionCharts/");
		
	# store chart attributes in a variable
	$strParam="caption=Sales by Product;numberPrefix=$;formatNumberScale=0;decimalPrecision=0";

 	#  Set chart attributes
 	$FC->setChartParams($strParam);
	
	
	## call FusionCharts PHP Class Function to add data from the array 
	$FC->addChartDataFromArray($arrData);
	
	# Render the chart
 	$FC->renderChart();
?>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>

</CENTER>
</BODY>
</HTML>