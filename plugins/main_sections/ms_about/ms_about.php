<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>CSS Star Wars Crawling Titles</title>

<style>
@import url(http://fonts.googleapis.com/css?family=Droid+Sans:400,700);

* { padding: 0; margin: 0; }

body, html
{
	width: 100%;
	height: 100%;
	font-family: "Droid Sans", arial, verdana, sans-serif;
	font-weight: 700;
	color: #ff6;
	background-color: #000;
	overflow: hidden;
}

p#start
{
	position: relative;
	width: 16em;
	font-size: 200%;
	font-weight: 400;
	margin: 20% auto;
	color: #4ee;
	opacity: 0;
	z-index: 1;
	-webkit-animation: intro 2s ease-out;
	-moz-animation: intro 2s ease-out;
	-ms-animation: intro 2s ease-out;
	-o-animation: intro 2s ease-out;
	animation: intro 2s ease-out;
}

@-webkit-keyframes intro {
	0% { opacity: 1; }
	90% { opacity: 1; }
	100% { opacity: 0; }
}

@-moz-keyframes intro {
	0% { opacity: 1; }
	90% { opacity: 1; }
	100% { opacity: 0; }
}

@-ms-keyframes intro {
	0% { opacity: 1; }
	90% { opacity: 1; }
	100% { opacity: 0; }
}

@-o-keyframes intro {
	0% { opacity: 1; }
	90% { opacity: 1; }
	100% { opacity: 0; }
}

@keyframes intro {
	0% { opacity: 1; }
	90% { opacity: 1; }
	100% { opacity: 0; }
}

h1
{
	position: absolute;
	width: 2.6em;
	left: 50%;
	top: 25%;
	font-size: 10em;
	text-align: center;
	margin-left: -1.3em;
	line-height: 0.8em;
	letter-spacing: -0.05em;
	color: #000;
	text-shadow: -2px -2px 0 #ff6, 2px -2px 0 #ff6, -2px 2px 0 #ff6, 2px 2px 0 #ff6;
	opacity: 0;
	z-index: 1;
	-webkit-animation: logo 5s ease-out 2.5s;
	-moz-animation: logo 5s ease-out 2.5s;
	-ms-animation: logo 5s ease-out 2.5s;
	-o-animation: logo 5s ease-out 2.5s;
	animation: logo 5s ease-out 2.5s;
}

h1 sub
{
	display: block;
	font-size: 0.3em;
	letter-spacing: 0;
	line-height: 0.8em;
}

@-webkit-keyframes logo {
	0% { -webkit-transform: scale(1); opacity: 1; }
	50% { opacity: 1; }
	100% { -webkit-transform: scale(0.1); opacity: 0; }
}

@-moz-keyframes logo {
	0% { -moz-transform: scale(1); opacity: 1; }
	50% { opacity: 1; }
	100% { -moz-transform: scale(0.1); opacity: 0; }
}

@-ms-keyframes logo {
	0% { -ms-transform: scale(1); opacity: 1; }
	50% { opacity: 1; }
	100% { -ms-transform: scale(0.1); opacity: 0; }
}

@-o-keyframes logo {
	0% { -o-transform: scale(1); opacity: 1; }
	50% { opacity: 1; }
	100% { -o-transform: scale(0.1); opacity: 0; }
}

@keyframes logo {
	0% { transform: scale(1); opacity: 1; }
	50% { opacity: 1; }
	100% { transform: scale(0.1); opacity: 0; }
}

/* the interesting 3D scrolling stuff */
#titles
{
	position: absolute;
	width: 18em;
	height: 50em;
	bottom: 0;
	left: 50%;
	margin-left: -9em;
	font-size: 350%;
	text-align: justify;
	overflow: hidden;
	-webkit-transform-origin: 50% 100%;
	-moz-transform-origin: 50% 100%;
	-ms-transform-origin: 50% 100%;
	-o-transform-origin: 50% 100%;
	transform-origin: 50% 100%;
	-webkit-transform: perspective(300px) rotateX(25deg);
	-moz-transform: perspective(300px) rotateX(25deg);
	-ms-transform: perspective(300px) rotateX(25deg);
	-o-transform: perspective(300px) rotateX(25deg);
	transform: perspective(300px) rotateX(25deg);
}

#titles:after
{
	position: absolute;
	content: ' ';
	left: 0;
	right: 0;
	top: 0;
	bottom: 60%;
	background-image: -webkit-linear-gradient(top, rgba(0,0,0,1) 0%, transparent 100%);
	background-image: -moz-linear-gradient(top, rgba(0,0,0,1) 0%, transparent 100%);
	background-image: -ms-linear-gradient(top, rgba(0,0,0,1) 0%, transparent 100%);
	background-image: -o-linear-gradient(top, rgba(0,0,0,1) 0%, transparent 100%);
	background-image: linear-gradient(top, rgba(0,0,0,1) 0%, transparent 100%);
	pointer-events: none;
}

#titles p
{
	text-align: justify;
	margin: 0.8em 0;
}

#titles p.center
{
	text-align: center;
}

#titles a
{
	color: #ff6;
	text-decoration: underline;
}

#titlecontent
{
	position: absolute;
	top: 100%;
	-webkit-animation: scroll 100s linear 4s infinite;
	-moz-animation: scroll 100s linear 4s infinite;
	-ms-animation: scroll 100s linear 4s infinite;
	-o-animation: scroll 100s linear 4s infinite;
	animation: scroll 100s linear 4s infinite;
}

/* animation */
@-webkit-keyframes scroll {
	0% { top: 100%; }
	100% { top: -170%; }
}

@-moz-keyframes scroll {
	0% { top: 100%; }
	100% { top: -170%; }
}

@-ms-keyframes scroll {
	0% { top: 100%; }
	100% { top: -170%; }
}

@-o-keyframes scroll {
	0% { top: 100%; }
	100% { top: -170%; }
}

@keyframes scroll {
	0% { top: 100%; }
	100% { top: -170%; }
}
</style>

</head>
<body>

<p id="start">A long long story of a opensource solution</p>

<h1>OCSInventoryNG<sub>Open Computers and Software Inventory Next Generation</sub></h1>

<div id="titles"><div id="titlecontent">

	<p class="center">LICENCE<br />Learn more about how OCS Inventory is licensed and how this affects you.</p>
	<p>OCS Inventory is released under the GNU General Public License, version 2.0 (GNU GPLv2). </p>
	
	<p>The GNU GPL provides for a person or persons to distribute OCS Inventory for a fee, </p>
	<p>but not actually charging for the software itself, because OCS Inventory is free.</p>

    <p>OCS Inventory is free to share and change, but if you do change it in anyway, can you also change the license and make it commercial? </p>
    <p>No! The whole GPL is devoted to ensuring this does not happen. Copyright, a much more refined and stringent law will prevent this as well.</p>


	<p>So with regard to OCS Inventory, the GPL and copyright:</p>


	<p>You MAY distribute it and charge for that service. You MAY change it, add design and content to it and you MAY charge for that. </p>
	<p>You may NOT alter the license and you must NOT alter the copyright.</p>

	<p>In other words, you must NOT pretend that OCS Inventory is yours, and you must NOT charge people for OCS Inventory.</p>
	<p>Use OCS Inventory to empower yourself and your clients. Charge for the value you add and not for the hard work </p>
	<p>that OCS Inventory Development Team and the OCS Inventory community have put into it.</p>

	<p>Guidelines</p>


	<p>OCS Inventory is "free" software released under the GNU General Public License, version 2.0 (GNU GPLv2).</p>


	<p>The word "free" has two legitimate general meanings; it can refer either to freedom or to price. </p>
	<p>When we speak of "free software", we're talking about freedom, not price. (Think of "free speech", not "free beer".)</p>


	<p>Free software is a matter of the users' freedom to run, copy, distribute, study, change and improve the software. </p>
	<p>More precisely, it refers to four kinds of freedom, for the users of the software:</p>

    <p>The freedom to run the program, for any purpose.</p>
    <p>The freedom to study how the program works, and adapt it to your needs (Access to the source code is a precondition for this.)</p>
    <p>The freedom to redistribute copies so you can help your neighbour.</p>
    <p>The freedom to improve the program, and release your improvements to the public, so that the whole community benefits.</p> 
    <p>(Access to the source code is a precondition for this)</p>

 

    <p>Who owns the copyright to OCS Inventory? The copyright to OCS Inventory is held by OCS Inventory Development Team.</p>
    <p>Are there any restrictions to your use of OCS Inventory? The GNU GPL grants you the freedom to use the software </p>
    <p>for whatever purpose you see fit.</p>
    <p>May I charge money for OCS Inventory? The GPL allows everyone the freedom to do this. The right to charge money </p>
    <p>to distribute OCS Inventory is part of the definition of "free" software.When people think of "selling software", </p>
    <p>they usually imagine doing it the way most companies do it: making the software proprietary rather than free. </p>
    <p>So to avoid ambiguity you may charge to distribute the software and any other service you provide along the way. </p>
    <p>You may not charge for the software itself.Remember if someone pays your fee the GPL also gives him or her the freedom</p> 
    <p>to pass on the software with or without a fee.</p>
    <p>May I remove the "copyright" statements from the source code to OCS Inventory? No, you must keep all copyright </p>
    <p> notices and credits in the source code.</p>
    <p>I have modified OCS Inventory for my own use. Do I have to release these modifications? </p>
    <p>The GPL permits anyone to make a modified version for their own use without the requirement to distribute it </p>
    <p>or pass on those changes to others.</p>
    <p>I have made a modification to the OCS Inventory core code. Do I have to release it under the GPL? </p>
    <p>If you chose to distribute your modifications to others it must be released under the same terms </p>
    <p>that you received the original code. So your modifications must be released under the GPL. </p>
    <p>You may of course in this case modify the headers for the source code to include your own copyright statement. </p>
    <p>If you do so you must clearly annotate in the source code your amendments, changes or additions.</p>
    <p>I have written an extension or additional module for OCS Inventory. Do I have to release it under the GPL? No. </p>
    <p>The GPL allows you to write your own extensions for OCS Inventory and to release those extensions under whatever license you chose.</p>

	<p>Disclaimer</p>


<p>This document refers to the software program OCS Inventory, Version 1.x and all subsequent versions, </p>
<p>released under the GNU General Public License and copyright OCS Inventory Development Team.</p>
<p>This document is subject to additions, modifications and other changes at any time without notice.</p>
<p>A lawyer has not prepared this document. You should consult a lawyer experienced in copyright, </p>
<p>licensing and intellectual property for clarification.</p>

</div></div>

</body>
</html>



