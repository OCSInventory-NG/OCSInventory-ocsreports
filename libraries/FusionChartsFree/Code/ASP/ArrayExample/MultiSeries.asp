<%@ Language=VBScript %>
<HTML>
<HEAD>
	<TITLE>
	FusionCharts Free - Array Example using Multi Series Column 3D Chart
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
<h4>Plotting multi-series chart from data contained in Array.</h4>
<%
	'In this example, we plot a multi series chart from data contained
	'in an array. The array will have three columns - first one for data label (product)
	'and the next two for data values. The first data value column would store sales information
	'for current year and the second one for previous year.
	
	'Let's store the sales data for 6 products in our array. We also store
	'the name of products. 
	Dim arrData(6,3)
	'Store Name of Products
	arrData(0,1) = "Product A"
	arrData(1,1) = "Product B"
	arrData(2,1) = "Product C"
	arrData(3,1) = "Product D"
	arrData(4,1) = "Product E"
	arrData(5,1) = "Product F"
	'Store sales data for current year
	arrData(0,2) = 567500
	arrData(1,2) = 815300
	arrData(2,2) = 556800
	arrData(3,2) = 734500
	arrData(4,2) = 676800
	arrData(5,2) = 648500
	'Store sales data for previous year
	arrData(0,3) = 547300
	arrData(1,3) = 584500
	arrData(2,3) = 754000
	arrData(3,3) = 456300
	arrData(4,3) = 754500
	arrData(5,3) = 437600

	'Now, we need to convert this data into multi-series XML. 
	'We convert using string concatenation.
	'strXML - Stores the entire XML
	'strCategories - Stores XML for the <categories> and child <category> elements
	'strDataCurr - Stores XML for current year's sales
	'strDataPrev - Stores XML for previous year's sales
	Dim strXML, strCategories, strDataCurr, strDataPrev, i
	
	'Initialize <graph> element
	strXML = "<graph caption='Sales by Product' numberPrefix='$' decimalPrecision='0' >"
	
	'Initialize <categories> element - necessary to generate a multi-series chart
	strCategories = "<categories>"
	
	'Initiate <dataset> elements
	strDataCurr = "<dataset seriesName='Current Year' color='AFD8F8'>"
	strDataPrev = "<dataset seriesName='Previous Year' color='F6BD0F'>"
	
	'Iterate through the data	
	For i=0 to UBound(arrData)-1
		'Append <category name='...' /> to strCategories
		strCategories = strCategories & "<category name='" & arrData(i,1) & "' />"
		'Add <set value='...' /> to both the datasets
		strDataCurr = strDataCurr & "<set value='" & arrData(i,2) & "' />"
		strDataPrev = strDataPrev & "<set value='" & arrData(i,3) & "' />"		
	Next
	
	'Close <categories> element
	strCategories = strCategories & "</categories>"
	
	'Close <dataset> elements
	strDataCurr = strDataCurr & "</dataset>"
	strDataPrev = strDataPrev & "</dataset>"
	
	'Assemble the entire XML now
	strXML = strXML & strCategories & strDataCurr & strDataPrev & "</graph>"
	
	'Create the chart - MS Column 3D Chart with data contained in strXML
	Call renderChart("../../FusionCharts/FCF_MSColumn3D.swf", "", strXML, "productSales", 600, 300)
%>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
<BR><H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
</CENTER>
</BODY>
</HTML>