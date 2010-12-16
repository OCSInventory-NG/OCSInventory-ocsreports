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
/*-------------------------------------------------------------
	mc.drawPoly is a method for creating regular
	polygons. Negative values for sides will draw the
	polygon in the reverse direction, which allows for
	creating knock-outs in masks.
-------------------------------------------------------------*/
MovieClip.prototype.drawPoly = function(x, y, sides, radius, angle) {
	// ==============
	// mc.drawPoly() - by Ric Ewing (ric@formequalsfunction.com) - version 1.4 - 4.7.2002
	// 
	// x, y = center of polygon
	// sides = number of sides (Math.abs(sides) must be > 2)
	// radius = radius of the points of the polygon from the center
	// angle = [optional] starting angle in degrees. (defaults to 0)
	// ==============
	if (arguments.length<4) {
		return;
	}
	// convert sides to positive value
	var count = Math.abs(sides);
	// check that count is sufficient to build polygon
	if (count>2) {
		// init vars
		var step, start, n, dx, dy;
		// calculate span of sides
		step = (Math.PI*2)/sides;
		// calculate starting angle in radians
		start = (angle/180)*Math.PI;
		this.moveTo(x+(Math.cos(start)*radius), y-(Math.sin(start)*radius));
		// draw the polygon
		for (n=1; n<=count; n++) {
			dx = x+Math.cos(start+(step*n))*radius;
			dy = y-Math.sin(start+(step*n))*radius;
			this.lineTo(dx, dy);
		}
	}
};
