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
public partial class FormBased_Chart : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {
        // Generate chart in Literal Control
        FCLiteral.Text = CreateChart();
    }

    public string CreateChart()
    {
        //We first request the data from the form (Default.asp)
        string intSoups, intSalads, intSandwiches, intBeverages, intDesserts;

        intSoups = Context.Items["Soups"].ToString();
        intSalads = Context.Items["Salads"].ToString();
        intSandwiches = Context.Items["Sandwiches"].ToString();
        intBeverages = Context.Items["Beverages"].ToString();
        intDesserts = Context.Items["Desserts"].ToString();

        //In this example, we're directly showing this data back on chart.
        //In your apps, you can do the required processing and then show the 
        //relevant data only.

        //Now that we've the data in variables, we need to convert this into XML.
        //The simplest method to convert data into XML is using string concatenation.	
        string strXML;
        //Initialize <graph> element
        strXML = "<graph caption='Sales by Product Category' subCaption='For this week' showPercentageInLabel='1' pieSliceDepth='25'  decimalPrecision='0' showNames='1'>";
        //Add all data
        strXML += "<set name='Soups' value='" + intSoups + "' />";
        strXML += "<set name='Salads' value='" + intSalads + "' />";
        strXML += "<set name='Sandwiches' value='" + intSandwiches + "' />";
        strXML += "<set name='Beverages' value='" + intBeverages + "' />";
        strXML += "<set name='Desserts' value='" + intDesserts + "' />";
        //Close <graph> element
        strXML += "</graph>";

        //Create the chart - Pie 3D Chart with data from strXML
        return FusionCharts.RenderChart("../FusionCharts/FCF_Pie3D.swf", "", strXML, "Sales", "600", "350", false, false);
    }
}
