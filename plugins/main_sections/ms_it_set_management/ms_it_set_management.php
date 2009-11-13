<?php
/*
 * formulaire de demande de création de paquet
 * 
 */
require_once('require/function_search.php');

if ($protectedPost['onglet'] == "" or !isset($protectedPost['onglet']))
$protectedPost['onglet']=3;
 //d�finition des onglets
$data_on[1]=$l->g(242);
$data_on[2]=$l->g(243);
$data_on[3]=$l->g(619);
$data_on[4]=$l->g(244);

//liste des profils
$list_profil[1]=$l->g(242);
$list_profil[2]=$l->g(243);
$list_profil[3]=$l->g(619);

$form_name = "admins";
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
onglet($data_on,$form_name,"onglet",4);
$table_name="TAB_ACCESSLVL".$protectedPost['onglet'];	
if (isset($protectedPost['VALID_MODIF'])){
	if ($protectedPost['CHANGE'] != ""){
		$sql_update="update operators set ACCESSLVL = '".$protectedPost['CHANGE']."' where ID='".$protectedPost['MODIF_ON']."'";
		mysql_query($sql_update, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));		
	$tab_options['CACHE']='RESET';
	}else
	echo "<div  align=center><font color=red size=4><b>".$l->g(909)."</b></font></div>";
	
}
//suppression d'une liste de users
if (isset($protectedPost['del_check']) and $protectedPost['del_check'] != ''){
	$list = "'".implode("','", explode(",",$protectedPost['del_check']))."'";
	$sql_delete="delete from tags where login in (".$list.")";
	mysql_query($sql_delete, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));	
	$sql_delete="delete from operators where id in (".$list.")";
	mysql_query($sql_delete, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));	
	$tab_options['CACHE']='RESET';	
}


