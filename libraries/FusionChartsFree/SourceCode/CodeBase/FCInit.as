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
//Initialization Actions
//Set the properties of Stage to enable dynamic re-sizing
Stage.scaleMode = "noScale";
Stage.align = "TL";
//Multi-lingual Feature
/*
To include or load  XML data files that are not Unicode-encoded, we set system.useCodepage to true. The Flash Player will now interpret the XML file using the traditional code page of the operating system running the Flash Player. This is generally CP1252 for an English Windows operating system and Shift-JIS for a Japanese operating system.
*/
System.useCodePage = true;
/*
_isOnline represents whether the chart is working in Local or online mode. If it's local mode, FusionCharts would cache the data, else it would apply other ways to always received updated data from the defined source
*/
_global._isOnline = (this._url.subStr(0, 7) == "http://") || (this._url.subStr(0, 8) == "https://");
/*
Get the required chart width and height
*/
_global._chartWidth = Number(getFirstValue(_root.chartWidth, LChartWidth));
_global._chartHeight = Number(getFirstValue(_root.chartHeight, LChartHeight));
/*
defaultDataFile represents the XML data file URI which would be loaded if no other URI or XML data has been provided to us.
*/
_global._defaultDataFile = getFirstValue(unescape(_root.defaultDataFile), "Data.xml");
/*
FCLastLevel is a variable used to keep track of the number of levels used up for rendering the chart. We use the rendering algorithm of Flash to order the z-axis-index of various elements of the chart. Initially, it is set to 1 (or _global._FCLastLevel as set in loader movie) as we haven't rendered any element as yet.
*/
_global._FCLastLevel = Number(getFirstValue(_root._FCLastLevel, 1));
/*
_FCXShift and _FCYShift refers to the initial x and y position for the chart. Basically, these parameters would be used when loading the chart inside other Flash movies
*/
_global._FCXShift = Number(getFirstValue(_root._FCXShift, 0));
_global._FCYShift = Number(getFirstValue(_root._FCYShift, 0));
/*
Calculate the chart horizontal and vertical center
*/
_global._chartHorCenter = _FCXShift+(_chartWidth/2);
_global._chartVerCenter = _FCYShift+(_chartHeight/2);
/*
_global._embedFontFace defines the font face which has been embedded in this Flash movie for rotated text boxes.
*/
_global._embedFontFace = getFirstValue(_root._embedFontFace, "Verdana");

