/*
 * 
 * tx to Damien ALEXANDRE
 * http://damienalexandre.fr/Info-Bulle-en-Javascript.html 
 * 
 * 
 *
 */


var i=false; // visible or not?

function GetId(id)
{
	return document.getElementById(id);
}

 
function move(e) {
  if(i) {  // calcul the position
    if (navigator.appName!="Microsoft Internet Explorer") { // IE or other?
    	GetId("mouse_pointer").style.left=e.pageX + 5+"px";
    	GetId("mouse_pointer").style.top=e.pageY + 10+"px";
    }
    else { // TeDeum Modif
    if(document.documentElement.clientWidth>0) {
		GetId("mouse_pointer").style.left=20+event.x+document.documentElement.scrollLeft+"px";
		GetId("mouse_pointer").style.top=10+event.y+document.documentElement.scrollTop+"px";
    } else {
		GetId("mouse_pointer").style.left=20+event.x+document.body.scrollLeft+"px";
		GetId("mouse_pointer").style.top=10+event.y+document.body.scrollTop+"px";
       }
    }
  }
}
 
function show_me(text) {
  if(i==false) {
  GetId("mouse_pointer").style.visibility="visible"; // show tooltip.
  GetId("mouse_pointer").innerHTML = text; // copy the text in html
  i=true;
  }
}
function hidden_me() {
if(i==true) {
GetId("mouse_pointer").style.visibility="hidden"; // hidden tooltip
i=false;
}
}
document.onmousemove=move; // when mouse move, calcul again the mouse pointer.
//-->
