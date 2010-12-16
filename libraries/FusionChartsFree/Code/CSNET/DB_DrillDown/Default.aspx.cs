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
using DataConnection;
using InfoSoftGlobal;

public partial class DB_DrillDown_Default : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {
        // Generate chart in Literal Control
        FCLiteral.Text = CreateChart();

    }

    public string CreateChart(){
        //In this example, we show how to connect FusionCharts to a database.
        //For the sake of ease, we've used an Access database which is present in
        //../App_Data/FactoryDB.mdb. It just contains two tables, which are linked to each
        //other. 

        //Database Objects - Initialization
        DbConn oRs; string strQuery;

        //strXML will be used to store the entire XML document generated
        string strXML;

        //Generate the graph element
        strXML = "<graph caption='Factory Output report' subCaption='By Quantity' decimalPrecision='0' showNames='1' numberSuffix=' Units' pieSliceDepth='30'  formatNumberScale='0' >";
        
        // Sql Query
        strQuery = "select a.FactoryId,a.FactoryName, sum(b.Quantity) as TotOutput from factory_master a,factory_output b where a.FactoryId=b.FactoryId group by a.FactoryId,a.FactoryName";

        // Open data reader
        oRs = new DbConn(strQuery);

        //Iterate through each factory
        while(oRs.ReadData.Read()){
            
            //Generate <set name='..' value='..' link='..' />
            //Note that we're setting link as Detailed.asp?FactoryId=<<FactoryId>>&FactoryName=<<FactoryName>>
            strXML += "<set name='" + oRs.ReadData["FactoryName"].ToString() + "' value='" + oRs.ReadData["TotOutput"].ToString() + "' link='" + Server.UrlEncode("Detailed.aspx?FactoryId=" + oRs.ReadData["FactoryId"].ToString() + "&FactoryName=" + oRs.ReadData["FactoryName"].ToString()) + "'/>";
           
        }
    
        //Finally, close <graph> element
        strXML += "</graph>";
        // Close Data Reader
        oRs.ReadData.Close();

        //Create the chart - Pie 3D Chart with data from strXML
        return FusionCharts.RenderChart("../FusionCharts/FCF_Pie3D.swf", "", strXML, "FactorySum", "650", "450", false, false);
    }
}
