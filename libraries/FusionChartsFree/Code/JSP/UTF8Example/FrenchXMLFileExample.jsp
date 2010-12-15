<%@ page language="java" contentType="text/html; charset=UTF-8"
    pageEncoding="UTF-8"%>
<HTML>
	<HEAD>
		<META http-equiv="Content-Type" content="text/html;charset=UTF-8"/> 
		<TITLE>FusionCharts Free - UTF8 Français (French) Example</TITLE>
		<%
			/*You need to include the following JS file, if you intend to embed the chart using JavaScript.
			Embedding using JavaScripts avoids the "Click to Activate..." issue in Internet Explorer
			When you make your own charts, make sure that the path to this JS file is correct. Else, you would get JavaScript errors.
			*/
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
	
	<BODY>
		<CENTER>
			<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> UTF8 Français (French) Example</h2>
			<h4>Basic Example using data from pre-built FrenchData.xml</h4>
			<%
				/*
				In this example, we show how to use UTF characters in charts created with FusionCharts 
				Here, the XML data for the chart is present in Data/FrenchData.xml. 
				The xml file should be created and saved with an editor
				which places the UTF8 BOM. The first line of the xml should contain the
				xml declaration like this: <?xml version="1.0" encoding="UTF-8" ?>
				*/
				/*
				The pageEncoding and chartSet headers for the page have been set to UTF-8
				in the first line of this jsp file.
				*/
					
				//Variable to contain dataURL
				String strDataURL = "Data/FrenchData.xml";
				
				//Create the chart - Pie 3D Chart with dataURL as strDataURL
			%> 
			<jsp:include page="../Includes/FusionChartsRenderer.jsp" flush="true"> 
							<jsp:param name="chartSWF" value="../../FusionCharts/FCF_Column3D.swf" /> 
							<jsp:param name="strURL" value="<%=strDataURL%>" /> 
							<jsp:param name="strXML" value="" /> 
							<jsp:param name="chartId" value="FrenchChart" /> 
							<jsp:param name="chartWidth" value="600" /> 
							<jsp:param name="chartHeight" value="300" /> 
						</jsp:include>
			<BR>
			<BR>
			<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a><BR>
			<H5><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
		</CENTER>
	</BODY>
</HTML>