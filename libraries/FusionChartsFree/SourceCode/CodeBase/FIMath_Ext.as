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
Extended Math Utility function definitions

This file contains extensions to the base Math Object.
All the functions contained in this file can be used with any other Flash MX/+ movie for common procedures
*/
Math.calculatePoint = function(fromX, fromY, distance, angle) {
	//This function calculates the x and y co-ordinates of a point at an angular distance of "distance,angle" from the base point fromX, fromY
	//Convert the angle into radians
	angle = angle*(Math.PI/180);
	var xPos = fromX+(distance*Math.Cos(angle));
	var yPos = fromY-(distance*Math.sin(angle));
	return ({x:xPos, y:yPos});
};