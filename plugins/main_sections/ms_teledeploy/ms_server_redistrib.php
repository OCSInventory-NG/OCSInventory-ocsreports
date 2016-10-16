<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */
/*
 * For redistribution's server
 */
$sadmin_profil=1;
require_once('require/function_server.php');

//delete one server or all
if($_POST["supp"]){
	if ($_POST["supp"] != "ALL"){
		$verif[0]['sql']="select fileid from download_enable,devices
				where download_enable.id=devices.ivalue
				and download_enable.SERVER_ID=".$_POST["supp"];
		$verif[0]['condition']='EXIST';
		$verif[0]['MSG_ERROR']=$l->g(689)." ".$l->g(687);
		$ok=verification($verif);
		if (isset($ok)){
            mysqli_query($_SESSION['OCS']["writeServer"],"delete from download_enable where SERVER_ID=".$_POST["supp"]);
			mysqli_query($_SESSION['OCS']["writeServer"], "delete from download_servers where hardware_id=".$_POST["supp"]);
		}
	}
	elseif ($_POST["supp"] == "ALL"){
		$verif[0]['sql']="select fileid from download_enable,devices
				where download_enable.id=devices.ivalue
				and GROUP_ID=".$systemid;
		$verif[0]['condition']='EXIST';
		$verif[0]['MSG_ERROR']=$l->g(688)." ".$l->g(690);
		$ok=verification($verif);
		if (isset($ok)){
			mysqli_query($_SESSION['OCS']["writeServer"],"delete from download_enable where GROUP_ID=".$systemid) ;
			$sql="delete from download_servers where GROUP_ID = ".$systemid;
			mysqli_query($_SESSION['OCS']["writeServer"],$sql);
		}
	}
}

