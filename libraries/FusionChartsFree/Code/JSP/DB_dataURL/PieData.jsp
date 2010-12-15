<%
	/*
	We've included ../Includes/DBConn.jsp, to get a connection to the specific database. Which database to connect to is 
	decided based on a config param present in the web.xml.
	Note that we have not used the jsp:include tag instead of the @include file directive.Either of them
	could be used.
	*/
%>
<%@ include file="../Includes/DBConn.jsp" %>

<%@ page import="java.sql.Statement"%>
<%@ page import="java.sql.ResultSet"%>
<%
	/*This page generates the XML data for the Pie Chart contained in
	Default.jsp. 	
	
	For the sake of ease, we've used a database which just contains two tables, which are linked to each
	other. 
	*/
		
	//Database Objects - Initialization
	Statement st1=null,st2=null;
	ResultSet rs1=null,rs2=null;
	
	String strQuery="";

	//strXML will be used to store the entire XML document generated
	String strXML="";

	//Generate the chart element
	strXML = "<graph caption='Factory Output report' subCaption='By Quantity' decimalPrecision='0' showNames='1' numberSuffix=' Units' pieSliceDepth='30' formatNumberScale='0'>";
	
	//Query to retrieve data about factory
	/*
	If the query varies from one database to another,get the database name from 
	the Servlet context, like this :
	String dbName=getServletContext().getInitParameter("dbName");
	And check this against the db name constants defined in 
	com.infosoftglobal.fusioncharts.Constants java class.
	And then for each db you can write a different query.
	*/
	
	strQuery = "select * from Factory_Master";
	//Create the statement
	st1=oConn.createStatement();
	//Execute the query
	rs1=st1.executeQuery(strQuery);
	
	String factoryId=null;
	String factoryName=null;
	String totalOutput="";
	
	while(rs1.next()) {
		factoryId=rs1.getString("FactoryId");
		factoryName=rs1.getString("FactoryName");
		//Now create second resultset to get details for this factory
		strQuery = "select sum(Quantity) as TotOutput from Factory_Output where FactoryId=" + factoryId;
		st2=oConn.createStatement();
		rs2 = st2.executeQuery(strQuery);
		if(rs2.next()){
			totalOutput=rs2.getString("TotOutput");
		}
		//Generate <set name='..' value='..'/>		
		strXML += "<set name='" + factoryName + "' value='" +totalOutput+ "' />";
		try {
			if(null!=rs2){
				rs2.close();
				rs2=null;
			}
		}catch(java.sql.SQLException e){
		 	System.out.println("Could not close the resultset");
		}
		try{
			if(null!=st2) {
				st2.close();
				st2=null;
			}
		}catch(java.sql.SQLException e){
		 	System.out.println("Could not close the statement");
		}
	}
	//Finally, close <graph> element
	strXML += "</graph>";
	
	try {
		if(null!=rs1){
			rs1.close();
			rs1=null;
		}
	}catch(java.sql.SQLException e){
		 //do something
		 System.out.println("Could not close the resultset");
	}	
	try {
		if(null!=st1) {
			st1.close();
			st1=null;
		}
	    }catch(java.sql.SQLException e){
		 	System.out.println("Could not close the statement");
		}
	try {
		if(null!=oConn) {
		    oConn.close();
		    oConn=null;
		}
	    }catch(java.sql.SQLException e){
		 	System.out.println("Could not close the connection");
		}
	//Set Proper output content-type
	 response.setContentType("text/xml"); 
	
	//Just write out the XML data
	//NOTE THAT THIS PAGE DOESN'T CONTAIN ANY HTML TAG, WHATSOEVER
%>
<%=strXML%>
