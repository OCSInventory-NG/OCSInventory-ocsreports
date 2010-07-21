<?php
/*
 *  
 * function for new tab
 * 
 * 
 */

$chiffres="onKeyPress=\"return scanTouche(event,/[0-9]/)\" onkeydown='convertToUpper(this)'
		  onkeyup='convertToUpper(this)' 
		  onblur='convertToUpper(this)'
		  onclick='convertToUpper(this)'";
 $majuscule="onKeyPress=\"return scanTouche(event,/[0-9 a-z A-Z]/)\" onkeydown='convertToUpper(this)'
		  onkeyup='convertToUpper(this)' 
		  onblur='convertToUpper(this)'";

 
 function prepare_sql_tab($list_fields,$explu){
 	$begin_arg=array();
 	$begin_sql="SELECT ";
 	foreach ($list_fields as $key=>$value){
 		if (!in_array($key,$explu)){
			$begin_sql .= '%s,';
			array_push($begin_arg,$value);		
 		}
	} 
	return array('SQL'=>substr($begin_sql,0,-1),'ARG'=>$begin_arg); 	
 	
 }
 
 
 
 /*
  * 
  * This function check an mail addresse 
  * 
  */  
 function VerifyMailadd($addresse)
{
   $Syntaxe='#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#';
   if(preg_match($Syntaxe,$addresse))
      return true;
   else
     return false;
}
 
function send_mail($mail_to,$subjet,$body){
	global $l;
// few personnes
	$to="";
	if (is_array($mail_to)){
		$to = implode(',',$mail_to);
	}else
     $to  = $mail_to;

     // message
     $message = '
     <html>
      <head>
       <title>' . $subjet . '</title>
      </head>
      <body>
       ' . $body . '
      </body>
     </html>
     ';

     // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
     $headers  = 'MIME-Version: 1.0' . "\r\n";
     $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

     // En-têtes additionnels
     $headers .= 'To: '. implode(',',$mail_to) . "\r\n";
     $headers .= 'From: Ocsinventory <Ocsinventory@ocsinventory.com>' . "\r\n";
  //   $headers .= 'Cc: anniversaire_archive@example.com' . "\r\n";
   //  $headers .= 'Bcc: anniversaire_verif@example.com' . "\r\n";

     // Envoi
     $test_mail=@mail($to, $subject, $message, $headers);
	if (!$test_mail){
		echo "<script>alert('" . $l->g(1057)."');</script>";		
	}
	
	
}
 
 
function replace_entity_xml($txt){
	$cherche = array("&","<",">","\"","'");
	$replace = array( "&amp;","&lt;","&gt;", "&quot;", "&apos;");
	return str_replace($cherche, $replace, $txt);		
}  


 
function printEnTete_tab($ent) {
	echo "<br><table border=0 WIDTH = '62%' ALIGN = 'Center' CELLPADDING='5'>
	<tr height=40px bgcolor=#f2f2f2 align=center><td><b>".$ent."</b></td></tr></table>";
}
 
//function for escape_string before use database
function escape_string($array){
	if (is_array($array)){
		foreach ($array as $key=>$value){
			$trait_array[$key]=mysql_real_escape_string($value);
		}
		return ($trait_array);
	}else
	return array();	
}

function xml_escape_string($array){
	foreach ($array as $key=>$value){
		$trait_array[$key]=xml_encode($value);
		//$trait_array[$key]=mysql_escape_string($value);
	}
	return ($trait_array);
}

function xml_encode( $txt ) {
		$cherche = array("&","<",">","\"","'","é","è","ô","Î","î","à","ç","ê","â");
		$replace = array( "&amp;","&lt;","&gt;", "&quot;", "&apos;","&eacute;","&egrave;","&ocirc;","&Icirc;","&icirc;","&agrave;","&ccedil;","&ecirc;","&acirc;");
		return str_replace($cherche, $replace, $txt);		
	
}

function xml_decode( $txt ) {
		$cherche = array( "&acirc;","&ecirc;","&ccedil;","&agrave;","&lt;","&gt;", "&quot;", "&apos;","&eacute;","&egrave;","&ocirc;","&Icirc;","&icirc;","&amp;");
		$replace = array( "â","ê","ç","à","<",">","\"","'","é","è","ô","Î","î", "&" );
	//	echo $txt;
		//echo str_replace("&toto;","ç",$txt);
		return str_replace($cherche, $replace, $txt);		
	
}


//ascending and descending sort
function tri($sql)
{
	global $protectedGet;
	
	if ($protectedGet['sens']){
	$sens=$protectedGet['sens'];
	$col=$protectedGet['col'];
	}
	else{
	$sens="ASC";
	$col=1;
	}	
	
	$sql= $sql." order by ".$col." ".$sens;
	return $sql;
	
}

//fonction qui permet d'afficher un tableau de donn�es
/*
 * $entete_colonne = array ; => ex: $i=0;
									while($colname = mysql_fetch_field($result))
										$entete2[$i++]=$colname->name;
 * $data= array; => ex: $i=0;
						while($item = mysql_fetch_object($result)){
							$data2[$i]['ID']=$item ->ID;
							$data2[$i]['PRIORITY']=$up.$item ->PRIORITY.$down;
							$data2[$i]['TITLE']=$item ->TITLE;
							}
 * $titre= varchar => ex: "Administration des messages"
 * $width= taille tableau => ex: "60"
 * $height= taille tableau => ex: "300"
 * $lien = array ; => liste des colonnes qui ont le tri
 * 
 */
 function tab_entete_fixe($entete_colonne,$data,$titre,$width,$height,$lien=array(),$option=array())
{
	echo "<div align=center>";
	global $protectedGet,$l;
	if ($protectedGet['sens'] == "ASC"){
	$sens="DESC";
	}
	else
	{
	$sens="ASC";
	}

	if(isset($data))
	{
	?>
	<script language='javascript'>		
	function changerCouleur(obj, state) {
			if (state == true) {
				bcolor = obj.style.backgroundColor;
				fcolor = obj.style.color;
				obj.style.backgroundColor = '#FFDAB9';
				obj.style.color = 'red';
				return true;
			} else {
				obj.style.backgroundColor = bcolor;
				obj.style.color = fcolor;
				return true;
			}
			return false;
		}
	</script>
	<?php
	if ($titre != "")
	printEnTete_tab($titre);
	echo "<br><div class='tableContainer' id='data' style=\"width:".$width."%;\"><table cellspacing='0' class='ta'><tr>";
		//titre du tableau
	$i=1;
	foreach($entete_colonne as $k=>$v)
	{
		if (in_array($v,$lien))
			echo "<th class='ta' >".$v."</th>";
		else
			echo "<th class='ta'><font size=1 align=center>".$v."</font></th>";	
		$i++;		
	}
	echo "
    </tr>
    <tbody class='ta'>";
	
//	$i=0;
	$j=0;
	//lignes du tableau
//	while (isset($data[$i]))
	//{
	foreach ($data as $k2=>$v2){
			($j % 2 == 0 ? $color = "#f2f2f2" : $color = "#ffffff");
			echo "<tr class='ta' bgcolor='".$color."'  onMouseOver='changerCouleur(this, true);' onMouseOut='changerCouleur(this, false);'>";
			foreach ($v2 as $k=>$v)
			{
				if (isset($option['B'][$i])){
					$begin="<b>";
					$end="</b>";				
				}else{
					$begin="";
					$end="";	
				}
				
				
				if ($v == "") $v="&nbsp";
				echo "<td class='ta' >".$begin.$v.$end."</td>";
				
			}
			$j++;
			echo "</tr><tr>";
			//$i++;
	
	}
	echo "</tr></tbody></table></div>";	
	}
	else{
	echo "<center><font size=5 color=red>".$l->g(766). " " . "</font></center>";
	return FALSE;
	}
	echo "</div>";
}






