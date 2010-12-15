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
using Utilities;
using InfoSoftGlobal;

public partial class DB_DrillDown_Detailed : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {
        // Generate chart in Literal Control
        FCLiteral.Text = CreateChart();
    }
    public string CreateChart(){
        //This page is invoked from Default.asp. When the user clicks on a pie
        //slice in Default.asp, the factory Id is passed to this page. We need
        //to get that factory id, get information from database and then show
        //a detailed chart.

        //First, get the factory Id
        string FactoryId, FactoryName;
        Util util = new Util();
        //Request the factory Id from Querystring
        FactoryId = Request["FactoryId"];
        FactoryName = Request["FactoryName"];

        DbConn oRs; string strQuery;
        //strXML will be used to store the entire XML document generated
        string strXML;

        //Generate the graph element string
        strXML = "<graph caption='" + FactoryName + " Output ' subcaption='(In Units)' xAxisName='Date' showValues='1' decimalPrecision='0' rotateNames='1' >";
        
        //Now, we get the data for that factory
        strQuery = "select * from Factory_Output where FactoryId=" + FactoryId;
        oRs = new DbConn(strQuery);
        while(oRs.ReadData.Read()){
            //Here, we convert date into a more readable form for set name.

            strXML += "<set name='" + Convert.ToDateTime(oRs.ReadData["DatePro"]).ToString("dd/MM/yyyy") + "' value='" + oRs.ReadData["Quantity"].ToString() + "' color='" + util.getFCColor() + "'/>";
            
        }
        //Close <graph> element
        strXML += "</graph>";
        oRs.ReadData.Close();

        //Create the chart - Column 2D Chart with data from strXML
        return FusionCharts.RenderChart("../FusionCharts/FCF_Column2D.swf", "", strXML, "FactoryDetailed", "600", "300", false, false);
    }
}
