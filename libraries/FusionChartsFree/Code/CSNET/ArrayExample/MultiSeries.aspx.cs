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

public partial class ArrayExample_MultiSeries : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {
        // Generate chart in Literal Control
        FCLiteral.Text = CreateChart();
    }

    public string CreateChart(){

        //In this example, we plot a multi series chart from data contained
        //in an array. The array will have three columns - first one for data label (product)
        //and the next two for data values. The first data value column would store sales information
        //for current year and the second one for previous year.

        //Let's store the sales data for 6 products in our array. We also store
        //the name of products. 
        string [,] arrData =new string[6, 3];
        //Store Name of Products
        arrData[0, 0] = "Product A";
        arrData[1, 0] = "Product B";
        arrData[2, 0] = "Product C";
        arrData[3, 0] = "Product D";
        arrData[4, 0] = "Product E";
        arrData[5, 0] = "Product F";
        //Store sales data for current year
        arrData[0, 1] = "567500";
        arrData[1, 1] = "815300";
        arrData[2, 1] = "556800";
        arrData[3, 1] = "734500";;
        arrData[4, 1] = "676800";
        arrData[5, 1] = "648500";
        //Store sales data for previous year
        arrData[0, 2] = "547300";
        arrData[1, 2] = "584500";
        arrData[2, 2] = "754000";
        arrData[3, 2] = "456300";
        arrData[4, 2] = "754500";
        arrData[5, 2] = "437600";

        //Now, we need to convert this data into multi-series XML. 
        //We convert using string concatenation.
        //strXML - Stores the entire XML
        //strCategories - Stores XML for the <categories> and child <category> elements
        //strDataCurr - Stores XML for current year's sales
        //strDataPrev - Stores XML for previous year's sales
        string strXML, strCategories, strDataCurr, strDataPrev;
        int i;

        //Initialize <graph> element
        strXML = "<graph caption='Sales by Product' numberPrefix='$' decimalPrecision='0' >";

        //Initialize <categories> element - necessary to generate a multi-series chart
        strCategories = "<categories>";

        //Initiate <dataset> elements
        strDataCurr = "<dataset seriesName='Current Year' color='AFD8F8'>";
        strDataPrev = "<dataset seriesName='Previous Year' color='F6BD0F'>";

        //Iterate through the data	
        for(i=0;i<6;i++){
            //Append <category name='...' /> to strCategories
            strCategories += "<category name='" + arrData[i, 0] + "' />";
            //Add <set value='...' /> to both the datasets
            strDataCurr += "<set value='" + arrData[i, 1] + "' />";
            strDataPrev += "<set value='" + arrData[i, 2] + "' />";
        }

        //Close <categories> element
        strCategories += "</categories>";

        //Close <dataset> elements
        strDataCurr += "</dataset>";
        strDataPrev += "</dataset>";

        //Assemble the entire XML now
        strXML += strCategories + strDataCurr + strDataPrev + "</graph>";

        //Create the chart - MS Column 3D Chart with data contained in strXML
        return FusionCharts.RenderChart("../FusionCharts/FCF_MSColumn3D.swf", "", strXML, "productSales", "600", "300", false, false);

    }

}
