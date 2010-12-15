<%@ Page Language="C#" %>

<%@ Import Namespace="InfoSoftGlobal" %>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<script runat="server">

    protected void Page_Load(object sender, EventArgs e)
    {

        //In this example, we show how to connect FusionCharts to a database 
        //using dataURL method. In our other examples, we've used dataXML method
        //where the XML is generated in the same page as chart. Here, the XML data
        //for the chart would be generated in PieData.asp.

        //For the sake of ease, we've used an Access database which is present in
        //../App_Data/FactoryDB.mdb. It just contains two tables, which are linked to each
        //other.

        //Variable to contain dataURL
        string strDataURL;
        //the asp script in piedata.asp interacts with the database, 
        //converts the data into proper XML form and finally 
        //relays XML data document to the chart
        strDataURL = "PieData.aspx";

        //Create the chart - Pie 3D Chart with dataURL as strDataURL
        FCLiteral.Text=FusionCharts.RenderChart("../FusionCharts/FCF_Pie3D.swf", strDataURL, "", "FactorySum", "650", "450", false, false);
    }
</script>

<html>
<head>
    <title>FusionCharts Free - dataURL and Database Example </title>
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
            <a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> - dataURL
            and Database</h2>
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
