<%@ Language=VBScript %>
<HTML>
<HEAD>
	<TITLE>
	FusionCharts Free - dataURL and Database  Example
	</TITLE>
	<%
	'You need to include the following JS file, if you intend to embed the chart using JavaScript.
	'Embedding using JavaScripts avoids the "Click to Activate..." issue in Internet Explorer
	'When you make your own charts, make sure that the path to this JS file is correct. Else, you would get JavaScript errors.
	%>	
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
	<%
	'We've included ../Includes/FusionCharts.asp, which contains functions
	'to help us easily embed the charts.
	%>
	<!-- #INCLUDE FILE="../Includes/FusionCharts.asp" -->	
<BODY>

<CENTER>
<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> - dataURL and Database</h2>

<%
	'In this example, we show how to connect FusionCharts to a database 
	'using dataURL method. In our other examples, we've used dataXML method
	'where the XML is generated in the same page as chart. Here, the XML data
	'for the chart would be generated in PieData.asp.
	
	'For the sake of ease, we've used an Access database which is present in
	'../DB/FactoryDB.mdb. It just contains two tables, which are linked to each
	'other.
		
	'Variable to contain dataURL
	Dim strDataURL	
	'the asp script in piedata.asp interacts with the database, 
	'converts the data into proper XML form and finally 
	'relays XML data document to the chart
	strDataURL = "PieData.asp"
	
	'Create the chart - Pie 3D Chart with dataURL as strDataURL
	Call renderChart("../../FusionCharts/FCF_Pie3D.swf", strDataURL, "", "FactorySum", 650, 450)

%>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<BR><H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>