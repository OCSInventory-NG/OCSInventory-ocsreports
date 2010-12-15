<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Default.aspx.cs" Inherits="DB_JS_Default" %>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title>FusionCharts Free - Client Side Dynamic Chart ( using Database) Example </title>
    <script language="Javascript" type="text/javascript" src="../FusionCharts/FusionCharts.js">
		//You need to include the above JS file, if you intend to embed the chart using JavaScript.
		//Embedding using JavaScripts avoids the "Click to Activate..." issue in Internet Explorer
		//When you make your own charts, make sure that the path to this JS file is correct. Else, you would get JavaScript errors.
    </script>

    <script language="JavaScript" type="text/javascript">
		//Here, we use a mix of server side script (ASP) and JavaScript to
		//render our data for factory chart in JavaScript variables. We'll later
		//utilize this data to dynamically plot charts.
		
		//All our data is stored in the data array. From ASP, we iterate through
		//each recordset of data and then store it as nested arrays in this data array.
		var data = new Array();
		
		//Write the data as JavaScript variables here
		<%=jsVarString%>
		
		 //The data is now present as arrays in JavaScript. Local JavaScript functions
		 //can access it and make use of it. We'll see how to make use of it.
		
		/** 
		 * updateChart method is invoked when the user clicks on a pie slice.
		 * In this method, we get the index of the factory, build the XML data
		 * for that that factory, using data stored in data array, and finally
		 * update the Column Chart.
		 *	@param	factoryIndex	Sequential Index of the factory.
		*/		
		function updateChart(factoryIndex){
			//defining array of colors
			//We also initiate a counter variable to help us cyclically rotate through
			//the array of colors.
			var FC_ColorCounter=0;
			//var arr_FCColors= new Array(20);
			var arr_FCColors= new Array("1941A5" , "AFD8F8", "F6BD0F", "8BBA00", "A66EDD", "F984A1", "CCCC00", "999999", "0099CC", "FF0000", "006F00", "0099FF", "FF66CC", "669966", "7C7CB4", "FF9933", "9900FF", "99FFCC", "CCCCFF", "669900");
			
			
			//Storage for XML data document
			var strXML = "<graph caption='Factory " + factoryIndex  + " Output ' subcaption='(In Units)' xAxisName='Date' decimalPrecision='0' rotateNames='1' >";
			
			//Add <set> elements
			var i=0;
			for (i=0; i<data[factoryIndex].length; i++){
				strXML = strXML + "<set name='" + data[factoryIndex][i][0] + "' value='" + data[factoryIndex][i][1] + "' color='"+ arr_FCColors[++FC_ColorCounter % arr_FCColors.length] +"' />";
			}
			
			//Closing graph Element
			strXML = strXML + "</graph>";
						
			//Update it's XML
			updateChartXML("FactoryDetailed",strXML);

		}
    </script>

    <style type="text/css">
	<!--
	body {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
	}
	.text{
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
	}
	-->
	</style>
</head>
<body>
    <center>
        <h3>
            <a href="http://www.fusioncharts.com" target="_blank">FusionCharts Free</a> Database
            + JavaScript Example</h3>
        <h5>
            Inter-connected charts - Click on any pie slice to see detailed chart below.</h5>
        <p>
            The charts in this page have been dynamically generated using data contained in
            a database. We've NOT hard-coded the data in JavaScript.</p>
        <asp:Literal ID="FCLiteral1" runat="server"></asp:Literal>
        <br />
        <asp:Literal ID="FCLiteral2" runat="server"></asp:Literal>
        <br />
        <br />
        <a href='../NoChart.html' target="_blank">Unable to see the chart(s) above?</a>
        <br />
        <h5>
            <a href='../default.aspx'>&laquo; Back to list of examples</a></h5>
    </center>
</body>
</html>
