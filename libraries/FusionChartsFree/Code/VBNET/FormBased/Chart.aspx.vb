Imports InfoSoftGlobal
Partial Class FormBased_Chart
    Inherits System.Web.UI.Page

    Protected Sub Page_Load(ByVal sender As Object, ByVal e As System.EventArgs) Handles Me.Load

        ' Generate chart in Literal Control
        FCLiteral.Text = CreateChart()

    End Sub

    Public Function CreateChart() As String
        'We first request the data from the form (Default.asp)
        Dim intSoups As String, intSalads As String, intSandwiches As String, intBeverages As String, intDesserts As String
        intSoups = Context.Items("Soups")
        intSalads = Context.Items("Salads")
        intSandwiches = Context.Items("Sandwiches")
        intBeverages = Context.Items("Beverages")
        intDesserts = Context.Items("Desserts")

        'In this example, we're directly showing this data back on chart.
        'In your apps, you can do the required processing and then show the 
        'relevant data only.

        'Now that we've the data in variables, we need to convert this into XML.
        'The simplest method to convert data into XML is using string concatenation.	
        Dim strXML As String
        'Initialize <graph> element
        strXML = "<graph caption='Sales by Product Category' subCaption='For this week' showPercentageInLabel='1' pieSliceDepth='25'  decimalPrecision='0' showNames='1'>"
        'Add all data
        strXML = strXML & "<set name='Soups' value='" & intSoups & "' />"
        strXML = strXML & "<set name='Salads' value='" & intSalads & "' />"
        strXML = strXML & "<set name='Sandwiches' value='" & intSandwiches & "' />"
        strXML = strXML & "<set name='Beverages' value='" & intBeverages & "' />"
        strXML = strXML & "<set name='Desserts' value='" & intDesserts & "' />"
        'Close <graph> element
        strXML = strXML & "</graph>"

        'Create the chart - Pie 3D Chart with data from strXML
        Return FusionCharts.RenderChart("../FusionCharts/FCF_Pie3D.swf", "", strXML, "Sales", "600", "350", False, False)
    End Function
End Class
