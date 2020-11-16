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
echo "<script language='javascript'>
	function active(id, sens) {
		var mstyle = document.getElementById(id).style.display	= (sens!=0?\"block\" :\"none\");
	}</script>";

function javascript_pack() {
    global $protectedPost;
    echo "<script language='javascript'>
	function time_deploy(name,name_value,other_name,other_value){
		var tps_cycle=" . $_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_CYCLE_LATENCY'] * $_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LENGTH'] . ";
		var nb_frag_by_cycle=" . ($protectedPost['PRIORITY'] != 0 ? floor($_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LENGTH'] / $protectedPost['PRIORITY']) : $_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LENGTH']) . ";
		if (name == 'tailleFrag'){
			var taille=name_value;
			var nb_frag=other_value;
		}
		else{
			var taille=other_value;
			var nb_frag=name_value;
		}
		var nb_cycle_for_download=nb_frag/nb_frag_by_cycle;
		var tps_cycle_for_download = nb_cycle_for_download*tps_cycle;
		var tps_frag_latency=nb_frag_by_cycle*" . $_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_FRAG_LATENCY'] . "*nb_cycle_for_download;
		var tps_period_latency=" . $_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LATENCY'] . "*nb_cycle_for_download;
		var download_speed=25000;
		var tps_download_speed=taille/download_speed;
		var tps_total=tps_cycle_for_download+tps_frag_latency+tps_period_latency+tps_download_speed;
		var heure=Math.floor(tps_total/3600);
		tps_total=tps_total-heure*3600;
		var minutes=Math.floor(tps_total/60);
		tps_total=Math.floor(tps_total-minutes*60);
		var affich=heure+'h'+minutes+'m'+tps_total+'s';
		document.getElementById('TPS').value = affich;
	}

	function maj(name,other_field,siz){
		if (document.getElementById(name).value != '' &&  document.getElementById(name).value != 0){
			if ( Math.ceil(document.getElementById(name).value*1024) < siz)
			document.getElementById(other_field).value = Math.ceil( siz / (Math.ceil(document.getElementById(name).value*1024)) );
			else{
			document.getElementById(other_field).value = 1;
			document.getElementById(name).value=Math.ceil(siz/1024)
			}
		}else
		document.getElementById(other_field).value = '';
		time_deploy(name,document.getElementById(name).value,other_field,document.getElementById(other_field).value);
	}
	</script>";
}

function looking4config() {
    if (!isset($_SESSION['OCS']['CONFIG_DOWNLOAD'])) {
        $values = look_config_default_values(array('DOWNLOAD_CYCLE_LATENCY', 'DOWNLOAD_PERIOD_LENGTH',
            'DOWNLOAD_FRAG_LATENCY', 'DOWNLOAD_PERIOD_LATENCY'));
        $_SESSION['OCS']['CONFIG_DOWNLOAD'] = $values['ivalue'];
    }
}

function time_deploy($label = '') {
    $champ = "disabled='disabled'";
    formGroup('text', 'TPS', $label, '10', '', '', '', '', '', $champ);
}

function input_pack_taille($name, $other_field, $size, $input_size, $input_value, $label = '', $addon = '', $modif = true) {
    if ($size > 1024 && $modif == true) {
        $champ = ' onKeyPress="maj(\'' . $name . '\', \'' . $other_field . '\', \'' . $size . '\');"
                   onkeydown="maj(\'' . $name . '\', \'' . $other_field . '\', \'' . $size . '\');"
                   onkeyup="maj(\'' . $name . '\', \'' . $other_field . '\', \'' . $size . '\');"
                   onblur="maj(\'' . $name . '\', \'' . $other_field . '\', \'' . $size . '\');"
                   onclick="maj(\'' . $name . '\', \'' . $other_field . '\', \'' . $size . '\');"
                   ';
    } elseif($modif == false) {
        $champ = " style='pointer-events:none; background:lightgrey;' ";
    } else {
        $champ = " style='pointer-events:none; background:lightgrey;' ";
    }
    formGroup('text', $name, $label, $input_size, '', $input_value, '', '', '', $champ, ($addon != '' ? $addon : ''));
}

function desactive_option($name, $list_id, $packid) {
    global $l;

    $sql_desactive = "delete from devices where name='%s' and ivalue=%s";
    $arg_desactive = array($name, $packid);
    if ($list_id != '') {
        $sql_desactive .= " and hardware_id in ";
        $sql = mysql2_prepare($sql_desactive, $arg_desactive, $list_id);
        $res_desactive = mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["writeServer"], $sql['ARG'], $l->g(512));
    } else {
	$sql_desactive .= " and hardware_id=%s";
        $arg_desactive[] = $_GET['systemid'];
        $res_desactive = mysql2_query_secure($sql_desactive, $_SESSION['OCS']["writeServer"], $arg_desactive, $l->g(512));
    }
    return( mysqli_affected_rows($_SESSION['OCS']["writeServer"]) );
}

