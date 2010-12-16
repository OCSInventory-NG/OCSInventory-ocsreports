<HTML>
	<HEAD>
		<TITLE>FusionCharts Free - Multiple Charts on one Page</TITLE>
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
			<h4>Multiple Charts on the same page. Each chart has a unique ID.</h4>
			<%
				
				/*
				 This page demonstrates how you can show multiple charts on the same page.
				 For this example, all the charts use the pre-built Data.xml (contained in /Data/ folder)
				 However, you can very easily change the data source for any chart. 
				
				 IMPORTANT NOTE: Each chart necessarily needs to have a unique ID on the page.
				 If you do not provide a unique Id, only the last chart might be visible.
				 Here, we've used the ID chart1, chart2 and chart3 for the 3 charts on page.
				 */
				
				//Create the chart - Column 3D Chart with data from Data/Data.xml
			%> 
						<jsp:include page="../Includes/FusionChartsRenderer.jsp" flush="true"> 
							<jsp:param name="chartSWF" value="../../FusionCharts/FCF_Column3D.swf" /> 
							<jsp:param name="strURL" value="Data/Data.xml" /> 
							<jsp:param name="strXML" value="" /> 
							<jsp:param name="chartId" value="chart1" /> 
							<jsp:param name="chartWidth" value="600" /> 
							<jsp:param name="chartHeight" value="300" /> 
							<jsp:param name="debugMode" value="false" /> 	
							<jsp:param name="registerWithJS" value="false" /> 							
						</jsp:include>
			<BR>
			<BR>
			<%
			//Create the chart - Column 2D Chart with data from Data/Data.xml
			%> 
						<jsp:include page="../Includes/FusionChartsRenderer.jsp" flush="true"> 
							<jsp:param name="chartSWF" value="../../FusionCharts/FCF_Column2D.swf" /> 
							<jsp:param name="strURL" value="Data/Data.xml" /> 
							<jsp:param name="strXML" value="" /> 
							<jsp:param name="chartId" value="chart2" /> 
							<jsp:param name="chartWidth" value="600" /> 
							<jsp:param name="chartHeight" value="300" /> 
							<jsp:param name="debugMode" value="false" /> 	
							<jsp:param name="registerWithJS" value="false" /> 							
						</jsp:include>
			<BR>
			<BR>
			<%	
				//Now, create a Line Chart	
			%> 
						<jsp:include page="../Includes/FusionChartsRenderer.jsp" flush="true"> 
							<jsp:param name="chartSWF" value="../../FusionCharts/FCF_Line.swf" /> 
							<jsp:param name="strURL" value="Data/Data.xml" /> 
							<jsp:param name="strXML" value="" /> 
							<jsp:param name="chartId" value="chart3" /> 
							<jsp:param name="chartWidth" value="600" /> 
							<jsp:param name="chartHeight" value="300" /> 
							<jsp:param name="debugMode" value="false" /> 	
							<jsp:param name="registerWithJS" value="false" /> 
						</jsp:include>
			<BR>
			<BR>
			<a href='../NoChart.html' target="_blank">Unable to see the charts above?</a><BR><H5 ><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
		</CENTER>
	</BODY>
</HTML>
