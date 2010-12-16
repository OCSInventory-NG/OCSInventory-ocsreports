<?php
//We've included ../../Includes/FusionCharts_Gen.php - FusionCharts PHP Class
//to help us easily embed the charts.
include("../../Includes/FusionCharts_Gen.php");
?>

<?php
	
	//This page demonstrates the ease of generating charts using FusionCharts PHPClass.
	//We creata a FusionCharts object instance
	//Set chart values and configurations and retunns the XML using getXML() funciton
	//and write it to the response stream to build the XML
	
	//Here, we've kept this example very simple.
	
	# Create column 3d chart object 
 	$FC = new FusionCharts("column3D","600","300"); 

	# Set Relative Path of swf file.
 	$FC->setSwfPath("../../FusionCharts/");
		
	# Store Chart attributes in a variable
	$strParam="caption=Monthly Unit Sales;xAxisName=Month;yAxisName=Units;decimalPrecision=0; formatNumberScale=0;showNames=1";
 	#  Set chart attributes
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
	
	# get the chart XML 
	$strXML=$FC->getXML();
	
	//set content type as XML
	header('Content-type: text/xml');
	#Return the chart XML for Column 3D Chart 
	print $strXML;
?>
