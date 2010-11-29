<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2010 (erwan(at)ocsinventory-ng(pt)org)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

$form_name='info_ipdiscover';
//$ban_head='no';
//$no_error='YES';
//require_once("header.php");

//recherche de la personne connect�e
if (isset($_SESSION['OCS']['TRUE_USER']))
$user=$_SESSION['OCS']['TRUE_USER'];
else
$user=$_SESSION['OCS']['loggeduser'];

//suppression d'une adresse mac
if(isset($protectedPost['SUP_PROF'])){
	//$del=mysql_escape_string($protectedPost['SUP_PROF']);
	mysql_query("DELETE FROM netmap WHERE mac='".$protectedPost['SUP_PROF']."'", $_SESSION['OCS']["writeServer"] ) or die(mysql_error());
	mysql_query("DELETE FROM network_devices WHERE macaddr='".$protectedPost['SUP_PROF']."'", $_SESSION['OCS']["writeServer"] ) or die(mysql_error());
	unset($_SESSION['OCS']['DATA_CACHE']['IPDISCOVER_'.$protectedGet['prov']]);
	
}
//identification d'une adresse mac
if (isset($protectedPost['Valid_modif_x'])){
	if (trim($protectedPost['COMMENT']) == "")
	$ERROR= $l->g(942);
	if (trim($protectedPost['TYPE']) == "")
	$ERROR= $l->g(943);
	if (isset($ERROR) and $protectedPost['MODIF_ID'] != '')
	$protectedPost['USER']=$protectedPost['USER_ENTER'];

	if (!isset($ERROR)){
		//$post=xml_escape_string($protectedPost);
		if ($protectedPost['USER_ENTER'] != ''){
			$sql="update network_devices 
					set DESCRIPTION = '".$protectedPost['COMMENT']."',
					TYPE = '".$protectedPost['TYPE']."',
					MACADDR = '".$protectedPost['mac']."',
					USER = '".$user."' where ID='".$protectedPost['MODIF_ID']."'";			
		}else{		
			$sql="insert into network_devices (DESCRIPTION,TYPE,MACADDR,USER)
			  VALUES('".$protectedPost['COMMENT']."',
			  '".$protectedPost['TYPE']."',
			  '".$protectedPost['mac']."',
			  '".$user."')";
		}
		mysql_query( $sql , $_SESSION['OCS']["writeServer"]) or die(mysql_error($_SESSION['OCS']["writeServer"]));
		//suppression du cache pour prendre en compte la modif
		unset($_SESSION['OCS']['DATA_CACHE']['IPDISCOVER_'.$protectedGet['prov']]);
	}else{		
		$protectedPost['MODIF']=$protectedPost['mac'];
	}	
}

