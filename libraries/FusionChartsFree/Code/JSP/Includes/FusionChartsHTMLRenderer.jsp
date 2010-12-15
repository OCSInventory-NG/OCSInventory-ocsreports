<%
/*
 * Works with all jdk versions >=1.4.
 * Creates the object tag required to embed a chart.
 * Generates the object tag to embed the swf directly into the html page.<br>
 * Note: Only one of the parameters strURL or strXML has to be not null for this<br>
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
 *                Whether to start the chart in debug mode (Nt used in Free version)
 */

%>
<%
	String chartSWF= request.getParameter("chartSWF"); 
	String strURL= request.getParameter("strURL");
	String strXML= request.getParameter("strXML");
	String chartId= request.getParameter("chartId");
	String chartWidthStr= request.getParameter("chartWidth");
	String chartHeightStr= request.getParameter("chartHeight");
	String debugModeStr= request.getParameter("debugMode"); // not used in Free version
	
	int chartWidth= 0;
	int chartHeight=0;
	boolean debugMode=false;
	boolean registerWithJS=false;
	int debugModeInt =0;
	

	if(null!=chartWidthStr && !chartWidthStr.equals("")){
		chartWidth = Integer.parseInt(chartWidthStr);
	}
	if(null!=chartHeightStr && !chartHeightStr.equals("")){
		chartHeight = Integer.parseInt(chartHeightStr);
	}
	if(null!=debugModeStr && !debugModeStr.equals("")){
		debugMode = new Boolean(debugModeStr);
		debugModeInt= boolToNum(debugMode);
	}
	
	
	String strFlashVars="";
	
	if (strXML.equals("")) {
	    // DataURL Mode
	    strFlashVars = "chartWidth=" + chartWidth + "&chartHeight="
	    + chartHeight + "&debugMode=" + debugModeInt
	    + "&dataURL=" + strURL + "";
	} else {
	    // DataXML Mode
	    strFlashVars = "chartWidth=" + chartWidth + "&chartHeight="
	    + chartHeight + "&debugMode=" + debugModeInt
	    + "&dataXML=" + strXML + "";
	}
%> 
			<!--START Code Block for Chart <%=chartId%> -->
			<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" 
			width="<%= chartWidth%>" height="<%= chartHeight%>" id="<%= chartId%>">
			<param name="allowScriptAccess" value="always" />
			<param name="movie" value="<%=chartSWF%>"/>
			<param name="FlashVars" value="<%=strFlashVars%>" />
			<param name="quality" value="high" />
			<embed src="<%=chartSWF%>" FlashVars="<%=strFlashVars%>" 
			quality="high" width="<%=chartWidth%>" 
			height="<%=chartHeight%>" name="<%=chartId%>"
			allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
			</object>
			<!--END Code Block for Chart <%=chartId%> -->
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