//variable pour la fonction champsform
$num_lig=0;
/* fonction li�e � show_modif
 * qui permet de cr�er une ligne dans le tableau de modification/ajout
 * $title = titre � l'affichage du champ
 * $value_default = - pour un champ text ou input, la valeur par d�faut du champ.
 * 					- pour un champ select, liste des valeurs du champ
 * $input_name = nom du champ que l'on va r�cup�rer en $protectedPost
 * $input_type = 0 : <input type='text'>
 * 				 1 : <textarea>
 * 				 2 : <select><option>
 * $donnees = tableau qui contient tous les champs � afficher � la suite
 * $nom_form = si un select doit effectuer un reload, on y met le nom du formulaire � reload
*/
function champsform($title,$value_default,$input_name,$input_type,&$donnees,$nom_form=''){
	global $num_lig;
	$donnees['tab_name'][$num_lig]=$title;	
	$donnees['tab_typ_champ'][$num_lig]['DEFAULT_VALUE']=$value_default;
	$donnees['tab_typ_champ'][$num_lig]['INPUT_NAME']=$input_name;
	$donnees['tab_typ_champ'][$num_lig]['INPUT_TYPE']=$input_type;
	if ($nom_form != "")
	$donnees['tab_typ_champ'][$num_lig]['RELOAD']=$nom_form;
	$num_lig++;
	return $donnees;
	
}

/*
 * fonction li�e � tab_modif_values qui permet d'afficher le champ d�fini avec la fonction champsform
 * $name = nom du champ
 * $input_name = nom du champ r�cup�r� dans le $protectedPost
 * $input_type = 0 : <input type='text'>
 * 				 1 : <textarea>
 * 				 2 : <select><option>
 * $input_reload = si un select doit effectuer un reload, on y met le nom du formulaire � reload
 * 
 */
function show_modif($name,$input_name,$input_type,$input_reload = "",$configinput=array('MAXLENGTH'=>100,'SIZE'=>20,'JAVASCRIPT'=>"",'DEFAULT'=>"YES",'COLS'=>30,'ROWS'=>5))
{
	global $protectedPost,$l,$pages_refs;
	
	if ($configinput == "")
		$configinput=array('MAXLENGTH'=>100,'SIZE'=>20,'JAVASCRIPT'=>"",'DEFAULT'=>"YES",'COLS'=>30,'ROWS'=>5);
	//del stripslashes if $name is not an array
	if (!is_array($name)){
	//	echo "toto";
		$name=htmlspecialchars($name, ENT_QUOTES);
	}
		if ($input_type == 1){
			
		return "<textarea name='".$input_name."' id='".$input_name."' cols='".$configinput['COLS']."' rows='".$configinput['ROWS']."'  class='down' \>".$name."</textarea>";
	
	}elseif ($input_type ==0)
	return "<input type='text' name='".$input_name."' id='".$input_name."' SIZE='".$configinput['SIZE']."' MAXLENGTH='".$configinput['MAXLENGTH']."' value=\"".$name."\" class='down'\" ".$configinput['JAVASCRIPT'].">";
	elseif($input_type ==2){
		
		$champs="<select name='".$input_name."' id='".$input_name."' ".$configinput['JAVASCRIPT'];
		if ($input_reload != "") $champs.=" onChange='document.".$input_reload.".submit();'";
		$champs.=" class='down' \>";
		if ($configinput['DEFAULT'] == "YES")
		$champs.= "<option value='' class='hi' \></option>";
		$countHl=0;		
		if ($name != ''){
			//print_r($protectedPost);
			foreach ($name as $key=>$value){
			//	echo $protectedPost[$input_name]." == ".$key."<br>";
				$champs.= "<option value=\"".$key."\"";
				if ($protectedPost[$input_name] == $key )
				$champs.= " selected";
				$champs.= ($countHl%2==1?" class='hi'":" class='down'")." \>".$value."</option>";
				$countHl++;
			}
		}
		$champs.="</select>";
		return $champs;
	}elseif($input_type == 3){
		$hid="<input type='hidden' id='".$input_name."' name='".$input_name."' value='".$name."'>";
	//	echo $name."<br>";
		return $name.$hid;
	}elseif ($input_type == 4)
	 return "<input size='".$configinput['SIZE']."' type='password' name='".$input_name."' class='hi' \>";
	elseif ($input_type == 5 and isset($name) and is_array($name)){	
		foreach ($name as $key=>$value){
			$champs.= "<input type='checkbox' name='".$input_name."_".$key."' id='".$input_name."_".$key."' ";
			if ($protectedPost[$input_name."_".$key] == 'on' )
			$champs.= " checked ";
			$champs.= " >" . $value . " <br>";
		}
		return $champs;
	}elseif($input_type == 6){
		if (isset($configinput['NB_FIELD']))
			$i=$configinput['NB_FIELD'];
		else
			$i=6;
		$j=0;
		echo $name;
		while ($j<$i){
			$champs.="<input type='text' name='".$input_name."_".$j."' id='".$input_name."_".$j."' SIZE='".$configinput['SIZE']."' MAXLENGTH='".$configinput['MAXLENGTH']."' value=\"".$protectedPost[$input_name."_".$j]."\" class='down'\" ".$configinput['JAVASCRIPT'].">";
			$j++;
		}
		return $champs;		
	}elseif($input_type == 7)
		return "<input type='hidden' id='".$input_name."' name='".$input_name."' value='".$name."'>";
	elseif ($input_type == 8){
		return "<input type='button' id='".$input_name."' name='".$input_name."' value='".$l->g(1048)."' OnClick='window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_upload_file_popup']."&head=1&n=".$input_name."&tab=".$name."&dde=".$configinput['DDE']."\",\"active\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=350\")'>";
	}elseif ($input_type == 9){
		$aff="";
		if (is_array($name)){
			foreach ($name as $key=>$value){
				$aff.="<a href=# onclick='window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_view_file']."&prov=dde_wk&no_header=1&value=".$key."\",\"toto\",\"location=0,status=0,scrollbars=1,menubar=0,resizable=0,width=800,height=500\");' >".
						$value."</a><br>";
						/*window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_view_file']."
							&prov=dde_wk&no_header=1&value=".$key."\",\"toto\",\"location=0,
							status=0,scrollbars=1,menubar=0,resizable=0,width=800,height=500\");*/
				//$aff.="index.php?'.PAG_INDEX.'='.$pages_refs['ms_view_file'].'&prov=dde_wk&no_header=1&value=';"
			}
		}
		return $aff;
		
	}elseif ($input_type == 10){
		//le format de de $name doit etre sous la forme d'une requete sql avec éventuellement
		//des arguments. Dans ce cas, les arguments sont séparés de la requête par $$$$
		//et les arguments entre eux par des virgules
		//echo $name;
		$sql=explode('$$$$',$name);
		if (isset($sql[1])){
			$arg_sql=explode(',',$sql[1]);	
			$i=0;
			while ($arg_sql[$i]){
				$arg[$i]=$protectedPost[$arg_sql[$i]];
				$i++;	
			}
		}
		if (isset($arg_sql))
		$result = mysql2_query_secure($sql[0], $_SESSION['OCS']["readServer"],$arg);
		else
		$result = mysql2_query_secure($sql[0], $_SESSION['OCS']["readServer"]);
		if (isset($result) and $result != ''){
			$i=0;
			while($colname = mysql_fetch_field($result))
			$entete2[$i++]=$colname->name;
			
			$i=0;		
			while ($item = mysql_fetch_object($result)){
				$j=0;
				while ($entete2[$j]){
					$data2[$i][$entete2[$j]]=$item ->$entete2[$j];
					$j++;
				}
				$i++;
			}
		}
	 		return tab_entete_fixe($entete2,$data2,"",60,300);		
		
	}elseif($input_type == 11 and isset($name) and is_array($name)){	
		foreach ($name as $key=>$value){
			$champs.= "<input type='radio' name='".$input_name."' id='".$input_name."' value='" . $key . "'";
			if ($protectedPost[$input_name] == $key ){
				$champs.= " checked ";
			}
			$champs.= " >" . $value . " <br>";
		}
		return $champs;		
	}
}

