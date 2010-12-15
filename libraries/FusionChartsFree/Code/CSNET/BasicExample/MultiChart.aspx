<%@ Page Language="C#" AutoEventWireup="true" CodeFile="MultiChart.aspx.cs" Inherits="BasicExample_MultiChart" %>

<html>
<head>
    <title>FusionCharts Free - Multiple Charts on one Page </title>
    <%
        //You need to include the following JS file, if you intend to embed the chart using JavaScript.
        //Embedding using JavaScripts avoids the "Click to Activate..." issue in Internet Explorer
        //When you make your own charts, make sure that the path to this JS file is correct. Else, you would get JavaScript errors.
    %>

    <script language="Javascript" type="text/javascript" src="../FusionCharts/FusionCharts.js"></script>

    <style type="text/css">
	<!--
	body {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
	}
	-->
	</style>
</head>
<body>
    <center>
        <h2>
            <a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> Examples</h2>
        <h4>
            Multiple Charts on the same page. Each chart has a unique ID.</h4>
        <%
	
            //This page demonstrates how you can show multiple charts on the same page.
            //For this example, all the charts use the pre-built Data.xml (contained in /Data/ folder)
            //However, you can very easily change the data source for any chart. 

            
        %>
        <asp:Literal ID="FCLiteral1" runat="server"></asp:Literal>
        <br />
        <br />
        <%	
            //Now, create a Column 2D Chart  
        %>
        <asp:Literal ID="FCLiteral2" runat="server"></asp:Literal>
        <br />
        <br />
        <%	
            //Now, create a Line 2D Chart
        %>
        <asp:Literal ID="FCLiteral3" runat="server"></asp:Literal>
        <br />
        <br />
        <a href='../NoChart.html' target="_blank">Unable to see the charts above?</a>
        <br />
        <h5>
            <a href='../Default.aspx'>&laquo; Back to list of examples</a></h5>
    </center>
</body>
</html>
