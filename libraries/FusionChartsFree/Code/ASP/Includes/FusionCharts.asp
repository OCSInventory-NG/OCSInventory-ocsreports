<%
'Page: FusionCharts.asp
'Author: InfoSoft Global (P) Ltd.

'This page contains functions that can be used to render FusionCharts.

'encodeDataURL function encodes the dataURL before it's served to FusionCharts.
'If you've parameters in your dataURL, you necessarily need to encode it.
'Param: strDataURL - dataURL to be fed to chart
'Param: addNoCacheStr - Whether to add aditional string to URL to disable caching of data
Function encodeDataURL(strDataURL, addNoCacheStr)
	'Add the no-cache string if required
	if addNoCacheStr=true then
		'We add ?FCCurrTime=xxyyzz
		'If the dataURL already contains a ?, we add &FCCurrTime=xxyyzz
		'We replace : with _, as FusionCharts cannot handle : in URLs
		if Instr(strDataURL,"?")<>0 then
			strDataURL = strDataURL & "&FCCurrTime=" & Replace(Now(),":","_")
		else
			strDataURL = strDataURL & "?FCCurrTime=" & Replace(Now(),":","_")
		end if
	end if	
	'URL Encode it
	encodeDataURL = Server.URLEncode(strDataURL)
End Function

'renderChart renders the JavaScript + HTML code required to embed a chart.
'This function assumes that you've already included the FusionCharts JavaScript class
'in your page.

' chartSWF - SWF File Name (and Path) of the chart which you intend to plot
' strURL - If you intend to use dataURL method for this chart, pass the URL as this parameter. Else, set it to "" (in case of dataXML method)
' strXML - If you intend to use dataXML method for this chart, pass the XML data as this parameter. Else, set it to "" (in case of dataURL method)
' chartId - Id for the chart, using which it will be recognized in the HTML page. Each chart on the page needs to have a unique Id.
' chartWidth - Intended width for the chart (in pixels)
' chartHeight - Intended height for the chart (in pixels)
Function renderChart(chartSWF, strURL, strXML, chartId, chartWidth, chartHeight)
	'First we create a new DIV for each chart. We specify the name of DIV as "chartId"Div.			
	'DIV names are case-sensitive.
%>
	<!-- START Script Block for Chart <%=chartId%> -->
	<div id='<%=chartId%>Div' align='center'>
		Chart.
		<%
		'The above text "Chart" is shown to users before the chart has started loading
		'(if there is a lag in relaying SWF from server). This text is also shown to users
		'who do not have Flash Player installed. You can configure it as per your needs.
		%>
	</div>
		<%
		'Now, we render the chart using FusionCharts Class. Each chart's instance (JavaScript) Id 
		'is named as chart_"chartId".		
		%>
	<script type="text/javascript">	
		//Instantiate the Chart	
		var chart_<%=chartId%> = new FusionCharts("<%=chartSWF%>", "<%=chartId%>", "<%=chartWidth%>", "<%=chartHeight%>");
		<% 
		'Check whether we've to provide data using dataXML method or dataURL method
		if strXML="" then %>
		//Set the dataURL of the chart
		chart_<%=chartId%>.setDataURL("<%=strURL%>");
		<% else %>
		//Provide entire XML data using dataXML method 
		chart_<%=chartId%>.setDataXML("<%=strXML%>");
		<% end if %>		
		//Finally, render the chart.
		chart_<%=chartId%>.render("<%=chartId%>Div");
	</script>	
	<!-- END Script Block for Chart <%=chartId%> -->
	<%
End Function

'renderChartHTML function renders the HTML code for the JavaScript. This
'method does NOT embed the chart using JavaScript class. Instead, it uses
'direct HTML embedding. So, if you see the charts on IE 6 (or above), you'll
'see the "Click to activate..." message on the chart.
' chartSWF - SWF File Name (and Path) of the chart which you intend to plot
' strURL - If you intend to use dataURL method for this chart, pass the URL as this parameter. Else, set it to "" (in case of dataXML method)
' strXML - If you intend to use dataXML method for this chart, pass the XML data as this parameter. Else, set it to "" (in case of dataURL method)
' chartId - Id for the chart, using which it will be recognized in the HTML page. Each chart on the page needs to have a unique Id.
' chartWidth - Intended width for the chart (in pixels)
' chartHeight - Intended height for the chart (in pixels)
Function renderChartHTML(chartSWF, strURL, strXML, chartId, chartWidth, chartHeight)
	'Generate the FlashVars string based on whether dataURL has been provided
	'or dataXML.
	Dim strFlashVars
	if strXML="" then
		'DataURL Mode
		strFlashVars = "&chartWidth=" & chartWidth & "&chartHeight=" & chartHeight & "&dataURL=" & strURL
	else
		'DataXML Mode
		strFlashVars = "&chartWidth=" & chartWidth & "&chartHeight=" & chartHeight & "&dataXML=" & strXML 		
	end if
	%>
	<!-- START Code Block for Chart <%=chartId%> -->
	<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="<%=chartWidth%>" height="<%=chartHeight%>" id="<%=chartId%>">
		<param name="allowScriptAccess" value="always" />
		<param name="movie" value="<%=chartSWF%>"/>		
		<param name="FlashVars" value="<%=strFlashVars%>" />
		<param name="quality" value="high" />
		<embed src="<%=chartSWF%>" FlashVars="<%=strFlashVars%>" quality="high" width="<%=chartWidth%>" height="<%=chartHeight%>" name="<%=chartId%>" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
	</object>
	<!-- END Code Block for Chart <%=chartId%> -->
	<%
End Function

'boolToNum function converts boolean values to numeric (1/0)
Function boolToNum(bVal)
	Dim intNum
	if bVal=true then
		intNum = 1
	else
		intNum = 0
	end if
	boolToNum = intNum
End Function
%>