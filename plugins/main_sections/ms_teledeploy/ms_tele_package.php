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
require_once('require/function_telediff.php');

echo "<div class='container'><div class='col-md-8 col-xs-offset-0 col-md-offset-2'>";

foreach ($_POST as $key => $value) {
    $temp_post[$key] = $value;
}
$protectedPost = $temp_post;
if (isset($protectedPost["VALID_END"])) {
    //configure description of this package
    $description_details = $protectedPost['DESCRIPTION'];
    if (is_defined($protectedPost['TYPE_PACK'])) {
        $description_details .= "  [Type=" . $protectedPost['TYPE_PACK'] . "]";
    }
    if (is_defined($protectedPost['VISIBLE'])) {
        $description_details .= "  [VISIBLE=" . $protectedPost['VISIBLE'] . "]";
    }

    $sql_details = array('document_root' => $protectedPost['document_root'],
        'timestamp' => $protectedPost['timestamp'],
        'nbfrags' => $protectedPost["nbfrags"],
        'name' => $protectedPost['NAME'],
        'os' => $protectedPost['OS'],
        'description' => $description_details,
        'size' => $protectedPost['SIZE'],
        'id_wk' => $protectedPost['LIST_DDE_CREAT']);

    $info_details = array('PRI' => $protectedPost['PRIORITY'],
        'ACT' => $protectedPost['ACTION'],
        'DIGEST' => $protectedPost['digest'],
        'PROTO' => $protectedPost['PROTOCOLE'],
        'DIGEST_ALGO' => $protectedPost["digest_algo"],
        'DIGEST_ENCODE' => $protectedPost["digest_encod"],
        'PATH' => $protectedPost['ACTION_INPUT'],
        'NAME' => $protectedPost['ACTION_INPUT'],
        'COMMAND' => $protectedPost['ACTION_INPUT'],
        'NOTIFY_USER' => $protectedPost['NOTIFY_USER'],
        'NOTIFY_TEXT' => $protectedPost['NOTIFY_TEXT'],
        'NOTIFY_COUNTDOWN' => $protectedPost['NOTIFY_COUNTDOWN'],
        'NOTIFY_CAN_ABORT' => $protectedPost['NOTIFY_CAN_ABORT'],
        'NOTIFY_CAN_DELAY' => $protectedPost['NOTIFY_CAN_DELAY'],
        'NEED_DONE_ACTION' => $protectedPost['NEED_DONE_ACTION'],
        'NEED_DONE_ACTION_TEXT' => $protectedPost['NEED_DONE_ACTION_TEXT'],
        'GARDEFOU' => "rien");
    create_pack($sql_details, $info_details);

    if ($protectedPost['REDISTRIB_USE'] == 1) {
        $timestamp_redistrib = time();
        $server_dir = $protectedPost['download_rep_creat'];
        //create zip file for redistribution servers
        $zipfile = new ZipArchive();
        $rep = $protectedPost['document_root'] . $sql_details['timestamp'] . "/";

        if (!file_exists($server_dir)) {
            mkdir($server_dir);
        }
        if (!file_exists($server_dir . $timestamp_redistrib)) {
            mkdir($server_dir . $timestamp_redistrib);
        }

        $zipfile->open($server_dir . $timestamp_redistrib . "/" . $timestamp_redistrib . "_redistrib.zip", ZipArchive::CREATE);
        $zipfile->addEmptyDir($sql_details['timestamp']);

        $dir = opendir($rep);

        while ($f = readdir($dir)) {
            if (is_file($rep . $f)) {
                $zipfile->addFile($rep . $f, $sql_details['timestamp'] . "/" . basename($rep . $f));
            }
        }

        $zipfile->close();

        closedir($dir);
        flush();

        //crypt the file
        $digest = crypt_file($server_dir . $timestamp_redistrib . "/" . $timestamp_redistrib . "_redistrib.zip", $protectedPost["digest_algo"], $protectedPost["digest_encod"]);
        //change name of this file to "tmp" for use function of create a package
        rename($server_dir . $timestamp_redistrib . "/" . $timestamp_redistrib . "_redistrib.zip", $server_dir . $timestamp_redistrib . "/tmp");
        //create temp file
        $fSize = filesize($server_dir . $timestamp_redistrib . "/tmp");
        $sql_details = array('document_root' => $server_dir,
            'timestamp' => $timestamp_redistrib,
            'nbfrags' => $protectedPost['nbfrags_redistrib'],
            'name' => $protectedPost['NAME'] . '_redistrib',
            'os' => $protectedPost['OS'],
            'description' => '[PACK REDISTRIBUTION ' . $protectedPost['timestamp'] . ']',
            'size' => $fSize,
            'id_wk' => $protectedPost['LIST_DDE_CREAT']);

        $info_details = array('PRI' => $protectedPost['REDISTRIB_PRIORITY'],
            'ACT' => 'STORE',
            'DIGEST' => $digest,
            'PROTO' => $protectedPost['PROTOCOLE'],
            'DIGEST_ALGO' => $protectedPost["digest_algo"],
            'DIGEST_ENCODE' => $protectedPost["digest_encod"],
            'PATH' => $protectedPost['DOWNLOAD_SERVER_DOCROOT'],
            'NAME' => '',
            'COMMAND' => '',
            'NOTIFY_USER' => '0',
            'NOTIFY_TEXT' => '',
            'NOTIFY_COUNTDOWN' => '',
            'NOTIFY_CAN_ABORT' => '0',
            'NOTIFY_CAN_DELAY' => '0',
            'NEED_DONE_ACTION' => '0',
            'NEED_DONE_ACTION_TEXT' => '',
            'GARDEFOU' => "rien");

        create_pack($sql_details, $info_details);
    }
    unset($protectedPost, $_SESSION['OCS']['DATA_CACHE']);
}
$form_name = "create_pack";
printEnTete($l->g(434));
echo open_form($form_name, '', "enctype='multipart/form-data'", "form-horizontal");