function tab_modif_values($tab_name,$tab_typ_champ,$tab_hidden,$title="",$comment="",$name_button="modif",$showbutton=true,$form_name='CHANGE',$showbutton_action='')
{
	global $l,$protectedPost;

	if ($form_name != 'NO_FORM')
	echo "<form name='" . $form_name . "' id='" 
		. $form_name . "' action='' method='POST'>";
	echo '<div class="mvt_bordure" >';
	if ($showbutton_action != '')
		echo "<table align='right' border='0'><tr><td colspan=10 align='right'>" . $showbutton_action . "</td></tr></table>";
	echo "<table align='center' border='0' cellspacing=20 >";
	echo "<tr><td colspan=10 align='center'><font color=red><b><i>" . $title . "</i></b></font></td></tr>";
	
    foreach ($tab_name as $key=>$values)
	{
		//print_r($tab_typ_champ[$key]['DEFAULT_VALUE']);
		echo "<tr><td>" . $values . "</td><td>" . $tab_typ_champ[$key]['COMMENT_BEFORE']
		   . show_modif($tab_typ_champ[$key]['DEFAULT_VALUE'],$tab_typ_champ[$key]['INPUT_NAME'],$tab_typ_champ[$key]['INPUT_TYPE'],$tab_typ_champ[$key]['RELOAD'],
		   				$tab_typ_champ[$key]['CONFIG']).$tab_typ_champ[$key]['COMMENT_BEHING']
		   . "</td></tr>";
	}
 echo "<tr ><td colspan=10 align='center'><i>".$comment."</i></td></tr>";
 	if ($showbutton){
		echo "<tr><td><input title='" . $l->g(625) 
					. "'  class='image' type='image'  src='image/modif_valid_v2.png' name='Valid_" 
					. $name_button 
					."'>";
		echo "<input title='" . $l->g(626) 
				. "' class='image' type='image'  src='image/modif_anul_v2.png' name='Reset_"
				. $name_button . "'></td></tr>";
 	}
	echo "</table>";
        echo "</div>";    
    if ($tab_hidden != ""){
    		
		foreach ($tab_hidden as $key=>$value)
		{
			echo "<input type='hidden' name='" . $key ."' id='" . $key  
				. "' value='" . htmlspecialchars($value, ENT_QUOTES) . "'>";
	
		}
    }
    if ($form_name != 'NO_FORM')
	echo "</form>";
}

function show_field($name_field,$type_field,$value_field,$config=array()){
	global $protectedPost;
	$i=0;
	foreach($name_field as $key=>$value){
		$tab_typ_champ[$key]['DEFAULT_VALUE']=$value_field[$key];
		$tab_typ_champ[$key]['INPUT_NAME']=$name_field[$key];
		$tab_typ_champ[$key]['INPUT_TYPE']=$type_field[$key];
		
		
		if (!isset($config['ROWS'][$key]) or $config['ROWS'][$key] == '')
			$tab_typ_champ[$key]['CONFIG']['ROWS']=7;
		else
			$tab_typ_champ[$key]['CONFIG']['ROWS']=$config['ROWS'][$key];
			
		if (!isset($config['COLS'][$key]) or $config['COLS'][$key] == '')
			$tab_typ_champ[$key]['CONFIG']['COLS']=40;
		else
			$tab_typ_champ[$key]['CONFIG']['COLS']=$config['COLS'][$key];		
		
		if (!isset($config['SIZE'][$key]) or $config['SIZE'][$key] == '')
			$tab_typ_champ[$key]['CONFIG']['SIZE']=50;
		else
			$tab_typ_champ[$key]['CONFIG']['SIZE']=$config['SIZE'][$key];
		
		if (!isset($config['MAXLENGTH'][$key]) or $config['MAXLENGTH'][$key] == '')
			$tab_typ_champ[$key]['CONFIG']['MAXLENGTH']=255;
		else
			$tab_typ_champ[$key]['CONFIG']['MAXLENGTH']=$config['MAXLENGTH'][$key];
			
		if (isset($config['COMMENT_BEHING'][$key]))	{
			$tab_typ_champ[$key]['COMMENT_BEHING']=	$config['COMMENT_BEHING'][$key];
		}		
		
			
		if (isset($config['DDE'][$key]))	{
			$tab_typ_champ[$key]['CONFIG']['DDE']=$config['DDE'][$key];
		}	
		
		if (isset($config['SELECT_DEFAULT'][$key]))	{
			$tab_typ_champ[$key]['DEFAULT']=$config['SELECT_DEFAULT'][$key];
		}
		if (isset($config['JAVASCRIPT'][$key]))	{
			$tab_typ_champ[$key]['CONFIG']['JAVASCRIPT']=$config['JAVASCRIPT'][$key];
		}
		$i++;
	}
//	$i=0;
//	while ($name_field[$i]){
//		$tab_typ_champ[$i]['DEFAULT_VALUE']=$value_field[$i];
//		$tab_typ_champ[$i]['INPUT_NAME']=$name_field[$i];
//		$tab_typ_champ[$i]['INPUT_TYPE']=$type_field[$i];
//		$tab_typ_champ[$i]['CONFIG']['ROWS']=7;
//		$tab_typ_champ[$i]['CONFIG']['COLS']=40;
//		$tab_typ_champ[$i]['CONFIG']['SIZE']=50;
//		$tab_typ_champ[$i]['CONFIG']['MAXLENGTH']=255;
//		$i++;
//	}
	return $tab_typ_champ;
}

function filtre($tab_field,$form_name,$query){
	global $protectedPost,$l;
	if ($protectedPost['RAZ_FILTRE'] == "RAZ")
	unset($protectedPost['FILTRE_VALUE'],$protectedPost['FILTRE']);
	if ($protectedPost['FILTRE_VALUE'] and $protectedPost['FILTRE']){
		$temp_query=explode("GROUP BY",$query);
		if ($temp_query[0] == $query)
		$temp_query=explode("group by",$query);
		
		if (substr_count(strtoupper ($temp_query[0]), "WHERE")>0){
			$t_query=explode("WHERE",$temp_query[0]);
			if ($t_query[0] == $temp_query[0])
			$t_query=explode("where",$temp_query[0]);
			$temp_query[0]= $t_query[0]." WHERE (".$t_query[1].") and ";
		
		}else
		$temp_query[0].= " where ";
	$query=$temp_query[0].$protectedPost['FILTRE']." like '%".$protectedPost['FILTRE_VALUE']."%' ";
	if (isset($temp_query[1]))
	$query.="GROUP BY ".$temp_query[1];
	}
	$view=show_modif($tab_field,'FILTRE',2);
	$view.=show_modif(stripslashes($protectedPost['FILTRE_VALUE']),'FILTRE_VALUE',0);
	echo $l->g(883).": ".$view."<input type='submit' value='".$l->g(1109)."' name='SUB_FILTRE'><a href=# onclick='return pag(\"RAZ\",\"RAZ_FILTRE\",\"".$form_name."\");'><img src=image/supp.png></a></td></tr><tr><td align=center>";
	echo "<input type=hidden name='RAZ_FILTRE' id='RAZ_FILTRE' value=''>";
	return $query;
}





function tab_list_error($data,$title)
{
	global $l;

	echo "<br>";
		echo "<table align='center' width='50%' border='0'  bgcolor='#C7D9F5' style='border: solid thin; border-color:#A1B1F9'>";
		echo "<tr><td colspan=20 align='center'><font color='RED'>".$title."</font></td></tr><tr>";	
		$i=0;
		$j=0;
		while ($data[$i])
		{
			if ($j == 10)
			{
				echo "</tr><tr>";
				$j=0;	
			}
			echo "<td align='center'>".$data[$i]."<td>";
			$i++;
			$j++;
		}
		echo "</td></tr></table>";
	
}