//formulaire de saisie de l'identification de l'adresse mac
if (isset($protectedPost['MODIF']) and $protectedPost['MODIF'] != ''){
	
	//cas d'une modification de la donn�e d�j� saisie
	if ($protectedGet['prov'] == "ident" and !isset($protectedPost['COMMENT'])){
		//$id=mysql_escape_string($protectedPost['MODIF']);
		$sql="select DESCRIPTION,TYPE,MACADDR,USER from network_devices where id ='".$protectedPost['MODIF']."'";
		$res = mysql_query($sql, $_SESSION['OCS']["readServer"] );
		$val = mysql_fetch_array( $res );
		$protectedPost['COMMENT']=$val['DESCRIPTION'];
		$protectedPost['MODIF']=$val['MACADDR'];
		$protectedPost['TYPE']=$val['TYPE'];
		$protectedPost['USER']=	$val['USER'];
		$protectedPost['MODIF_ID']=$protectedPost['MODIF'];
	}
	$tab_hidden['USER_ENTER']=$protectedPost['USER'];	
	$tab_hidden['MODIF_ID']=$protectedPost['MODIF_ID'];	
	echo "<br>";
	echo "<br>";
	//si on est dans le cas d'une modif, on affiche le login qui a saisi la donn�e
	if ($protectedPost['MODIF_ID'] != ''){
		$tab_typ_champ[3]['DEFAULT_VALUE']=$protectedPost['USER'];
		$tab_typ_champ[3]['INPUT_NAME']="USER";
		$tab_typ_champ[3]['INPUT_TYPE']=3;
		$tab_name[3]=$l->g(944)." : ";
		
		$title=$l->g(945);		
	}else{
		$title=$l->g(946);	
	}
	
	$tab_typ_champ[0]['DEFAULT_VALUE']=$protectedPost['MODIF'];
	$tab_typ_champ[0]['INPUT_NAME']="MAC";
	$tab_typ_champ[0]['INPUT_TYPE']=3;
	$tab_name[0]=$l->g(95).": ";
	
	$tab_typ_champ[1]['DEFAULT_VALUE']=$protectedPost['COMMENT'];
	$tab_typ_champ[1]['INPUT_NAME']="COMMENT";
	$tab_typ_champ[1]['INPUT_TYPE']=0;
	$tab_typ_champ[1]['CONFIG']['SIZE']=60;
	$tab_typ_champ[1]['CONFIG']['MAXLENGTH']=255;
	$tab_name[1]=$l->g(53).": ";
	
	$sql="select distinct NAME from devicetype ";
	$res=mysql_query($sql, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
	while ($row=mysql_fetch_object($res)){
		$list_type[$row->NAME]=$row->NAME;
	}
	$tab_typ_champ[2]['DEFAULT_VALUE']=$list_type;
	$tab_typ_champ[2]['INPUT_NAME']="TYPE";
	$tab_typ_champ[2]['INPUT_TYPE']=2;
	$tab_name[2]=$l->g(66).": ";
	
	
	//printEnTete("Ajout d'un nouveau p�riph�rique");

	$tab_hidden['mac']=$protectedPost['MODIF'];	
	if (isset($ERROR))
		msg_error($ERROR);
	tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title,$comment="");	
}
else{ //affichage des p�riph�riques
	if (!(isset($protectedPost["pcparpage"])))
		 $protectedPost["pcparpage"]=5;
	if (isset($protectedGet['value'])){
		//$netid=mysql_escape_string($protectedGet['value']);
		if ($protectedGet['prov'] == "no_inv"){
			$title=$l->g(947);
			$sql="SELECT ip, mac, mask, date, name FROM netmap n 
				LEFT JOIN networks ns ON ns.macaddr=n.mac
				WHERE n.netid='".$protectedGet['value']."' 
				AND (ns.macaddr IS NULL OR ns.IPSUBNET <> n.netid) 
				AND mac NOT IN (SELECT DISTINCT(macaddr) FROM network_devices)";
			$list_fields= array($l->g(34) => 'ip','MAC'=>'mac',
								$l->g(208)=>'mask',
								$l->g(232)=>'date',
								$l->g(318)=>'name',
								'SUP'=>'mac',
								'MODIF'=>'mac');
			$tab_options['MODIF']['IMG']="image/prec16.png";
			$tab_options['LBL']['MODIF']=$l->g(114);
			$default_fields= $list_fields;
		}elseif($protectedGet['prov'] == "ident"){
			$title=$l->g(948);
			$sql="select n.ID,n.TYPE,n.DESCRIPTION,a.IP,a.MAC,a.MASK,a.NETID,a.NAME,a.date,n.USER
				 from network_devices n LEFT JOIN netmap a ON a.mac=n.macaddr
				 where netid='".$protectedGet['value']."'";
				 $list_fields= array($l->g(66) => 'TYPE',$l->g(53)=>'DESCRIPTION',
								$l->g(34)=>'IP',
								'MAC'=>'MAC',
								$l->g(208)=>'MASK',
								$l->g(316)=>'NETID',
								$l->g(318)=>'NAME',
								$l->g(232)=>'date',
								$l->g(369)=>'USER',
								'SUP'=>'MAC',
								'MODIF'=>'ID');
				$default_fields= array($l->g(34)=>$l->g(34),$l->g(66)=>$l->g(66),$l->g(53)=>$l->g(53),
									'MAC'=>'MAC',$l->g(232)=>$l->g(232),$l->g(369)=>$l->g(369),'SUP'=>'SUP','MODIF'=>'MODIF');

		}
		printEnTete($title);
		echo "<br><br>";		
		$tab_options['LBL']['MAC']=$l->g(95);		
		$tab_options['FILTRE']['ip']=$l->g(66);
		$list_col_cant_del=array($l->g(66)=>$l->g(66),'SUP'=>'SUP','MODIF'=>'MODIF');
		$table_name="IPDISCOVER_".$protectedGet['prov'];
		$form_name=$table_name;
		echo "<form name='".$form_name."' id='".$form_name."' action='' method='post'>";
		$result_exist=tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$sql,$form_name,80,$tab_options); 
		echo "</form>";
	}
}
//require_once($_SESSION['OCS']['FOOTER_HTML']);
?>
