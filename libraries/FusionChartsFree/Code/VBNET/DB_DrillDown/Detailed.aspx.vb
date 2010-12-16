Imports DataConnection
Imports Utilities
Imports InfoSoftGlobal
Partial Class DB_DrillDown_Detailed
    Inherits System.Web.UI.Page

    Protected Sub Page_Load(ByVal sender As Object, ByVal e As System.EventArgs) Handles Me.Load
        ' Generate chart in Literal Control
        FCLiteral.Text = CreateChart()
    End Sub

    Public Function CreateChart() As String
        'This page is invoked from Default.asp. When the user clicks on a pie
        'slice in Default.asp, the factory Id is passed to this page. We need
        'to get that factory id, get information from database and then show
        'a detailed chart.

        'First, get the factory Id
        Dim FactoryId As String, FactoryName As String
        Dim util As New Util
        'Request the factory Id from Querystring
        FactoryId = Request.QueryString("FactoryId")
        FactoryName = Request.QueryString("FactoryName")

        Dim oRs As DbConn, strQuery As String
        'strXML will be used to store the entire XML document generated
        Dim strXML As String

        'Generate the graph element string
        strXML = "<graph caption='Factory " & FactoryName & " Output ' subcaption='(In Units)' xAxisName='Date' showValues='1' decimalPrecision='0' rotateNames='1' >"

        ' SQL Query
        strQuery = "select * from Factory_Output where FactoryId=" & FactoryId

        ' Open Data Reader
        oRs = New DbConn(strQuery)
        'Now, we get the data for that factory
        While oRs.ReadData.Read()
            'Here, we convert date into a more readable form for set name.
            strXML = strXML & "<set name='" & Convert.ToDateTime(oRs.ReadData("DatePro")).ToString("dd/MM/yyyy") & "' value='" & oRs.ReadData("Quantity").ToString() & "' color='" & util.getFCColor() & "'/>"

        End While
        'Close <graph> element
        strXML = strXML & "</graph>"
        ' Close Data Reader
        oRs.ReadData.Close()

        'Create the chart - Column 2D Chart with data from strXML
        Return FusionCharts.RenderChart("../FusionCharts/FCF_Column2D.swf", "", strXML, "FactoryDetailed", "600", "300", False, False)
    End Function

    
End Class
