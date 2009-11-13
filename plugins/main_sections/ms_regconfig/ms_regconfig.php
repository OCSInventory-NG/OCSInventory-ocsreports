<?php
$list_registry_key=array('HKEY_CLASSES_ROOT',
						 'HKEY_CURRENT_USER',
						 'HKEY_LOCAL_MACHINE',
						 'HKEY_USERS',
						 'HKEY_CURRENT_CONFIG',
						 'HKEY_DYN_DATA (Windows 9X only)');
//require_once('require/function_registry.php');
//cas d'une suppression d'une cl� de registre
if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != ''){	
	$sql_reg="delete from regconfig where id='".$protectedPost['SUP_PROF']."'";
	mysql_query($sql_reg, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));
	$tab_options['CACHE']='RESET';
}

//cas de ajout/modification d'une cl�
if (isset($protectedPost['Valid_modif_x'])){
	if (trim($protectedPost["NAME"])!= "" and 
		trim($protectedPost["REGTREE"])!= "" and
		trim($protectedPost["REGKEY"])!= "" and
		trim($protectedPost["REGVALUE"])!= "")
	{
		unset($req);
		if (isset($protectedPost['id'])){
			$req = "UPDATE regconfig SET ".	
				"NAME='".$protectedPost["NAME"]."',".
				"REGTREE='".$protectedPost["REGTREE"]."',".
				"REGKEY='".$protectedPost["REGKEY"]."',".
				"REGVALUE='".$protectedPost["REGVALUE"]."' ".
				"where ID='".$protectedPost['id']."'";
		}else{
			$sql_verif="select ID from regconfig 
						where REGTREE='".$protectedPost["REGTREE"]."' 
							and REGKEY='".$protectedPost["REGKEY"]."'
							and REGVALUE='".$protectedPost["REGVALUE"]."'";
			$res=mysql_query($sql_verif, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
			$row=mysql_fetch_object($res);
			if (!is_numeric($row->ID)){				
			$req = "INSERT INTO regconfig (NAME,REGTREE,REGKEY,REGVALUE)
					VALUES('".$protectedPost["NAME"]."','".$protectedPost["REGTREE"]."','".$protectedPost["REGKEY"]."','".$protectedPost["REGVALUE"]."')";
			}else
			$error=$l->g(987);
			
		}
		
		if (isset($req)){
			$result = mysql_query($req, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));
			$tab_options['CACHE']='RESET';
		}
	}else{
		$error=$l->g(988);
		
		
	}
	
}
if (isset($error)){
	if (isset($protectedPost['id']))
		$protectedPost['MODIF']=$protectedPost['id'];
		$protectedPost['ajout']='ADD';
	echo "<font color=red><b>".$error."</b></font>";
}

//cas d'une modification d'une cl� de registre

if (isset($protectedPost['MODIF']) and $protectedPost['MODIF'] != ''){	
	if (!isset($protectedPost['NAME'])){
		$sql="select * from regconfig where id = '".$protectedPost['MODIF']."'";
		//$sql="select NAME,ID,MASK from subnet where netid='".$netid."'";
		$res=mysql_query($sql, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
		$row=mysql_fetch_object($res);
		$protectedPost['NAME']=$row->NAME;
		$protectedPost['REGTREE']=$row->REGTREE;
		$protectedPost['REGKEY']=$row->REGKEY;
		$protectedPost['REGVALUE']=$row->REGVALUE;
	}
	$title=$l->g(108);
	$tab_hidden['id']=$row->ID;
}

if (isset($tab_hidden['id']) or isset($protectedPost['ajout'])){	
	$tab_typ_champ[0]['DEFAULT_VALUE']=$protectedPost['NAME'];
	$tab_typ_champ[0]['INPUT_NAME']="NAME";
	$tab_typ_champ[0]['CONFIG']['SIZE']=60;
	$tab_typ_champ[0]['CONFIG']['MAXLENGTH']=255;
	$tab_typ_champ[0]['INPUT_TYPE']=0;
	$tab_name[0]=$l->g(252).": ";
	$tab_typ_champ[1]['DEFAULT_VALUE']=$list_registry_key;
	$tab_typ_champ[1]['INPUT_NAME']="REGTREE";
	$tab_typ_champ[1]['INPUT_TYPE']=2;
	$tab_name[1]=$l->g(253).":";
	$tab_typ_champ[2]['DEFAULT_VALUE']=$protectedPost['REGKEY'];
	$tab_typ_champ[2]['INPUT_NAME']="REGKEY";
	$tab_typ_champ[2]['CONFIG']['SIZE']=60;
	$tab_typ_champ[2]['CONFIG']['MAXLENGTH']=255;
	$tab_name[2]=$l->g(254).": ";
	$tab_typ_champ[3]['DEFAULT_VALUE']=$protectedPost['REGVALUE'];
	$tab_typ_champ[3]['INPUT_NAME']="REGVALUE";
	$tab_typ_champ[3]['CONFIG']['SIZE']=60;
	$tab_typ_champ[3]['CONFIG']['MAXLENGTH']=255;
	$tab_typ_champ[3]['INPUT_TYPE']=0;
	$tab_name[3]=$l->g(255).": ";
	$tab_hidden['FILTRE']=$protectedPost['FILTRE'];
	$tab_hidden['pcparpage']=$protectedPost['pcparpage'];
	$tab_hidden['page']=$protectedPost['page'];
	$tab_hidden['old_pcparpage']=$protectedPost['old_pcparpage'];
	$tab_hidden['tri2']=$protectedPost['tri2'];
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment="");
}






	$form_name="registry";
	$table_name="registry";
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$list_fields = array ( 'ID'   => "id", 
						   $l->g(49) => "name", 
						   'REGTREE' => "REGTREE",
						   'REGKEY' => "REGKEY",
						   'REGVALUE' => "REGVALUE",
						   'SUP' => "id",
						   'MODIF' => "id");
	
	$list_col_cant_del=array('SUP'=>'SUP','MODIF' => 'MODIF',$l->g(49)=> $l->g(49));
	$default_fields= array($l->g(49) => "name", 
						   'REGTREE' => "REGTREE",
						   'REGKEY' => "REGKEY",
						   'REGVALUE' => "REGVALUE",
						   'SUP' => "id",
						   'MODIF' => "id");
	$queryDetails  = "SELECT  ";
	foreach ($list_fields as $lbl=>$value){
		if ($lbl != 'SUP' and $lbl != 'MODIF'){
			$queryDetails .= $value;
				$queryDetails .=",";		
		}
	}
	$queryDetails  = substr($queryDetails,0,-1)." from regconfig ";
	$tab_options['FILTRE']['name']=$l->g(49);
	$tab_options['FILTRE']['REGKEY']='REGKEY';
	$tab_options['FILTRE']['REGVALUE']='REGVALUE';
	$tab_options['REPLACE_VALUE']['REGTREE']=$list_registry_key;
	$tab_options['LBL_POPUP']['SUP']='name';
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,95,$tab_options);
	echo "<br><input  class='bouton' name='ajout' type='submit' value='".$l->g(116)."' >";

	echo "</form>";

?>