<%@ Page Language="VB" %>

<%@ Import Namespace="InfoSoftGlobal" %>

<script runat="server">

    Protected Sub Page_Load(ByVal sender As Object, ByVal e As System.EventArgs)
        'Create the chart - Column 3D Chart with data from Data/Data.xml
        FCLiteral.Text = FusionCharts.RenderChartHTML("../FusionCharts/FCF_Column3D.swf", "Data/Data.xml", "", "myFirst", "600", "300", False)
    End Sub
</script>

<html>
<head>
    <title>FusionCharts Free - Simple Column 3D Chart </title>
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
            Basic example using pre-built Data.xml</h4>
        <%
	
            'This page demonstrates the ease of generating charts using FusionCharts.
            'For this chart, we've used a pre-defined Data.xml (contained in /Data/ folder)
            'Ideally, you would NOT use a physical data file. Instead you'll have 
            'your own ASP.NET scripts virtually relay the XML data document. Such examples are also present.
            'For a head-start, we've kept this example very simple.
		
        %>
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
