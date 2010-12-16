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

//------------ COLOR RELATED FUNCTIONS --------------------//
function getDarkColor(sourceHexColor, intensityRequired) {
	// This function sets a darker color for the specified object
	// Based on the intensity specified
	//First format the hexcolor to trim the # and leading spaces
	sourceHexColor = formatHexColor(sourceHexColor);
	//Format the color in RGB notation
	sourceclrRGB = parseInt(sourceHexColor, 16);
	//Now, get the r,g,b values separated out of the specified color
	var r = Math.floor(sourceclrRGB/65536);
	var g = Math.floor((sourceclrRGB-r*65536)/256);
	var b = sourceclrRGB-r*65536-g*256;
	//Now, get the darker color based on the Intesity Specified
	var darkColor = (r*intensityRequired) << 16 | (g*intensityRequired) << 8 | (b*intensityRequired);
	return (darkColor);
};
function getLightColor(sourceHexColor, intensityRequired){
	// This function sets a lighter color for the specified object
	// Based on the intensity specified
	//First format the hexcolor to trim the # and the leading spaces
	sourceHexColor = formatHexColor(sourceHexColor);
	//Format the color in RGB notation	
	sourceclrRGB = parseInt(sourceHexColor, 16);
	//Now, get the r,g,b values separated out of the specified color
	var r = Math.floor(sourceclrRGB/65536);
	var g = Math.floor((sourceclrRGB-r*65536)/256);
	var b = sourceclrRGB-r*65536-g*256;
	//Now, get the lighter color based on the Intesity Specified
	var lightColor = (256-((256-r)*intensityRequired)) << 16 | (256-((256-g)*intensityRequired)) << 8 | (256-((256-b)*intensityRequired));
	return (lightColor);
};
