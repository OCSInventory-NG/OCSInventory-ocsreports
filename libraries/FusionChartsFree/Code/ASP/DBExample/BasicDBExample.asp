<%@ Language=VBScript %>
<HTML>
<HEAD>
	<TITLE>	FusionCharts Free - Database Example	</TITLE>
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
	<!-- #INCLUDE FILE="../Includes/DBConn.asp" -->
<BODY>

<CENTER>
<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> Database Example</h2>

<%
	'In this example, we show how to connect FusionCharts to a database.
	'For the sake of ease, we've used an Access database which is present in
	'../DB/FactoryDB.mdb. It just contains two tables, which are linked to each
	'other. 
		
	'Database Objects - Initialization
	Dim oRs, oRs2, strQuery
	'strXML will be used to store the entire XML document generated
	Dim strXML
	
	'Create the recordset to retrieve data
	Set oRs = Server.CreateObject("ADODB.Recordset")

	'Generate the graph element
	strXML = "<graph caption='Factory Output report' subCaption='By Quantity' decimalPrecision='0' showNames='1'  numberSuffix=' Units'  pieSliceDepth='30' formatNumberScale='0'>"
	
	'Iterate through each factory
	strQuery = "select * from Factory_Master"
	Set oRs = oConn.Execute(strQuery)
	
	While Not oRs.Eof
		'Now create second recordset to get details for this factory
		Set oRs2 = Server.CreateObject("ADODB.Recordset")
		strQuery = "select sum(Quantity) as TotOutput from Factory_Output where FactoryId=" & ors("FactoryId")
		Set oRs2 = oConn.Execute(strQuery)				
		'Generate <set name='..' value='..' />		
		strXML = strXML & "<set name='" & ors("FactoryName") & "' value='" & ors2("TotOutput") & "' />"
		'Close recordset
		Set oRs2 = Nothing
		oRs.MoveNext
	Wend
	'Finally, close <graph> element
	strXML = strXML & "</graph>"
	Set oRs = nothing
	
	'Create the chart - Pie 3D Chart with data from strXML
	Call renderChart("../../FusionCharts/FCF_Pie3D.swf", "", strXML, "FactorySum", 650, 450)
	
%>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<BR><H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>