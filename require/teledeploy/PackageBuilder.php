<?php
/*
 * Copyright 2005-2020 OCSInventory-NG/OCSInventory-ocsreports contributors.
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

 /**
  * Class for PackageBuilder
  */
class PackageBuilder
{
	private $downloadConfig = [];
	private $packageBuilderForm;
	private $packageBuilderParseXml;
	
	/**
	 * Method __construct
	 *
	 * @param $packageBuilderForm $packageBuilderForm [explicite description]
	 * @param $packageBuilderParseXml $packageBuilderParseXml [explicite description]
	 *
	 * @return void
	 */
	function __construct($packageBuilderForm, $packageBuilderParseXml) {
		$this->downloadConfig = look_config_default_values([
			'DOWNLOAD_PACK_DIR' => 'DOWNLOAD_PACK_DIR',
			'DOWNLOAD_ACTIVATE_FRAG' => 'DOWNLOAD_ACTIVATE_FRAG',
			'DOWNLOAD_RATIO_FRAG' => 'DOWNLOAD_RATIO_FRAG',
			'DOWNLOAD_AUTO_ACTIVATE' => 'DOWNLOAD_AUTO_ACTIVATE',
			'DOWNLOAD_URI_FRAG' => 'DOWNLOAD_URI_FRAG',
			'DOWNLOAD_URI_INFO' => 'DOWNLOAD_URI_INFO',
			'DOWNLOAD_PROTOCOL' => 'DOWNLOAD_PROTOCOL'
		]);

		if (empty($this->downloadConfig['tvalue']['DOWNLOAD_URI_FRAG'])) {
            $this->downloadConfig['tvalue']['DOWNLOAD_URI_FRAG'] = $_SERVER["SERVER_ADDR"]."/download";
        }
        if (empty($this->downloadConfig['tvalue']['DOWNLOAD_URI_INFO'])) {
            $this->downloadConfig['tvalue']['DOWNLOAD_URI_INFO'] = $_SERVER["SERVER_ADDR"]."/download";
        }
		if (empty($this->downloadConfig['tvalue']['DOWNLOAD_PROTOCOL'])) {
            $this->downloadConfig['tvalue']['DOWNLOAD_PROTOCOL'] = "HTTP";
        }

		$this->packageBuilderForm = $packageBuilderForm;
		$this->packageBuilderParseXml = $packageBuilderParseXml;
	}
	
