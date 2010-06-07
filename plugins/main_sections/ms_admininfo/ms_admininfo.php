<?php 
	$list_fields=array();
	if (!isset($protectedPost['SHOW']))
		$protectedPost['SHOW'] = 'NOSHOW';


	$form_name="affich_tag";
	$table_name=$form_name;
	if (isset($protectedPost['Valid_modif_x'])){
		if ($protectedPost['TAG_MODIF'] == $_SESSION['OCS']['TAG_LBL'])
		$lbl_champ='TAG';
		else
		$lbl_champ=$protectedPost['TAG_MODIF'];
		$sql=" update accountinfo set ".$lbl_champ."='";
		if ($protectedPost['FIELD_FORMAT'] == "date")
		$sql.= dateToMysql($protectedPost['NEW_VALUE'])."'";
		else
		$sql.= $protectedPost['NEW_VALUE']."'";
		$sql.=" where hardware_id=".$systemid; 
		mysql_query($sql, $_SESSION['OCS']["writeServer"]);
		//reg�n�ration du cache
		$tab_options['CACHE']='RESET';
	}
	
	echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
	$queryDetails = "SHOW COLUMNS FROM accountinfo";
	$resultDetails = mysql_query($queryDetails, $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
	$item=mysql_fetch_array($resultDetails,MYSQL_ASSOC);
	$i=0;
	$queryDetails = "";
	while (@mysql_field_name($resultDetails,$i)){
		if(mysql_field_type($resultDetails,$i)=="date"){
			//echo dateFromMysql($item[mysql_field_name($resultDetails,$i)])." => ".mysql_field_name($resultDetails,$i);
			$value = "'".dateFromMysql($item[mysql_field_name($resultDetails,$i)])."'";
		}else
			$value = mysql_field_name($resultDetails,$i);
		$lbl=mysql_field_name($resultDetails,$i);	
		if ($lbl != 'HARDWARE_ID'){
			if ($lbl == 'TAG')
			$lbl=$_SESSION['OCS']['TAG_LBL'];
			$queryDetails .= "SELECT hardware_id as ID,'".$lbl."' as libelle, ".$value." as valeur FROM accountinfo WHERE hardware_id=".$systemid." UNION ";
		}
		$type_field[$lbl]=mysql_field_type($resultDetails,$i);
		$i++;
	}
	$queryDetails=substr($queryDetails,0,-6);
	$list_fields['Information']='libelle';
	$list_fields['Valeur']='valeur';
	//$list_fields['SUP']= 'ID';
	$list_fields['MODIF']= 'libelle';
	$list_col_cant_del=$list_fields;
	$default_fields= $list_fields;

	tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,80,$tab_options);
	//print_r($type_field);
	if (isset($protectedPost['MODIF']) and $protectedPost['MODIF'] != ''){
		switch ($type_field[$protectedPost['MODIF']]){
			case "int" : $java = $chiffres;
							break;
			case "string"  : $java = $majuscule;
							break;
			case "date"  : $java = "READONLY ".dateOnClick('NEW_VALUE');
							break;
			default : $java;
		}
		
		$truename=$protectedPost['MODIF'];
		if ($protectedPost['MODIF'] == $_SESSION['OCS']['TAG_LBL'])
			$truename='TAG';			
		if ($type_field[$protectedPost['MODIF']]=="date"){
		$tab_typ_champ[0]['COMMENT_BEHING'] =datePick('NEW_VALUE');
		$tab_typ_champ[0]['DEFAULT_VALUE']=dateFromMysql($item[$truename]);
		}else
		$tab_typ_champ[0]['DEFAULT_VALUE']=$item[$truename];
		$tab_typ_champ[0]['INPUT_NAME']="NEW_VALUE";
		$tab_typ_champ[0]['INPUT_TYPE']=0;
		$tab_typ_champ[0]['CONFIG']['JAVASCRIPT']=$java;
		$tab_typ_champ[0]['CONFIG']['MAXLENGTH']=100;
		$tab_typ_champ[0]['CONFIG']['SIZE']=40;
		$data_form[0]=$protectedPost['MODIF'];
		tab_modif_values($data_form,$tab_typ_champ,array('TAG_MODIF'=>$protectedPost['MODIF'],'FIELD_FORMAT'=>$type_field[$protectedPost['MODIF']]),$l->g(895),"");
		
	}
	echo "</form>";
?>



