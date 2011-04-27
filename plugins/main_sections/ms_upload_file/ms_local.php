<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2005
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2010 Erwan Goalou
require_once('require/function_files.php');
$form_name="insert_computers";
$data_on['FILE']=$l->g(288);
$data_on['MANUEL']=$l->g(1258);
echo "<form name='".$form_name."' id='".$form_name."' method='POST' action=''>";
$protectedPost['onglet']='FILE';
//onglet($data_on,$form_name,"onglet",4);
echo "<div class='mlt_bordure' >";

if ($protectedPost['onglet'] == 'FILE'){
	echo "<script language='javascript'>  
	    
	    function getext(filename){
	    	 var parts = filename.split('.');
	   		return(parts[(parts.length-1)]);    
	    }
	    
	    function namefile(filename){
	     	var parts = filename.split('.');
	    	return(parts[0]);    
	    }    
	
	    function verif_file_format(champ){
	        var ExtList=new Array('ocs','OCS','xml','XML');
			filename = document.getElementById(champ).value.toLowerCase();
			fileExt = getext(filename);
			for (i=0; i<ExtList.length; i++)
			{
				if ( fileExt == ExtList[i] ) 
				{
					return (true);
				}
			}
			alert('".mysql_real_escape_string($l->g(559))."');
			return (false);
	     }
	          
	</script>";
	//   
	$css="mvt_bordure";
	$form_name1="SEND_FILE";
	$data_config=look_config_default_values(array('LOCAL_URI_SERVER'),'',
											array('TVALUE'=>array('LOCAL_URI_SERVER'=>'http://localhost:80/ocsinventory')));
											
	$server = $data_config['tvalue']['LOCAL_URI_SERVER'];
	$array_port=explode(':',$server);
	$port_trait=array_pop($array_port);
	$array_port=explode('/',$port_trait);
	$port=$array_port[0];
	if(is_uploaded_file($_FILES['file_upload']['tmp_name'])) {
		
			$fd = fopen($_FILES['file_upload']['tmp_name'], "r");
			if ($_FILES['file_upload']['size'] != 0){
				$contents = fread($fd, filesize ($_FILES['file_upload']['tmp_name']));
				fclose($fd);
		
				$result = post_ocs_file_to_server($contents, $server, $port);
				
				if (isset($result["errno"])) {
					$errno = $result["errno"];
					$errstr = $result["errstr"];
					msg_error($l->g(344). " ". $errno . " / " . $errstr);
				}else {
					if( ! strstr ( $result[0], "200") )
						msg_error($l->g(344). " " . $result[0]);
					else {
						msg_success($l->g(287)." OK");
					}
				}
			}else
				msg_error($l->g(1244));
	}
	printEntete("<i>".$l->g(560).": ".$server);
	echo "</form>";
	echo "<br>";
	echo "<form name='".$form_name1."' id='".$form_name1."' method='POST' action='' enctype='multipart/form-data' onsubmit=\"return verif_file_format('file_upload');\">";
	echo "<div class='".$css."' >";
	echo $l->g(1048).": <input id='file_upload' name='file_upload' type='file' accept=''>";
	echo "<br><br><input name='GO' id='GO' type='submit' value='".$l->g(13)."'>";
	echo "</form>";
	echo "<br>";
	echo "</div>";
}else{
	require_once('require/function_computers.php');
	require_once('require/function_admininfo.php');	
	//list fields for form
	$form_fields_typeinput=array('COMPUTER_NAME_GENERIC'=>$l->g(35),
					   'SERIAL_GENERIC'=>$l->g(36),
					   'ADDR_MAC_GENERIC'=>$l->g(95));
	
	
	if (isset($protectedPost['Valid_modif_x'])){
		$error='';
		if (!is_numeric($protectedPost['NB_COMPUTERS']))
			$error.=$l->g(28).',';
			
		foreach ($form_fields_typeinput as $key=>$value){
			if (trim($protectedPost[$key]) == '')
				$error.=$value.',';
			
		}
		
		if ($error == ""){
			$check_trait=array();
			foreach ($protectedPost as $key=>$value){
				if ($value != ''){
					if (substr($key,0,7) == 'fields_' or $key == 'TAG'){
						$temp_field=explode('_',$key);
						
						//checkbox cas
						if (isset($temp_field[2])){
							$check_trait[$temp_field[0].'_'.$temp_field[1]].=$temp_field[2]."&&&";
						}else{
							$fields[]=$key;
							$values_fields[]=$value;
						}
					}			
				}	
			}
			//cas of checkbox
			if ($check_trait != array()){				
				foreach ($check_trait as $key=>$value){
					$fields[]=$key;
					$values_fields[]=$value;					
				}							
			}
			/*if ($protectedPost['NB_COMPUTERS'] === 1)
				$protectedPost['NB_COMPUTERS']='';*/
			$i=0;
			while ($i < $protectedPost['NB_COMPUTERS']){
				$id_computer=insert_manual_computer($protectedPost,$protectedPost['NB_COMPUTERS']);
				if (is_array($fields))
			 		insertinfo_computer($id_computer,$fields,$values_fields);
				
				$i++;			
			}
			msg_success($l->g(881));
		}else
			msg_error($l->g(684)."<br>".$error);
	}
	
	
	$i=0;
	$info_form['FIELDS']['name_field'][$i]='NB_COMPUTERS';
	$info_form['FIELDS']['type_field'][$i]=0;
	$info_form['FIELDS']['value_field'][$i]=($protectedPost['NB_COMPUTERS'] != '' ? $protectedPost['NB_COMPUTERS']:'1');
	$info_form['FIELDS']['tab_name'][$i]=$l->g(28);
	$config[$i]['CONFIG']['SIZE']=4	;
	$config[$i]['CONFIG']['MAXLENGTH']=4	;
	$other_data['COMMENT_BEHING'][$i]='';
	$config[$i]['CONFIG']['JAVASCRIPT']=$chiffres;
	
	foreach ($form_fields_typeinput as $key=>$value){
		$i++;
		$info_form['FIELDS']['name_field'][$i]=$key;
		$info_form['FIELDS']['type_field'][$i]=0;
		$info_form['FIELDS']['value_field'][$i]=$protectedPost[$key];
		$info_form['FIELDS']['tab_name'][$i]=$value."*";
		$config[$i]['CONFIG']['SIZE']=30;
		$other_data['COMMENT_BEHING'][$i]='';		
	}
		
	$accountinfo_form=show_accountinfo('','COMPUTERS','5'); 
	
	//merge data
	$info_form['FIELDS']['name_field']=array_merge ($info_form['FIELDS']['name_field'],$accountinfo_form['FIELDS']['name_field']);
	$info_form['FIELDS']['type_field']=array_merge ($info_form['FIELDS']['type_field'],$accountinfo_form['FIELDS']['type_field']);
	$info_form['FIELDS']['value_field']=array_merge ($info_form['FIELDS']['value_field'],$accountinfo_form['FIELDS']['value_field']);
	$info_form['FIELDS']['tab_name']=array_merge ($info_form['FIELDS']['tab_name'],$accountinfo_form['FIELDS']['tab_name']);
	$config=array_merge ($config,$accountinfo_form['CONFIG']);
	$other_data['COMMENT_BEHING']=array_merge ($other_data['COMMENT_BEHING'],$accountinfo_form['COMMENT_BEHING']);

	
	$tab_typ_champ=show_field($info_form['FIELDS']['name_field'],$info_form['FIELDS']['type_field'],$info_form['FIELDS']['value_field']);
	foreach ($config as $key=>$value){
		$tab_typ_champ[$key]['CONFIG']=$value['CONFIG'];
		$tab_typ_champ[$key]['COMMENT_BEHING']=$other_data['COMMENT_BEHING'][$key];		
	}
	
	
	if (isset($tab_typ_champ)){
			tab_modif_values($info_form['FIELDS']['tab_name'],$tab_typ_champ,$tab_hidden);
	}	
	echo "</div>";
	echo "</form>";
}

?>
