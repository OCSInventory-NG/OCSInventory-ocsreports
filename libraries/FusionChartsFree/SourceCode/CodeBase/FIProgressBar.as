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
FIProgressBar Class

	The FIProgressBar Class implements a very lightweight progress Bar. It handles all the inputs
	and outputs required by a Progress Bar component, including the minimum value, maximum value,
	current value and other cosmetic properties.

	This file is a pure ActionScript Object.

Component APIs:
	setLimits(minValue, maxValue)
	-----------------------------
	setLimits helps to set the numerical maximum and minimum value for the progress bar to be plotted.
	To plot a progress bar to show the loading of a movie - minValue would be 0 and	maxValue would be 
	the total size of the flash movie.

	setPosition(x,y)
	----------------
	Helps set the X and Y position of the progress bar. The x and y represents the top left position of
	the bar.

	setSize(width, height)
	----------------------
	Helps set the width and height of the progress bar.

	setColors(Background Color, Border Color)
	-----------------------------------------
	Helps set the background and border color. All colors to be provided in hex code without #.

	setBorderThickness()
	--------------------
	Helps set the border thickness

	setValue(value)
	---------------
	This method sets and updates the current value of the progress bar. Use this method to update the 
	progress bar.
	
	draw()
	------
	Draws the base of the progress bar. To be called once only after initialization.
*/
_global.FIProgressBar = function() {
	//Initializations
	this.minValue = -1;
	this.maxValue = -1;
	this.previousValue = -1;
	this.currentValue = -1;
};
FIProgressBar.prototype = new MovieClip();
Object.registerClass("FIProgressBar", FIProgressBar);
FIProgressBar.prototype.onLoad = function() {
	//Nothing to do on-load event
};
FIProgressBar.prototype.setPosition = function(x, y) {
	//This method sets the x and y position of the progress bar
	this.x = x;
	this.y = y;
};
FIProgressBar.prototype.setSize = function(width, height) {
	//This method sets the width and height position of the progress bar
	this.width = width;
	this.height = height;
};
FIProgressBar.prototype.setColors = function(bgColor, borderColor) {
	//This method sets the colors
	this.bgColor = parseInt(bgColor, 16);
	this.borderColor = parseInt(borderColor, 16);
};
FIProgressBar.prototype.setBorderThickness = function(borderThickness) {
	//Sets the border thickness
	this.borderThickness = borderThickness;
};
FIProgressBar.prototype.setLimits = function(minValue, maxValue) {
	//This method sets the minimum and maximum value for the progress bar
	//Set the minimum and maximum values only if they are not 0, a number and not null
	if (minValue != undefined && minValue != null && isNaN(minValue) == false) {
		this.minValue = minValue;
	}
	if (maxValue != undefined && maxValue != null && isNaN(maxValue) == false && maxValue>0) {
		this.maxValue = maxValue;
	}
};
FIProgressBar.prototype.setValue = function(intValue) {
	//Update the current value
	if (intValue != undefined && intValue != null && isNaN(intValue) == false && ((intValue>=this.minValue) && (intValue<=this.maxValue))) {
		this.previousValue = this.currentValue;
		this.currentValue = intValue;
	}
	//Re-draw progress fill bar only if the current value differs from the previous value
	if (this.previousValue != this.currentValue) {
		this.updateProgess();
	}
};
FIProgressBar.prototype.draw = function() {
	//Here, we draw the progress bar border and background
	//Draw the border
	this.createEmptyMovieClip("PGBorder", 1);
	with (this.PGBorder) {
		lineStyle(this.borderThickness, this.borderColor, 100);
		moveTo(this.x, this.y);
		lineTo(this.x+this.width, this.y);
		lineTo(this.x+this.width, this.y+this.height);
		lineTo(this.x, this.y+this.height);
		lineTo(this.x, this.y);
	}
};
FIProgressBar.prototype.updateProgess = function() {
	//Calculate the width required to be filled
	var fillWidth;
	fillWidth = ((this.currentValue-this.minValue)/this.maxValue)*this.width;
	//Draw the fill bar
	this.createEmptyMovieClip("PGFill", 0);
	with (this.PGFill) {
		lineStyle(this.borderThickness, this.borderColor, 0);
		beginFill(this.bgColor, 100);
		moveTo(this.x, this.y);
		lineTo(this.x+fillWidth, this.y);
		lineTo(this.x+fillWidth, this.y+this.height);
		lineTo(this.x, this.y+this.height);
		lineTo(this.x, this.y);
		endFill();
	}
};
FIProgressBar.prototype.erase = function() {
	//This function removes the progress bar
	this.removeMovieClip();
};