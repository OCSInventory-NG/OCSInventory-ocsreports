<?php
require_once('require/function_search.php');
//if ($protectedPost['CHOISE'] == 'SEL'){
//			$array_id=$protectedGet['idchecked'];		
//		}elseif ($protectedPost['CHOISE'] == 'REQ'){
//			if (is_array($_SESSION['ID_REQ']))
//			$array_id=implode(',',$_SESSION['ID_REQ']);	
//			else
//			$array_id=$_SESSION['ID_REQ'];	
//			//print_r($_SESSION['ID_REQ']);
//}
$form_name="lock_affect";
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''><div align=center>";
$list_id=multi_lot($form_name,$l->g(601));
//$list_fields=array();
//if (!isset($protectedPost['SHOW']))
//		$protectedPost['SHOW'] = 'NOSHOW';
if (isset($protectedPost['Valid_modif_x'])){
		foreach ($protectedPost as $key=>$value){
			$temp="";
			if (substr($key, 0, 5) == "check"){
				$temp=substr($key, 5);
				$tag_value=$protectedPost[$temp];
				if ($temp == $_SESSION['TAG_LBL'])
					$temp="TAG";
				$list_tag[$temp]=$tag_value;
			} 				
		}
	if (isset($list_tag)){	
		$sql= "update accountinfo set ";
		foreach($list_tag as $tag=>$value){
			 $sql.=$tag." = \"".$value."\" ,";
		}
		$sql=substr($sql,0, -1)." where hardware_id in (".$list_id.")";
		//echo "<br>".$sql;
		mysql_query($sql, $_SESSION["writeServer"]) or die(mysql_error($_SESSION["writeServer"]));	
		unset($_SESSION['DATA_CACHE']['TAB_MULTICRITERE']);
		echo "<script language='javascript'> window.opener.document.multisearch.submit();</script>";
		//echo $sql;
	}		
}

if (isset($protectedPost['RAZ']) and $protectedPost['RAZ'] != "" and $protectedPost['pack_list'] != ""){
	$sql="select ID from download_enable 
			where fileid='".$protectedPost['pack_list']."'";
	$result = mysql_query($sql, $_SESSION["readServer"]) or die(mysql_error($_SESSION["readServer"]));	
	while($item = mysql_fetch_object($result)){	
		$list_download_id[]=$item->ID;
	}
	//		and d.hardware_id in (".$array_id.")";
			
			
	$sql="delete from devices 
			where IVALUE in (".implode(',',$list_download_id).") 
			and NAME='DOWNLOAD'
			and hardware_id in (".$list_id.")";
	//echo $sql."<br>";
	mysql_query($sql, $_SESSION["writeServer"]) or die(mysql_error($_SESSION["writeServer"]));	
	echo "<br><font color=green>".mysql_affected_rows()." ".$l->g(1026)."</font>";
	
}
	if ($_SESSION["lvluser"] == SADMIN){
		$def_onglets['TAG']=$l->g(1022); 
		$def_onglets['SUP_PACK']=$l->g(1021); 
		//$def_onglets['SERV']=strtoupper($l->g(651));
		if ($protectedPost['onglet'] == "")
		$protectedPost['onglet']="TAG";	
		//show onglet
		onglet($def_onglets,$form_name,"onglet",7);
	}
	
	
	//print_r($protectedPost);
	if (isset($protectedPost['CHOISE']) and $protectedPost['CHOISE'] != ""){
		if ($protectedPost['onglet']=="TAG" or !isset($protectedPost['onglet'])){		
			$queryDetails = "show columns from accountinfo";
			$resultDetails = mysql_query($queryDetails, $_SESSION["readServer"]) or die(mysql_error($_SESSION["readServer"]));
			$i=0;
			while($item = mysql_fetch_object($resultDetails)){
				if ($item->Field != "HARDWARE_ID"){
					if ($item->Field == "TAG")
						$truename=$_SESSION['TAG_LBL'];
					else
						$truename=$item->Field;
					$java="";
					switch ($item->Type){
						case "int(11)" : $java = $chiffres;
										break;
						case "varchar(255)"  : $java = $majuscule;
										break;
						case "date"  : $java = "READONLY ".dateOnClick($truename);
										//$tab_typ_champ[$i]['COMMENT_BEHING'] =datePick($truename);
										break;
						default : $java;
					}
					$tab_typ_champ[$i]['COMMENT_BEHING']="<input type='checkbox' name='check".$truename."' id='check".$truename."' ".(isset($protectedPost['check'.$truename])? " checked ": "").">";
					$tab_typ_champ[$i]['INPUT_NAME']=$truename;
					$tab_typ_champ[$i]['INPUT_TYPE']=0;
					$tab_typ_champ[$i]['CONFIG']['JAVASCRIPT']=$java." onclick='document.getElementById(\"check".$truename."\").checked = true' ";
					$tab_typ_champ[$i]['CONFIG']['MAXLENGTH']=100;
					$tab_typ_champ[$i]['CONFIG']['SIZE']=40;
					$tab_typ_champ[$i]['DEFAULT_VALUE']=$protectedPost[$truename];
					$tab_name[$i]=$truename;
					$i++;
				}
			}
		
				tab_modif_values($tab_name,$tab_typ_champ,array('TAG_MODIF'=>$protectedPost['MODIF'],'FIELD_FORMAT'=>$type_field[$protectedPost['MODIF']]),$l->g(895),"");
		}elseif ($protectedPost['onglet']=="SUP_PACK"){
			echo "<table cellspacing='5' width='80%' BORDER='0' ALIGN = 'Center' CELLPADDING='0' BGCOLOR='#C7D9F5' BORDERCOLOR='#9894B5'><tr><td>";
			
			$queryDetails = "select fileid,name from download_available  where name != '' order by 1 desc";
			$resultDetails = mysql_query($queryDetails, $_SESSION["readServer"]) or die(mysql_error($_SESSION["readServer"]));
			while($val = mysql_fetch_array($resultDetails)){
				$List[$val["fileid"]]=$val["name"];		
			}
			$select=show_modif($List,'pack_list',2,$form_name);
			echo  "<tr><td align=center>".$l->g(970)." :".$select."</td></tr>";
			if ($protectedPost['pack_list'] != ""){
				$sql ="select count(*) c, tvalue from download_enable d_e,devices d
						where d.name='DOWNLOAD' and d.IVALUE=d_e.ID and d_e.fileid=".$protectedPost['pack_list']."
						and d.hardware_id in (".$list_id.") group by tvalue";
	
				$result = mysql_query($sql, $_SESSION["readServer"]) or die(mysql_error($_SESSION["readServer"]));
				while ($item = mysql_fetch_object($result)){
					if ($item->tvalue == "")
						$value=$l->g(482);
					else
						$value=$item->tvalue;
				echo "<tr><td colspan=10 align=center>".$item->c." ".$l->g(1023)." ".$value." ".$l->g(1024)."</td></tr>";
				}
			}
			echo "<tr><td colspan=10 align=center><input type='submit' name='RAZ' value='".$l->g(1025)."'></td></tr>";
			
			
			echo "</table>";
		}
	}
	
echo "</form>";
?>
