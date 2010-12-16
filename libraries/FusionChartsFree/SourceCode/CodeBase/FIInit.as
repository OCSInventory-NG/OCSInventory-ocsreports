/*
 * FusionCharts Free v2
 * http://www.fusioncharts.com/free
 *
 * Copyright (c) 2009 InfoSoft Global (P) Ltd.
 * Dual licensed under the MIT (X11) and GNU GPL licenses.
 * http://www.fusioncharts.com/free/license
 *
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 * GPL License: http://www.gnu.org/copyleft/gpl.html
 *
 * Date: 2009-08-21
 */
//--------------------------------------------------------------------------------
/*
Chart Initialization functions

This file contains functions and constant definitions only, and is not associated with a movie clip
*/
//Multi-lingual Feature
/*
To include or load  XML data files that are not Unicode-encoded, 
we set system.useCodepage to true. The Flash Player will now interpret the 
XML file using the traditional code page of the operating system running 
the Flash Player. This is generally CP1252 for an English Windows operating 
system and Shift-JIS for a Japanese operating system.
*/
System.useCodePage = true;
/*
_isOnline represents whether the chart is working in Local or online mode. 
If it's local mode, FusionCharts would cache the data, else it would apply 
other ways to always received updated data from the defined source
*/
_isOnline = (this._url.subStr(0, 7) == "http://") || (this._url.subStr(0, 8) == "https://");
/*
Get the required chart width and height - if the user has specified any values, enforce that.
*/
_chartWidth = Number(getFirstValue(this.chartWidth, this.StageWidth, LChartWidth, Stage.width));
_chartHeight = Number(getFirstValue(this.chartHeight, this.StageHeight, LChartHeight, Stage.height));
/*
defaultDataFile represents the XML data file URI which would be loaded if no other URI or XML data has been provided to us.
*/
_defaultDataFile = getFirstValue(unescape(this.defaultDataFile), "Data.xml");
/*
_lastLevel is a variable used to keep track of the number of levels used up for rendering the chart. We use the rendering algorithm of Flash to order the z-axis-index of various elements of the chart. Initially, it is set to 1 (or this._lastLevel as set in loader movie) as we haven't rendered any element as yet.
*/
_lastLevel = Number(getFirstValue(this._lastLevel, this._FCLastLevel, 1));
/*
_xShift and _yShift refers to the initial x and y position for the chart. Basically, these parameters would be used when loading the chart inside other Flash movies
*/
_xShift = Number(getFirstValue(this._xShift, this._FCXShift, 0));
_yShift = Number(getFirstValue(this._yShift, this._FCYShift, 0));
/*
Calculate the chart horizontal and vertical center
*/
_chartHorCenter = _xShift+(_chartWidth/2);
_chartVerCenter = _yShift+(_chartHeight/2);
/*
_embedFontFace defines the font face which has been embedded in this Flash movie for rotated text boxes.
*/
_embedFontFace = getFirstValue(this._embedFontFace, "Verdana");

