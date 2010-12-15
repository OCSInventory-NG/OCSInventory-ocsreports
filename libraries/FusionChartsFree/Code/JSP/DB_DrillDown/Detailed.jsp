<%@ include file="../Includes/DBConn.jsp"%>

<%@ page import="com.fusioncharts.FusionChartsHelper"%>

<%@ page import="java.sql.Statement"%>
<%@ page import="java.sql.ResultSet"%>
<%@ page import="java.text.SimpleDateFormat"%>
<HTML>
	<HEAD>
		<TITLE>FusionCharts Free - Database and Drill-Down Example</TITLE>
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
			<h2><a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> Database and Drill-Down Example</h2>
			<h4>Detailed report for the factory</h4>
				<%
					/*This page is invoked from Default.jsp. When the user clicks on a pie
					slice in Default.jsp, the factory Id is passed to this page. We need
					to get that factory id, get the information from database and then show
					a detailed chart.
					*/
					
					//First, get the factory Id
					String factoryId=null;
					//Request the factory Id from parameters
					factoryId = request.getParameter("FactoryId");
					String chartCode="";
					if(null!=factoryId){
						// we will use the FusionChartsHelper class for colors
					     FusionChartsHelper colorHelper= new FusionChartsHelper();
						ResultSet rs=null;
						String strQuery;
						Statement st=null;
						
						java.sql.Date date=null;
						java.util.Date uDate=null;
						String uDateStr="";
						String quantity="";
						String strXML="";
						//Generate the chart element string
						strXML = "<graph caption='Factory " +factoryId +" Output ' subcaption='(In Units)' xAxisName='Date' showValues='1' decimalPrecision='0'>";
						//Now, we get the data for that factory
						strQuery = "select * from Factory_Output where FactoryId=" +factoryId;
						
						st=oConn.createStatement();
						rs = st.executeQuery(strQuery);
						while(rs.next()){	
							date=rs.getDate("DatePro");
							quantity=rs.getString("Quantity");
							if(date!=null) {
								  uDate=new java.util.Date(date.getTime());
								  // Format the date so that the displayed date is easy to read
								  SimpleDateFormat sdf=new SimpleDateFormat("d/M");
								  uDateStr=sdf.format(uDate);
							}
							strXML += "<set name='" +uDateStr+"' value='" +quantity+"' color='" + colorHelper.getFCColor() + "'/>";
						}
						//Close <graph> element
						strXML +="</graph>";
						//close resultset,statement,connection
						try {
							if(null!=rs){
								rs.close();
								rs=null;
							}
						}catch(java.sql.SQLException e){
							 //do something
							 System.out.println("Could not close the resultset");
						}	
						try {
							if(null!=st) {
								st.close();
								st=null;
							}
						}catch(java.sql.SQLException e){
							 	//do something
							 	System.out.println("Could not close the statement");
						}
						try {
							if(null!=oConn) {
							    oConn.close();
							    oConn=null;
							}
						}catch(java.sql.SQLException e){
							 	//do something
							 	System.out.println("Could not close the connection");
						}
						
						//Create the chart - Column 2D Chart with data from strXML
					
				%> 
				<jsp:include page="../Includes/FusionChartsRenderer.jsp" flush="true"> 
						<jsp:param name="chartSWF" value="../../FusionCharts/FCF_Column2D.swf" /> 
						<jsp:param name="strURL" value="" /> 
						<jsp:param name="strXML" value="<%=strXML%>" /> 
						<jsp:param name="chartId" value="FactoryDetailed" /> 
						<jsp:param name="chartWidth" value="600" /> 
						<jsp:param name="chartHeight" value="300" /> 
						<jsp:param name="debugMode" value="false" /> 	
						<jsp:param name="registerWithJS" value="false" /> 						
				</jsp:include>
				<% }
				%> 
				<BR>
				<a href='Default.jsp?animate=0'>Back to Summary</a> <BR>
				<BR>
				<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a><BR>
				<H5><a href='../default.htm'>&laquo; Back to list of examples</a></h5>
			</CENTER>
	</BODY>
</HTML>