<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2005
// Web: http://ocsinventory.sourceforge.net
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2007/02/08 15:53:24 $$Author: plemmet $($Revision: 1.6 $)
if($protectedGet["suppAcc"]) {
	@mysql_query("ALTER TABLE accountinfo DROP ".$protectedGet["suppAcc"], $_SESSION['OCS']["writeServer"]);
	unset($_SESSION['OCS']["availFieldList"], $_SESSION['OCS']["optCol"]);
	echo "<br><br><center><font face='Verdana' size=-1 color='red'><b>". $protectedGet["suppAcc"] ."</b> ".$l->g(226)." </font></center><br>";
}
$tab_type=array('TXT'=>$l->g(229),
				'INT'=>$l->g(230),
				'REAL'=>$l->g(231),
				'DATE'=>$l->g(232),
				'CHECKBOX'=>'CHECKBOX',
				'LIST'=>'LIST',
				'RADIO_BUTTON'=>'Radio bouton');



if($protectedPost["nom"])
{
	unset($_SESSION['OCS']["availFieldList"], $_SESSION['OCS']["optCol"]);
	switch($protectedPost["type"]) {
		case $l->g(229): $suff = "VARCHAR(255)"; break;
		case $l->g(230): $suff = "INT"; break;
		case $l->g(231): $suff = "REAL"; break;
		case $l->g(232): $suff = "DATE"; break;
	}
	
	$queryAccAddN = "ALTER TABLE accountinfo ADD ".$protectedPost["nom"]." $suff";
	if(mysql_query($queryAccAddN, $_SESSION['OCS']["writeServer"]))
		echo "<br><br><center><font face='Verdana' size=-1 color='green'><b>". $protectedPost["nom"] ."</b> ".$l->g(234)." </font></center><br>";
	else 
		echo "<br><br><center><font face='Verdana' size=-1 color='red'><b>".$l->g(259)."</b></font></center><br>";
}//fin if	
?>
			<script language=javascript>
				function confirme(did)
				{
					if(confirm("<?php echo $l->g(227)?> "+did+" ?"))
						window.location="index.php?<?php echo PAG_INDEX; ?>=<?php echo $protectedGet[PAG_INDEX]?>&c=<?php echo ($_SESSION['OCS']["c"]?$protectedGet["c"]:2)?>&a=<?php echo $protectedGet["a"]?>&page=<?php echo $protectedGet["page"]?>&suppAcc="+did;
				}
			</script>
<?php 
printEnTete($l->g(56));
echo "
			<br>
		 <form name='ajouter_reg' method='POST'>
	<center>
	<table width='60%'>
	<tr>
		<td align='right' width='50%'>
			<font face='Verdana' size='-1'>".$l->g(228)." :&nbsp;&nbsp;&nbsp;&nbsp;</font>
		</td>
		<td width='50%' align='left'><input size=40 name='nom'>
		</td>
	</tr>
	<tr>
		<td align=center>
			<font face='Verdana' size='-1'>".$l->g(66).":</font>
		</td>
		<td>
			<select name='type'>
				<option>".$l->g(229)."</option>
				<option>".$l->g(230)."</option>
				<option>".$l->g(231)."</option>
				<option>".$l->g(232)."</option>
			</select>
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
		<tr>
		<td colspan='2' align='center'>
			<input class='bouton' name='enre' type='submit' value=".$l->g(114)."> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</td>
	</tr>
	
	</table></center></form><br>
	";
	printEnTete($l->g(233));
	$reqAc = mysql_query("SHOW COLUMNS FROM accountinfo", $_SESSION['OCS']["readServer"]) or die(mysql_error($_SESSION['OCS']["readServer"]));
	echo "<br><table BORDER='0' WIDTH = '50%' ALIGN = 'Center' CELLPADDING='0' BGCOLOR='#C7D9F5' BORDERCOLOR='#9894B5'>";
	echo "<tr><td align='center'><b>".$l->g(49)."</b></font></td><td align='center'><b>".$l->g(66)."</b></font></td></tr>";		
	while($colname=mysql_fetch_array($reqAc)) {		
		if( $colname["Field"] != "DEVICEID" && $colname["Field"] != TAG_NAME && $colname["Field"] != "HARDWARE_ID" ) {
			$x++;
			echo "<TR height=20px bgcolor='". ($x%2==0 ? "#FFFFFF" : "#F2F2F2") ."'>";	// on alterne les couleurs de ligne			
			echo "<td align=center>".$colname["Field"]."</font></td><td align=center>".$colname["Type"]."</font></td><td align=center>
			<a href=# OnClick='confirme(\"".$colname["Field"]."\");'><img src=image/supp.png></a></td></tr>";
		}
	}
	echo "</table><br>";

?>

