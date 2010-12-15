<%@ Page Language="VB" AutoEventWireup="false" CodeFile="Default.aspx.vb" Inherits="DB_DrillDown_Default" %>

<html>
<head>
    <title>FusionCharts Free - Database and Drill-Down Example </title>
    <%
        'You need to include the following JS file, if you intend to embed the chart using JavaScript.
        'Embedding using JavaScripts avoids the "Click to Activatee that the path to this JS file is correct. Else, you would get JavaScript errors.
    %>

    <script language="Javascript" type="text/javascript" src="../FusionCharts/FusionCharts.js"></script>

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
</head>
<body>
    <center>
        <h2>
            <a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> Database
            and Drill-Down Example</h2>
        <h4>
            Click on any pie slice to see detailed data.</h4>
        <asp:Literal ID="FCLiteral" runat="server"></asp:Literal>
        <br />
        <br />
        <a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
        <br />
        <h5>
            <a href='../default.aspx'>&laquo; Back to list of examples</a></h5>
    </center>
</body>
</html>
