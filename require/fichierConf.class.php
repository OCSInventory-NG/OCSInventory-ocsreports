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
	var 	$plug_language;		
	function language($language,$plugin='') // constructeur
	{
		if ($plugin != ''){
			require_once('require/function_files.php');
			$rep_list=ScanDirectory($_SESSION['OCS']['main_sections_dir'],'.');
			foreach ($rep_list as $key){
				if (file_exists ($_SESSION['OCS']['main_sections_dir'].$key.'/language/'.$language.".txt"))	{
					$file=fopen($_SESSION['OCS']['main_sections_dir'].$key.'/language/'.$language.".txt","r");
					while (!feof($file)) {
							$val = fgets($file, 1024);
							$tok1   =  rtrim(strtok($val," "));
							$tok2   =  rtrim(strtok(""));
							$this->plug_language[$tok1] = $tok2;
						}
					fclose($file);		
						echo $_SESSION['OCS']['main_sections_dir'].$key.'/language/'.$language.".txt<br>";
				}
					/*if ($file) {	
						while (!feof($file)) {
							$val = fgets($file, 1024);
							$tok1   =  rtrim(strtok($val," "));
							$tok2   =  rtrim(strtok(""));
							$this->plug_language[$tok1] = $tok2;
						}
						fclose($file);				
					} */
			}
			//echo $_SESSION['OCS']['main_sections_dir'].$key.'/language/'.$language.".txt";
			//p($rep_list);
		}

		if (!isset($_SESSION['OCS']['plugins_dir']) or $_SESSION['OCS']['plugins_dir'] == "")
		$_SESSION['OCS']['plugins_dir']="plugins/";
		$language_file=$_SESSION['OCS']['plugins_dir']."language/".$language."/".$language.".txt";
		if (file_exists ( $language_file) ){		
			$file=fopen($language_file,"r");		
			if ($file) {	
				while (!feof($file)) {
					$val = fgets($file, 1024);
					$tok1   =  rtrim(strtok($val," "));
					$tok2   =  rtrim(strtok(""));
					$this->tableauMots[$tok1] = $tok2;
				}
				fclose($file);	
			
			} 
		}

	}		
	function g($i)
	{
		global $tab_dont_see;
		//If word doesn't exist for language, return default english word 
		if ($this->tableauMots[$i] == NULL) {
			$defword = new language('english');
			$word= $defword->tableauMots[$i];
		}else
			$word=$this->tableauMots[$i]; 
		//language mode
		if ($_SESSION['OCS']['MODE_LANGUAGE']=="ON"){
			if (!in_array($i, $tab_dont_see))
			$_SESSION['OCS']['EDIT_LANGUAGE'][$i]=$word;
			$word.="{".$i."}";
		}
		return stripslashes($word);
	}
	
	
	function g_plug($i){
		if ($this->plug_language[$i] == NULL) {
			$defword = new language('english','plugin');
			$word= $defword->plug_language[$i];
		}else
			$word=$this->plug_language[$i]; 
		return stripslashes($word);		
	}

}		

?>