//Modif server's machine
if (isset($_POST['Valid_modif']) && isset($_POST['modif']) && $_POST['modif'] != ""){
	$default_values=look_config_default_values(array('DOWNLOAD_SERVER_URI','DOWNLOAD_SERVER_DOCROOT'));
	if (trim($_POST['URL']) == "")
	$_POST['URL']=$default_values['tvalue']['DOWNLOAD_SERVER_URI'];
	if (trim($_POST['REP_STORE']) == "")
	$_POST['REP_STORE']=$default_values['tvalue']['DOWNLOAD_SERVER_DOCROOT'];
		
	if ($_POST['modif'] != "ALL")
	{
		
			$sql= "update download_servers set URL='".$_POST['URL']."' ,ADD_REP='".$_POST['REP_STORE']."' where hardware_id=".$_POST['modif'];
			mysqli_query($_SESSION['OCS']["writeServer"],$sql);
			$sql= "update download_enable set pack_loc='".$_POST['URL']."' where SERVER_ID=".$_POST['modif'];
			mysqli_query($_SESSION['OCS']["writeServer"],$sql);
	
	}else
	{
	
			$sql="update download_servers set URL='".$_POST['URL']."' ,ADD_REP='".$_POST['REP_STORE']."' where GROUP_ID=".$systemid;
			mysqli_query($_SESSION['OCS']["writeServer"],$sql);
			$sql= "update download_enable set pack_loc='".$_POST['URL']."' where GROUP_ID=".$systemid;
			mysqli_query($_SESSION['OCS']["writeServer"],$sql);
	
	}
}
//view of all group's machin
if (isset($systemid))
{
	if ($_POST['tri2'] == "")
	$_POST['tri2']=1;
	if (!(isset($_POST["pcparpage"])) && isset($_GET['res_pag']))
	$_POST["pcparpage"]=$_GET['res_pag'];
	if (!(isset($_POST["page"])) && isset($_GET['page']))
	$_POST["page"]=$_GET['page'];
	$form_name='nb_4_pag';
	echo open_form($form_name);
	$limit=nb_page($form_name);
	$sql="select download_servers.HARDWARE_ID ID,
			  hardware.NAME,
			  hardware.IPADDR,
			  hardware.DESCRIPTION,
			  download_servers.URL,
			  download_servers.ADD_REP
		from hardware right join download_servers on hardware.id=download_servers.hardware_id
		where download_servers.GROUP_ID=".$systemid." order by ".$_POST['tri2']." ".$_POST['sens'];
	$reqCount="select count(*) nb from (".$sql.") toto";
	$resCount = mysqli_query($_SESSION['OCS']["readServer"],$reqCount);
	$valCount = mysqli_fetch_array($resCount);
	$sql.=" limit ".$limit["BEGIN"].",".$limit["END"];
		$result = mysqli_query($_SESSION['OCS']["readServer"],$sql);
		$i=0;
	if ($_POST['sens'] == "ASC")
		$sens="DESC";
	else
		$sens="ASC";
	while($colname = mysqli_fetch_field($result)){
		$col=$colname->name;
		$deb="<a OnClick='tri(\"".$col."\",\"tri2\",\"".$sens."\",\"sens\",\"".$form_name."\")' >";
		$fin="</a>";
		$entete[$i++]=$deb.$col.$fin;			
	}
		$entete[$i++]="SUP <br><img src=image/delete-small.png OnClick='confirme(\"\",\"ALL\",\"".$form_name."\",\"supp\",\"".$l->g(640)." ".$l->g(643)." \");'>";
		$entete[$i]="MODIF  <img src=image/modif_all.png  OnClick='pag(\"ALL\",\"modif\",\"".$form_name."\")'>";

	$i=0;
	//" du groupe ".$data[$_GET['viewmach']]['ID'].
	while($item = mysqli_fetch_object($result)){
			$data2[$i]['ID']=$item ->ID;
			$data2[$i]['NAME']=$item ->NAME;
			$data2[$i]['IP_ADDR']=$item ->IPADDR;
			$data2[$i]['DESCRIPTION']=$item ->DESCRIPTION;
			$data2[$i]['URL']="http://".$item ->URL;
			$data2[$i]['REP_STORE']=$item ->ADD_REP;
			$data2[$i]['SUP']="<img src=image/delete-small.png OnClick='confirme(\"".$item ->NAME."\",\"".$item ->ID."\",\"".$form_name."\",\"supp\",\"".$l->g(640)." ".$l->g(644)." \");'>";
			if ($data2[$i]['IP_ADDR'] != "" )
			$data2[$i]['MODIF']="<img src=image/modif_tab.png OnClick='pag(\"".$i."\",\"modif\",\"".$form_name."\")'>";
			else
			$data2[$i]['MODIF']="";
			$i++;
	}
	 $total="<font color=red> (<b>".$valCount['nb']." ".$l->g(652)."</b>)</font>";
	tab_entete_fixe($entete,$data2,$l->g(645).$total,"95","300");
	show_page($valCount['nb'],$form_name);
	echo "<input type='hidden' id='supp' name='supp' value=''>";	
	echo "<input type='hidden' id='modif' name='modif' value=''>";
	echo "<input type='hidden' id='tri2' name='tri2' value='".$_POST['tri2']."'>";
	echo "<input type='hidden' id='sens' name='sens' value='".$_POST['sens']."'>";
	echo "</table>";
	echo close_form();
	//detail of group's machin
	if ($_POST['modif']!=""  && !isset($_POST['Valid_modif']) && !isset($_POST['Reset_modif']))
	{
		$tab_name[1]=$l->g(646).": ";
		$tab_name[2]=$l->g(648).": ";
		$tab_typ_champ[1]['DEFAULT_VALUE']=substr($data2[$_POST['modif']]['URL'],7);
		$tab_typ_champ[1]['COMMENT_BEFORE']="<b>http://</b>";
		$tab_typ_champ[1]['COMMENT_AFTER']="<small>".$l->g(691)."</small>";
		$tab_typ_champ[1]['INPUT_NAME']="URL";
		$tab_typ_champ[1]['INPUT_TYPE']=0;
		$tab_typ_champ[2]['DEFAULT_VALUE']=$data2[$_POST['modif']]['REP_STORE'];
		$tab_typ_champ[2]['INPUT_NAME']="REP_STORE";
		$tab_typ_champ[2]['INPUT_TYPE']=0;
		$tab_hidden["modif"]=$data2[$_POST['modif']]['ID'];
		$tab_hidden["pcparpage"]=$_POST['pcparpage'];
		$tab_hidden["page"]=$_POST['page'];
		$tab_hidden["old_pcparpage"]=$_POST['old_pcparpage'];
		if ($_POST['modif'] == "ALL"){
			$tab_hidden["modif"]="ALL";
			$title= $l->g(692);
		}
		else
			$title= $l->g(693)." ".$data2[$_POST['modif']]['NAME'];
	        $comment=$l->g(694);
	        tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,array(
	        	'title' => $title,
	        	'comment' => $comment
	        ));
		
	}
	
}
?>
