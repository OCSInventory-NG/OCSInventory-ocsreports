Imports DataConnection
Imports InfoSoftGlobal
Partial Class DB_DrillDown_Default
    Inherits System.Web.UI.Page


    Protected Sub Page_Load(ByVal sender As Object, ByVal e As System.EventArgs) Handles Me.Load
        ' Generate chart in Literal Control
        FCLiteral.Text = CreateChart()
    End Sub

    Public Function CreateChart() As String
        'In this example, we show how to connect FusionCharts to a database.
        'For the sake of ease, we've used an Access database which is present in
        '../App_Data/FactoryDB.mdb. It just contains two tables, which are linked to each
        'other. 

        'Database Objects - Initialization
        Dim oRs As DbConn, strQuery As String
        'strXML will be used to store the entire XML document generated
        Dim strXML As String

        'Generate the graph element
        strXML = "<graph caption='Factory Output report' subCaption='By Quantity' decimalPrecision='0' showNames='1' numberSuffix=' Units' pieSliceDepth='30'  formatNumberScale='0' >"

        ' SQL Query
        strQuery = "select a.FactoryId,a.FactoryName, sum(b.Quantity) as TotOutput from factory_master a,factory_output b where a.FactoryId=b.FactoryId group by a.FactoryId,a.FactoryName"

        ' Open data reader
        oRs = New DbConn(strQuery)

        'Iterate through each factory
        While oRs.ReadData.Read()
        
            'Generate <set name='..' value='..' link='..' />
            'Note that we're setting link as Detailed.asp?FactoryId=<<FactoryId>>&FactoryName=<<FactoryName>>
            strXML = strXML & "<set name='" & oRs.ReadData("FactoryName").ToString() & "' value='" & oRs.ReadData("TotOutput").ToString() & "' link='" & Server.UrlEncode("Detailed.aspx?FactoryId=" & oRs.ReadData("FactoryId").ToString()) & "&FactoryName=" & oRs.ReadData("FactoryName").ToString() & "'/>"
        
        End While
        'Finally, close <graph> element
        strXML = strXML & "</graph>"
        ' Close Data Reader
        oRs.ReadData.Close()

        'Create the chart - Pie 3D Chart with data from strXML
        Return FusionCharts.RenderChart("../FusionCharts/FCF_Pie3D.swf", "", strXML, "FactorySum", "650", "450", False, False)
    End Function

    
End Class
