<?php
    echo pack("CCC",0xef,0xbb,0xbf);
    	
	// FusionCharts Class for XML Generation
	include("../../Includes/FusionCharts_Gen.php");

	/*
		
	Steps to ensure UTF8 xml output for FusionCharts:
		1. Output the BOM bytes 0xef 0xbb 0xbf as shown above in the first few lines
		2. Put the xml declaration <?xml version='1.0' encoding='UTF-8'?> immediately after the output from previous step.
		3. Declare contentType to be text/xml, charSet.
		4. Use getBytes to get the data from UTF field in the database and to convert it into String, use new String(bytes,"UTF-8")
	Do not output anything other than the BOM, xml declaration and the xml itself. (no empty lines too!)
	*/ 
		
    # Create a Column 3D chart object 
 	$FC = new FusionCharts("Column3D","650","450"); 

	   
	// Store Chart attributes in a variable
	$strParam="caption=Ventes mensuelles;xAxisName=Mois;yAxisName=Units; decimalPrecision=0;formatNumberScale=0";
	
	#  Set chart attributes
 	$FC->setChartParams($strParam);
	
	# Add Chart Data 
	$FC->addChartData("462","name=Janvier");
	$FC->addChartData("857","name=");
	$FC->addChartData("671","name=Mars");
    $FC->addChartData("494","name=");
    $FC->addChartData("761","name=Mai");
    $FC->addChartData("960","name=");
	$FC->addChartData("629","name=Juillet");
    $FC->addChartData("622","name=");
    $FC->addChartData("376","name=Septembre");
    $FC->addChartData("494","name=");
    $FC->addChartData("761","name=Novembre");
    $FC->addChartData("960","name=");

	
    //Set Proper output content-type and charset
    header('Content-type: text/xml;charset=UTF-8');
	
    //Just write out the XML data
    //NOTE THAT THIS PAGE DOESN'T CONTAIN ANY HTML TAG, WHATSOEVER
?>
<?xml version='1.0' encoding='UTF-8'?><?php  echo $FC->getXML(); ?>