function active_option($name, $list_id, $packid, $tvalue = '') {
    global $l;

		if(strpos($packid, ',')) {
			$pack_id = explode(',',$packid);
			foreach($pack_id as $key => $value){
				desactive_option($name, $list_id, $value);
		    $sql_active = "insert into devices (HARDWARE_ID, NAME, IVALUE,TVALUE) select ID,'%s','%s',";
		    if ($tvalue == '') {
		        $sql_active .= "null from hardware where id in ";
		        $arg_active = array($name, $value);
		    } else {
		        $sql_active .= "'%s' from hardware where id in ";
		        $arg_active = array($name, $value, $tvalue);
		    }
		    $sql = mysql2_prepare($sql_active, $arg_active, $list_id);
		    mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["writeServer"], $sql['ARG'], $l->g(512));
			}
		} else {
				desactive_option($name, $list_id, $packid);
		    $sql_active = "insert into devices (HARDWARE_ID, NAME, IVALUE,TVALUE) select ID,'%s','%s',";
		    if ($tvalue == '') {
		        $sql_active .= "null from hardware where id in ";
		        $arg_active = array($name, $packid);
		    } else {
		        $sql_active .= "'%s' from hardware where id in ";
		        $arg_active = array($name, $packid, $tvalue);
		    }
		    $sql = mysql2_prepare($sql_active, $arg_active, $list_id);
		    mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["writeServer"], $sql['ARG'], $l->g(512));
		}

    return( mysqli_affected_rows($_SESSION['OCS']["writeServer"]) );
}

function desactive_download_option($list_id, $packid) {
    desactive_option('DOWNLOAD_FORCE', $list_id, $packid);
    desactive_option('DOWNLOAD_SCHEDULE', $list_id, $packid);
    desactive_option('DOWNLOAD_POSTCMD', $list_id, $packid);
}

function desactive_packet($list_id, $packid) {
    desactive_download_option($list_id, $packid);
    $nb_line = desactive_option('DOWNLOAD', $list_id, $packid);
    return $nb_line;
}

function active_serv($list_id, $packid, $id_rule) {
    global $l;
    require_once('function_server.php');
    //get all condition of this rule
    $sql = "select PRIORITY,CFIELD,OP,COMPTO,SERV_VALUE from download_affect_rules where rule=%s order by PRIORITY";
    $arg = $id_rule;
    $res_rules = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    while ($val_rules = mysqli_fetch_array($res_rules)) {
        $cfield[$val_rules['PRIORITY']] = $val_rules['CFIELD'];
        $op[$val_rules['PRIORITY']] = $val_rules['OP'];
        $compto[$val_rules['PRIORITY']] = $val_rules['COMPTO'];
    }
    $nb_insert = 0;
    foreach ($cfield as $key => $value) {
        $rule_detail = array('cfield' => $cfield[$key], 'op' => $op[$key], 'compto' => $compto[$key]);
        $result = insert_with_rules($list_id, $rule_detail, $packid);
        $nb_insert += $result['nb_insert'];
        $m = 0;
        while ($result['exist'][$m]) {
            $exist[] = $result['exist'][$m];
            $m++;
        }
        $nb_exist += $result['nb_exist'];

        if ($result['not_match'] == "") {
            break;
        } else {
            unset($list_id);
            $list_id = $result['not_match'];
        }
    }

    if (isset($result['not_match'])) {
        tab_list_error($result['not_match'], $result['nb_not_match'] . " " . $l->g(658) . " " . $l->g(887) . "<br>");
    }

    if (isset($exist)) {
        tab_list_error($exist, $nb_exist . " " . $l->g(659) . " " . $l->g(482));
    }
    return $nb_insert;
}

