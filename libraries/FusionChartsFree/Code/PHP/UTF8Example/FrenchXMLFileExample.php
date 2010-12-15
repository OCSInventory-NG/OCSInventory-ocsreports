<?php
//We've included ../Includes/FusionCharts.php, which contains functions
//to help us easily embed the charts.
include("../Includes/FusionCharts.php");
?>
<HTML>
<HEAD>
        <TITLE>
        FusionCharts Free - UTF8 Français (French) Example
        </TITLE>
        <?php
        //You need to include the following JS file, if you intend to embed the chart using JavaScript.
        //Embedding using JavaScripts avoids the "Click to Activate..." issue in Internet Explorer
        //When you make your own charts, make sure that the path to this JS file is correct. Else, you 
        //would get JavaScript errors.
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
<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> UTF8 Français (French) Example</h2>
<h4>Basic example using pre-built FrenchData.xml</h4>
<?php
       /*
	In this example, we show how to use UTF characters in charts created with FusionCharts 
	Here, the XML data for the chart is present in Data/FrenchData.xml. 
	The xml file should be created and saved with an editor
	which places the UTF8 BOM. The first line of the xml should contain the
	xml declaration like this: <?xml version="1.0" encoding="UTF-8" ?>
	*/
        
        
        //Create the chart - Column 3D Chart with data from Data/FrenchData.xml
        echo renderChart("../../FusionCharts/FCF_Column3D.swf", "Data/FrenchData.xml", "", "FrenchChart", 600, 300);
?>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>

</CENTER>
</BODY>
</HTML>