	/**
	 * Method buildPackage
	 *
	 * @param $post $post [explicite description]
	 * @param $file $file [explicite description]
	 *
	 * @return void
	 */
	public function buildPackage($post, $file = null) {
		global $l;
		
		$timestamp = time();
		$packageInfos = [];
		$digest = null;
		$details = [
			'frag' => 0,
			'size' => 0
		];
		// Get Xml option info
		$xmlDetails = $this->packageBuilderParseXml->parseOptions($post['FORMTYPE']);

		if(isset($this->downloadConfig['tvalue']['DOWNLOAD_PACK_DIR'])) {
			$downloadPath = $this->downloadConfig['tvalue']['DOWNLOAD_PACK_DIR'].'/download/'.$timestamp;
		} else {
			$downloadPath = VARLIB_DIR . '/download/'.$timestamp;
		}

		// Create folder if not exists
		if (!file_exists($downloadPath)) {
			mkdir($downloadPath);
		}

		if((isset($post['getcode']) && trim($post['getcode']) != "")) {
			$script = $downloadPath.'/'.$xmlDetails->packagebuilder->codeasfile->filename;
			
			// if os is linux or macos, CRLF needs to be replaced by LF
			if ($post['os_selected'] == 'linux' || $post['os_selected'] == 'macos') {
				$post['getcode'] = str_replace("\r\n", "\n", $post['getcode']);
			}
			// Create script file
			$handscript = fopen($script, "w+");
			fwrite($handscript, $post['getcode']);
			fclose($handscript);
		}

		if(!empty($file["additionalfiles"]['size']) && file_exists($file["additionalfiles"]["tmp_name"])) {
			//verif if is an archive file
			$name_file_extention = explode('.', $file["additionalfiles"]["name"]);
			$extention = array_pop($name_file_extention);

			// If have a specific xml filename
			if(isset($xmlDetails->packagebuilder->filesinarchive->replacename)) {
				$filename = $xmlDetails->packagebuilder->filesinarchive->replacename;
			} else {
				$filename = $file["additionalfiles"]["name"];
			}

			// If not an archive
			if (strtoupper($extention) != "ZIP" && strtoupper($extention) != "GZ") { 
				//switch os selected to zip or tar
				switch($post['os_selected']) {
					case 'windows':
						if($post['FORMTYPE'] == "updateagentopt") {
							$filepath = $this->zipScriptFile(dirname($file["additionalfiles"]["tmp_name"]).'/', basename($file["additionalfiles"]["tmp_name"]), $filename, true);
						} else {
							$filepath = $this->zipScriptFile(dirname($file["additionalfiles"]["tmp_name"]).'/', basename($file["additionalfiles"]["tmp_name"]), $filename);
						}
						break;
					default:
						if($post['FORMTYPE'] == "installpluginlinuxopt") {
							$filepath = $this->tarScriptFile(dirname($file["additionalfiles"]["tmp_name"]).'/', basename($file["additionalfiles"]["tmp_name"]), $filename, true);
						} else {
							$filepath = $this->tarScriptFile(dirname($file["additionalfiles"]["tmp_name"]).'/', basename($file["additionalfiles"]["tmp_name"]), $filename);
						}
						break;
				}	
			} else {
				$filepath = $file["additionalfiles"]["tmp_name"];
			}

			$digest = md5_file($filepath);
			// Create package archive
			$details = $this->fragmentPackage($filepath, $downloadPath, $timestamp);
		} elseif(isset($post['getcode']) && file_exists($downloadPath.'/'.$xmlDetails->packagebuilder->codeasfile->filename)) {
			$fileNameInArchive = $xmlDetails->packagebuilder->codeasfile->filename;
			switch($post['os_selected']) {
				case 'windows':
					$zipScript = $this->zipScriptFile($downloadPath.'/', $fileNameInArchive, $fileNameInArchive);
					$digest = md5_file($zipScript);
					// Create package archive
					$details = $this->fragmentPackage($zipScript, $downloadPath, $timestamp);
					unlink($zipScript);
					break;
				default:
					$tarScript = $this->tarScriptFile($downloadPath.'/', $fileNameInArchive, $fileNameInArchive);
					$digest = md5_file($tarScript);
					// Create package archive
					$details = $this->fragmentPackage($tarScript, $downloadPath, $timestamp);
					unlink($tarScript);
					break;
			}	
		}

		// Replace dynamic value from xml
		if($xmlDetails->replace == "true") {
			$xmlDetails = $this->replaceXmlValue($post, $xmlDetails);
		}
		
		// Generate info xml
		$info = $this->writePackageInfo($xmlDetails, $timestamp, $details['frag'], $digest);
		// Create info file
		$handinfo = fopen($downloadPath."/info", "w+");
        fwrite($handinfo, $info);
		fclose($handinfo);

		//delete all package with the same id
		mysql2_query_secure("DELETE FROM download_available WHERE FILEID='%s'", $_SESSION['OCS']["writeServer"], $timestamp);
		
        //insert new package
        $req = "INSERT INTO download_available(FILEID, NAME, PRIORITY, FRAGMENTS, SIZE, OSNAME, COMMENT) 
				VALUES ('%s','%s','%s','%s','%s','%s','%s')";
        $arg = array(
			$timestamp, 
			strip_tags_array($post['NAME']), 
			$xmlDetails->packagedefinition->PRI, 
			$details['frag'],
			$details['size'], 
			strtoupper($post["os_selected"]), 
			strip_tags_array($post['DESCRIPTION'])
		);
			
        mysql2_query_secure($req, $_SESSION['OCS']["writeServer"], $arg);

		addLog($l->g(512), $l->g(617) . " " . $timestamp);
		//info message
		msg_success($l->g(437) . " " . $downloadPath);
		//delete cache for activation
		unset($_SESSION['OCS']['DATA_CACHE']['LIST_PACK']);
		unset($_SESSION['OCS']['NUM_ROW']['LIST_PACK']);

		$packageInfos['NAME'] = strip_tags_array($post['NAME']);
		$packageInfos['DESCRIPTION'] = strip_tags_array($post['DESCRIPTION']);
		$packageInfos['FRAG'] = $details['frag'];
		$packageInfos['SIZE'] = $details['size'];
		$packageInfos['PRIO'] = $xmlDetails->packagedefinition->PRI;
		$packageInfos['ACTIVATE'] = $l->g(454);

		// Autot activate package
		if(isset($this->downloadConfig['ivalue']['DOWNLOAD_AUTO_ACTIVATE']) && $this->downloadConfig['ivalue']['DOWNLOAD_AUTO_ACTIVATE'] == 1) {
			$packageInfos['ACTIVATE'] = $this->activate_package($timestamp, $this->downloadConfig['tvalue']['DOWNLOAD_URI_INFO'], $this->downloadConfig['tvalue']['DOWNLOAD_URI_FRAG']);
		}

		return $packageInfos;
	}