function loadInfo($serv, $tstamp) {

    $fname = $serv . "/" . $tstamp . "/info";
    $info = @file_get_contents($fname);
    if (!$info) {
        return false;
    }

    @preg_match_all("/((?:\d|\w)+)=\"((?:\d|\w)+)\"/", $info, $resul);
    if (!$resul) {
        return false;
    }
    $noms = array_flip($resul[1]);
    foreach ($noms as $nom => $int) {
        $noms[$nom] = $resul[2][$int];
    }
    return( $noms );
}

function activ_pack($fileid, $https_server, $file_serv) {
    global $l;
    //checking if corresponding available exists
    $reqVerif = "SELECT * FROM download_available WHERE fileid=%s";
    $argVerif = $fileid;
    if (!mysqli_num_rows(mysql2_query_secure($reqVerif, $_SESSION['OCS']["readServer"], $argVerif))) {

        $infoTab = loadInfo($https_server, $file_serv);
        if ($infoTab == '') {
            $infoTab = array("PRI" => '10', "FRAGS" => '0');
        }
        $req1 = "INSERT INTO download_available(FILEID, NAME, PRIORITY, FRAGMENTS, OSNAME) VALUES
			  ('%s', 'Manual_%s', %s, %s, 'N/A')";
        $arg1 = array($fileid, $fileid, $infoTab["PRI"], $infoTab["FRAGS"]);
        mysql2_query_secure($req1, $_SESSION['OCS']["writeServer"], $arg1);
    }

		$reqEnable = "SELECT * FROM download_enable WHERE fileid=%s";
		$argEnable = array($fileid);
		$result = mysql2_query_secure($reqEnable, $_SESSION['OCS']["readServer"], $argEnable);
		$listInfoLoc = array();
		$listPackLoc = array();
		while($recVerif = mysqli_fetch_array($result)){
				$listInfoLoc[] = $recVerif['INFO_LOC'];
				$listPackLoc[] = $recVerif['PACK_LOC'];
		}

		if(!in_array( $https_server, $listInfoLoc) && !in_array($file_serv, $listPackLoc)){
				$req = "INSERT INTO download_enable(FILEID, INFO_LOC, PACK_LOC, CERT_FILE, CERT_PATH) VALUES
				('%s', '%s', '%s', 'INSTALL_PATH/cacert.pem','INSTALL_PATH')";
    		$arg = array($fileid, $https_server, $file_serv);
    		mysql2_query_secure($req, $_SESSION['OCS']["writeServer"], $arg, $l->g(512));
		}
}

