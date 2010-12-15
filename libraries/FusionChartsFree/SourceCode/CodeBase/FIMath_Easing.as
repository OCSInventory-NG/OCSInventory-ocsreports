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
Math.easeOutCirc = function(t, b, c, d) {
	return c*Math.sqrt(1-(t=t/d-1)*t)+b;
};
Math.easeOutCubic = function (t, b, c, d) {
	return c*((t=t/d-1)*t*t + 1) + b;
};