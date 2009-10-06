<?php

//$header_html = 'NO';
$form_name='debug';

//liste des modes de fonctionnement
$list_mode[1]=$l->g(1010);
$list_mode[2]=$l->g(1011);
$list_mode[3]=$l->g(1012);
$list_mode[4]=$l->g(1013);
$list_mode[5]='FUSER';
if (!($_SESSION["lvluser"] == SADMIN or $_SESSION['TRUE_LVL'] == SADMIN))
	die("FORBIDDEN");
echo "<br><br><br>";	
$tab_typ_champ[0]['DEFAULT_VALUE']=$list_mode;
	$tab_typ_champ[0]['INPUT_NAME']="MODE";
	$tab_typ_champ[0]['INPUT_TYPE']=2;
	$tab_name[0]=$l->g(1014)." :";
	$tab_typ_champ[0]['RELOAD']="CHANGE";
if ($protectedPost['MODE'] == 5){
	$tab_typ_champ[1]['DEFAULT_VALUE']=$protectedPost['FUSER'];
	$tab_typ_champ[1]['INPUT_NAME']="FUSER";
	$tab_typ_champ[1]['INPUT_TYPE']=0;
	$tab_name[1]=$l->g(926)." ";	
}
tab_modif_values($tab_name,$tab_typ_champ,'',$l->g(1015),$comment="");


if (isset($protectedPost['Reset_modif_x'])){
	echo "<script>";
	echo "self.close();</script>";
}

//passage en mode
if (isset($protectedPost['Valid_modif_x']) and $protectedPost["MODE"] != ""){
	AddLog("MODE",$list_mode[$protectedPost["MODE"]]);
	if ($protectedPost["MODE"] == 2){
		unset($_SESSION['MODE_LANGUAGE']);
		$_SESSION['DEBUG']="ON";
	}
	elseif ($protectedPost["MODE"] == 3){
		unset($_SESSION['DEBUG']);
		$_SESSION['MODE_LANGUAGE']="ON";	
	}
	elseif ($protectedPost["MODE"] == 4){
		$_SESSION['MODE_LANGUAGE']="ON";	
		$_SESSION['DEBUG']="ON";
	}elseif ($protectedPost["MODE"] == 5 and $protectedPost["FUSER"] != ""){
		if (!isset($_SESSION['TRUE_USER'])){
			$_SESSION['TRUE_USER']=$_SESSION["loggeduser"];
			$_SESSION['TRUE_LVL']=$_SESSION["lvluser"];
		}
		$_SESSION["loggeduser"]=$protectedPost["FUSER"];
	unset($_SESSION["lvluser"],$_SESSION["ipdiscover"]);	
	}elseif ($protectedPost["MODE"] == 5 and $protectedPost["FUSER"] == ""){
		$_SESSION["loggeduser"]=$_SESSION['TRUE_USER'];
		$_SESSION["lvluser"]=$_SESSION['TRUE_LVL'];
		unset($_SESSION["mesmachines"],$_SESSION["mytag"],$_SESSION['TRUE_USER'],$_SESSION['TRUE_LVL'],$_SESSION["ipdiscover"]);		
	}else	
	unset($_SESSION['DEBUG'],$_SESSION['MODE_LANGUAGE']);

	echo "<script>";
		echo "window.opener.document.forms['log_out'].submit();";
		echo "self.close();</script>";
}

?>
