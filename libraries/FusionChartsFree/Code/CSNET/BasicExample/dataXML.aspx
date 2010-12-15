<%@ Page Language="C#" AutoEventWireup="true" CodeFile="dataXML.aspx.cs" Inherits="BasicExample_dataXML" %>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title>FusionCharts Free - Simple Column 3D Chart using dataXML method </title>
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
            JavaScript embedding using dataXML method (with XML data hard-coded in ASP.Net page
            itself)</h4>
        <p>
            If you view the source of this page, you'll see that the XML data is present in
            this same page (inside HTML code). We're not calling any external XML (or script)
            files to serve XML data. dataXML method is ideal when you've to plot small amounts
            of data.</p>
        <asp:Literal ID="FCLiteral" runat="server"></asp:Literal>
        <br />
        <br />
        <a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
        <br />
        <h5>
            <a href='../Default.aspx'>&laquo; Back to list of examples</a></h5>
    </center>
</body>
</html>
