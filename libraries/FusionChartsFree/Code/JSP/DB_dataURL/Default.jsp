<HTML>
	<HEAD>
		<TITLE>FusionCharts Free - dataURL and Database Example</TITLE>
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
			<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> dataURL and Database</h2>
			<%
				/*
				In this example, we show how to connect FusionCharts to a database 
				using dataURL method. In our other examples, we've used dataXML method
				where the XML is generated in the same page as chart. Here, the XML data
				for the chart would be generated in PieData.jsp.
				*/
				
				/*
				To illustrate how to pass additional data as querystring to dataURL, 
				we've added an animate	property, which will be passed to PieData.jsp. 
				PieData.jsp would handle this animate property and then generate the 
				XML accordingly.
				*/
				
				/*For the sake of ease, we've used an Access database which is present in
				../DB/FactoryDB.mdb. It just contains two tables, which are linked to each
				other.
				*/
					
				//Variable to contain dataURL
				String strDataURL="";
				
				//NOTE: It's necessary to encode the dataURL if you've added parameters to it
				strDataURL = "PieData.jsp";
				
				//Create the chart - Pie 3D Chart with dataURL as strDataURL
			%> 
			<jsp:include page="../Includes/FusionChartsRenderer.jsp" flush="true"> 
							<jsp:param name="chartSWF" value="../../FusionCharts/FCF_Pie3D.swf" /> 
							<jsp:param name="strURL" value="<%=strDataURL%>" /> 
							<jsp:param name="strXML" value="" /> 
							<jsp:param name="chartId" value="FactorySum" /> 
							<jsp:param name="chartWidth" value="650" /> 
							<jsp:param name="chartHeight" value="450" /> 
							<jsp:param name="debugMode" value="false" /> 	
							<jsp:param name="registerWithJS" value="false" /> 							
						</jsp:include>
			<BR>
			<BR>
			<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a><BR>
			<H5><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
		</CENTER>
	</BODY>
</HTML>
