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
XML Initialization functions and data loading (and related) functions

This file contains functions and constant definitions only, and is not associated with a movie clip
*/
//A temporary XML object to load the XML data
this.TxmlDataDoc = new XML();

//If this.data is empty, set it this.dataXML
if (this.data==null || this.data==undefined || this.data==""){
	this.data = this.dataXML;
}

function isDataURLProvided() {
	//This function checks whether we've been provided with dataURL or data XML
	//By default we assume that the dataURL has been provided and store the value in a temporary flag
	var LBoolURLProvided;
	LBoolURLProvided = true;
	//Now check.
	if (this.dataURL.length<1) {
		//Since the length of dataURL is less than 1
		//We haven't been provided with dataURL
		if (this.data == "" || this.data == null || this.data == undefined) {
			//Now, if we haven't been provided with data XML also.
			//We set the data URL to a default data file
			this.dataURL = _defaultDataFile;
		} else {
			//We have been provided with the full XML document
			//So, re-set the flag
			LBoolURLProvided = false;
		}
	}
	return LBoolURLProvided;
}
function filterDataURL(strURL) {
	// This function filters the dataURL provided to it.
	/*
	Steps to filter it before we can invoke the XML request. The filter involves the following jobs:
	1. Convert from old * encoded format to the normal format - to support backward compatibility (pre 2.2 AutoFit charts)
	2. Convert the URL Encoded dataURL back to normal form.
	3. Create the no-cache form of the URL
	*/
	//Convert from old format to new
	strURL = convertFromOldDataUrl(strURL);
	//Unescape the XML URL to convert the hexadecimal coded characters back into normal
	strURL = unescape(strURL);
	//Get the no-cache URL
	strURL = getNoCacheURL(strURL);
	//Return it
	return strURL;
}
/*
showDataError function shows a data error to the user and stops the play of the movie. It also erases the progress bar.
*/
function showDataError(strError) {
	createText(2, strError, _chartHorCenter, _chartVerCenter-(LPBarHeight/2), "Verdana", "10", LPBarTextColor, false, "center", "left", null, false);
	//Hide the progress bar
	FIPB.erase();
	//Stop the play
	stop();
}

/*
dataLoaded function acts as the onLoad event handler for this.TxmlDataDoc XML Object.
success is a Boolean value indicating whether the XML object was successfully loaded. If the XML document is received successfully, the success parameter is true. If the document was not received, or if an error occurred in receiving the response from the server, the success parameter is false. 
Based on this success parameter, we'll show the required msgs to the user.
*/
function dataLoaded(success) {
	if (success) {
		//Data has been loaded successfully - so check for validity of data
		if (TxmlDataDoc.status == 0) {
			//Data is error free
			//So jump to FDataLoadFinalize Frame
			gotoAndPlay("FDataLoadFinalize");
		} else {		
			//XML Data is not well-formed. Show an error
			showDataError("Invalid XML Data");
		}
	} else {
		//An error occurred while fetching the data. Show an error to viewer
		showDataError("Error in Loading Data");
	}
}
//--- Functions below are not directly invoked. They're invoked from some other functions --//
function convertFromOldDataUrl(strOldUrl) {
	//This function converts the old format dataURL into normalized form to provide backward compatibility.
	//In the old format, the parameters in dataURL were separated by * instead of ? and &.
	//e.g., DataProvider.asp*id=1*subId=34 instead of DataProvider.asp?id=1&subId=34
	var strURL = new String(strOldUrl);
	//First thing, we check if the dataURL is actually in old format	
	if (strURL.indexOf("*") != -1) {
		//Use the split function of array to split the URL wherever a * is found
		var arrUrl = new Array();
		arrUrl = strURL.split("*");
		var finalUrl = "";
		//Now, join them depending on their position
		for (loopvar=0; loopvar<arrUrl.length; loopvar++) {
			if (loopvar == 0) {
				finalUrl = arrUrl[0];
			} else if (loopvar == 1) {
				finalUrl = finalUrl+"?"+arrUrl[1];
			} else {
				finalUrl = finalUrl+"&"+arrUrl[loopvar];
			}
		}
		//Return the formatted URL
		return finalUrl;
	} else {
		//Simply return the URL sent to this function
		return strOldUrl;
	}
}
function getNoCacheURL(strURL) {
	//In this function, we create a non-cache URL. If we're not working in local mode, we'll append the time at the end of the dataURL so that a new XML document is sent by the server for each requestand the XML data is not cached. Suppose, the dataURL is data.asp, so we'll convertit to data.asp?curr=43743 so that we can fool the server and get new data every time we request for it.
	// :Explanation: How to stop the caching of the XML data document
	//If the chart is not working in local mode, we will add a continuously updating data (number of milliseconds that have elapsed since the movie started playing) at the end of the dataURL. This will result in having a new dataURL every time we need to get the data from the server and therefore the server will be "fooled" thereby passing on updated dataeach time.
	//We add the time in the format ?curr=xxxxx or &curr=xxxxx depending on whetherthere's already a ? present in the dataURL or not. That is, if filtered dataURLis data.asp?param1=value1, then we add curr as data.asp?param1=value1&curr=xxxxx. However, if dataURL is simply data.asp, we add curr as data.asp?curr=xxxxx	
	if (_isOnline) {
		//Do this only if we are dealing with dataURL and we are working online
		if (strURL.indexOf("?") == -1) {
			//If a ? exists in the data url
			strURL = strURL+"?curr="+getTimer();
		} else {
			//If a ? does NOT exist in the data url
			strURL = strURL+"&curr="+getTimer();
		}
	}
	return strURL;
}