function activ_pack_server($fileid, $https_server, $id_server_group) {
    global $protectedPost;
    //search all computers have this package
    $sqlDoub = "select SERVER_ID,INFO_LOC from download_enable where FILEID= %s";
    $argDoub = $fileid;
    $resDoub = mysql2_query_secure($sqlDoub, $_SESSION['OCS']["readServer"], $argDoub);

    //exclu them
    while ($valDoub = mysqli_fetch_array($resDoub)) {
        if ($valDoub['SERVER_ID'] != "") {
            $listDoub[] = $valDoub['SERVER_ID'];
        }

        //Update https server location if different from mysql database
        if ($valDoub['INFO_LOC'] != $https_server) {
            $sql_update_https = "UPDATE download_enable SET download_enable.INFO_LOC='%s' WHERE SERVER_ID=%s";
            $arg_update_https = array($https_server, $valDoub['SERVER_ID']);
            mysql2_query_secure($sql_update_https, $_SESSION['OCS']["writeServer"], $arg_update_https);
        }
    }
    //If this list is not null, we create the end of sql request
    if (isset($listDoub)) {
        $listDoub = " AND HARDWARE_ID not in (" . implode(',', $listDoub) . ")";
    }
    //on insert l'activation du paquet pour les serveurs du groupe
    $sql = "insert into download_enable (FILEID,INFO_LOC,PACK_LOC,CERT_PATH,CERT_FILE,SERVER_ID,GROUP_ID)
				select %s,'%s',url,'INSTALL_PATH','INSTALL_PATH/cacert.pem',
				 HARDWARE_ID, GROUP_ID from download_servers where GROUP_ID=%s" . $listDoub;
    $arg = array($fileid, $https_server, $id_server_group);

    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);

    $query = "UPDATE download_available set COMMENT = '%s' WHERE FILEID = %s";
    $arg_query = array($protectedPost['id_server_add'], $fileid);
    mysql2_query_secure($query, $_SESSION['OCS']["writeServer"], $arg_query);
}

// del packet and mark it as deleted in the database
function del_pack($fileid) {
    global $l;
    //find all activate package
    $reqEnable = "SELECT id FROM download_enable WHERE FILEID='%s'";
    $argEnable = $fileid;
    $resEnable = mysql2_query_secure($reqEnable, $_SESSION['OCS']["readServer"], $argEnable);
    while ($valEnable = mysqli_fetch_array($resEnable)) {
        $list_id[] = $valEnable["id"];
    }
    //delete packet in DEVICES table
    if ($list_id != "") {
        foreach ($list_id as $v) {
            desactive_packet('', $v);
        }
    }
    //delete activation of this pack
    $reqDelEnable = "DELETE FROM download_enable WHERE FILEID='%s'";
    $argDelEnable = $fileid;
    mysql2_query_secure($reqDelEnable, $_SESSION['OCS']["writeServer"], $argDelEnable);

    //put pack on deleted state
    $reqDelAvailable = "UPDATE download_available SET DELETED = '1' WHERE FILEID='%s'";
    $argDelAvailable = $fileid;

    mysql2_query_secure($reqDelAvailable, $_SESSION['OCS']["writeServer"], $argDelAvailable);
    //what is the directory of this package?
    $info = look_config_default_values('DOWNLOAD_PACK_DIR');
    $document_root = $info['tvalue']['DOWNLOAD_PACK_DIR'];
    //if no directory in base, take $_SERVER["DOCUMENT_ROOT"]
    if (!isset($document_root)) {
        $document_root = VARLIB_DIR;
    }

    if (@opendir($document_root . "/download/" . $fileid)) {
        //delete all files from this package
        if (!@recursive_remove_directory($document_root . "/download/" . $fileid)) {
            msg_error($l->g(472) . " " . $document_root . "/download/" . $fileid);
        }
    }

    // delete redistribution package
    $dl_rep_redist = look_config_default_values('DOWNLOAD_REP_CREAT');
    $document_root = $dl_rep_redist['tvalue']['DOWNLOAD_REP_CREAT'];

    if (!$document_root) {
        $document_root = VARLIB_DIR . '/download/server';
    }
    $redist_package = realpath($document_root . "/" . $fileid);

    if ($redist_package && @opendir($redist_package)) {
        //delete all files from this package
        if (!@recursive_remove_directory($redist_package)) {
            msg_error($l->g(472) . " " . $redist_package);
        }
    }

    addLog($l->g(512), $l->g(888) . " " . $fileid);
}

// Definitly remove packet from database
function remove_packet($fileid) {
    //delete info of this pack
    $reqDelAvailable = "DELETE FROM `download_available` WHERE `FILEID` = '%s'";
    $argDelAvailable = $fileid;

    mysql2_query_secure($reqDelAvailable, $_SESSION['OCS']["writeServer"], $argDelAvailable);
}

