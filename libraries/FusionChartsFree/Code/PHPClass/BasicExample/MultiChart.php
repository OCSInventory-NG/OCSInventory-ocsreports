<?php
//We've included ../Includes/FusionCharts_Gen.php, which contains FusionCharts PHP Class
//to help us easily embed the charts.
include("../Includes/FusionCharts_Gen.php");
?>
<HTML>
<HEAD>
	<TITLE>
	FusionCharts Free - Multiple Charts on one Page
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
<h4>Multiple Charts on the same page</h4>
<?php
	
	//This page demonstrates how you can show multiple charts on the same page.
	//For this example, We have created 2 instances of the FusionCharts PHP Class
	//supplied data to both and rendered them
	

	//---------- Creating First Chart ----------------------------------------------
	# Create FusionCharts PHP object
	 $FC = new FusionCharts("Column3D","300","250"); 
	 # set the relative path of the swf file
	 $FC->setSWFPath("../../FusionCharts/");
	 
	 #  Set chart attributes
	 $strParam="caption=Weekly Sales;subcaption=Revenue;xAxisName=Week;yAxisName=Revenue;numberPrefix=$;decimalPrecision=0";
	
	 $FC->setChartParams($strParam);
	 
	 # add chart values and category names for the First Chart
	 $FC->addChartData("40800","name=Week 1");
	 $FC->addChartData("31400","name=Week 2");
	 $FC->addChartData("26700","name=Week 3");
	 $FC->addChartData("54400","name=Week 4");
	//------------------------------------------------------------------- 
	
	//----- Creating Second Chart ---------------------------------------
	# Create FusionCharts PHP object
	 $FC2 = new FusionCharts("Column3D","300","250"); 
	 # set the relative path of the swf file
	 $FC2->setSWFPath("../../FusionCharts/");
	 
	 #  Setting chart attributes
	 $strParam="caption=Weekly Sales;subcaption=Quantity;xAxisName=Week;yAxisName=Quantity;decimalPrecision=0";
	
	 $FC2->setChartParams($strParam);
	 
	 # add chart values and  category names for the second chart
	 $FC2->addChartData("32","name=Week 1");
	 $FC2->addChartData("35","name=Week 2");
	 $FC2->addChartData("26","name=Week 3");
	 $FC2->addChartData("44","name=Week 4");
	
	//------------------------------------------------------------------------- 
	 # Render First Chart
	 $FC->renderChart();
	
	 # Render Second Chart
	 $FC2->renderChart();
 
?>

<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the charts above?</a>
<H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>