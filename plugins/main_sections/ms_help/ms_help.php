<?php

$form_name="help";
if ($protectedPost['onglet'] == "" or !isset($protectedPost['onglet']))
$protectedPost['onglet']=1;
 //d�finition des onglets
$data_on[1]=$l->g(1122);
$data_on[2]=$l->g(1123);
$data_on[3]=$l->g(1124);
echo "<form action='' name='".$form_name."' id='".$form_name."' method='POST'>";
onglet($data_on,$form_name,"onglet",3);
echo '<div class="mlt_bordure" >';
if ($protectedPost['onglet'] == 2){
	echo "<iframe width=\"647\" height=\"400\" src=\"http://webchat.freenode.net/?channels=ocsinventory-ng&uio=d4\">
		</iframe>";
}elseif($protectedPost['onglet'] == 1){
	echo "<iframe  width=\"100%\" height=\"100%\" src=\"http://wiki.ocsinventory-ng.org/\">
	</iframe>";
}elseif($protectedPost['onglet'] == 3){
		echo "<iframe  width=\"100%\" height=\"100%\" src=\"http://forums.ocsinventory-ng.org\">
	</iframe>";
	
}
echo "</div>";
echo "</form>";

?>