function recursive_remove_directory($directory, $empty = false) {
    if (substr($directory, -1) == '/') {
        $directory = substr($directory, 0, -1);
    }

    if (!file_exists($directory) || !is_dir($directory)) {
        return false;
    } elseif (is_readable($directory)) {
        $handle = opendir($directory);
        while (false !== ($item = readdir($handle))) {
            if ($item != '.' && $item != '..') {
                $path = $directory . '/' . $item;
                if (is_dir($path)) {
                    recursive_remove_directory($path);
                } else {
                    unlink($path);
                }
            }
        }
        closedir($handle);
        if ($empty == false) {
            if (!rmdir($directory)) {
                return false;
            }
        }
    }
    return true;
}

function create_pack($sql_details, $info_details, $modif = "true") {
    global $l;

    if (DEMO) {
        msg_info($l->g(2103));
        return;
    }

    // Convert packages details in a properly UTF-8 encoded HTML string.
    foreach ($info_details as $key => $value) {
        if (mb_detect_encoding($value)!== 'UTF-8') {
          $info_details[$key] = mb_convert_encoding($value, 'UTF-8');
        }
        $info_details[$key] = htmlspecialchars($value, ENT_QUOTES);
    }

    if($modif == "true"){
	    //get temp file
        $fname = $sql_details['document_root'] . $sql_details['timestamp'] . "/tmp";
        //cut this package
        if ($size = @filesize($fname)) {
            $handle = fopen($fname, "rb");
            $read = 0;
            if(!isset($sql_details['nbfrags'])) $sql_details['nbfrags'] = 1;
            for ($i = 1; $i < $sql_details['nbfrags']; $i++) {
                $contents = fread($handle, $size / $sql_details['nbfrags']);
                $read += strlen($contents);
                $handfrag = fopen($sql_details['document_root'] . $sql_details['timestamp'] . "/" . $sql_details['timestamp'] . "-" . $i, "w+b");
                fwrite($handfrag, $contents);
                fclose($handfrag);
            }

            $contents = fread($handle, $size - $read);
            $read += strlen($contents);
            $handfrag = fopen($sql_details['document_root'] . $sql_details['timestamp'] . "/" . $sql_details['timestamp'] . "-" . $i, "w+b");
            fwrite($handfrag, $contents);
            fclose($handfrag);
            fclose($handle);

            unlink($sql_details['document_root'] . $sql_details['timestamp'] . "/tmp");
        } else {
            if (!file_exists($sql_details['document_root'] . $sql_details['timestamp'])) {
                mkdir($sql_details['document_root'] . $sql_details['timestamp']);
            }
        }

        if (!is_defined($info_details['DIGEST'])) {
            $sql_details['nbfrags'] = 0;
        }

        //create info
        if($sql_details['nbfrags'] == null) {
            $sql_details['nbfrags'] = '0';
        }

        $info = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $info .= "<DOWNLOAD ID=\"" . $sql_details['timestamp'] . "\" " .
                "PRI=\"" . $info_details['PRI'] . "\" " .
                "ACT=\"" . $info_details['ACT'] . "\" " .
                "DIGEST=\"" . $info_details['DIGEST'] . "\" " .
                "PROTO=\"" . $info_details['PROTO'] . "\" " .
                "FRAGS=\"" . $sql_details['nbfrags'] . "\" " .
                "DIGEST_ALGO=\"" . $info_details['DIGEST_ALGO'] . "\" " .
                "DIGEST_ENCODE=\"" . $info_details['DIGEST_ENCODE'] . "\" ";
        if ($info_details['ACT'] == 'STORE') {
            $info .= "PATH=\"" . $info_details['PATH'] . "\" ";
        }
        if ($info_details['ACT'] == 'LAUNCH') {
            $info .= "NAME=\"" . $info_details['NAME'] . "\" ";
        }
        if ($info_details['ACT'] == 'EXECUTE') {
            $info .= "COMMAND=\"" . $info_details['COMMAND'] . "\" ";
        }

        $notifyText = htmlspecialchars($info_details['NOTIFY_TEXT'], ENT_QUOTES);
        $actionText = addslashes($info_details['NEED_DONE_ACTION_TEXT']);

        $info .= "NOTIFY_USER=\"" . $info_details['NOTIFY_USER'] . "\" " .
                "NOTIFY_TEXT=\"" . $notifyText . "\" " .
                "NOTIFY_COUNTDOWN=\"" . $info_details['NOTIFY_COUNTDOWN'] . "\" " .
                "NOTIFY_CAN_ABORT=\"" . $info_details['NOTIFY_CAN_ABORT'] . "\" " .
                "NOTIFY_CAN_DELAY=\"" . $info_details['NOTIFY_CAN_DELAY'] . "\" " .
                "NEED_DONE_ACTION=\"" . $info_details['NEED_DONE_ACTION'] . "\" " .
                "NEED_DONE_ACTION_TEXT=\"" . $actionText . "\" " .
                "GARDEFOU=\"" . $info_details['GARDEFOU'] . "\" />\n";

        $handinfo = fopen($sql_details['document_root'] . $sql_details['timestamp'] . "/info", "w+");
        fwrite($handinfo, $info);
        fclose($handinfo);

        //delete all package with the same id
        mysql2_query_secure("DELETE FROM download_available WHERE FILEID='%s'", $_SESSION['OCS']["writeServer"], $sql_details['timestamp']);
        //insert new package
        $req = "INSERT INTO download_available(FILEID, NAME, PRIORITY, FRAGMENTS, SIZE, OSNAME, COMMENT,ID_WK) VALUES
            ( '%s', '%s','%s', '%s','%s', '%s', '%s','%s' )";
        $arg = array($sql_details['timestamp'], $sql_details['name'], $info_details['PRI'], $sql_details['nbfrags'],
            $sql_details['size'], $sql_details['os'], $sql_details['description'], $sql_details['id_wk']);
        mysql2_query_secure($req, $_SESSION['OCS']["writeServer"], $arg);
    } else {
        $file = $sql_details['document_root'] . $sql_details['timestamp'] . "/info";

        $dom = new DOMDocument();
        $dom->load($file);

        $xpath = new DOMXPath($dom);
        $elements = $xpath->query("//*[starts-with(local-name(), 'DOWNLOAD')]");

        $notifyText = $info_details['NOTIFY_TEXT'];
        $actionText = addslashes($info_details['NEED_DONE_ACTION_TEXT']);

        if ($elements->length >= 1) {
            $element = $elements->item(0);
            $element->setAttribute('PRI', $info_details['PRI']);
            $element->setAttribute('ACT', $info_details['ACT']);
            $element->setAttribute('PROTO', $info_details['PROTO']);
            if ($info_details['ACT'] == 'STORE') {
                $element->setAttribute('PATH', $info_details['PATH']);
            }
            if ($info_details['ACT'] == 'EXECUTE') {
                $element->setAttribute('COMMAND', $info_details['COMMAND']);
            }
            $element->setAttribute('NOTIFY_USER', $info_details['NOTIFY_USER']);
            $element->setAttribute('NOTIFY_TEXT', $notifyText);
            $element->setAttribute('NOTIFY_COUNTDOWN', $info_details['NOTIFY_COUNTDOWN']);
            $element->setAttribute('NOTIFY_CAN_ABORT', $info_details['NOTIFY_CAN_ABORT']);
            $element->setAttribute('NOTIFY_CAN_DELAY', $info_details['NOTIFY_CAN_DELAY']);
            $element->setAttribute('NEED_DONE_ACTION', $info_details['NEED_DONE_ACTION']);
            $element->setAttribute('NEED_DONE_ACTION_TEXT', $actionText);
            $element->setAttribute('GARDEFOU', $info_details['GARDEFOU']);
        }

        $dom->save($file);

        // Update package
        $req = "UPDATE download_available SET NAME = '%s', PRIORITY = '%s', OSNAME = '%s', COMMENT = '%s' WHERE FILEID = '%s'";
        $arg = array($sql_details['name'], $info_details['PRI'], $sql_details['os'], $sql_details['description'], $sql_details['timestamp']);
        mysql2_query_secure($req, $_SESSION['OCS']["writeServer"], $arg);
    }
  
    addLog($l->g(512), $l->g(617) . " " . $sql_details['timestamp']);
    //info message
    msg_success($l->g(437) . " " . $sql_details['document_root'] . $sql_details['timestamp']);
    //delete cache for activation
    unset($_SESSION['OCS']['DATA_CACHE']['LIST_PACK']);
    unset($_SESSION['OCS']['NUM_ROW']['LIST_PACK']);
}

