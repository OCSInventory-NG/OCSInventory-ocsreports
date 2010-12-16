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
All the functions contained in this file can be used with any other Flash MX/+ movie for common procedures
*/
function leftTrimString(strValue, strCharToBeTrimmed) {
	// This function left trims a certain character from the specified string
	strString = new String(strValue);
	//If the specified character is present in the string, continue...
	if (strString.indexOf(strCharToBeTrimmed) != -1) {
		//Get the length of the string
		intLength = strString.length;
		//The funda : Get the position of the first charatcer which isn't the character to be trimmed
		//And, then extract the rest of the string from that point onwards till the end
		startCharPos = -1;
		for (i=0; i<=intLength; i++) {
			if ((strString.charAt(i) != strCharToBeTrimmed) && (startCharPos == -1)) {
				startCharPos = i;
			}
		}
		strString = strString.subString(startCharPos);
	}
	return strString;
};
function formatHexColor(sourceHexColor) {
	// This function formats a hex color code into the format required by FusionCharts
	// In FusionCharts, the hex color code is represented without any #
	//First remove the leading spaces if any - just a precautionary measure
	var strHexColor = leftTrimString(sourceHexColor, " ");
	//Now remove the leading # from the hex color code if any
	strHexColor = leftTrimString(strHexColor, "#");
	return String(strHexColor);
};
function formatCommas(strNum, thousandSeparator, decimalSeparator) {
	//This function adds proper commas to a number
	//strNum - number in string format
	//***Why are numbers taken in string format***//
	//Here, we are asking for numbers in string format to preserve the leading 0s of decimals
	//Like as in -20.00, if number is just passed as number, Flash automatically reduces it to -20
	//intNum would represent the number in number format
	var intNum, strDecimalPart = "", boolIsNegative = false, intNumberFloor;
	var strNumberFloor, formattedNumber;
	var startPos, endPos;
	//Define startPos and endPos
	startPos = 0;
	endPos = strNum.length;
	intNum = Number(strNum);
	//If the number isn't a number at all, return an empty string
	if (intNum == NaN) {
		return "";
	}
	//Extract the decimal part
	if (strNum.indexOf(".") != -1) {
		strDecimalPart = strNum.subString(strNum.indexOf(".")+1, strNum.length);
		endPos = strNum.indexOf(".");
	}
	//Now, if the number is negative, get the value into the flag
	if (intNum<0) {
		boolIsNegative = true;
		startPos = 1;
	}
	//Now, extract the floor of the number
	intNumberFloor = strNum.subString(startPos, endPos);
	//Convert into string
	strNumberFloor = new String(intNumberFloor);
	//Now, intActualNumber contains the actual number to be formatted with commas		
	// If it's length is greater than 3, then format it
	if (strNumberFloor.length>3) {
		// Get the length of the number
		var lenNumber = strNumberFloor.length;
		for (var i = 0; i<=lenNumber; i++) {
			//Append proper commans
			if ((i>2) && ((i-1)%3 == 0)) {
				formattedNumber = strNumberFloor.charAt(lenNumber-i)+thousandSeparator+formattedNumber;
			} else {
				formattedNumber = strNumberFloor.charAt(lenNumber-i)+formattedNumber;
			}
		}
	} else {
		formattedNumber = strNumberFloor;
	}
	// Now, append the decimal part back	
	if (strDecimalPart != "") {
		formattedNumber = formattedNumber+decimalSeparator+strDecimalPart;
	}
	//Now, if neg num
	if (boolIsNegative == true) {
		formattedNumber = "-"+formattedNumber;
	}
	return formattedNumber;
};