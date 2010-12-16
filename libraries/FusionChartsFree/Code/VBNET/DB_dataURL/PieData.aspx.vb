Imports DataConnection
Partial Class DB_dataURL_PieData
    Inherits System.Web.UI.Page
    Protected Sub Page_Load(ByVal obj As Object, ByVal e As EventArgs) Handles Me.Load
        'This page generates the XML data for the Pie Chart contained in
        'Default.aspx. 	

        'For the sake of ease, we've used an Access database which is present in
        '../App_Data/FactoryDB.mdb. It just contains two tables, which are linked to each
        'other. 

        'Database Objects - Initialization
        Dim oRs As DbConn, strQuery As String
        'strXML will be used to store the entire XML document generated
        Dim strXML As String

        'Generate the graph element
        strXML = "<graph caption='Factory Output report' subCaption='By Quantity' decimalPrecision='0' showNames='1' numberSuffix=' Units' pieSliceDepth='30' formatNumberScale='0'>"

        ' SQL Query
        strQuery = "select a.FactoryId,a.FactoryName, sum(b.Quantity) as TotOutput from factory_master a,factory_output b where a.FactoryId=b.FactoryId group by a.FactoryId,a.FactoryName"

        ' Open Data Reader
        oRs = New DbConn(strQuery)

        'Iterate through each factory
        While oRs.ReadData.Read()
            
            'Generate <set name='..' value='..'/>		
            strXML = strXML & "<set name='" & oRs.ReadData("FactoryName").ToString() & "' value='" & oRs.ReadData("TotOutput").ToString() & "' />"

        End While
        ' Close Data Reader
        oRs.ReadData.Close()
        'Finally, close <graph> element
        strXML = strXML & "</graph>"

        'Set Proper output content-type
        Response.ContentType = "text/xml"

        'Just write out the XML data
        'NOTE THAT THIS PAGE DOESN'T CONTAIN ANY HTML TAG, WHATSOEVER
        Response.Write(strXML)
    End Sub
End Class
