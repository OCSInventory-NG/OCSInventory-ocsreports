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
Utility function definitions

This file contains extensions to the base Math Object.
All the functions contained in this file can be used with any other Flash MX/+ movie for common procedures
*/
Math.formatDecimals = function(num, decimalPrecision) {
	//An extension of the Math object
	//This function formats a number into specified number of decimal places
	//If no decimal places needed, we're done
	if (decimalPrecision<=0) {
		return Math.round(num);
	}
	//Round the number to specified decimal places
	//e.g. 12.3456 to 3 digits (12.346) -> mult. by 10^decimalPrecision, round, div. by 10^decimalPrecision
	var tenToPower = Math.pow(10, decimalPrecision);
	var cropped = String(Math.round(num*tenToPower)/tenToPower);
	//Add decimal point if missing
	if (cropped.indexOf(".") == -1) {
		cropped += ".0";
		//e.g. 5 -> 5.0 (at least one zero is needed)
	}
	//Finally, force correct number of zeroes; add some if necessary
	var halves = cropped.split(".");
	//Grab numbers to the right of the decimal
	//Compare digits in right half of string to digits wanted
	var zerosNeeded = decimalPrecision-halves[1].length;
	//Number of zeros to add
	for (var i = 1; i<=zerosNeeded; i++) {
		//Add them
		cropped += "0";
	}
	return (cropped);
};