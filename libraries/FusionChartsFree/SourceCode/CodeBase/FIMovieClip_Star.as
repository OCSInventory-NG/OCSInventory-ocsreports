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
Extended function definitions

This file contains extensions to the base MovieClip Object.
All the functions contained in this file can be used with any other Flash MX/+ movie for common procedures
*/
//-------------------------------------------------------------
//	MovieClip.drawStar is a method for drawing star shaped polygons. 
//-------------------------------------------------------------
MovieClip.prototype.drawStar = function(x, y, points, innerRadius, outerRadius, angle) {
	// ==============
	// x, y = center of star
	// points = number of points (Math.abs(points) must be > 2)
	// innerRadius = radius of the indent of the points
	// outerRadius = radius of the tips of the points
	// angle = [optional] starting angle in degrees. (defaults to 0)
	// ==============
	if (arguments.length<5) {
		return;
	}
	var count = Math.abs(points);
	if (count>2) {
		// init vars
		var step, halfStep, start, n, dx, dy;
		// calculate distance between points
		step = (Math.PI*2)/points;
		halfStep = step/2;
		// calculate starting angle in radians
		start = (angle/180)*Math.PI;
		this.moveTo(x+(Math.cos(start)*outerRadius), y-(Math.sin(start)*outerRadius));
		// draw lines
		for (n=1; n<=count; n++) {
			dx = x+Math.cos(start+(step*n)-halfStep)*innerRadius;
			dy = y-Math.sin(start+(step*n)-halfStep)*innerRadius;
			this.lineTo(dx, dy);
			dx = x+Math.cos(start+(step*n))*outerRadius;
			dy = y-Math.sin(start+(step*n))*outerRadius;
			this.lineTo(dx, dy);
		}
	}
};
