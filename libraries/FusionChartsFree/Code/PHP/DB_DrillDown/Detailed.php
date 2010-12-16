<?php
//We've included ../Includes/FusionCharts.php, which contains functions
//to help us easily embed the charts.
include("../Includes/FusionCharts.php");
include("../Includes/DBConn.php");
//We've also included ../Includes/FC_Colors.asp, having a list of colors
//to apply different colors to the chart's columns. We provide a function for it - getFCColor()
include("../Includes/FC_Colors.php");

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
    $FactoryId = $_GET['FactoryId'];

    //Generate the graph element string
    $strXML = "<graph caption='Factory " . $FactoryId . " Output ' subcaption='(In Units)' xAxisName='Date' formatNumberScale='0' decimalPrecision='0'>";

    // Connet to the DB
    $link = connectToDB();

    //Now, we get the data for that factory
    $strQuery = "select * from Factory_Output where FactoryId=" . $FactoryId;
    $result = mysql_query($strQuery) or die(mysql_error());
    
    //Iterate through each factory
    if ($result) {
        while($ors = mysql_fetch_array($result)) {
            //Here, we convert date into a more readable form for set name.
            $strXML .= "<set name='" . datePart("d",$ors['DatePro']) . "/" . datePart("m",$ors['DatePro']) . "' value='" . $ors['Quantity'] . "' color='" . getFCColor() . "'/>";
        }
    }
    mysql_close($link);

    //Close <graph> element
    $strXML .= "</graph>";
	
    //Create the chart - Column 2D Chart with data from strXML
	echo renderChart("../../FusionCharts/FCF_Column2D.swf", "", $strXML, "FactoryDetailed", 600, 300);
?>
<BR>
<a href='Default.php'>Back to Summary</a>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>