function nb_page($form_name,$taille_cadre='80',$bgcolor='#C7D9F5',$bordercolor='#9894B5'){
	global $protectedPost,$l;

	if ($protectedPost['old_pcparpage'] != $protectedPost['pcparpage'])
	$protectedPost['page']=0;
	if (!(isset($protectedPost["pcparpage"])) or $protectedPost["pcparpage"] == "")
	 $protectedPost["pcparpage"]=20;
	echo "<table align=center width='80%' border='0' bgcolor=#f2f2f2>";
	//gestion d"une phrase d'alerte quand on utilise le filtre
	if (isset($protectedPost['FILTRE_VALUE']) and $protectedPost['FILTRE_VALUE'] != '' and $protectedPost['RAZ_FILTRE'] != 'RAZ')
		echo "<tr><td align=center><b><font color=red>".$l->g(884)."</font></b></td></tr>";
	echo "<tr><td align=right>";
	if (!isset($protectedPost['SHOW']))
	$protectedPost['SHOW'] = "SHOW";
	if ($protectedPost['SHOW'] == 'SHOW')
	echo "<a href=# OnClick='pag(\"NOSHOW\",\"SHOW\",\"".$form_name."\");'><img src=image/no_show.png></a>";
	else
	echo "<a href=# OnClick='pag(\"SHOW\",\"SHOW\",\"".$form_name."\");'><img src=image/show.png></a>";
	echo "</td></tr></table>";
	echo "<table align=center width='80%' border='0' bgcolor=#f2f2f2";
	if($protectedPost['SHOW'] == 'NOSHOW')
	echo " style='display:none;'";
	echo "><tr><td align=center>";
	echo "<table cellspacing='5' width='".$taille_cadre."%' BORDER='0' ALIGN = 'Center' CELLPADDING='0' BGCOLOR='".$bgcolor."' BORDERCOLOR='".$bordercolor."'><tr><td align=center>";

	    $machNmb = array(5=>5,10=>10,15=>15,20=>20,50=>50,100=>100,200=>200);
      $pcParPageHtml= $l->g(340).": ".show_modif($machNmb,'pcparpage',2,$form_name,array('DEFAULT'=>'NO'));
	$pcParPageHtml .=  "</td></tr></table>
	</td></tr><tr><td align=center>";
	echo $pcParPageHtml;


	if (isset($protectedPost["pcparpage"])){
		$deb_limit=$protectedPost['page']*$protectedPost["pcparpage"];
		$fin_limit=$deb_limit+$protectedPost["pcparpage"]-1;		
	}

	echo "<input type='hidden' id='SHOW' name='SHOW' value='".$protectedPost['SHOW']."'>";

	return (array("BEGIN"=>$deb_limit,"END"=>$fin_limit));
}

function show_page($valCount,$form_name){
	global $protectedPost;
	if (isset($protectedPost["pcparpage"]) and $protectedPost["pcparpage"] != 0)
	$nbpage= ceil($valCount/$protectedPost["pcparpage"]);
	if ($nbpage >1){
	$up=$protectedPost['page']+1;
	$down=$protectedPost['page']-1;
	echo "<table align='center' width='99%' border='0' bgcolor=#f2f2f2>";
	echo "<tr><td align=center>";
	if ($protectedPost['page'] > 0)
	echo "<img src='image/prec24.png' OnClick='pag(\"".$down."\",\"page\",\"".$form_name."\")'>";
	//if ($nbpage<10){
		$i=0;
		$deja="";
		while ($i<$nbpage){			
			$point="";
			if ($protectedPost['page'] == $i){
				if ($i<$nbpage-10 and  $i>10  and $deja==""){
				$point=" ... ";
				$deja="ok";	
				}
				if($i<$nbpage-10 and  $i>10){
					$point2=" ... ";
				}
				echo $point."<font color=red>".$i."</font> ".$point2;
			}
			elseif($i>$nbpage-10 or $i<10)
			echo "<a OnClick='pag(\"".$i."\",\"page\",\"".$form_name."\")'>".$i."</a> ";
			elseif ($i<$nbpage-10 and  $i>10 and $deja==""){
				echo " ... ";
				$deja="ok";	
			}
			$i++;
		}

	if ($protectedPost['page']< $nbpage-1)
	echo "<img src='image/proch24.png' OnClick='pag(\"".$up."\",\"page\",\"".$form_name."\")'>";
	
	}
	echo "</td></tr></table>";
	echo "<input type='hidden' id='page' name='page' value='".$protectedPost['page']."'>";
	echo "<input type='hidden' id='old_pcparpage' name='old_pcparpage' value='".$protectedPost['pcparpage']."'>";
}


function onglet($def_onglets,$form_name,$post_name,$ligne)
{
	global $protectedPost;
/*	$protectedPost['onglet_soft']=stripslashes($protectedPost['onglet_soft']);
	$protectedPost['old_onglet_soft']=stripslashes($protectedPost['old_onglet_soft']);*/
	if ($protectedPost["old_".$post_name] != $protectedPost[$post_name]){
	$protectedPost['page']=0;
	}
	/*This fnction use code of Douglas Bowman (Sliding Doors of CSS)
	http://www.alistapart.com/articles/slidingdoors/
	THANKS!!!!
		$def_onglets is array like :  	$def_onglets[$l->g(499)]=$l->g(499); //Serveur
										$def_onglets[$l->g(728)]=$l->g(728); //Inventaire
										$def_onglets[$l->g(312)]=$l->g(312); //IP Discover
										$def_onglets[$l->g(512)]=$l->g(512); //T�l�d�ploiement
										$def_onglets[$l->g(628)]=$l->g(628); //Serveur de redistribution 
		
	behing this function put this lign:
	echo "<form name='modif_onglet' id='modif_onglet' method='POST' action='index.php?multi=4'>";
	
	At the end of your page, close this form
	$post_name is the name of var will be post
	$ligne is if u want have onglet on more ligne*/
	if ($def_onglets != ""){
	echo "<LINK REL='StyleSheet' TYPE='text/css' HREF='css/onglets.css'>\n";
	echo "<table cellspacing='0' BORDER='0' ALIGN = 'Center' CELLPADDING='0'><tr><td><div id='header'>";
	echo "<ul>";
	$current="";
	$i=0;
	  foreach($def_onglets as $key=>$value){
	  	
	  	if ($i == $ligne){
	  		echo "</ul><ul>";
	  		$i=0;
	  		
	  	}
	  	echo "<li ";
	  	if (is_numeric($protectedPost[$post_name])){
			if ($protectedPost[$post_name] == $key or (!isset($protectedPost[$post_name]) and $current != 1)){
			 echo "id='current'";  
	 		 $current=1;
			}
	  	}else{
	  		//echo "<script>alert('".mysql_escape_string(stripslashes($protectedPost[$post_name]))." => ".$key."')</script>";
			if (mysql_escape_string(stripslashes($protectedPost[$post_name])) === mysql_escape_string(stripslashes($key)) or (!isset($protectedPost[$post_name]) and $current != 1)){
				 echo "id='current'";  
	 			 $current=1;
			}
		}
	
	  	echo "><a OnClick='pag(\"".htmlspecialchars($key, ENT_QUOTES)."\",\"".$post_name."\",\"".$form_name."\")'>".htmlspecialchars($value, ENT_QUOTES)."</a></li>";
	  $i++;	
	  }	
	echo "</ul>
	</div></td></tr></table>";
	echo "<input type='hidden' id='".$post_name."' name='".$post_name."' value='".$protectedPost[$post_name]."'>";
	echo "<input type='hidden' id='old_".$post_name."' name='old_".$post_name."' value='".$protectedPost[$post_name]."'>";
	}
	
}


