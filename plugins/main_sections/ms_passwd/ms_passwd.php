<?php
/*
 * Change your password 
 * 
 */

printEntete($l->g(236));

if (isset($protectedPost['subPass'])){
		if ($protectedPost['NEW_PASS'] != $protectedPost['VERIF_PASS']){
			$ERROR=$l->g(240);
		}
		
		if (!isset($ERROR)){
			$reqOp="UPDATE operators set passwd ='".md5($protectedPost['NEW_PASS'])."' WHERE id='".$_SESSION["loggeduser"]."'";
			if( ! @mysql_query( $reqOp, $_SESSION["writeServer"] ))
					echo mysql_error($_SESSION["writeServer"]);
			echo "<font color=green><b>".$l->g(241)."</b></font>";
		}else
			echo "<font color=red><b>".$ERROR."</b></font>";
		
	
}

$form_name="pass";
echo "<br><form name=".$form_name." action=# method=post><center><table><tr><td>";
echo "<b>".$l->g(237)."</b></td><td>".show_modif($choise_req_selection,'NEW_PASS',4,$form_name)."</td></tr><tr><td>";
echo "<b>".$l->g(238)."</b></td><td>".show_modif($choise_req_selection,'VERIF_PASS',4,$form_name)."</td></tr>";
echo "<tr><td colspan=10 align=center><input name=subPass type=submit value=".$l->g(13)."></td></tr></table>";
echo "</center></form>";
?>	