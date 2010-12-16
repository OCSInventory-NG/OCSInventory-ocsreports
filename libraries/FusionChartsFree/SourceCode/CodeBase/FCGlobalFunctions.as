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
Global utility function definitions

This file contains functions only, and is not associated with a movie clip
*/
_global.getFirstValue = function() {
	//This function is used to return the first non-null non-undefined non-empty value in a list of values
	for (var i = 0; i<arguments.length; i++) {
		if (arguments[i] != null && arguments[i] != undefined && arguments[i] != "") {
			return arguments[i];
		}
	}
	return "";
};
_global.createText = function(depth, strText, xPos, yPos, fontFamily, fontSize, fontColor, isBold, alignPos, vAlignPos, rotationAngle, isHTML) {
	//This function creates a text field according to the parameters passed
	//Parameters
	//--------------------------
	//depth - movie clip depth - Number
	//strText - text to be displayed in the text box - string
	//xPos - x position of the text box - Number
	//yPos - y position of the text box - Number
	//fontFamily - font face of the text - string
	//fontSize - size of the text - Number
	//fontColor - color without # like FFFFDD, 000222 - string
	//isBold - bold property - boolean
	//alignPos - alignment position - "center", "left", or "right" - string
	//vAlignPos- vertical alignment position - "center", "left", or "right" - string
	//rotationAngle - rotation angle of text (if any - else null)
	//isHTML - option whether the text would be rendered as HTML or as text
	//Returns - the width,height, xPos and yPos of the textbox created
	//As an object with properties textWidth and textHeight
	//--------------------------
	//Set defaults for isBold, alignPos, vAlignPos, rotationAngle, isHTML
	if (isBold == undefined || isBold == null || isBold == "") {
		isBold = false;
	}
	alignPos = getFirstValue(alignPos, "center");
	vAlignPos = getFirstValue(vAlignPos, "center");
	if (rotationAngle == undefined || rotationAngle == null || rotationAngle == "") {
		//By default, we assume that text would NOT be rotated
		rotationAngle = null;
	}
	//By default, we render all text as HTML
	if (isHTML == undefined || isHTML == null || isHTML == "") {
		isHTML = true;
	}
	//First, create a new Textformat object
	var fcTextFormat = new TextFormat();
	//Set the properties of the text format objects
	fcTextFormat.font = fontFamily;
	fcTextFormat.color = parseInt(fontColor, 16);
	fcTextFormat.size = fontSize;
	fcTextFormat.bold = isBold;
	//Create a textProperties object which would be returned to the caller 
	//to represent the text width and height
	var LTextProperties;
	LTextProperties = new Object();
	//Create the actual text field object now. - a & b are undefined variables
	//We want the initial text field size to be flexible
	createTextField("ASMovText_"+depth, depth, xPos, yPos, maxWidth, b);
	//Get a reference to the text field MC
	var fcText = eval("ASMovText_"+depth);
	//Set the properties
	fcText.multiLine = true;
	fcText.autoSize = alignPos;
	fcText.selectable = false;
	fcText.html = isHTML;
	//Set the text
	if (isHTML) {
		//If it's HTML text, set as htmlText
		fcText.htmlText = strText;
	} else {
		//Else, set as plain text
		fcText.text = strText;
	}
	//Now, depending on the rotation angle, set the embedding of fonts
	if (rotationAngle != null || rotationAngle != undefined) {
		//Set embedFonts to true
		fcText.embedFonts = true;
		//Set rotation
		fcText._rotation = rotationAngle;
	}
	//Apply the text format
	fcText.setTextFormat(fcTextFormat);
	//Re-adjust the rotation orientation (alignment)
	if (rotationAngle == null || rotationAngle == undefined) {
		switch (vAlignPos.toUpperCase()) {
		case "LEFT" :
			//Left is equivalent to top (of the ypos mid line - virtual)
			//        TEXT HERE
			//---------MID LINE---------
			//       (empty space)                 
			fcText._y = fcText._y-(fcText._height);
			break;
		case "CENTER" :
			//       (empty space)                 
			//---------TEXT HERE---------
			//       (empty space)                 
			fcText._y = fcText._y-(fcText._height/2);
			break;
		case "RIGHT" :
			//Right is equivalent to bottom
			//       (empty space)                 
			//---------MID LINE---------
			//         TEXT HERE
			fcText._y = fcText._y;
			break;
		}
	} else {
		//Now, re-adjust the x orientation of the text
		if (rotationAngle>=270) {
			switch (vAlignPos.toUpperCase()) {
			case "LEFT" :
				fcText._x = fcText._x;
				break;
			case "CENTER" :
				fcText._x = fcText._x-(fcText._width/2);
				break;
			case "RIGHT" :
				fcText._x = fcText._x-(fcText._width);
				break;
			}
		} else {
			switch (vAlignPos.toUpperCase()) {
			case "LEFT" :
				fcText._x = fcText._x+(fcText._width);
				break;
			case "CENTER" :
				fcText._x = fcText._x+(fcText._width/2);
				break;
			case "RIGHT" :
				fcText._x = fcText._x;
				break;
			}
		}
	}
	//Set 4 properties of the temporary object
	//textWidth, textHeight, textX, textY
	//These properties will be returned to the caller function.
	//for text manipulation
	LTextProperties.textWidth = fcText._width;
	LTextProperties.textHeight = fcText._height;
	//For fonts not included
	if (LTextProperties.textHeight<=4) {
		LTextProperties.textHeight = fontSize*2;
	}
	LTextProperties.textX = fcText._x;
	LTextProperties.textY = fcText._y;
	//Return this object
	return LTextProperties;
	//Delete the temporary objects
	delete LTextProperties;
	delete fcTextFormat;
	delete fcText;
};
_global.deleteText = function(depth) {
	//This function deletes a text box created using createText
	//Get a reference to the textbox
	mcText = eval("ASMovText_"+depth);
	//Remove it
	mcText.removeTextField();
	//Delete the reference
	delete mcText;
};
