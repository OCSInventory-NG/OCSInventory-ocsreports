Imports Utilities
Imports InfoSoftGlobal
Partial Class ArrayExample_SingleSeries
    Inherits System.Web.UI.Page

    Protected Sub Page_Load(ByVal sender As Object, ByVal e As System.EventArgs) Handles Me.Load
        ' Generate chart in Literal Control
        FCLiteral.Text = CreateChart()
    End Sub

    Public Function CreateChart() As String

        'In this example, we plot a single series chart from data contained
        'in an array. The array will have two columns - first one for data label
        'and the next one for data values.

        'Let's store the sales data for 6 products in our array). We also store
        'the name of products. 
        Dim arrData(6, 2) As String
        ' Creating util Object
        Dim util As New Util()
        'Store Name of Products
        arrData(0, 1) = "Product A"
        arrData(1, 1) = "Product B"
        arrData(2, 1) = "Product C"
        arrData(3, 1) = "Product D"
        arrData(4, 1) = "Product E"
        arrData(5, 1) = "Product F"
        'Store sales data
        arrData(0, 2) = "567500"
        arrData(1, 2) = "815300"
        arrData(2, 2) = "556800"
        arrData(3, 2) = "734500"
        arrData(4, 2) = "676800"
        arrData(5, 2) = "648500"

        'Now, we need to convert this data into XML. We convert using string concatenation.
        Dim strXML As String, i As Integer
        'Initialize <graph> element
        strXML = "<graph caption='Sales by Product' numberPrefix='$' formatNumberScale='0' decimalPrecision='0'>"

        'Convert data to XML and append
        For i = 0 To UBound(arrData) - 1
            'add values using <set name='...' value='...' color='...'/>
            strXML = strXML & "<set name='" & arrData(i, 1) & "' value='" & arrData(i, 2) & "' color='" & util.getFCColor() & "' />"
        Next
        'Close <graph> element
        strXML = strXML & "</graph>"

        'Create the chart - Column 3D Chart with data contained in strXML
        Return FusionCharts.RenderChart("../FusionCharts/FCF_Column3D.swf", "", strXML, "productSales", "600", "300", False, False)

    End Function

    
End Class
