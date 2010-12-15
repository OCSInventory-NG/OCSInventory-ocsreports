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
	mc.drawRect is a method for creating regular
	or rounded corner rectangles. 
-------------------------------------------------------------*/
MovieClip.prototype.drawRect = function( x, y, w, h, cornerRadius) {
	//We first see if the user has defined a corner radius for us. If yes, we need to create curves and calculate
	if (cornerRadius>0) {
		//Initialize variables to be usd
		var theta, angle, cx, cy, px, py;
		//Make sure that (w,h) is larger than 2*cornerRadius
		if (cornerRadius>Math.min(w, h)/2) {
			cornerRadius = Math.min(w, h)/2;
		}
		// theta = 45 degrees in radians
		theta = Math.PI/4;
		// draw top line
		this.moveTo(x+cornerRadius, y);
		this.lineTo(x+w-cornerRadius, y);
		//angle is currently 90 degrees
		angle = -Math.PI/2;
		// draw tr corner in two parts
		cx = Math.round(x+w-cornerRadius+(Math.cos(angle+(theta/2))*cornerRadius/Math.cos(theta/2)));
		cy = Math.round(y+cornerRadius+(Math.sin(angle+(theta/2))*cornerRadius/Math.cos(theta/2)));
		px = Math.round(x+w-cornerRadius+(Math.cos(angle+theta)*cornerRadius));
		py = Math.round(y+cornerRadius+(Math.sin(angle+theta)*cornerRadius));
		this.curveTo(cx, cy, px, py);
		angle += theta;
		cx = Math.round(x+w-cornerRadius+(Math.cos(angle+(theta/2))*cornerRadius/Math.cos(theta/2)));
		cy = Math.round(y+cornerRadius+(Math.sin(angle+(theta/2))*cornerRadius/Math.cos(theta/2)));
		px = Math.round(x+w-cornerRadius+(Math.cos(angle+theta)*cornerRadius));
		py = Math.round(y+cornerRadius+(Math.sin(angle+theta)*cornerRadius));
		this.curveTo(cx, cy, px, py);
		// draw right line
		this.lineTo(x+w, y+h-cornerRadius);
		// draw br corner
		angle += theta;
		cx = x+w-cornerRadius+(Math.cos(angle+(theta/2))*cornerRadius/Math.cos(theta/2));
		cy = y+h-cornerRadius+(Math.sin(angle+(theta/2))*cornerRadius/Math.cos(theta/2));
		px = x+w-cornerRadius+(Math.cos(angle+theta)*cornerRadius);
		py = y+h-cornerRadius+(Math.sin(angle+theta)*cornerRadius);
		this.curveTo(cx, cy, px, py);
		angle += theta;
		cx = x+w-cornerRadius+(Math.cos(angle+(theta/2))*cornerRadius/Math.cos(theta/2));
		cy = y+h-cornerRadius+(Math.sin(angle+(theta/2))*cornerRadius/Math.cos(theta/2));
		px = x+w-cornerRadius+(Math.cos(angle+theta)*cornerRadius);
		py = y+h-cornerRadius+(Math.sin(angle+theta)*cornerRadius);
		this.curveTo(cx, cy, px, py);
		// draw bottom line
		this.lineTo(x+cornerRadius, y+h);
		// draw bl corner
		angle += theta;
		cx = x+cornerRadius+(Math.cos(angle+(theta/2))*cornerRadius/Math.cos(theta/2));
		cy = y+h-cornerRadius+(Math.sin(angle+(theta/2))*cornerRadius/Math.cos(theta/2));
		px = x+cornerRadius+(Math.cos(angle+theta)*cornerRadius);
		py = y+h-cornerRadius+(Math.sin(angle+theta)*cornerRadius);
		this.curveTo(cx, cy, px, py);
		angle += theta;
		cx = x+cornerRadius+(Math.cos(angle+(theta/2))*cornerRadius/Math.cos(theta/2));
		cy = y+h-cornerRadius+(Math.sin(angle+(theta/2))*cornerRadius/Math.cos(theta/2));
		px = x+cornerRadius+(Math.cos(angle+theta)*cornerRadius);
		py = y+h-cornerRadius+(Math.sin(angle+theta)*cornerRadius);
		this.curveTo(cx, cy, px, py);
		// draw left line
		this.lineTo(x, y+cornerRadius);
		// draw tl corner
		angle += theta;
		cx = x+cornerRadius+(Math.cos(angle+(theta/2))*cornerRadius/Math.cos(theta/2));
		cy = y+cornerRadius+(Math.sin(angle+(theta/2))*cornerRadius/Math.cos(theta/2));
		px = x+cornerRadius+(Math.cos(angle+theta)*cornerRadius);
		py = y+cornerRadius+(Math.sin(angle+theta)*cornerRadius);
		this.curveTo(cx, cy, px, py);
		angle += theta;
		cx = x+cornerRadius+(Math.cos(angle+(theta/2))*cornerRadius/Math.cos(theta/2));
		cy = y+cornerRadius+(Math.sin(angle+(theta/2))*cornerRadius/Math.cos(theta/2));
		px = x+cornerRadius+(Math.cos(angle+theta)*cornerRadius);
		py = y+cornerRadius+(Math.sin(angle+theta)*cornerRadius);
		this.curveTo(cx, cy, px, py);
	} else {
		//No corner radius defined - so draw a simple rectangle
		this.moveTo(x, y);
		this.lineTo(x+w, y);
		this.lineTo(x+w, y+h);
		this.lineTo(x, y+h);
		this.lineTo(x, y);
	}
};