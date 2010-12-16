<?php
//We've included ../Includes/FusionCharts.php, which contains functions
//to help us easily embed the charts.
include("../Includes/FusionCharts.php");
?>
<HTML>
<HEAD>
	<TITLE>
	FusionCharts Free - Simple Column 3D Chart using dataXML method
	</TITLE>	
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
<h4>Basic example using dataXML method (with XML data hard-coded in PHP page itself)</h4>
<p>If you view the source of this page, you'll see that the XML data is present in this same page (inside HTML code). We're not calling any external XML (or script) files to serve XML data. dataXML method is ideal when you've to plot small amounts of data.</p>
<?php
	
	//This page demonstrates the ease of generating charts using FusionCharts.
	//For this chart, we've used a string variable to contain our entire XML data.
	
	//Ideally, you would generate XML data documents at run-time, after interfacing with
	//forms or databases etc.Such examples are also present.
	//Here, we've kept this example very simple.
	
	//Create an XML data document in a string variable
	$strXML = "<graph caption='Monthly Unit Sales' xAxisName='Month' yAxisName='Units' decimalPrecision='0' formatNumberScale='0'>";
	$strXML .= "<set name='Jan' value='462' color='AFD8F8' />";
	$strXML .= "<set name='Feb' value='857' color='F6BD0F' />";
	$strXML .= "<set name='Mar' value='671' color='8BBA00' />";
	$strXML .= "<set name='Apr' value='494' color='FF8E46'/>";
	$strXML .= "<set name='May' value='761' color='008E8E'/>";
	$strXML .= "<set name='Jun' value='960' color='D64646'/>";
	$strXML .= "<set name='Jul' value='629' color='8E468E'/>";
	$strXML .= "<set name='Aug' value='622' color='588526'/>";
	$strXML .= "<set name='Sep' value='376' color='B3AA00'/>";
	$strXML .= "<set name='Oct' value='494' color='008ED6'/>";
	$strXML .= "<set name='Nov' value='761' color='9D080D'/>";
	$strXML .= "<set name='Dec' value='960' color='A186BE'/>";
	$strXML .=  "</graph>";
	
	//Create the chart - Column 3D Chart with data from strXML variable using dataXML method
	echo renderChartHTML("../../FusionCharts/FCF_Column3D.swf", "", $strXML, "myNext", 600, 300);
?>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>