function gestion_col($entete,$data,$list_col_cant_del,$form_name,$tab_name,$list_fields,$default_fields,$id_form='form'){
	global $protectedPost,$l;
	//r�cup�ration des colonnes du tableau dans le cookie
	if (isset($_COOKIE[$tab_name]) and !isset($_SESSION['OCS']['col_tab'][$tab_name])){
		$col_tab=explode("///", $_COOKIE[$tab_name]);
		foreach ($col_tab as $key=>$value){
				$_SESSION['OCS']['col_tab'][$tab_name][$key]=$value;
		}			
	}
	if (isset($protectedPost['SUP_COL']) and $protectedPost['SUP_COL'] != ""){
		unset($_SESSION['OCS']['col_tab'][$tab_name][$protectedPost['SUP_COL']]);
	}
	if ($protectedPost['restCol'.$tab_name]){
		$_SESSION['OCS']['col_tab'][$tab_name][$protectedPost['restCol'.$tab_name]]=$protectedPost['restCol'.$tab_name];
	}
	if ($protectedPost['RAZ'] != ""){
		unset($_SESSION['OCS']['col_tab'][$tab_name]);
		$_SESSION['OCS']['col_tab'][$tab_name]=$default_fields;
	}
	if (!isset($_SESSION['OCS']['col_tab'][$tab_name]))
	$_SESSION['OCS']['col_tab'][$tab_name]=$default_fields;
	
	//v�rification de l'existance des champs cant_delete dans la session
	//print_r($list_col_cant_del);
	foreach ($list_col_cant_del as $key=>$value){
		if (!in_array($key,$_SESSION['OCS']['col_tab'][$tab_name])){
			$_SESSION['OCS']['col_tab'][$tab_name][$key]=$key;
		}
	}
	foreach ($entete as $k=>$v){
		if (in_array($k,$_SESSION['OCS']['col_tab'][$tab_name])){
			$data_with_filter['entete'][$k]=$v;	
			if (!isset($list_col_cant_del[$k]))
			$data_with_filter['entete'][$k].="<a href=# onclick='return pag(\"".$k."\",\"SUP_COL\",\"".$id_form."\");'><img src=image/supp.png></a>";
		}	
		else
		$list_rest[$k]=$v;

		
	}

	foreach ($data as $k=>$v){
		foreach ($v as $k2=>$v2){
			if (in_array($k2,$_SESSION['OCS']['col_tab'][$tab_name])){
				$data_with_filter['data'][$k][$k2]=$v2;
			}
		}

	}
	if (is_array ($list_rest)){
		$select_restCol= $l->g(349).": ".show_modif($list_rest,'restCol'.$tab_name,2,$form_name);
		$select_restCol .=  "<a href=# OnClick='pag(\"".$tab_name."\",\"RAZ\",\"".$id_form."\");'><img src=image/supp.png></a></td></tr></table>"; //</td></tr><tr><td align=center>
		echo $select_restCol;
	}else
		echo "</td></tr></table>";
	echo "<input type='hidden' id='SUP_COL' name='SUP_COL' value=''>";
	echo "<input type='hidden' id='TABLE_NAME' name='TABLE_NAME' value='".$tab_name."'>";
	echo "<input type='hidden' id='RAZ' name='RAZ' value=''>";
	return( $data_with_filter);
	
	
}