	public function activate_package($fileid, $https_server, $file_serv) {
		global $l;

		$reqEnable = "SELECT * FROM download_enable WHERE FILEID = %s";
		$argEnable = array($fileid);
		$result = mysql2_query_secure($reqEnable, $_SESSION['OCS']["readServer"], $argEnable);

		$listInfoLoc = [];
		$listPackLoc = [];

		while($recVerif = mysqli_fetch_array($result)){
			$listInfoLoc[] = $recVerif['INFO_LOC'];
			$listPackLoc[] = $recVerif['PACK_LOC'];
		}

		if(!in_array($https_server, $listInfoLoc) && !in_array($file_serv, $listPackLoc)){
			$req = "INSERT INTO download_enable(FILEID, INFO_LOC, PACK_LOC, CERT_FILE, CERT_PATH) 
					VALUES ('%s', '%s', '%s', 'INSTALL_PATH/cacert.pem','INSTALL_PATH')";
			$arg = array($fileid, $https_server, $file_serv);
			$result = mysql2_query_secure($req, $_SESSION['OCS']["writeServer"], $arg, $l->g(512));

			if($result) {
				return $l->g(455);
			}
		}
		return $l->g(454);
	}

	private function tarScriptFile($path, $name, $newName, $attachmentScript = null) {
		$DelFilePath = $path.$name;
		$tarPath = $path.$name.".tar";

		try {
			$tar = new PharData($tarPath);
			// ADD FILES TO archive.tar FILE
			$tar->addFile($DelFilePath, $newName);
			if($attachmentScript == true) {
				$tar->addFile("config/teledeploy/script/installplugin.sh", "installplugin.sh");
			}
			// COMPRESS archive.tar FILE. COMPRESSED FILE WILL BE archive.tar.gz
			$tar->compress(Phar::GZ);
			// NOTE THAT BOTH FILES WILL EXISTS. SO IF YOU WANT YOU CAN UNLINK archive.tar
			if(file_exists($DelFilePath)) {
				unlink($DelFilePath); 
			}
			if(file_exists($tarPath)) {
				unlink($tarPath); 
			}
		} catch (Exception $e) {
			error_log(print_r("error when tar gz file",true));
		}

		return $tarPath.".gz";
	}

	private function zipScriptFile($path, $name, $newName, $attachmentScript = null) {
		$DelFilePath = $path.$name;
		$zipPath = $path.$name.".zip";

		$zip = new ZipArchive();
		
		if($zip->open($zipPath, ZIPARCHIVE::CREATE) == TRUE) {
			$zip->addFile($DelFilePath, $newName);
			if($attachmentScript == true) {
				$zip->addFile("config/teledeploy/script/scheduledupdateagent.ps1", "scheduledupdateagent.ps1");
				$zip->addFile("config/teledeploy/script/removeupdateagent.ps1", "removeupdateagent.ps1");
			}
		}
		
		// close and save archive
		$zip->close();
		
		if(file_exists($DelFilePath)) {
			unlink($DelFilePath); 
		}

		return $zipPath;
	}
	
	/**
	 * Method replaceXmlValue
	 *
	 * @param $post $post [explicite description]
	 * @param $xmlDetails $xmlDetails [explicite description]
	 *
	 * @return void
	 */
	private function replaceXmlValue($post, $xmlDetails) {
		if($post['FORMTYPE'] != "custompackageopt") {
			$post['PROTO'] = $this->downloadConfig['tvalue']['DOWNLOAD_PROTOCOL'];
		}
		foreach($xmlDetails->packagedefinition as $packagedefinition) {
			foreach ($packagedefinition as $key => $value) {
				if (preg_match_all('/:(.*?):/', $value, $match) != 0) {
					foreach($match[0] as $id => $replace) {
						$xmlDetails->packagedefinition->$key = str_replace($replace, str_replace('"', "'", $post[$match[1][$id]]), $xmlDetails->packagedefinition->$key);
					}
				}
			}
		}

		return $xmlDetails;
	}
	