//suppression d'un user
if (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != ''){
	$sql_delete="delete from tags where login='".$protectedPost['SUP_PROF']."'";
	mysql_query($sql_delete, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));	
	$sql_delete="delete from operators where id= '".$protectedPost['SUP_PROF']."'";
	mysql_query($sql_delete, $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));	
	$tab_options['CACHE']='RESET';
}
//ajout d'un user
if (isset($protectedPost['Valid_modif_x'])){
	if (trim($protectedPost['ID']) == "")
		$ERROR=$l->g(997);
	if (!array_key_exists($protectedPost['ACCESSLVL'], $list_profil))
		$ERROR=$l->g(998);
	if (!isset($ERROR)){
		$sql="select id from operators where id= '".$protectedPost['ID']."'";
		$res=mysql_query($sql, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
		$row=mysql_fetch_object($res);
		if (isset($row->id)){
			$ERROR=$l->g(999);
			echo "<script>alert('".$ERROR."')</script>";
		}else{
		
			$sql=" insert into operators (id,firstname,lastname,accesslvl,comments";
			if (isset($protectedPost['PASSWORD']))
				$sql.=",passwd";
			$sql.=") value ('".$protectedPost['ID']."',
							'".$protectedPost['FIRSTNAME']."',
							'".$protectedPost['LASTNAME']."',
							'".$protectedPost['ACCESSLVL']."',
							'".$protectedPost['COMMENTS']."'";
			if (isset($protectedPost['PASSWORD']))
				$sql.=",'".md5($protectedPost['PASSWORD'])."'";
			$sql.=")";
			//echo $sql;
			mysql_query($sql, $_SESSION['OCS']["writeServer"]);
			unset($_SESSION['OCS']['DATA_CACHE'],$protectedPost['ID'],$protectedPost['FIRSTNAME'],$protectedPost['LASTNAME'],
					$protectedPost['ACCESSLVL'],$protectedPost['COMMENTS'],$protectedPost['PASSWORD']);
			$msg=$l->g(373);
		}		
	}else
	echo "<script>alert('".$ERROR."')</script>";

	}
echo '<div class="mlt_bordure" >';
	//echo "<table ALIGN = 'Center' class='onglet'><tr><td align =center>";
//add user
if ($protectedPost['onglet'] == 4){	

	$tab_typ_champ[0]['DEFAULT_VALUE']=$protectedPost['ID'];
	$tab_typ_champ[0]['INPUT_NAME']="ID";
	$tab_typ_champ[0]['CONFIG']['SIZE']=60;
	$tab_typ_champ[0]['CONFIG']['MAXLENGTH']=255;
	$tab_typ_champ[0]['INPUT_TYPE']=0;
	$tab_name[0]=$l->g(995).": ";
	
	$tab_typ_champ[1]['DEFAULT_VALUE']=$protectedPost['FIRSTNAME'];
	$tab_typ_champ[1]['INPUT_NAME']="FIRSTNAME";
	$tab_typ_champ[1]['CONFIG']['SIZE']=60;
	$tab_typ_champ[1]['CONFIG']['MAXLENGTH']=255;
	$tab_typ_champ[1]['INPUT_TYPE']=0;
	$tab_name[1]=$l->g(49).": ";
	
	$tab_typ_champ[2]['DEFAULT_VALUE']=$protectedPost['LASTNAME'];
	$tab_typ_champ[2]['INPUT_NAME']="LASTNAME";
	$tab_typ_champ[2]['CONFIG']['SIZE']=60;
	$tab_typ_champ[2]['CONFIG']['MAXLENGTH']=255;
	$tab_typ_champ[2]['INPUT_TYPE']=0;
	$tab_name[2]=$l->g(996).": ";
	
	$tab_typ_champ[3]['DEFAULT_VALUE']=$protectedPost['COMMENTS'];
	$tab_typ_champ[3]['INPUT_NAME']="COMMENTS";
	$tab_typ_champ[3]['CONFIG']['SIZE']=60;
	$tab_typ_champ[3]['CONFIG']['MAXLENGTH']=255;
	$tab_typ_champ[3]['INPUT_TYPE']=0;
	$tab_name[3]=$l->g(51).": ";
		
	$tab_typ_champ[4]['DEFAULT_VALUE']=$list_profil;
	$tab_typ_champ[4]['INPUT_NAME']="ACCESSLVL";
	$tab_typ_champ[4]['INPUT_TYPE']=2;
	$tab_name[4]=$l->g(66).":";
	if ($_SESSION['OCS']['cnx_origine'] == "LOCAL"){
		//rajouter le password si authentification locale
		$tab_typ_champ[5]['DEFAULT_VALUE']=$protectedPost['PASSWORD'];
		$tab_typ_champ[5]['INPUT_NAME']="PASSWORD";
		$tab_typ_champ[5]['CONFIG']['SIZE']=30;
		$tab_typ_champ[5]['INPUT_TYPE']=0;
		$tab_name[5]=$l->g(217).":";
	}
	if (isset($msg))
	echo "<font color=green>".$msg."</font>";
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$l->g(244),$comment="");
}else{
	echo "<tr><td align=center>";
	//affichage
	$list_fields= array('ID'=>'ID',
						'FIRSTNAME'=>'FIRSTNAME',
						'LASTNAME'=>'LASTNAME',
						'ACCESSLVL'=>'ACCESSLVL',
						'COMMENTS'=>'COMMENTS',
						'SUP'=>'ID',
						'MODIF'=>'ID',
						'CHECK'=>'ID');
	$list_col_cant_del=array('ID'=>'ID','SUP'=>'SUP','MODIF'=>'MODIF','CHECK'=>'CHECK');
	$default_fields=$list_fields; 
	$queryDetails = 'SELECT ';
	foreach ($list_fields as $key=>$value){
		if($key != 'SUP' and $key != 'MODIF' and $key != 'CHECK')
		$queryDetails .= $key.',';		
	} 
	$queryDetails=substr($queryDetails,0,-1);
	$queryDetails .= " FROM operators where ACCESSLVL=".$protectedPost['onglet'];
	$tab_options['FILTRE']=array('LASTNAME'=>'LASTNAME','ID'=>'ID');
	if ($protectedPost['onglet'] == ADMIN){
		$tab_options['LIEN_LBL']['ID']='index.php?'.PAG_INDEX.'='.$pages_refs['ms_custom_perim'].'&head=1&id=';'admin_perim.php?id=';
		$tab_options['LIEN_CHAMP']['ID']='ID';
		$tab_options['LIEN_TYPE']['ID']='POPUP';
		$tab_options['POPUP_SIZE']['ID']="width=550,height=650";
	}
	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,100,$tab_options);
		//traitement par lot
	$img['image/sup_search.png']=$l->g(162);
	del_selection($form_name);
}

//echo "</td></tr></table>";
if ($protectedPost['MODIF'] != ''){
	$choix=show_modif(array(1=>$data_on[1],2=>$data_on[2],3=>$data_on[3]),'CHANGE',2);
	echo "<tr><td align=center><b>".$l->g(911)."<font color=red> ".$protectedPost['MODIF']." </font></b>".$choix." <input type='submit' name='VALID_MODIF' value='".$l->g(910)."'></td></tr>";
	echo "<input type='hidden' name='MODIF_ON' value='".$protectedPost['MODIF']."'>";
}
echo '</div>';
//echo "</table>";
echo "</form>";




?>