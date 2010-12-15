<%
	/*
	 * Works with all jdk versions >=1.4.
	 * Creates the JavaScript + HTML code required to embed a chart.<br>
	 * Uses the javascript FusionCharts class to create the chart by supplying <br>
	 * the required parameters to it.<br>
	 * Note: Only one of the parameters strURL or strXML has to be non-empty for this<br>
	 * method to work. If both the parameters are provided then strURL is used for further processing.<br>
	 * 
	 * @param chartSWF -
	 *                SWF File Name (and Path) of the chart which you intend
	 *                to plot
	 * @param strURL -
	 *                If you intend to use dataURL method for this chart,
	 *                pass the URL as this parameter. Else, set it to "" (in
	 *                case of dataXML method)
	 * @param strXML -
	 *                If you intend to use dataXML method for this chart,
	 *                pass the XML data as this parameter. Else, set it to ""
	 *                (in case of dataURL method)
	 * @param chartId -
	 *                Id for the chart, using which it will be recognized in
	 *                the HTML page. Each chart on the page needs to have a
	 *                unique Id.
	 * @param chartWidth -
	 *                Intended width for the chart (in pixels)
	 * @param chartHeight -
	 *                Intended height for the chart (in pixels)
  	 * @param debugMode -
   	 *                Whether to start the chart in debug mode 
     * @param registerWithJS -
     *                Whether to ask chart to register itself with
     *                JavaScript	
	 */
%>
<%
	String chartSWF = request.getParameter("chartSWF");
	String strURL = request.getParameter("strURL");
	String strXML = request.getParameter("strXML");
	String chartId = request.getParameter("chartId");
	String chartWidthStr = request.getParameter("chartWidth");
	String chartHeightStr = request.getParameter("chartHeight");
	String debugModeStr= request.getParameter("debugMode"); 
	String registerWithJSStr= request.getParameter("registerWithJS"); 

	int chartWidth = 600;
	int chartHeight = 300;
	boolean debugMode=false;
	boolean registerWithJS=false;
	int debugModeInt = 0;
	int regWithJSInt = 0;
	

	if (null != chartWidthStr && !chartWidthStr.equals("")) {
		chartWidth = Integer.parseInt(chartWidthStr);
	}
	if (null != chartHeightStr && !chartHeightStr.equals("")) {
		chartHeight = Integer.parseInt(chartHeightStr);
	}
	if(null!=debugModeStr && !debugModeStr.equals("")){
		debugMode = new Boolean(debugModeStr);
		debugModeInt=boolToNum(debugMode);
	}
	if(null!=registerWithJSStr && !registerWithJSStr.equals("")){
		registerWithJS = new Boolean(registerWithJSStr);
		regWithJSInt=boolToNum(registerWithJS);
	}
	
	
%>
			<!-- START Script Block for Chart <%=chartId%> -->
			<div id='<%=chartId %>Div' align='center'>Chart.</div>
			<script type='text/javascript'>
			var chart_<%=chartId%> = new FusionCharts("<%=chartSWF %>", "<%=chartId%>", "<%=chartWidth %>", "<%= chartHeight%>", "<%= debugModeInt%>", "<%= regWithJSInt%>");
			
			<%	// Check whether we've to provide data using dataXML method or dataURL
				// method
			      
				if (strXML.equals("")) {
			%>
				    <!-- Set the dataURL of the chart-->
				    chart_<%= chartId%>.setDataURL("<%= strURL%>");
			
				<%} else {%>
				    // Provide entire XML data using dataXML method
				    chart_<%= chartId%>.setDataXML("<%= strXML%>");
				<%}%>
				<!-- Finally, render the chart.-->
				chart_<%=chartId%>.render("<%=chartId%>Div");
			</script>
			<!--END Script Block for Chart <%=chartId%> -->
<%!
    /**
     * Converts a Boolean value to int value<br>
     * 
     * @param bool Boolean value which needs to be converted to int value 
     * @return int value correspoding to the boolean : 1 for true and 0 for false
     */
   public int boolToNum(Boolean bool) {
	int num = 0;
	if (bool.booleanValue()) {
	    num = 1;
	}
	return num;
    }
%>