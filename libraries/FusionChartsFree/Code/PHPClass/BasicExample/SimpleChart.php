<?php
//We've included ../Includes/FusionCharts.php, which contains functions
//to help us easily embed the charts.
include("../Includes/FusionCharts.php");
?>
<HTML>
<HEAD>
        <TITLE>
        FusionCharts Free - Simple Column 3D Chart using dataURL method
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
<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> Examples</h2>
<h4>Simple Column 3D Chart using dataURL method</h4>
<?php
        
        //This page demonstrates the ease of generating charts using FusionCharts PHP Class.
        //For this chart, we've used a Data.php which uses FusionCharts PHP Class (contained in /Data/ folder)
        //This file will generate the chart  XML and pass it to the chart
        //We will use FusionCharts PHP function - renderChart() to render the chart usin the XML
        //For a head-start, we've kept this example very simple.
        
        
        //Create the chart - Column 3D Chart with data from Data/Data.xml
        echo renderChart("../../FusionCharts/FCF_Column3D.swf", "Data/Data.php", "", "myFirst", 600, 300, false, false);
?>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>

</CENTER>
</BODY>
</HTML>
