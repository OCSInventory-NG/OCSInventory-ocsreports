<%	 byte[] utf8Bom =  new byte[]{(byte) 0xef, (byte) 0xbb, (byte) 0xbf};
String utf8BomStr = new String(utf8Bom,"UTF-8");
%><%=utf8BomStr%><?xml version='1.0' encoding='UTF-8'?><%@ page language="java" contentType="text/xml; charset=UTF-8"
    pageEncoding="UTF-8"%><%request.setCharacterEncoding("UTF-8");%><%@ include file="../Includes/DBConn.jsp" %><%@ page import="java.sql.Statement,java.sql.ResultSet"%><%
	/*
	This page generates the XML data for the Pie Chart contained in JapaneseDBExample.jsp. 	
	
	For the sake of ease, we've used the same database as used by other examples. 
	We have added one more table Japanese_Factory_Master with stores the names of the factory in Japanese language.
	
	Steps to ensure UTF8 xml output for FusionCharts:
		1. Output the BOM bytes 0xef 0xbb 0xbf as shown above in the first few lines
		2. Put the xml declaration <?xml version='1.0' encoding='UTF-8'?> immediately after the output from previous step.
		3. Declare contentType to be text/xml, charSet and pageEncoding to be UTF-8
		4. Use getBytes to get the data from UTF field in the database and to convert it into String, use new String(bytes,"UTF-8")
	Do not output anything other than the BOM, xml declaration and the xml itself. (no empty lines too!)
	*/
		
	//Database Objects - Initialization
	Statement st1=null,st2=null;
	ResultSet rs1=null,rs2=null;
	
	String strQuery="";

	//strXML will be used to store the entire XML document generated
	String strXML ="";
	//Generate the chart element
	strXML = "<graph caption='工場出力レポート' subCaption='量で' decimalPrecision='0' showNames='1' numberSuffix=' Units' pieSliceDepth='30' formatNumberScale='0'>";
	
	//Query to retrieve data about factory

	strQuery = "select * from Japanese_Factory_Master";
	//Create the statement
	st1=oConn.createStatement();
	//Execute the query
	rs1=st1.executeQuery(strQuery);
	
	String factoryId=null;
	String factoryName=null;
	String totalOutput="";
	
	while(rs1.next()) {
		factoryId=rs1.getString("FactoryId");
		byte[] b = rs1.getBytes("FactoryName");
		factoryName=new String (b, "UTF-8");
		
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
	    
	//Just write out the XML data
	//NOTE THAT THIS PAGE DOESN'T CONTAIN ANY HTML TAG, WHATSOEVER
%><%=strXML%>