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
FIDate represents a very basic date class. In this date class, months are indexed from 1-12 instead of 0-11 (as in Flash).
*/
function FIDate(date, dateFormat) {
	var tempArr = new Array();
	tempArr = date.split("/");
	switch (dateFormat.toUpperCase()) {
	case "DD/MM/YYYY" :
	case "DD,MM,YYYY" :
	case "DD-MM-YYYY" :
	case "D/M/Y" :
		dd = tempArr[0];
		mm = tempArr[1];
		yyyy = tempArr[2];
		break;
	case "MM/DD/YYYY" :
	case "MM,DD,YYYY" :
	case "MM-DD-YYYY" :
	case "M/D/Y" :
		dd = tempArr[1];
		mm = tempArr[0];
		yyyy = tempArr[2];
		break;
	case "YYYY/MM/DD" :
	case "YYYY-MM-DD" :
	case "YYYY,MM,DD" :
	case "Y/M/D" :
		dd = tempArr[2];
		mm = tempArr[1];
		yyyy = tempArr[0];
		break;
	case "YYYY/DD/MM" :
	case "YYYY-DD-MM" :
	case "YYYY,DD,MM" :
	case "Y/D/M" :
		dd = tempArr[1];
		mm = tempArr[2];
		yyyy = tempArr[0];
		break;
	}

	//Convert to numbers
	yyyy = Number(yyyy);
	mm = Number(mm);
	dd = Number(dd);
	//If any of them is non number-set our defaults
	if (isNan(yyyy)==true){
	yyyy=2000;
	}
	if (isNan(mm)==true){
	mm=1;
	}
	if (isNan(dd)==true){
	dd=1;
	}
	//Check for number of digits in yyyy
	var n = yyyy, numDigits = 0;
	while (n != 0) {
		numDigits++;
		n = Math.round(n/10);
	}
	//if it is less than 3 then add 2000 or 1900
	if (numDigits<=2) {
		var d = new Date();
		var now = d.getUTCFullYear();
		//add 2000 if 2 digit is less than current 2 digits of the year
		if ((yyyy+Number(2000))<=Number(now)) {
			yyyy = yyyy+Number(2000);
		} else {
			yyyy = yyyy+Number(1900);
		}
	}
	//Conditional checks
	//Month cannot be greater than 12
	if (mm>12) {
		mm = 12;
	}
	//Date cannot be greater than 31
	if (dd>31) {
		dd = 31;
	}
	//End Conditional Checks
	//Date function
	this.yyyy = yyyy;
	this.mm = mm;
	this.dd = dd;
}
FIDate.prototype.getYear = function() {
	//This method returns the year of the date
	return this.yyyy;
};
FIDate.prototype.getMonth = function() {
	//This method returns the month
	return this.mm;
};
FIDate.prototype.getDate = function() {
	//This method returns the date
	return this.dd;
};
function dateDiff(startDate, endDate) {
	//This function returns the difference between two dates in days
	var sDate = startDate;
	var eDate = endDate;
	//Number of days in each month
	var daysInMonths = new Array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	var startMonthDays, endMonthDays, yearDiff, monthDiffDays;
	monthDiffDays = 0;
	//For days falling in the same month
	if (sDate.getMonth() != eDate.getMonth()) {
		startMonthDays = daysInMonths[sDate.getMonth()]-sDate.getDate()+1;
		endMonthDays = eDate.getDate();
	} else {
		//If they belong to same year too
		if (sDate.getYear() == eDate.getYear()) {
			//Just calculate the diff in days
			startMonthDays = eDate.getDate()-sDate.getDate()+1;
			endMonthDays = 0;
		} else {
			//Normal calculations
			startMonthDays = daysInMonths[sDate.getMonth()]-sDate.getDate()+1;
			endMonthDays = eDate.getDate();
		}
	}
	//Now, get the difference in months/years
	yearDiff = eDate.getYear()-sDate.getYear();
	for (var i = (sDate.getMonth()+1); i<=(((yearDiff*12)+eDate.getMonth())-1); i++) {
		var monthIndex = (i%12 == 0) ? (12) : (i%12);
		monthDiffDays = monthDiffDays+daysInMonths[monthIndex];
		//Exception for leap year
		leapYearIndex = sDate.getYear()+int(i/12);
		if ((monthIndex == 2) && (((leapYearIndex%4 == 0) && (leapYearIndex%100 != 0)) || (leapYearIndex%400 == 0))) {
			monthDiffDays = monthDiffDays+1;
		}
	}
	//Return value
	return startMonthDays+monthDiffDays+endMonthDays;
}