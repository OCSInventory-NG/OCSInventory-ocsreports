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

public partial class DB_dataURL_PieData : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {
        //This page generates the XML data for the Pie Chart contained in
        //Default.asp. 	

        //For the sake of ease, we've used an Access database which is present in
        //../App_Data/FactoryDB.mdb. It just contains two tables, which are linked to each
        //other. 

        //Database Objects - Initialization
        DbConn oRs; string strQuery;

        //strXML will be used to store the entire XML document generated
        string strXML;

        //Generate the graph element
        strXML = "<graph caption='Factory Output report' subCaption='By Quantity' decimalPrecision='0' showNames='1' numberSuffix=' Units' pieSliceDepth='30' formatNumberScale='0'>";

        //Iterate through each factory
        strQuery = "select * from Factory_Master";
        oRs = new DbConn(strQuery);

        while (oRs.ReadData.Read())
        {
            //Now create second recordset to get details for this factory
            strQuery = "select sum(Quantity) as TotOutput from Factory_Output where FactoryId=" + oRs.ReadData["FactoryId"].ToString();

            DbConn oRs2 = new DbConn(strQuery);
            oRs2.ReadData.Read();
            //Generate <set name='..' value='..'/>		
            strXML += "<set name='" + oRs.ReadData["FactoryName"].ToString() + "' value='" + oRs2.ReadData["TotOutput"].ToString() + "' />";
            //Close recordset
            oRs2.ReadData.Close();

        }
        oRs.ReadData.Close();
        //Finally, close <graph> element
        strXML += "</graph>";

        //Set Proper output content-type
        Response.ContentType = "text/xml";

        //Just write out the XML data
        //NOTE THAT THIS PAGE DOESN'T CONTAIN ANY HTML TAG, WHATSOEVER
        Response.Write(strXML);

    }

}