if (isset($protectedPost['valid'])) {
    looking4config();

    //file exist
    if (file_exists($_FILES["teledeploy_file"]["tmp_name"]) && is_readable($_FILES["teledeploy_file"]["tmp_name"]) && filesize($_FILES["teledeploy_file"]["tmp_name"]) > 0) {
        //is it a zip file or TAR.GZ file?
        $name_file_extention = explode('.', $_FILES["teledeploy_file"]["name"]);
        $extention = array_pop($name_file_extention);
        if (strtoupper($extention) != "ZIP" && strtoupper($extention) != "GZ") {
            $error = $l->g(1231);
            //ok
        } elseif (strtoupper($extention) == "GZ" && strtoupper(array_pop($name_file_extention)) != "TAR") {
            $error = $l->g(1232);
        }
    }
    //file not exist
    else {
        if ($protectedPost['ACTION'] != 'EXECUTE') {
            $error = $l->g(436) . " " . $_FILES["teledeploy_file"]["tmp_name"];
        }
    }

    //the package name is exist in database?
    $verifN = "SELECT fileid FROM download_available WHERE name='%s'";
    $argverifN = $protectedPost["NAME"];
    $resN = mysql2_query_secure($verifN, $_SESSION['OCS']["readServer"], $argverifN);
    if (mysqli_num_rows($resN) != 0) {
        $error = $l->g(551);
    }

    if ($error) {
        msg_error($error);
        unset($protectedPost['valid']);
    } else {
        //some fields are empty?
        echo "<script language='javascript'>
			function verif2()
			 {
				var msg = '';
				if (document.getElementById(\"tailleFrag\").value == ''){
					 document.getElementById(\"tailleFrag\").style.backgroundColor = 'RED';
					 msg='NULL';
				}
				if (document.getElementById(\"nbfrags\").value == ''){
					 document.getElementById(\"nbfrags\").style.backgroundColor = 'RED';
					 msg='NULL';
				}
				msg_trait(msg);
			}

			function verif_redistributor(){
				var msg = '';
				if (document.getElementById(\"tailleFrag\").value == ''){
					 document.getElementById(\"tailleFrag\").style.backgroundColor = 'RED';
					 msg='NULL';
				}
				if (document.getElementById(\"nbfrags\").value == ''){
					 document.getElementById(\"nbfrags\").style.backgroundColor = 'RED';
					 msg='NULL';
				}
				if (document.getElementById(\"tailleFrag_redistrib\").value == ''){
							 document.getElementById(\"tailleFrag_redistrib\").style.backgroundColor = 'RED';
							 msg='NULL';
				}
				if (document.getElementById(\"nbfrags_redistrib\").value == ''){
					 	document.getElementById(\"nbfrags_redistrib\").style.backgroundColor = 'RED';
						 msg='NULL';
				}

				msg_trait(msg);
			}

			function msg_trait(msg){
				if (msg != ''){
					alert ('" . $l->g(1001) . "');
					return false;
				}else{
						pag(\"END\",\"VALID_END\",\"" . $form_name . "\");
						return true;
					}
			}
		</script>";

        //get the file
        if (!($_FILES["teledeploy_file"]["size"] == 0 && $protectedPost['ACTION'] == 'EXECUTE')) {
            $size = filesize($_FILES["teledeploy_file"]["tmp_name"]);
            //crypt the file
            $digest = crypt_file($_FILES["teledeploy_file"]["tmp_name"], $protectedPost["digest_algo"], $protectedPost["digest_encod"]);
            //create temp file
            creat_temp_file($protectedPost['document_root'] . $protectedPost['timestamp'], $_FILES["teledeploy_file"]["tmp_name"]);
        }
        $digName = $protectedPost["digest_algo"] . " / " . $protectedPost["digest_encod"];

        $title_creat = "<h4>" . $l->g(435) . ": " . "[" . $protectedPost['NAME'] . "]</h4><br/>";
        $name_file = $l->g(446) . ": " . $_FILES["teledeploy_file"]["name"] . "<br/>";
        $ident = $l->g(460) . ": " . $protectedPost['timestamp'] . "<br/>";
        $view_digest = $l->g(461) . ": " . $digName . " " . $digest . "<br/>";
        $total_ko = $l->g(462) . ": " . round($size / 1024) . " " . $l->g(516) . "<br/><br/>";

        echo $title_creat . $name_file . $ident . $view_digest . $total_ko;

        // INPUT
        input_pack_taille("tailleFrag", "nbfrags", round($size), '8', round($size / 1024), $l->g(463), $l->g(516));
        input_pack_taille("nbfrags", "tailleFrag", round($size), '5', '1', $l->g(464), '<span class="glyphicon glyphicon-th-large"></span>');
        time_deploy($l->g(1002));
        $java_script = "verif2();";

        if ($protectedPost['REDISTRIB_USE'] == 1) {
            echo "<br />";
            echo "<h4>" . $l->g(1003) . "</h4>";
            input_pack_taille("tailleFrag_redistrib", "nbfrags_redistrib", round($size), '8', round($size / 1024), $l->g(463), $l->g(516));
            input_pack_taille("nbfrags_redistrib", "tailleFrag_redistrib", round($size), '5', '1', $l->g(464), '<span class="glyphicon glyphicon-th-large"></span>');
            $java_script = "verif_redistributor();";
        }

        echo "<input type='button' class='btn btn-success' name='TEST_END' id='TEST_END' OnClick='" . $java_script . "' value='" . $l->g(13) . "'>";
        echo "<input type='hidden' name='digest' value='" . $digest . "'>";
        echo "<input type='hidden' name='VALID_END' id='VALID_END' value=''>";
        echo "<input type='hidden' name='SIZE' value='" . $size . "'>";
    }
}

$default_value = array(
    'OS' => 'WINDOWS',
    'PROTOCOLE' => 'HTTP',
    'PRIORITY' => '5',
    'ACTION' => 'STORE',
    'REDISTRIB_PRIORITY' => '5'
);

if (!$protectedPost) {
    //get timestamp
    $protectedPost['timestamp'] = time();

    foreach ($default_value as $key => $value) {
        $protectedPost[$key] = $value;
    }
    $val_document_root = look_config_default_values(array('DOWNLOAD_PACK_DIR'));
    if (isset($val_document_root["tvalue"]['DOWNLOAD_PACK_DIR'])) {
        $document_root = $val_document_root["tvalue"]['DOWNLOAD_PACK_DIR'] . "/download/";
    } else {
        //if no directory in base, take $_SERVER["DOCUMENT_ROOT"]
        $document_root = VARLIB_DIR . '/download/';
    }

    $rep_exist = file_exists($document_root);
    //create directory if it's not exist
    if (!$rep_exist) {
        $creat = @mkdir($document_root);
        if (!$creat) {
            msg_error($document_root . "<br>" . $l->g(1004) . ".<br>" . $l->g(1005));
            return;
        }
    }
    //apache user can be write in this directory?
    $rep_ok = is_writable($document_root);
    if (!$rep_ok) {
        msg_error($l->g(1007) . " " . $document_root . " " . $l->g(1004) . ".<br>" . $l->g(1005));
        return;
    }
    $protectedPost['document_root'] = $document_root;
}

echo "<input type='hidden' name='document_root' value='" . $protectedPost['document_root'] . "'>
	 <input type='hidden' id='timestamp' name='timestamp' value='" . $protectedPost['timestamp'] . "'>";
echo "<script language='javascript'>
		function changeLabelAction(){
            var displayText = {'EXECUTE' : '" . $l->g(444) . "', 'STORE' : '" . $l->g(445) . "', 'LAUNCH' : '" . $l->g(446) . "'};
			var select = $(\"#ACTION :selected \");
			var label = $(\"label[for='ACTION_INPUT']\");

			switch(select.val()){
				case '0':
					label.html(displayText.EXECUTE);
					break;
				case '1':
					label.html(displayText.STORE);
					break;
				case '2':
					label.html(displayText.LAUNCH);
					break;
				default:
					label.html('ERROR');
			}
		}
		function verif()
		 {
			var msg = '';
			champs = ['NAME','DESCRIPTION','OS','PROTOCOLE','PRIORITY','ACTION','ACTION_INPUT','REDISTRIB_USE'];
			champs_OS = ['NOTIFY_USER','NEED_DONE_ACTION'];
			champs_ACTION=new Array('teledeploy_file');
			champs_REDISTRIB_USE=new Array('REDISTRIB_PRIORITY');
			champs_NOTIFY_USER=['NOTIFY_TEXT','NOTIFY_COUNTDOWN','NOTIFY_CAN_ABORT','NOTIFY_CAN_DELAY'];
			champs_NEED_DONE_ACTION=new Array('NEED_DONE_ACTION_TEXT');

			for (var n = 0; n < champs.length; n++)
			{
				if (document.getElementById(champs[n]).value == ''){
				 document.getElementById(champs[n]).style.backgroundColor = 'RED';
				 msg='NULL';
				 }
				else
				 document.getElementById(champs[n]).style.backgroundColor = '';
			}

			for (var n = 0; n < champs_OS.length; n++)
			{
				if (document.getElementById('OS').value == 'WINDOWS' && document.getElementById(champs_OS[n]).value == ''){
				 document.getElementById(champs_OS[n]).style.backgroundColor = 'RED';
				 msg='NULL';
				 }
				else
				 document.getElementById(champs_OS[n]).style.backgroundColor = '';
			}
			for (var n = 0; n < champs_ACTION.length; n++)
			{
				var name_file=document.getElementById(champs_ACTION[n]).value;
				name_file=name_file.toUpperCase();
				if (document.getElementById(\"OS\").value == 'WINDOWS')
					var debut=name_file.length-3;
				else
					var debut=name_file.length-6;
				if (document.getElementById('ACTION').value != 'EXECUTE' && document.getElementById(champs_ACTION[n]).value == ''){
					alert('" . $l->g(602) . "');
				 	document.getElementById(champs_ACTION[n]).style.backgroundColor = 'RED';
				 	msg='NULL';
				 }
				else if (document.getElementById('ACTION').value != 'EXECUTE' && name_file.substring(debut,name_file.length) != 'ZIP' && document.getElementById(\"OS\").value == 'WINDOWS'){
					alert('" . $l->g(1231) . "');
					document.getElementById(champs_ACTION[n]).style.backgroundColor = 'RED';
					msg='NULL';
				}else if (document.getElementById('ACTION').value != 'EXECUTE' && name_file.substring(debut,name_file.length) != 'TAR.GZ' && document.getElementById(\"OS\").value != 'WINDOWS'){
					alert('" . $l->g(1232) . "');
					document.getElementById(champs_ACTION[n]).style.backgroundColor = 'RED';
					msg='NULL';
				}
				 document.getElementById(champs_ACTION[n]).style.backgroundColor = '';
			}

			for (var n = 0; n < champs_REDISTRIB_USE.length; n++)
			{
				if (document.getElementById('REDISTRIB_USE').value == 1 && document.getElementById(champs_REDISTRIB_USE[n]).value == ''){
				 document.getElementById(champs_REDISTRIB_USE[n]).style.backgroundColor = 'RED';
				 msg='NULL';
				 }
				else
				 document.getElementById(champs_REDISTRIB_USE[n]).style.backgroundColor = '';
			}

			for (var n = 0; n < champs_NOTIFY_USER.length; n++)
			{
				if (document.getElementById('NOTIFY_USER').value == 1 && document.getElementById(champs_NOTIFY_USER[n]).value == ''){
				 document.getElementById(champs_NOTIFY_USER[n]).style.backgroundColor = 'RED';
				 msg='NULL';
				 }
				else
				 document.getElementById(champs_NOTIFY_USER[n]).style.backgroundColor = '';
			}

			for (var n = 0; n < champs_NEED_DONE_ACTION.length; n++)
			{
				if (document.getElementById('NEED_DONE_ACTION').value == 1 && document.getElementById(champs_NEED_DONE_ACTION[n]).value == ''){
				 document.getElementById(champs_NEED_DONE_ACTION[n]).style.backgroundColor = 'RED';
				 msg='NULL';
				 }
				else
				 document.getElementById(champs_NEED_DONE_ACTION[n]).style.backgroundColor = '';
			}

			if (msg != ''){
			alert ('" . $l->g(1001) . "');
			return false;
			}else
			return true;
		}
	</script>";

echo "<div ";
if ($protectedPost['valid'])
    echo " style='display:none;'";
echo ">";

$arrayName = array(
    "os" => $l->g(25),
    "name" => $l->g(49),
    "visible" => $l->g(52),
    "description" => $l->g(53),
    "package_name" => $l->g(438),
    "proto" => $l->g(439),
    "prio" => $l->g(440),
    "action" => $l->g(443),
    "title_user_notif" => $l->g(447),
    "warn_user" => $l->g(448),
    "notify_text" => $l->g(449),
    "notify_countdown" => $l->g(450),
    "user_can_abort" => $l->g(451),
    "user_can_delay" => $l->g(452),
    "need_user_action" => $l->g(453),
    "file" => $l->g(549),
    "title_redistribution" => $l->g(628),
    "redistribution" => $l->g(1008),
    "path_remote_server" => $l->g(1009)
);
$config_input = array(
    'MAXLENGTH' => 255,
    'SIZE' => 50
);
$list_os = array("WINDOWS", "LINUX", "MAC");
$list_proto = array("HTTP");

$arrayName = [
	"os" => $l->g(25),
	"name" => $l->g(49),
	"visible" => $l->g(52),
	"description" => $l->g(53),
	"package_name" => $l->g(438),
	"proto" => $l->g(439),
	"prio" => $l->g(440),
	"action" => $l->g(443),
	"title_user_notif" => $l->g(447),
	"warn_user" => $l->g(448),
	"notify_text" => $l->g(449),
	"notify_countdown" => $l->g(450),
	"user_can_abort" => $l->g(451),
	"user_can_delay" => $l->g(452),
	"need_user_action" => $l->g(453),
	"file" => $l->g(549),
	"title_redistribution" => $l->g(628),
	"redistribution" => $l->g(1008),
	"path_remote_server" => $l->g(1009)
];
$config_input=[
	'MAXLENGTH'=>255,
	'SIZE'=>50
];
$list_os = [
	"WINDOWS" => "WINDOWS",
	"LINUX" => "UNIX/LINUX",
	"MAC" => "MACOS"
];
$list_proto = ["HTTP"];

$i = 0;
while ($i < 10) {
    $list_prio["$i"] = "$i";
    $i++;
}

$yes_no = array("0", "1");

$list_action = [
	"EXECUTE" => $l->g(456),
	"STORE" => $l->g(457),
	"LAUNCH" => $l->g(458)
];

$arrayDisplayValue = [
	"yes_no" => [
		"0" => $l->g(454),
		"1" => $l->g(455)
	],
];
$list_action = array("EXECUTE", "STORE", "LAUNCH");

$arrayDisplayValue = array(
    "ACTION" => array(
        "EXECUTE" => $l->g(456),
        "STORE" => $l->g(457),
        "LAUNCH" => $l->g(458)
    ),
    "yes_no" => array(
        "0" => $l->g(454),
        "1" => $l->g(455)
    ),
    "OS" => array(
        "WINDOWS" => "WINDOWS",
        "LINUX" => "UNIX/LINUX",
        "MAC" => "MACOS"
    )
);

formGroup('text', 'NAME', $arrayName['name'], $config_input['SIZE'], $config_input['MAXLENGTH'], $protectedPost['NAME']);

formGroup('text', 'DESCRIPTION', $arrayName['description'], $config_input['MAXLENGTH'], $protectedPost['DESCRIPTION']);
formGroup('select', 'OS', $arrayName['os'], '', $config_input['MAXLENGTH'], $protectedPost, '', $list_os, $list_os, "onchange='active(\"OS_div\", this.value==\"WINDOWS\");' ");
formGroup('select', 'PROTOCOLE', $arrayName['proto'], '', $config_input['MAXLENGTH'], $protectedPost, '', $list_proto, $list_proto);
formGroup('select', 'PRIORITY', $arrayName['prio'], '', $config_input['MAXLENGTH'], $protectedPost, '', $list_prio, $list_prio);
formGroup('file', 'teledeploy_file', $arrayName['file'], '', $config_input['MAXLENGTH'], $protectedPost['teledeploy_file'], '', '', "accept='archive/zip'");
formGroup('select', 'ACTION', $arrayName['action'], '', $config_input['MAXLENGTH'], $protectedPost, '', $list_action, $list_action, "onchange='changeLabelAction()' ");
formGroup('text', 'ACTION_INPUT', $l->g(444), '' ,$config_input['MAXLENGTH'], $protectedPost['ACTION_INPUT']);

echo "<br />";
echo "<h4>" . $arrayName['title_redistribution'] . "</h4>";
echo "<br />";

//redistrib
if ($_SESSION['OCS']["use_redistribution"] == 1) {

    $sql = "select NAME,TVALUE from config where NAME ='DOWNLOAD_REP_CREAT'
		  union select NAME,TVALUE from config where NAME ='DOWNLOAD_SERVER_DOCROOT'";
    $resdefaultvalues = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
    while ($item = mysqli_fetch_object($resdefaultvalues)) {
        $default[$item->NAME] = $item->TVALUE;
    }
    if (!$default['DOWNLOAD_REP_CREAT']) {
        $default['DOWNLOAD_REP_CREAT'] = $_SERVER["DOCUMENT_ROOT"] . "/download/server/";
    }
}
?>
<script type="text/javascript">
    function redistributeUse() {
        active("REDISTRIB_USE_div", $('#REDISTRIB_USE').val());
    }
    function notifyUser() {
        active("NOTIFY_USER_div", $('#NOTIFY_USER').val());
    }
    function needDoneAction() {
        active("NEED_DONE_ACTION_div", $('#NEED_DONE_ACTION').val());
    }
</script>
<?php
formGroup('select', 'REDISTRIB_USE', $arrayName['redistribution'], $config_input['MAXLENGTH'], $config_input['MAXLENGTH'], $protectedPost['REDISTRIB_USE'], '', [0,1], [0 => 'No', 1 => 'Yes'], "onchange='redistributeUse()' ");
?>
<div id="REDISTRIB_USE_div" style="display: none;">
    <?php
    formGroup('text', 'DOWNLOAD_SERVER_DOCROOT', $arrayName['path_remote_server'], $config_input['MAXLENGTH'], $config_input['MAXLENGTH'], $protectedPost['DOWNLOAD_SERVER_DOCROOT'], '', $list_prio);
    formGroup('select', 'REDISTRIB_PRIORITY', $arrayName['prio'], $config_input['MAXLENGTH'], $config_input['MAXLENGTH'], $protectedPost, '', $list_prio, $list_prio);
    echo "</div>";

    echo "<br />";
    echo "<div id='OS_div'>";
    echo "<h4>" . $arrayName['title_user_notif'] . "</h4>";
    echo "<br />";

    formGroup('select', 'NOTIFY_USER', $arrayName['warn_user'], $config_input['MAXLENGTH'], $config_input['MAXLENGTH'], $protectedPost['NOTIFY_USER'], '', array(0, 1), array(0 => 'No', 1 => 'Yes'), "onchange='notifyUser()'");
    ?>
    <div id="NOTIFY_USER_div" style="display: none;">
        <?php
        formGroup('text', 'NOTIFY_TEXT', $arrayName['notify_text'], '', '', $protectedPost['NOTIFY_TEXT']);
        formGroup('text', 'NOTIFY_COUNTDOWN', $arrayName['notify_countdown'], 4, 4, $protectedPost['NOTIFY_TEXT'], '', '', '', ' onkeypress="return scanTouche(event,/[0-9]/);" onkeydown="convertToUpper(this);" onkeyup="convertToUpper(this);" onblur="convertToUpper(this);" onclick="convertToUpper(this);"', $l->g(511));
        formGroup('select', 'NOTIFY_CAN_ABORT', $arrayName['user_can_abort'], '', '', $protectedPost['NOTIFY_CAN_ABORT'], '', array(0, 1), array(0 => 'No', 1 => 'Yes'));
        formGroup('select', 'NOTIFY_CAN_DELAY', $arrayName['user_can_delay'], '', '', $protectedPost['NOTIFY_CAN_ABORT'], '', array(0, 1), array(0 => 'No', 1 => 'Yes'));
        ?>
    </div>
    <?php
    formGroup('select', 'NEED_DONE_ACTION', $arrayName['need_user_action'], $config_input['MAXLENGTH'], $config_input['MAXLENGTH'], $protectedPost['NEED_DONE_ACTION'], '', array(0, 1), array(0 => 'No', 1 => 'Yes'), "onchange='needDoneAction()'");
    ?>
    <div id="NEED_DONE_ACTION_div" style="display: none;">
        <?php
        formGroup('text', 'NEED_DONE_ACTION_TEXT', $arrayName['notify_text'], '', '', $protectedPost['NEED_DONE_ACTION_TEXT']);
        ?>
    </div>

</div>
<input type='submit' name='valid' id='valid' class="btn btn-success" value='<?php echo $l->g(13) ?>' OnClick='return verif();'>
<input type='hidden' id='digest_algo' name='digest_algo' value='MD5'>
<input type='hidden' id='digest_encod' name='digest_encod' value='Hexa'>
<input type='hidden' id='download_rep_creat' name='download_rep_creat' value='<?php echo $default['DOWNLOAD_REP_CREAT'] ?>'>

</div></div>
<?php
echo close_form();
?>