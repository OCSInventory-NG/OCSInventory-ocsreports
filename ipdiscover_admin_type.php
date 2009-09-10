<?php
/*
 * Created on 7 mai 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require ('fichierConf.class.php');
$form_name='admin_type';
$ban_head='no';
$no_error='YES';
require_once("header.php");
if (!($_SESSION["lvluser"] == SADMIN or $_SESSION['TRUE_LVL'] == SADMIN))
	die("FORBIDDEN");
echo "<br><br><br>";	
echo "<form name='".$form_name."' id='".$form_name."' action='' method='post'>";

if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != ''){
	//$del_type=mysql_real_escape_string($protectedPost['SUP_PROF']);
	$sql="delete from devicetype where id='".$protectedPost['SUP_PROF']."'";
	mysql_query($sql, $_SESSION["writeServer"]) or die(mysql_error($_SESSION["writeServer"]));	
	$tab_options['CACHE']='RESET';	
	
}

if (isset($protectedPost['Valid_modif_x'])){
	//$new_type=mysql_real_escape_string($protectedPost['TYPE_NAME']);
	if (trim($protectedPost['TYPE_NAME']) == ''){
		$ERROR=$l->g(936);		
	}else{
		$sql="select ID from devicetype where NAME = '".$protectedPost['TYPE_NAME']."'";
		$res = mysql_query($sql, $_SESSION["readServer"] );
		$row=mysql_fetch_object($res);
		if (isset($row->ID))
		$ERROR=$l->g(937);	
	}
	if (isset($ERROR)){
		echo "<font color=red><b>".$ERROR."</b></font>";
		$protectedPost['ADD_TYPE']="VALID";
	}
	else{
		$sql="insert into devicetype (NAME) VALUES ('".$protectedPost['TYPE_NAME']."')";
		mysql_query($sql, $_SESSION["writeServer"]) or die(mysql_error($_SESSION["writeServer"]));	
		$tab_options['CACHE']='RESET';	
	}
}





if (isset($protectedPost['ADD_TYPE'])){
	$tab_typ_champ[0]['DEFAULT_VALUE']=$protectedPost['TYPE_NAME'];
	$tab_typ_champ[0]['INPUT_NAME']="TYPE_NAME";
	$tab_typ_champ[0]['CONFIG']['SIZE']=60;
	$tab_typ_champ[0]['CONFIG']['MAXLENGTH']=255;
	$tab_typ_champ[0]['INPUT_TYPE']=0;
	$tab_name[0]=$l->g(938).": ";
	$tab_hidden['pcparpage']=$protectedPost["pcparpage"];
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment="");	
}else{




//if( $_SESSION["lvluser"]!=LADMIN && $_SESSION["lvluser"]!=SADMIN  )
//	die("FORBIDDEN");
$sql="select ID,NAME from devicetype";
$list_fields= array('ID' => 'ID',
					$l->g(49)=>'NAME',
					'SUP'=>'ID');
//$list_fields['SUP']='ID';	
$default_fields=$list_fields;
$list_col_cant_del=$list_fields;
if (!(isset($protectedPost["pcparpage"])))
	 $protectedPost["pcparpage"]=5;
$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql,$form_name,80,$tab_options); 

echo "<input type = submit value='".$l->g(307)."' name='ADD_TYPE'>";	
}
echo "</form>";
	
require_once($_SESSION['FOOTER_HTML']);
?>
