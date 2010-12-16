using System;
using System.Data;
using System.Configuration;
using System.Collections;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;
using InfoSoftGlobal;

public partial class BasicExample_MultiChart : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {
        //IMPORTANT NOTE: Each chart necessarily needs to have a unique ID on the page.
        //If you do not provide a unique Id, only the last chart might be visible.
        //Here, we've used the ID chart1, chart2 and chart3 for the 3 charts on page.

        //Create the chart - Column 3D Chart with data from Data/Data.xml
        FCLiteral1.Text=FusionCharts.RenderChart("../FusionCharts/FCF_Column3D.swf", "Data/Data.xml", "", "chart1", "600", "300", false, false);
        //Now, create a Column 2D Chart
        FCLiteral2.Text = FusionCharts.RenderChart("../FusionCharts/FCF_Column2D.swf", "Data/Data.xml", "", "chart2", "600", "300", false, false);
        //Now, create a Line 2D Chart
        FCLiteral3.Text = FusionCharts.RenderChart("../FusionCharts/FCF_Line.swf", "Data/Data.xml", "", "chart3", "600", "300", false, false);
    }
}
