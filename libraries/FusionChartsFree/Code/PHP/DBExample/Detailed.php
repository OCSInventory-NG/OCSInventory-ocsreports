<?php
//We've included ../Includes/FusionCharts.php, which contains functions
//to help us easily embed the charts.
include("../Includes/FusionCharts.php");
include("../Includes/DBConn.php");
?>
<HTML>
<HEAD>
	<TITLE>
	FusionCharts - Database and Drill-Down Example
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
<h2>FusionCharts Database and Drill-Down Example</h2>
<h4>Detailed report for the factory</h4>
<?php
    //This page is invoked from Default.php. When the user clicks on a pie
    //slice in Default.php, the factory Id is passed to this page. We need
    //to get that factory id, get information from database and then show
    //a detailed chart.

    //Request the factory Id from Querystring
    $FactoryId = $_GET['FactoryId'];

    //Generate the chart element string
    $strXML = "<chart palette='2' caption='Factory " . $FactoryId ." Output ' subcaption='(In Units)' xAxisName='Date' showValues='1' labelStep='2' >";

    // Connet to the DB
    $link = connectToDB();

    //Now, we get the data for that factory
    $strQuery = "select * from Factory_Output where FactoryId=" . $FactoryId;
    $result = mysql_query($strQuery) or die(mysql_error());
    
    //Iterate through each factory
    if ($result) {
        while($ors = mysql_fetch_array($result)) {
            //Here, we convert date into a more readable form for set label.
            $strXML .= "<set label='" . datePart("d",$ors['DatePro']) . "/" . datePart("m",$ors['DatePro']) . "' value='" . $ors['Quantity'] . "'/>";
        }
    }
    mysql_close($link);

    //Close <chart> element
    $strXML .= "</chart>";
	
    //Create the chart - Column 2D Chart with data from strXML
    echo renderChart("../../FusionCharts/Column2D.swf", "", $strXML, "FactoryDetailed", 600, 300);
?>
<BR>
<a href='Default.php?animate=0'>Back to Summary</a>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
</CENTER>
</BODY>
</HTML>