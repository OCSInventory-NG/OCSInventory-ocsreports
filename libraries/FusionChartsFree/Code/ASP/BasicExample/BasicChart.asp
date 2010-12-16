<%@ Language=VBScript %>
<HTML>
<HEAD>
	<TITLE>
	FusionCharts Free - Simple Column 3D Chart
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
	<%
	'We've included ../Includes/FusionCharts.asp, which contains functions
	'to help us easily embed the charts.
	%>
<!-- #INCLUDE FILE="../Includes/FusionCharts.asp" -->
<BODY>

<CENTER>
<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> Examples</h2>
<h4>Basic example using pre-built Data.xml</h4>
<%
	
	'This page demonstrates the ease of generating charts using FusionCharts.
	'For this chart, we've used a pre-defined Data.xml (contained in /Data/ folder)
	'Ideally, you would NOT use a physical data file. Instead you'll have 
	'your own ASP scripts virtually relay the XML data document. Such examples are also present.
	'For a head-start, we've kept this example very simple.
	
	
	'Create the chart - Column 3D Chart with data from Data/Data.xml
	Call renderChartHTML("../../FusionCharts/FCF_Column3D.swf", "Data/Data.xml", "", "myFirst", 600, 300)
	
%>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<BR><H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>