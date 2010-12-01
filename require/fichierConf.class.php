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
//Modified on $Date: 2010 $$Author: Erwan Goalou
$tab_dont_see=array(527,528,529,530,531,532,533,534,535,536,537,538,539,540,541,542,543,544,545);
class language
{		
	var  	$tableauMots;    // tableau contenant tous les mots du fichier 			
	function language($language) // constructeur
	{
		//looking for if languages table exist
		$table_exist=false;
		$sql="SHOW TABLES";
		$result = @mysql_query($sql, $_SESSION['OCS']["readServer"]);
		while ($table = @mysql_fetch_object($result)){
			foreach ($table as $value){
				if ($value == "languages")
				$table_exist=true;
			}
			
		}
		//TEMPORAIRE=> PAS DE JSON
//		if ($table_exist){
//			$sql="select json_value from languages where name ='".$language."'";
//			$result = @mysql_query($sql, $_SESSION['OCS']["readServer"]);
//			$item = @mysql_fetch_object($result);
//		}
		if (!isset($_SESSION['OCS']['plugins_dir']) or $_SESSION['OCS']['plugins_dir'] == "")
		$_SESSION['OCS']['plugins_dir']="plugins/";
		$language_file=$_SESSION['OCS']['plugins_dir']."language/".$language."/".$language.".txt";
		if (file_exists ( $language_file) 
		and !isset($item->json_value)
		){		
			$file=fopen($language_file,"r");		
			if ($file) {	
				while (!feof($file)) {
					$val = fgets($file, 1024);
					$tok1   =  rtrim(strtok($val," "));
					$tok2   =  rtrim(strtok(""));
					$this->tableauMots[$tok1] = $tok2;
				}
				fclose($file);	
			/*	$toto=$this->tableauMots;
				if (!isset($item->json_value) and $table_exist){
					$sql="insert into languages (name,json_value) values ('".$language."','".mysql_real_escape_string(json_encode($toto))."')"; 
					@mysql_query( $sql, $_SESSION['OCS']["writeServer"] );
				}*/
			
			} 
		}
		/*else{
			$this->tableauMots=json_decode($item->json_value,true);
		}*/
	}		
	function g($i)
	{
		global $tab_dont_see;
		//If word doesn't exist for language, return default english word 
		if ($this->tableauMots[$i] == NULL) {
			$defword = new language(english);
			$word= $defword->tableauMots[$i];
		}else
			$word=$this->tableauMots[$i]; 
		//language mode
		if ($_SESSION['OCS']['MODE_LANGUAGE']=="ON"){
			if (!in_array($i, $tab_dont_see))
			$_SESSION['OCS']['EDIT_LANGUAGE'][$i]=$word;
			$word.="{<i><b>".$i."</b></i>}";		
		}
		return stripslashes($word);
	}

}		

?>
