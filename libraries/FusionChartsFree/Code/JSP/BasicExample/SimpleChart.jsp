<HTML>
	<HEAD>
		<TITLE>FusionCharts Free - Simple Column 3D Chart</TITLE>
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
			-->
		</style>
	</HEAD>
	<BODY>
		<CENTER>
			<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> Examples</h2>
			<h4>Embedding chart using FusionCharts JavaScript class and using dataURL method.</h4>
			<%
				
				/*This page demonstrates the ease of generating charts using FusionCharts.
				For this chart, we've used a pre-defined Data.xml (contained in /Data/ folder)
				Ideally, you would NOT use a physical data file. Instead you'll have 
				your own code virtually relay the XML data document. Such examples are also present.
				For a head-start, we've kept this example very simple.
				*/
				
				//Create the chart - Column 3D Chart with data from Data/Data.xml
				
			%> 
			<jsp:include page="../Includes/FusionChartsRenderer.jsp" flush="true"> 
				<jsp:param name="chartSWF" value="../../FusionCharts/FCF_Column3D.swf" /> 
				<jsp:param name="strURL" value="Data/Data.xml" /> 
				<jsp:param name="strXML" value="" /> 
				<jsp:param name="chartId" value="myFirst" /> 
				<jsp:param name="chartWidth" value="600" /> 
				<jsp:param name="chartHeight" value="300" /> 
				<jsp:param name="debugMode" value="false" /> 	
				<jsp:param name="registerWithJS" value="false" /> 				
			</jsp:include>
			<BR>
			<BR>
			<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a><BR><H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
		</CENTER>
	</BODY>
</HTML>