function crypt_file($dir_FILES, $digest_algo, $digest_encod) {
    //crypt this file
    if ($digest_algo == "SHA1") {
        $digest = sha1_file($dir_FILES, true);
    } else {
        $digest = md5_file($dir_FILES);
    }

    if ($digest_encod == "Base64") {
        $digest = base64_encode($digest);
    }
    return $digest;
}

function creat_temp_file($directory, $dir_FILES, $modif = false) {
    if (DEMO) {
        return;
    }

    if (!file_exists($directory . "/tmp")) {
			if($modif == false){
				if (!@mkdir($directory) || !copy($dir_FILES, $directory . "/tmp")) {
            msg_error("ERROR: can't create or write in " . $directory . " folder, please refresh when fixed.<br>(or try disabling php safe mode)");
        }
			}else{
				if (!copy($dir_FILES, $directory . "/tmp")) {
            msg_error("ERROR: can't create or write in " . $directory . " folder, please refresh when fixed.<br>(or try disabling php safe mode)");
        }
			}

    }
}

function tps_estimated($val_details) {
    global $l;
    if ($val_details == "") {
        return;
    }
    /*     * *******************************DETAIL SUR LE TEMPS APPROXIMATIF DE TELEDEPLOIEMENT**************************************** */
    looking4config();
    if ($val_details['priority'] == 0) {
        $val_details['priority'] = 1;
    }
    //durée complète d'un cycle en seconde
    $tps_cycle = $_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_CYCLE_LATENCY'] * $_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LENGTH'];
    //nbre de téléchargement de fragment par cycle
    $nb_frag_by_cycle = floor($_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LENGTH'] / $val_details['priority']);
    //nombre de cycles necessaires pour le téléchargement complet
    $nb_cycle_for_download = $val_details['fragments'] / $nb_frag_by_cycle;
    //temps dans le cycle
    $tps_cycle_for_download = $nb_cycle_for_download * $tps_cycle;
    //temps entre chaque fragment pour tous les cycles
    $tps_frag_latency = ($nb_frag_by_cycle * $_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_FRAG_LATENCY']) * $nb_cycle_for_download;
    //temps entre chaque période
    $tps_period_latency = $_SESSION['OCS']['CONFIG_DOWNLOAD']['DOWNLOAD_PERIOD_LATENCY'] * $nb_cycle_for_download;
    //ajout de la vitesse de téléchargement
    $download_speed = 25000;
    $tps_download_speed = $val_details['size'] / $download_speed;

    //temps total de téléchargement:
    $tps_total = $tps_cycle_for_download + $tps_frag_latency + $tps_period_latency + $tps_download_speed
    ;
    $heure = floor($tps_total / 3600);
    $tps_total -= $heure * 3600;
    $minutes = floor($tps_total / 60);
    $tps_total -= $minutes * 60;
    $tps = $heure . "h " . $minutes . "min ";
    if ($heure == 0 && $minutes == 0) {
        $tps .= floor($tps_total) . " " . $l->g(511);
    }
    return $tps;
}

function found_info_pack($id) {
    global $l;
    if (!is_numeric($id)) {
        return array('ERROR' => $l->g(1129));
    }

    $sql = "select NAME,PRIORITY,FRAGMENTS,SIZE,OSNAME,COMMENT from download_available where fileid=%s";
    $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $id);
    $val = mysqli_fetch_array($res);
    return $val;
}

function multiexplode ($delimiters, $date)
{
		$ready = str_replace($delimiters, $delimiters[0], $date);
		$launch = explode($delimiters[0], $ready);
		return $launch;
}
?>
