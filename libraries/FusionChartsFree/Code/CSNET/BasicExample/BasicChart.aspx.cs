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

public partial class BasicExample_BasicChart : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {
        //This page demonstrates the ease of generating charts using FusionCharts.
        //For this chart, we've used a pre-defined Data.xml (contained in /Data/ folder)
        //Ideally, you would NOT use a physical data file. Instead you'll have 
        //your own ASP scripts virtually relay the XML data document. Such examples are also present.
        //For a head-start, we've kept this example very simple.


        //Create the chart - Column 3D Chart with data from Data/Data.xml

        // Generate chart in Literal Control
        FCLiteral.Text = FusionCharts.RenderChartHTML("../FusionCharts/FCF_Column3D.swf", "Data/Data.xml", "", "myFirst", "600", "300", false);
    }
}
