MovieClip.prototype.draw3DPie = function(radius, yRadius, startAngle, sweepAngle, pieH, clr, pieBorderThickness, pieBorderAlpha, pieFillAlpha) {
	if (sweepAngle>360 || sweepAngle<-360) {
		sweepAngle = 360;
	}
	if (startAngle>360) {
		startAngle %= 360;
	}
	//If no y radius has been defined
	if (yRadius == undefined) {
		yRadius = radius;
	}
	var centerX = 0;
	var centerY = -pieH/2;
	var clr2 = getDarkColor(clr, 0.7);
	this.clear();
	this.lineStyle(pieBorderThickness, parseInt(clr, 16), pieBorderAlpha);
	this.beginFill(parseInt(clr, 16), pieFillAlpha);
	this.moveTo(centerX, centerY);
	//move to center
	var obj = new Object();
	//empty object
	this.drawArc(centerX, centerY, radius, yRadius, startAngle, sweepAngle, obj);
	this.lineTo(centerX, centerY);
	this.endFill();
	//draw visible pie sides if any
	if (pieH) {
		this.lineStyle(pieBorderThickness, clr2, pieBorderAlpha);
		this.beginFill(clr2, pieFillAlpha);
		var mid = false;
		var rht = false;
		var lft = false;
		var bck = false;
		//draw height on bottom 
		if (sweepAngle<360 && (obj.y1>=centerY || obj.y2>=centerY)) {
			if (obj.x1<=obj.x2) {
				if (obj.y1>=centerY && obj.y2>=centerY) {
					mid = true;
				} else if (obj.y1>=centerY) {
					rht = true;
				} else if (obj.y2>=centerY) {
					bck = true;
				}
			} else {
				if (obj.y1>centerY) {
					rht = true;
				}
				if (obj.y2>centerY) {
					lft = true;
				}
			}
		} else if (sweepAngle>=360 || obj.x1<obj.x2) {
			bck = true;
		}
		if (mid) {
			this.moveTo(obj.x1, obj.y1);
			this.drawArc(centerX, centerY+pieH, radius, yRadius, startAngle, sweepAngle);
			this.drawArc(centerX, centerY, radius, yRadius, startAngle+sweepAngle, -sweepAngle);
		}
		if (rht) {
			this.moveTo(obj.x1, obj.y1);
			this.drawArc(centerX, centerY+pieH, radius, yRadius, startAngle, 360-startAngle);
			this.drawArc(centerX, centerY, radius, yRadius, 0, startAngle-360);
		}
		if (lft) {
			var tmp = (startAngle+sweepAngle)%180;
			this.moveTo(centerX-radius, centerY);
			this.drawArc(centerX, centerY+pieH, radius, yRadius, 180, tmp);
			this.drawArc(centerX, centerY, radius, yRadius, 180+tmp, -tmp);
		}
		if (bck) {
			this.moveTo(centerX-radius, centerY);
			this.drawArc(centerX, centerY+pieH, radius, yRadius, 180, 180);
			this.drawArc(centerX, centerY, radius, yRadius, 0, -180);
		}
		this.endFill();
	}
	//Label text positions
	var labelOff = -5;
	var labelAng = (startAngle+sweepAngle/2)+labelOff;
	var tbLabelX = (radius+pieH)*Math.cos(labelAng*Math.PI/180);
	var tbLabelY = -(yRadius+pieH)*Math.sin(labelAng*Math.PI/180);
	return {valueTBX:tbLabelX, valueTBY:tbLabelY};
};
MovieClip.prototype.drawArc = function(x, y, r, yRadius, startAngle, sweepAngle, obj) {
	//This method renders pie shaped arc (wedges)
	//x, y is the center point of the arc
	//r is the radius
	//yRadius is the y-radius
	//startAngle is the start angle of the arc anti-clockwise w.r.t. x axis
	//sweepAngle is the arc angle (sweeping angle)
	//The start angle cannot be more than 360
	if (startAngle>360) {
		startAngle -= 360;
	}
	//If no y radius has been defined
	if (yRadius == undefined) {
		yRadius = r;
	}
	//	Same with sweepAngle
	if (Math.abs(sweepAngle)>360) {
		sweepAngle = 360;
	}
	// Flash uses 8 segments per circle, to match that, we draw in a maximum
	// of 45 degree segments. First we calculate how many segments are needed
	// for our arc.
	var nSubArcs = Math.floor(Math.abs(sweepAngle)/45);
	//After multiples of 45, we'll have some left over degs
	var leftoverAng = Math.abs(sweepAngle)-nSubArcs*45;
	//Finding the angle (radians) and its half (for control points)
	var ang45 = ((sweepAngle>=0) ? 45 : -45)*Math.PI/180;
	var ang225 = ang45/2;
	var cosHT = Math.cos(ang225);
	//Convert the offset angle to radians
	startAngle *= (Math.PI/180);
	//Get the starting position of the arc
	var x1 = x+Math.cos(startAngle)*r;
	var y1 = y-Math.sin(startAngle)*yRadius;
	//Draw a line from the center of the arc to the starting position
	this.lineTo(x1, y1);
	//Determine the mid angle - to calculate the control point
	var angleMid = startAngle-ang225;
	//x2 and y2 are the end points of arc
	//cx and cy are the control points for each arc segment
	var i, x2, y2, cx, cy;
	var rc = r/cosHT;
	for (i=0; i<nSubArcs; i++) {
		//Create each arc segment
		x2 = x+Math.cos(startAngle+ang45)*r;
		y2 = y-Math.sin(startAngle+ang45)*yRadius;
		cx = x+Math.cos(startAngle+ang225)*(rc);
		cy = y-Math.sin(startAngle+ang225)*(yRadius/cosHT);
		this.curveTo(cx, cy, x2, y2);
		startAngle += ang45;
	}
	if (leftoverAng) {
		//Now, if some angle is left over, create the arc for that too
		leftoverAng *= ((sweepAngle<0) ? -Math.PI : Math.PI)/180;
		rc = r/Math.cos(leftoverAng/2);
		rc2 = yRadius/Math.cos(leftoverAng/2);
		x2 = x+Math.cos(startAngle+leftoverAng)*r;
		y2 = y-Math.sin(startAngle+leftoverAng)*yRadius;
		cx = x+Math.cos(startAngle+leftoverAng/2)*rc;
		cy = y-Math.sin(startAngle+leftoverAng/2)*rc2;
		//Draw curve to the end of sub-arc segment through control points cx, cy
		this.curveTo(cx, cy, x2, y2);
	}
	//Now, return the starting and endind point of arc as object
	if (obj != undefined) {
		obj.x1 = Math.round(x1*1000)/1000;
		obj.y1 = Math.round(y1*1000)/1000;
		obj.x2 = Math.round(x2*1000)/1000;
		obj.y2 = Math.round(y2*1000)/1000;
	}
};
