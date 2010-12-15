<%@ Page Language="VB" %>

<%@ Import Namespace="InfoSoftGlobal" %>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<script runat="server">
    
    Protected Sub Page_Load(ByVal sender As Object, ByVal e As System.EventArgs)
        ' Generate chart in Literal Control
        FCLiteral.Text = CreateChart()
    End Sub
    
    Public Function CreateChart() As String
        'In this example, we plot a Stacked chart from data contained
        'in an array. The array will have three columns - first one for Quarter Name
        'and the next two for data values. The first data value column would store sales information
        'for Product A and the second one for Product B.
		
        Dim arrData(4, 3) As String
        'Store Name of Products
        arrData(0, 1) = "Quarter 1"
        arrData(1, 1) = "Quarter 2"
        arrData(2, 1) = "Quarter 3"
        arrData(3, 1) = "Quarter 4"
        'Sales data for Product A
        arrData(0, 2) = "567500"
        arrData(1, 2) = "815300"
        arrData(2, 2) = "556800"
        arrData(3, 2) = "734500"
        'Sales data for Product B
        arrData(0, 3) = "547300"
        arrData(1, 3) = "594500"
        arrData(2, 3) = "754000"
        arrData(3, 3) = "456300"

        'Now, we need to convert this data into multi-series XML. 
        'We convert using string concatenation.
        'strXML - Stores the entire XML
        'strCategories - Stores XML for the <categories> and child <category> elements
        'strDataProdA - Stores XML for current year's sales
        'strDataProdB - Stores XML for previous year's sales
        Dim strXML As String, strCategories As String, strDataProdA As String, strDataProdB As String, i As Integer
	
        'Initialize <graph> element
        strXML = "<graph caption='Sales' numberPrefix='$' formatNumberScale='0' decimalPrecision='0'>"
	
        'Initialize <categories> element - necessary to generate a stacked chart
        strCategories = "<categories>"
	
        'Initiate <dataset> elements
        strDataProdA = "<dataset seriesName='Product A' color='AFD8F8'>"
        strDataProdB = "<dataset seriesName='Product B' color='F6BD0F'>"
	
        'Iterate through the data	
        For i = 0 To UBound(arrData) - 1
            'Append <category name='...' /> to strCategories
            strCategories = strCategories & "<category name='" & arrData(i, 1) & "' />"
            'Add <set value='...' /> to both the datasets
            strDataProdA = strDataProdA & "<set value='" & arrData(i, 2) & "' />"
            strDataProdB = strDataProdB & "<set value='" & arrData(i, 3) & "' />"
        Next
	
        'Close <categories> element
        strCategories = strCategories & "</categories>"
	
        'Close <dataset> elements
        strDataProdA = strDataProdA & "</dataset>"
        strDataProdB = strDataProdB & "</dataset>"
	
        'Assemble the entire XML now
        strXML = strXML & strCategories & strDataProdA & strDataProdB & "</graph>"
	
        'Create the chart - Stacked Column 3D Chart with data contained in strXML
        Return FusionCharts.RenderChart("../FusionCharts/FCF_StackedColumn3D.swf", "", strXML, "productSales", "600", "300", False, False)
    End Function

    
</script>

<html>
<head>
    <title>FusionCharts Free - Array Example using Stacked Column 3D Chart </title>
    <%
        'You need to include the following JS file, if you intend to embed the chart using JavaScript.
        'Embedding using JavaScripts avoids the "Click to Activate..." issue in Internet Explorer
        'When you make your own charts, make sure that the path to this JS file is correct. Else, you would get JavaScript errors.
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
            Plotting Stacked Chart from data contained in Array.</h4>
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
