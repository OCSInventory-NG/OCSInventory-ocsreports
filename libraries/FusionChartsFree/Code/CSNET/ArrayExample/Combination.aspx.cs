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

public partial class ArrayExample_Combination : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {
        // Generate chart in Literal Control
        FCLiteral.Text = CreateChart();
    }

    public string CreateChart()
    {
        //In this example, we plot a Combination chart from data contained
        //in an array. The array will have three columns - first one for Quarter Name
        //second one for sales figure and third one for quantity. 

        string[,] arrData = new string[4, 3];
        //Store Quarter Name
        arrData[0, 0] = "Quarter 1";
        arrData[1, 0] = "Quarter 2";
        arrData[2, 0] = "Quarter 3";
        arrData[3, 0] = "Quarter 4";
        //Store revenue data
        arrData[0, 1] = "576000";
        arrData[1, 1] = "448000";
        arrData[2, 1] = "956000";
        arrData[3, 1] = "734000";
        //Store Quantity
        arrData[0, 2] = "576";
        arrData[1, 2] = "448";
        arrData[2, 2] = "956";
        arrData[3, 2] = "734";

        //Now, we need to convert this data into combination XML. 
        //We convert using string concatenation.
        //strXML - Stores the entire XML
        //strCategories - Stores XML for the <categories> and child <category> elements
        //strDataRev - Stores XML for current year's sales
        //strDataQty - Stores XML for previous year's sales
        string strXML, strCategories, strDataRev, strDataQty;
        int i;

        //Initialize <graph> element
        strXML = "<graph caption='Product A - Sales Details' PYAxisName='Revenue' SYAxisName='Quantity (in Units)' numberPrefix='$' formatNumberScale='0' showValues='0' decimalPrecision='0' anchorSides='10' anchorRadius='3' anchorBorderColor='FF8000'>";

        //Initialize <categories> element - necessary to generate a multi-series chart
        strCategories = "<categories>";

        //Initiate <dataset> elements
        strDataRev = "<dataset seriesName='Revenue' color='AFD8F8' >";
        strDataQty = "<dataset seriesName='Quantity' parentYAxis='S' color='FF8000' >";


        //Iterate through the data	
        for (i = 0; i < 4; i++)
        {
            //Append <category name='...' /> to strCategories
            strCategories += "<category name='" + arrData[i, 0] + "' />";
            //Add <set value='...' color='...'/> to both the datasets

            strDataRev += "<set value='" + arrData[i, 1] + "' />";
            strDataQty += "<set value='" + arrData[i, 2] + "' />";
        }

        //Close <categories> element
        strCategories += "</categories>";

        //Close <dataset> elements
        strDataRev += "</dataset>";
        strDataQty += "</dataset>";

        //Assemble the entire XML now
        strXML += strCategories + strDataRev + strDataQty + "</graph>";

        //Create the chart - MS Column 3D Line Combination Chart with data contained in strXML
        return FusionCharts.RenderChart("../FusionCharts/FCF_MSColumn3DLineDY.swf", "", strXML, "productSales", "600", "300", false, false);
    }
}
