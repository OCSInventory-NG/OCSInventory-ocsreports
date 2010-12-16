Imports InfoSoftGlobal

Partial Class BasicExample_MultiChart
    Inherits System.Web.UI.Page

    Protected Sub Page_Load(ByVal sender As Object, ByVal e As System.EventArgs) Handles Me.Load
        'This page demonstrates how you can show multiple charts on the same page.
        'For this example, all the charts use the pre-built Data.xml (contained in /Data/ folder)
        'However, you can very easily change the data source for any chart. 

        'IMPORTANT NOTE: Each chart necessarily needs to have a unique ID on the page.
        'If you do not provide a unique Id, only the last chart might be visible.
        'Here, we've used the ID chart1, chart2 and chart3 for the 3 charts on page.

        'Create the chart - Column 3D Chart with data from Data/Data.xml

        ' Generate chart in Literal Control
        FCLiteral1.Text = FusionCharts.RenderChart("../FusionCharts/FCF_Column3D.swf", "Data/Data.xml", "", "chart1", "600", "300", False, False)

        'Now, create a Column 2D Chart
        FCLiteral2.Text = FusionCharts.RenderChart("../FusionCharts/FCF_Column2D.swf", "Data/Data.xml", "", "chart2", "600", "300", False, False)

        'Now, create a Line 2D Chart
        FCLiteral3.Text = FusionCharts.RenderChart("../FusionCharts/FCF_Line.swf", "Data/Data.xml", "", "chart3", "600", "300", False, False)
    End Sub
End Class