function tab_req($table_name,$list_fields,$default_fields,$list_col_cant_del,$queryDetails,$form_name,$width='100',$tab_options='')
{
	global $protectedPost,$l,$pages_refs;
	if (!$tab_options['AS'])
	$tab_options['AS']=array();
	echo "<script language='javascript'>
		function checkall()
		 {
			for(i=0; i<document.".$form_name.".elements.length; i++)
			{
				if(document.".$form_name.".elements[i].name.substring(0,5) == 'check'){
			        if (document.".$form_name.".elements[i].checked)
						document.".$form_name.".elements[i].checked = false;
					else
						document.".$form_name.".elements[i].checked = true;
				}
			}
		}
	</script>";

	$link=$_SESSION['OCS']["readServer"];	
	
	//show select nb page
	$limit=nb_page($form_name,100,"","");
	
	//you want to filter your result
	if (isset($tab_options['FILTRE']))
	$queryDetails=filtre($tab_options['FILTRE'],$form_name,$queryDetails);
	
	//by default, sort by column 1
	if ($protectedPost['tri2'] == "" or (!in_array ($protectedPost['tri2'], $list_fields) and !in_array ($protectedPost['tri2'], $tab_options['AS'])))
	$protectedPost['tri2']=1;

	//by default, sort ASC
	if ($protectedPost['sens'] == "")
	$protectedPost['sens']='ASC';
	
	//if data is signed
	if ($tab_options['TRI']['SIGNED'][$protectedPost['tri2']])
		$queryDetails.= " order by cast(".$protectedPost['tri2']." as signed) ".$protectedPost['sens'];
	else
		$queryDetails.= " order by ".$protectedPost['tri2']." ".$protectedPost['sens'];
	
	$limit_result_cache=200;
	//$tab_options['CACHE']='RESET';
	//suppression de la limite de cache
	//si on est sur la m�me page mais pas sur le m�me onglet
	if ($_SESSION['OCS']['csv'][$table_name] != $queryDetails ){
		unset($protectedPost['page']);
		$tab_options['CACHE']='RESET';
	}

	
	//Delete cache 
	if ($tab_options['CACHE']=='RESET' or (isset($protectedPost['SUP_PROF']) and $protectedPost['SUP_PROF'] != '') ){
		if ($_SESSION['OCS']['DEBUG'] == 'ON')
	 		echo "<br><b><font color=red>".$l->g(5003)."</font></b><br>";
		unset($_SESSION['OCS']['DATA_CACHE'][$table_name]);
		unset($_SESSION['OCS']['NUM_ROW'][$table_name]);	
	}

	if (isset($_SESSION['OCS']['NUM_ROW'][$table_name])
			and $_SESSION['OCS']['NUM_ROW'][$table_name]>$limit["BEGIN"] 
			and $_SESSION['OCS']['NUM_ROW'][$table_name]<=$limit["END"]
			and !isset($_SESSION['OCS']['DATA_CACHE'][$table_name][$limit["END"]])){
				
		if ($_SESSION['OCS']['DEBUG'] == 'ON')
			 echo "<br><b><font color=red>".$l->g(5004)." ".$limit["END"]." => ".($_SESSION['OCS']['NUM_ROW'][$table_name]-1)." </font></b><br>";
		$limit["END"]=$_SESSION['OCS']['NUM_ROW'][$table_name]-1;

	}
		
		
		
		
	if (isset($_SESSION['OCS']['DATA_CACHE'][$table_name][$limit["END"]]) and isset($_SESSION['OCS']['NUM_ROW'][$table_name])){
		//echo "toto";
		if ($_SESSION['OCS']['DEBUG'] == 'ON')
	 		echo "<br><b><font color=red>".$l->g(5005)."</font></b><br>";
	 		$var_limit=$limit["BEGIN"];
	 		while ($var_limit<=$limit["END"]){
	 			$sql_data[$var_limit]=$_SESSION['OCS']['DATA_CACHE'][$table_name][$var_limit];
	 			$var_limit++;
	 		}
			$num_rows_result=$_SESSION['OCS']['NUM_ROW'][$table_name];
			$result_data=gestion_donnees($sql_data,$list_fields,$tab_options,$form_name,$default_fields,$list_col_cant_del,$queryDetails);
			$data=$result_data['DATA'];
			
			$entete=$result_data['ENTETE'];
			$correct_list_col_cant_del=$result_data['correct_list_col_cant_del'];
			$correct_list_fields=$result_data['correct_list_fields'];
		$i=1;		
	}else
	{
		//echo $table_name;
		//print_r($_SESSION['OCS']['SQL_DATA_FIXE'][$table_name]);
		//recherche des valeurs fixe avec la requete sql stock�e
		if (isset($_SESSION['OCS']['SQL_DATA_FIXE'][$table_name])){
			foreach ($_SESSION['OCS']['SQL_DATA_FIXE'][$table_name] as $key=>$sql){
				if ($table_name == "TAB_MULTICRITERE"){
				$sql.=" and hardware_id in (".implode(',',$_SESSION['OCS']['ID_REQ']).")";
				//ajout du group by pour r�gler le probl�me des r�sultats multiples sur une requete
				//on affiche juste le premier crit�re qui match
				$sql.=" group by hardware_id ";
				}
				
			
				
				//ajout du tri sur la requete de valeurs fixe si cela a �t� demand�
				if ($protectedPost['tri_fixe']!='' and strstr($sql,$protectedPost['tri_fixe']))
				$sql.=" order by ".$protectedPost['tri_fixe']." ".$protectedPost['sens'];
			//	$sql.=" limit 200";
				$result = mysql_query($sql, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
			//	echo "<b>".$sql."</b><br><br><br>";
			while($item = mysql_fetch_object($result)){
				
					if ($item->HARDWARE_ID != "")
					$champs_index=$item->HARDWARE_ID;
					elseif($item->FILEID != "")
					$champs_index=$item->FILEID;
		//echo $champs_index."<br>";
					if (isset($tablename_fixe_value)){
//						echo "<br>";
//						echo $champs_index;
						if (strstr($sql,$tablename_fixe_value[0]))
							$list_id_tri_fixe[]=$champs_index;
					}
					foreach ($item as $field=>$value){
						
							if ($field != "HARDWARE_ID" and $field != "FILEID" and $field != "ID"){
					//			echo "<br>champs => ".$field."   valeur => ".$value;
							$tab_options['VALUE'][$field][$champs_index]=$value;
							}
					}
				}
			}
		}
		
//	print_r($tab_options['VALUE']);
	//	print_r($list_id_tri_fixe);
		//on vide les valeurs pr�c�dentes
		//pour optimiser la place sur le serveur
		unset($_SESSION['OCS']['csv'],$_SESSION['OCS']['list_fields']);		
		$_SESSION['OCS']['csv'][$table_name]=$queryDetails;
		
		//requete de count
		if (!isset($_SESSION['OCS']['NUM_ROW'][$table_name])){
			unset($_SESSION['OCS']['NUM_ROW']);
				$querycount_begin="select count(*) count_nb_ligne ";
				if (stristr($queryDetails,"group by") and substr_count($queryDetails,"group by") == 1){
					$querycount_end=",".substr(	$queryDetails,6);	
				}else
					$querycount_end=stristr($queryDetails, 'from ');	
			
				$querycount=$querycount_begin.$querycount_end;
				if (isset($tab_options['ARG_SQL_COUNT'])){
						$resultcount = mysql2_query_secure($querycount, $link,$tab_options['ARG_SQL_COUNT']);
				}
				elseif (isset($tab_options['ARG_SQL']))
					$resultcount = mysql2_query_secure($querycount, $link,$tab_options['ARG_SQL']);
				else
					$resultcount = mysql2_query_secure($querycount, $link);
				//En dernier recourt, si le count n'est pas bon,
				//on joue la requete initiale
				if (!$resultcount){
					if (isset($tab_options['ARG_SQL']))
						$resultcount = mysql2_query_secure($queryDetails, $link,$tab_options['ARG_SQL']);
					else
						$resultcount = mysql2_query_secure($queryDetails, $link);
					
				}
				$num_rows_result = mysql_num_rows($resultcount);
				if ($num_rows_result==1){
					$count=mysql_fetch_object($resultcount);
					$num_rows_result = $count->count_nb_ligne;
				}
				$_SESSION['OCS']['NUM_ROW'][$table_name]=$num_rows_result;
		}else{
			$num_rows_result=$_SESSION['OCS']['NUM_ROW'][$table_name];
			if ($_SESSION['OCS']['DEBUG'] == 'ON')
	 		echo "<br><b><font color=red>".$l->g(5007)."</font></b><br>";
		}
				//echo $querycount;
		//FIN REQUETE COUNT
		if (isset($limit)){
			//print_r($limit);
			if ($limit["END"]<$limit_result_cache)
			$queryDetails.=" limit ".$limit_result_cache;
			else{
			//	echo "<font color=red>".floor($limit["END"]/$limit_result_cache)."</font><br>";
			$queryDetails.=" limit ".floor($limit["END"]/$limit_result_cache)*$limit_result_cache.",".$limit_result_cache;
			}
//			//if ($limit["END"] != 0)
//			$queryDetails.=$limit["END"];
//			if ($limit["BEGIN"] != 0)
//			$queryDetails.=",".$limit["BEGIN"];
		}
		//$queryDetails="select SQL_CALC_FOUND_ROWS ".substr($queryDetails,6);
		if (isset($tab_options['ARG_SQL']))
			$resultDetails = mysql2_query_secure($queryDetails, $link,$tab_options['ARG_SQL']);
		else
			$resultDetails = mysql2_query_secure($queryDetails, $link);
		flush();
	//echo "<br>".$queryDetails;
//	flush();

		$i=floor($limit["END"]/$limit_result_cache)*$limit_result_cache;
		$index=$limit["BEGIN"];
		$value_data_begin=$limit["BEGIN"];
		$value_data_end=$limit["END"]+1;
		//echo $num_rows_result;
		if ($index>$num_rows_result){
			$value_data_end=$num_rows_result-1;
		}
		//echo $queryDetails;
		while($item = mysql_fetch_object($resultDetails)){
			//echo "<br>INDEX=".$index;
			if ($i>=$index){
				//print_r($item);
				unset($champs_index);
				if ($item->ID != "")
				$champs_index=$item->ID;
				elseif($item->FILEID != "")
				$champs_index=$item->FILEID;
	
				if (isset($list_id_tri_fixe)){
					$index=$champs_index;	
				}
				
				if ($index>$num_rows_result){
					break;
				}
				
						//on arr�te le traitement si on est au dessus du nombre de ligne
				
				
				
				foreach($item as $key => $value){
					$sql_data_cache[$index][$key]=$value;					
					if ($index<$value_data_end and $index>=$value_data_begin){
						flush();
						$sql_data[$index][$key]=$value;							
						foreach ($list_fields as $key=>$value){
							if ($tab_options['VALUE'][$key]){
								//echo "toto";
								if ($tab_options['VALUE'][$key][$champs_index] == "" and isset($tab_options['VALUE_DEFAULT'][$key]))
								$sql_data[$index][$value]=$tab_options['VALUE_DEFAULT'][$key];
								else
								$sql_data[$index][$value]=$tab_options['VALUE'][$key][$champs_index];
							}
							//echo $sql_data[$index][$value]."<br>";
						}
					//	print_r($sql_data);
					}			
					//ajout des valeurs statiques
						foreach ($list_fields as $key=>$value){
							if ($tab_options['VALUE'][$key]){
							//	echo "toto2";
								if ($tab_options['VALUE'][$key][$champs_index] == "" and isset($tab_options['VALUE_DEFAULT'][$key]))
								$sql_data_cache[$index][$value]=$tab_options['VALUE_DEFAULT'][$key];
								else
								$sql_data_cache[$index][$value]=$tab_options['VALUE'][$key][$champs_index];
							}
						
						}
				}		
				$index++;
			}
			$i++;
		}
//		if ($i == 1){
//			$num_rows_result=1;
//			$_SESSION['OCS']['NUM_ROW'][$table_name]=1;
//		}
		flush();
			//traitement du tri des r�sultats sur une valeur fixe
		if (isset($list_id_tri_fixe)){
				$i=0;
			//parcourt des id tri�s
			while ($list_id_tri_fixe[$i]){
				if ($limit["BEGIN"] <= $i and $i <$limit["BEGIN"]+$limit_result_cache)
				$sql_data_tri_fixe[$i]=$sql_data[$list_id_tri_fixe[$i]];
				
				$i++;	
			}
			unset($sql_data);
			$sql_data=$sql_data_tri_fixe;
			
		}
	//	print_r($sql_data_cache);
		//on vide le cache des autres tableaux
		//pour optimiser la place dispo sur le serveur
		unset($_SESSION['OCS']['DATA_CACHE']);
		$_SESSION['OCS']['DATA_CACHE'][$table_name]=$sql_data_cache;
		
		//print_r($sql_data);
		$result_data=gestion_donnees($sql_data,$list_fields,$tab_options,$form_name,$default_fields,$list_col_cant_del,$queryDetails);
		$data=$result_data['DATA'];
	//	print_r($data);
		$entete=$result_data['ENTETE'];
		$correct_list_col_cant_del=$result_data['correct_list_col_cant_del'];
		$correct_list_fields=$result_data['correct_list_fields'];
		//print_r($result_data);
	}

	if ($num_rows_result > 0){
		//print_r($limit);
//		foreach ($data as $i=>$value){
//			if ($i>=$limit["BEGIN"] and $i<=$limit["END"])
//			$data_limit[]=$value;
//		}
		//print_r($data_limit);
//		unset($data);
//		$data=$data_limit;
		$title=$num_rows_result." ".$l->g(90);
		if (isset($tab_options['LOGS']))
		addLog($tab_options['LOGS'],$num_rows_result." ".$l->g(90));
		$title.= "<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_csv']."&no_header=1&tablename=".$table_name."&base=".$tab_options['BASE']."'><small> (".$l->g(183).")</small></a>";
		//print_r($correct_list_col_cant_del);
		$result_with_col=gestion_col($entete,$data,$correct_list_col_cant_del,$form_name,$table_name,$list_fields,$correct_list_fields,$form_name);
	//	print_r($result_with_col['data']);

	//echo "<br>tri=".$protectedPost['tri2'];
		tab_entete_fixe($result_with_col['entete'],$result_with_col['data'],$title,$width,"",array(),$tab_options);
		show_page($num_rows_result,$form_name);
		echo "<input type='hidden' id='tri2' name='tri2' value='".$protectedPost['tri2']."'>";
		echo "<input type='hidden' id='tri_fixe' name='tri_fixe' value='".$protectedPost['tri_fixe']."'>";
		echo "<input type='hidden' id='sens' name='sens' value='".$protectedPost['sens']."'>";
		echo "<input type='hidden' id='SUP_PROF' name='SUP_PROF' value=''>";
		echo "<input type='hidden' id='MODIF' name='MODIF' value=''>";
		echo "<input type='hidden' id='SELECT' name='SELECT' value=''>";
		echo "<input type='hidden' id='OTHER' name='OTHER' value=''>";
		echo "<input type='hidden' id='ACTIVE' name='ACTIVE' value=''>";
		echo "<input type='hidden' id='CONFIRM_CHECK' name='CONFIRM_CHECK' value=''>";
		echo "<input type='hidden' id='OTHER_BIS' name='OTHER_BIS' value=''>";
		return TRUE;
	}else{
	echo "</td></tr></table><font color=red size=5><B>".$l->g(766). " " . "</B></font>";
	return FALSE;
	}
}




//fonction qui permet de g�rer les donn�es � afficher dans le tableau
function gestion_donnees($sql_data,$list_fields,$tab_options,$form_name,$default_fields,$list_col_cant_del,$queryDetails){
	global $l,$protectedPost,$pages_refs;
	$_SESSION['OCS']['list_fields']=$list_fields;
	//requete de condition d'affichage
	//attention: la requete doit etre du style:
	//select champ1 AS FIRST from table where...
	if (isset($tab_options['REQUEST'])){
		foreach ($tab_options['REQUEST'] as $field_name => $value){
			$resultDetails = mysql_query($value, $_SESSION['OCS']["readServer"]) or mysql_error($_SESSION['OCS']["readServer"]);
			while($item = mysql_fetch_object($resultDetails)){
				$tab_condition[$field_name][$item -> FIRST]=$item -> FIRST;
			}		
		}
	}
	//echo "toto";
	//print_r($list_col_cant_del);//print_r($sql_data);
	if (isset($sql_data)){
		foreach ($sql_data as $i=>$donnees){
			//print_r($donnees);
			foreach($list_fields as $key=>$value){
				$truelabel=$key;
				//gestion des as de colonne
				if ($tab_options['AS'][$value])
				$value=$tab_options['AS'][$value];
				//echo $value."<br>";				
				$num_col=$key;
				if ($default_fields[$key])
				$correct_list_fields[$num_col]=$num_col;
				if ($list_col_cant_del[$key])
				$correct_list_col_cant_del[$num_col]=$num_col;
				
//				if (strstr($value, '.')){
//					echo "<br>".$value;
				if (substr($value,0,2) == "h." 
						or substr($value,0,2) == "a." 
						or substr($value,0,2) == "e."
						or substr($value,0,2) == "n." 
						or substr($value,0,2) == "b."){
				$no_alias_value=substr(strstr($value, '.'), 1);
				}else
				 $no_alias_value=$value;
			//	echo $no_alias_value."<br>";
				//echo $donnees[$no_alias_value]."<br>";
				
				//si aucune valeur, on affiche un espace
				if ($donnees[$no_alias_value] == "")
				$value_of_field = "&nbsp";
				else //sinon, on affiche la valeur
				{
					if (get_magic_quotes_gpc()==true){
					$value_of_field=stripslashes($donnees[$no_alias_value]);
					}else
					$value_of_field=$donnees[$no_alias_value];
				}
				//$value_of_field=utf8_encode($value_of_field);
				$col[$i]=$key;
				if ($protectedPost['sens'] == "ASC")
					$sens="DESC";
				else
					$sens="ASC";
					
				$affich='OK';
				//on n'affiche pas de lien sur les colonnes non pr�sentes dans la requete
				if (isset($tab_options['NO_TRI'][$key]))					
				$lien='KO';	
				else
				$lien='OK';
				if (isset($tab_options['REPLACE_VALUE'][$key])){
					$value_of_field=$tab_options['REPLACE_VALUE'][$key][$value_of_field];
				
				}
				unset($key2);
				if (isset($tab_condition[$key])){
						if ((!$tab_condition[$key][$donnees[$tab_options['FIELD'][$key]]] and !$tab_options['EXIST'][$key])
							or ($tab_condition[$key][$donnees[$tab_options['FIELD'][$key]]] and $tab_options['EXIST'][$key])){
							if ($key == "STAT" or $key == "SUP" or $key == "CHECK"){
								$key2 = "NULL";
							}else{
								$data[$i][$num_col]=$value_of_field;
								$affich="KO";
							}
						}
				}
				//if (!isset($entete[$num_col])){
					if (!isset($tab_options['LBL'][$key])){
					$entete[$num_col]=$key;
					}else
					$entete[$num_col]=$tab_options['LBL'][$key];
				//}
				//si un lien doit �tre mis sur le champ
				//l'option $tab_options['NO_LIEN_CHAMP'] emp�che de mettre un lien sur certaines
				//valeurs du champs
				//exemple, si vous ne voulez pas mettre un lien si le champ est 0,
				//$tab_options['NO_LIEN_CHAMP'][$key] = array(0);
				if (isset($tab_options['LIEN_LBL'][$key]) 
					and (!isset($tab_options['NO_LIEN_CHAMP'][$key]) or !in_array($value_of_field,$tab_options['NO_LIEN_CHAMP'][$key]))){
				$affich="KO";
					if (!isset($tab_options['LIEN_TYPE'][$key]))
					$data[$i][$num_col]="<a href='".$tab_options['LIEN_LBL'][$key].$donnees[$tab_options['LIEN_CHAMP'][$key]]."' target='_blank'>".$value_of_field."</a>";
					else{
						if (!isset($tab_options['POPUP_SIZE'][$key]))
						$size="width=550,height=350";
						else
						$size=$tab_options['POPUP_SIZE'][$key];
						$data[$i][$num_col]="<a href=# onclick=window.open(\"".$tab_options['LIEN_LBL'][$key].$donnees[$tab_options['LIEN_CHAMP'][$key]]."\",\"".$key."\",\"location=0,status=0,scrollbars=1,menubar=0,resizable=0,".$size."\")>".$value_of_field."</a>";
					
					}
				}	

				
				if (isset($tab_options['JAVA']['CHECK'])){
						$javascript="OnClick='confirme(\"".str_replace("'", "", $donnees[$tab_options['JAVA']['CHECK']['NAME']])."\",".$value_of_field.",\"".$form_name."\",\"CONFIRM_CHECK\",\"".$tab_options['JAVA']['CHECK']['QUESTION']." \")'";
				}else
						$javascript="";
				
				//si on a demander un affichage que sur certaine ID
				if (is_array($tab_options) and !$tab_options['SHOW_ONLY'][$key][$value_of_field] and $tab_options['SHOW_ONLY'][$key]){
					$key = "NULL";
				}		
				if ($affich == 'OK'){
				//	echo $key."<br>";
					if ($key == "NULL"){
						$data[$i][$num_col]="&nbsp";
						//if (!isset($entete[$num_col]))
						$entete[$num_col]=$truelabel;
						$lien = 'KO';
					}elseif ($key == "GROUP_NAME"){
						$data[$i][$num_col]="<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_group_show']."&popup=1&systemid=".$donnees['ID']."' target='_blank'>".$value_of_field."</a>";
					}elseif ($key == "SUP"){
						if (isset($tab_options['LBL_POPUP'][$key]))
						$lbl_msg=$donnees[$tab_options['LBL_POPUP'][$key]];
						else
						$lbl_msg=$value_of_field;
						$data[$i][$num_col]="<a href=# OnClick='confirme(\"\",\"".$value_of_field."\",\"".$form_name."\",\"SUP_PROF\",\"".$l->g(640)." ".$lbl_msg."\");'><img src=image/supp.png></a>";
						$lien = 'KO';
						if (!$entete[$num_col] or $entete[$num_col] == $key)
						$entete[$num_col]=$l->g(122);
						if (isset($key2))
						$data[$i][$num_col]="&nbsp";
					}elseif ($key == "MODIF"){
						if (!isset($tab_options['MODIF']['IMG']))
						$image="image/modif_tab.png";
						else
						$image=$tab_options['MODIF']['IMG'];
						$data[$i][$num_col]="<a href=# OnClick='pag(\"".$value_of_field."\",\"MODIF\",\"".$form_name."\");'><img src=".$image."></a>";
						$lien = 'KO';
						if (!$entete[$num_col] or $entete[$num_col] == $key)
						$entete[$num_col]=$l->g(115);
						if (isset($key2))
						$data[$i][$num_col]="&nbsp";
					}elseif ($key == "SELECT"){
						$data[$i][$num_col]="<a href=# OnClick='confirme(\"\",\"".$value_of_field."\",\"".$form_name."\",\"SELECT\",\"".$tab_options['QUESTION']['SELECT']."\");'><img src=image/prec16.png></a>";
						$lien = 'KO';
					}elseif ($key == "OTHER"){
						$data[$i][$num_col]="<a href=# OnClick='pag(\"".$value_of_field."\",\"OTHER\",\"".$form_name."\");'><img src=image/red.png></a>";
						$lien = 'KO';
					}elseif ($key == "ZIP"){
						$data[$i][$num_col]="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_tele_compress']."&no_header=1&timestamp=".$value_of_field."&type=".$tab_options['TYPE']['ZIP']."\",\"compress\",\"\")><img src=image/archives.png></a>";
						$lien = 'KO';
					}elseif ($key == "STAT"){
						$data[$i][$num_col]="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_tele_stats']."&no_header=1&stat=".$value_of_field."\",\"stats\",\"\")><img src='image/stat.png'></a>";
						$lien = 'KO';
					}elseif ($key == "ACTIVE"){
						$data[$i][$num_col]="<a href=# OnClick='window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_tele_popup_active']."&head=1&active=".$value_of_field."\",\"active\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=350\")'><img src='image/activer.png' ></a>";
						$lien = 'KO';
					}elseif ($key == "SHOWACTIVE"){
						$data[$i][$num_col]="<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_tele_actives']."&head=1&timestamp=".$donnees['FILEID']."' target=_blank>".$value_of_field."</a>";
					}
					elseif ($key == "CHECK"){
						if (!$entete[$num_col] or $entete[$num_col] == $key)
						$entete[$num_col]=$l->g(1119) . "<input type='checkbox' name='ALL' id='ALL' Onclick='checkall();'>";
						$data[$i][$num_col]="<input type='checkbox' name='check".$value_of_field."' id='check".$value_of_field."' ".$javascript." ".(isset($protectedPost['check'.$value_of_field])? " checked ": "").">";
						$lien = 'KO';		
						if (isset($key2))
						$data[$i][$num_col]="&nbsp";					
					}elseif ($key == "NAME"){
							$data[$i][$num_col]="<a href='index.php?".PAG_INDEX."=".$pages_refs['ms_computer']."&head=1&systemid=".$donnees['ID']."'  target='_blank'>".$value_of_field."</a>";
							if (!$entete[$num_col] or $entete[$num_col] == $key)
							$entete[$num_col]=$l->g(23);
					}elseif ($key == "MAC"){
						if (isset($_SESSION['OCS']["mac"][substr($value_of_field,0,8)]))
						$constr=$_SESSION['OCS']["mac"][substr($value_of_field,0,8)];
						else
						$constr="<font color=red>".$l->g(885)."</font>";
						$data[$i][$num_col]=$value_of_field." (<small>".$constr."</small>)";						
					}elseif ($key == "PERCENT_BAR"){
						require_once("function_graphic.php");
						$data[$i][$num_col]="<CENTER>".percent_bar($value_of_field)."</CENTER>";
						//$lien = 'KO';						
					}
					else{						
						if ($tab_options['OTHER'][$key][$value_of_field]){
							$end="<a href=# OnClick='pag(\"".$value_of_field."\",\"OTHER\",\"".$form_name."\");'><img src=".$tab_options['OTHER']['IMG']."></a>";
						}elseif ($tab_options['OTHER_BIS'][$key][$value_of_field]){
							$end="<a href=# OnClick='pag(\"".$value_of_field."\",\"OTHER_BIS\",\"".$form_name."\");'><img src=".$tab_options['OTHER_BIS']['IMG']."></a>";
						}else{
							$end="";
						}
						
						$data[$i][$num_col]=$value_of_field.$end;
						
					}
					
				}
	
					if ($lien == 'OK'){
						$deb="<a onclick='return tri(\"".$value."\",\"".$sens."\",\"".$form_name."\");' >";
						$fin="</a>";
						$entete[$num_col]=$deb.$entete[$num_col].$fin;
						if ($protectedPost['tri2'] == $value){
							if ($protectedPost['sens'] == 'ASC')
								$img="<img src='image/down.png'>";
							else
								$img="<img src='image/up.png'>";
							$entete[$num_col]=$img.$entete[$num_col];
						}
					}

			}
			
			
		}
		if ($tab_options['UP']){
			$i=0;
			while($data[$i]){
				foreach ($tab_options['UP'] as $key=>$value){
					if ($data[$i][$key] == $value){
						$value_temp=$data[$i];				
						unset($data[$i]);
					}	
				}				
				$i++;	
			}
			array_unshift ($data, $value_temp);
		}
	return array('ENTETE'=>$entete,'DATA'=>$data,'correct_list_fields'=>$correct_list_fields,'correct_list_col_cant_del'=>$correct_list_col_cant_del);
	}else
	return "NO_DATA";
}
function del_selection($form_name){
	global $l;
echo "<script language=javascript>
			function garde_check(image,id)
			 {
				var idchecked = '';
				for(i=0; i<document.".$form_name.".elements.length; i++)
				{					
					if(document.".$form_name.".elements[i].name.substring(0,5) == 'check'){
				        if (document.".$form_name.".elements[i].checked)
							idchecked = idchecked + document.".$form_name.".elements[i].name.substring(5) + ',';
					}
				}
				idchecked = idchecked.substr(0,(idchecked.length -1));
				confirme('',idchecked,\"".$form_name."\",\"del_check\",\"".$l->g(900)."\");
			}
		</script>";
		echo "<table align='center' width='30%' border='0'>";
		echo "<tr><td>";
		//foreach ($img as $key=>$value){
			echo "<td align=center><a href=# onclick=garde_check(\"image/sup_search.png\",\"\")><img src='image/sup_search.png' title='".$l->g(162)."' ></a></td>";
		//}
	 echo "</tr></tr></table>";
	 echo "<input type='hidden' id='del_check' name='del_check' value=''>";
}
?>
