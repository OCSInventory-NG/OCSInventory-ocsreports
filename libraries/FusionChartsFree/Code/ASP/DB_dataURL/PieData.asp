<%@ Language=VBScript %>

<!-- #INCLUDE FILE="../Includes/DBConn.asp" -->
<%
	'This page generates the XML data for the Pie Chart contained in
	'Default.asp. 	
	
	'For the sake of ease, we've used an Access database which is present in
	'../DB/FactoryDB.mdb. It just contains two tables, which are linked to each
	'other. 
		
	'Database Objects - Initialization
	Dim oRs, oRs2, strQuery
	'strXML will be used to store the entire XML document generated
	Dim strXML
			
	'Create the recordset to retrieve data
	Set oRs = Server.CreateObject("ADODB.Recordset")

	'Generate the graph element
	strXML = "<graph caption='Factory Output report' subCaption='By Quantity' decimalPrecision='0' showNames='1' numberSuffix=' Units' pieSliceDepth='30' formatNumberScale='0'>"

	'Iterate through each factory
	strQuery = "select * from Factory_Master"
	Set oRs = oConn.Execute(strQuery)
	
	While Not oRs.Eof
		'Now create second recordset to get details for this factory
		Set oRs2 = Server.CreateObject("ADODB.Recordset")
		strQuery = "select sum(Quantity) as TotOutput from Factory_Output where FactoryId=" & ors("FactoryId")
		Set oRs2 = oConn.Execute(strQuery)				
		'Generate <set name='..' value='..'/>		
		strXML = strXML & "<set name='" & ors("FactoryName") & "' value='" & ors2("TotOutput") & "' />"
		'Close recordset
		Set oRs2 = Nothing
		oRs.MoveNext
	Wend
	'Finally, close <graph> element
	strXML = strXML & "</graph>"
	Set oRs = nothing
		
	'Set Proper output content-type
	Response.ContentType = "text/xml"
	
	'Just write out the XML data
	'NOTE THAT THIS PAGE DOESN'T CONTAIN ANY HTML TAG, WHATSOEVER
	Response.Write(strXML)
%>
