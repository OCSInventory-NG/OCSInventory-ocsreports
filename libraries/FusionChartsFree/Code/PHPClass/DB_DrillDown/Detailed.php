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
<h4>Detailed report for the factory</h4>
<?php
    //This page is invoked from Default.php. When the user clicks on a pie
    //slice in Default.php, the factory Id is passed to this page. We need
    //to get that factory id, get information from database and then show
    //a detailed chart.

    //Request the factory Id from Querystring
    $FactoryId = $_REQUEST['FactoryId'];
	$FactoryName = $_REQUEST['FactoryName'];
	
	# Create a column 3d chart object 
 	$FC = new FusionCharts("Column2D","600","300"); 

	# Set Relative Path of swf file.
 	$FC->setSwfPath("../../FusionCharts/");
	
    // Store Chart attributes in a variable
	$strParam="caption=$FactoryName Output;subcaption=(In Units);xAxisName=Date; formatNumberScale=0;decimalPrecision=0;rotateNames=1;showValues=0";

 	#  Set chart attributes
 	$FC->setChartParams($strParam);
	
    // Connet to the DataBase
    $link = connectToDB();

    //Now, we get the data for that factory 
	 //storing chart values in 'Quantity' column and category names in 'DDate'
    $strQuery = "select Quantity, DATE_FORMAT(DatePro,'%e-%b-%Y') as DDate from Factory_Output where FactoryId=" . $FactoryId;
    $result = mysql_query($strQuery) or die(mysql_error());
    
    //Pass the SQL query result to the FusionCharts PHP Class' function 
    //that will extract data from database and add to the chart.
    if ($result) {
       $FC->addDataFromDatabase($result, "Quantity", "DDate");
    }
    mysql_close($link);

    //Create the chart
 	$FC->renderChart();
?>
<BR>
<a href='Default.php'>Back to Summary</a>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>