	/**
	 * Method fragmentPackage
	 *
	 * @param $fname $fname [explicite description]
	 * @param $downloadPath $downloadPath [explicite description]
	 * @param $timestamp $timestamp [explicite description]
	 *
	 * @return void
	 */
	private function fragmentPackage($fname, $downloadPath, $timestamp) {
		$size = @filesize($fname);

		// If package fragmentation disabled, frag = 1
		if((isset($this->downloadConfig['ivalue']['DOWNLOAD_ACTIVATE_FRAG']) && $this->downloadConfig['ivalue']['DOWNLOAD_ACTIVATE_FRAG'] == 1) && $this->downloadConfig['ivalue']['DOWNLOAD_RATIO_FRAG'] != null) {
			$frag = 0;
			$sizeBis = $size / pow(1024, 2);

			while($sizeBis > 0) {
				$sizeBis = $sizeBis - intval($this->downloadConfig['ivalue']['DOWNLOAD_RATIO_FRAG']);
				$frag ++;
			}
		} else {
			$frag = 1;
		}
		
		$handle = fopen($fname, "rb");
		$read = 0;
		
		for ($i = 1; $i < $frag; $i++) {
			$contents = fread($handle, $size / $frag);
			$read += strlen($contents);
			$handfrag = fopen($downloadPath."/".$timestamp."-".$i, "w+b");
			fwrite($handfrag, $contents);
			fclose($handfrag);
		}

		$contents = fread($handle, $size - $read);
		$read += strlen($contents);
		$handfrag = fopen($downloadPath."/".$timestamp."-".$i, "w+b");
		
		fwrite($handfrag, $contents);
		fclose($handfrag);
		fclose($handle);
		
		$details['frag'] = $i;
		$details['size'] = $size;

		return $details;
	}
	
	/**
	 * Create Info package file
	 * 
	 * @param obj $xmlDetails
	 * @param int $timestamp
	 * @param int $nbFrag
	 * @param int $digest
	 * 
	 * @return string $info
	 */
	private function writePackageInfo($xmlDetails, $timestamp, $nbFrag, $digest) {

		$info  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $info .= "<DOWNLOAD ID=\"" . $timestamp . "\" " .
                 "PRI=\"" . $xmlDetails->packagedefinition->PRI . "\" " .
                 "ACT=\"" . $xmlDetails->packagedefinition->ACT . "\" " .
                 "DIGEST=\"" . $digest . "\" " .
                 "PROTO=\"" . $xmlDetails->packagedefinition->PROTO . "\" " .
                 "FRAGS=\"" . $nbFrag . "\" " .
                 "DIGEST_ALGO=\"MD5\" " .
                 "DIGEST_ENCODE=\"Hexa\" ";
        if ($xmlDetails->packagedefinition->ACT  == 'STORE') {
            $info .= "PATH=\"" . $xmlDetails->packagedefinition->COMMAND . "\" ";
        }

		if ($xmlDetails->packagedefinition->ACT  == 'LAUNCH' && $xmlDetails->formoption->ISCUSTOM == false) {
			// if pkg built from linux bashscript option and not custom package, 
			// default retrieved if a command but w/ LAUNCH, the agent will be expecting a name
			$name = substr($xmlDetails->packagedefinition->COMMAND, strpos($xmlDetails->packagedefinition->COMMAND, ' ') + 1);
			$info .= "NAME=\"" . $name . "\" ";

		} else if ($xmlDetails->packagedefinition->ACT  == 'LAUNCH') {
            $info .= "NAME=\"" . $xmlDetails->packagedefinition->COMMAND . "\" ";
        }

        if ($xmlDetails->packagedefinition->ACT  == 'EXECUTE') {
            $info .= "COMMAND=\"" . $xmlDetails->packagedefinition->COMMAND . "\" ";
        }
				
		return $info . ("NOTIFY_USER=\"" . $xmlDetails->packagedefinition->NOTIFY_USER  . "\" " .
				 "NOTIFY_TEXT=\"" . $xmlDetails->packagedefinition->NOTIFY_TEXT . "\" " .
				 "NOTIFY_COUNTDOWN=\"" . $xmlDetails->packagedefinition->NOTIFY_COUNTDOWN . "\" " .
				 "NOTIFY_CAN_ABORT=\"" . $xmlDetails->packagedefinition->NOTIFY_CAN_ABORT . "\" " .
				 "NOTIFY_CAN_DELAY=\"" . $xmlDetails->packagedefinition->NOTIFY_CAN_DELAY . "\" " .
				 "NEED_DONE_ACTION=\"" . $xmlDetails->packagedefinition->NEED_DONE_ACTION . "\" " .
				 "NEED_DONE_ACTION_TEXT=\"" . $xmlDetails->packagedefinition->NEED_DONE_ACTION_TEXT . "\" " .
				 "GARDEFOU=\"rien\" />\n");
		
	}
	
	/**
	 * Method getPackageFileId
	 *
	 * @param $name $name [explicite description]
	 * @param $session $session [explicite description]
	 *
	 * @return void
	 */
	public function getPackageFileId($name , $session = null) {
		
		$sql = "SELECT fileid FROM download_available WHERE name='%s'";
		$arg = array($name);
		if($session != null) {
			$result = mysql2_query_secure($sql, $session, $arg);
		} else {
			$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
		}

		if (mysqli_num_rows($result) != 0) {
			return false;
		}
	}

	
}