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

function get_profiles() {
    $profiles = array();
    $serializer = new XMLProfileSerializer();
    $profilesRoot = PROFILES_DIR;

    // Scan and parse each profile file
    foreach (scandir($profilesRoot) as $file) {
        $path = $profilesRoot . '/' . $file;
        $parts = pathinfo($path);

        // Check if it is an XML file
        if ($parts['extension'] === 'xml' && is_file($path)) {
            $profiles[$parts['filename']] = $serializer->unserialize($parts['filename'], file_get_contents($path));
        }
    }

    return $profiles;
}

function get_profile_labels() {
    $labels = array();
    foreach (get_profiles() as $name => $profile) {
        $labels[$name] = $profile->getLabelTranslated();
    }
    return $labels;
}

//Function to delete one or an array of user
function delete_list_user($list_to_delete) {
    $table = array('tags' => 'login', 'operators' => 'id');

    foreach ($table as $table_name => $field) {
        $arg_sql = array($table_name, $field);
        $sql_delete = "delete from %s where %s in ";
        $sql_delete = mysql2_prepare($sql_delete, $arg_sql, $list_to_delete);
        mysql2_query_secure($sql_delete['SQL'], $_SESSION['OCS']["writeServer"], $sql_delete['ARG']);
    }
}

function add_user($data_user, $list_profil = '') {
    global $l;

    $passwordConfig = look_config_default_values(['SECURITY_PASSWORD_ENABLED', 'SECURITY_PASSWORD_MIN_CHAR', 'SECURITY_PASSWORD_FORCE_NB', 'SECURITY_PASSWORD_FORCE_UPPER', 'SECURITY_PASSWORD_FORCE_SPE_CHAR']);

    if (isset($data_user['PASSWORD'])) {
        $password = $data_user['PASSWORD'];
    }
    $data_user = strip_tags_array($data_user);

    //Password check
    if ($passwordConfig['ivalue']['SECURITY_PASSWORD_ENABLED'] == 1){
        if ($passwordConfig['ivalue']['SECURITY_PASSWORD_MIN_CHAR'] > strlen($password)){
            $ERROR = $l->g(1496) . " " . $passwordConfig['ivalue']['SECURITY_PASSWORD_MIN_CHAR'] . " " . $l->g(1458);
        }
        if ($passwordConfig['ivalue']['SECURITY_PASSWORD_FORCE_NB'] == 1){
            if (!preg_match('/[0-9]/', $password)){
                $ERROR = $l->g(1498);
            }
        }
        if ($passwordConfig['ivalue']['SECURITY_PASSWORD_FORCE_UPPER'] == 1){
            if (!preg_match('/[A-Z]/', $password)){
                $ERROR = $l->g(1497);
            }
        }
        if ($passwordConfig['ivalue']['SECURITY_PASSWORD_FORCE_SPE_CHAR'] == 1){
            if (strpbrk($password, '*.! @#$%^&(){}[]:;<>,.?/~_+-=|\\') === false){
                $ERROR = $l->g(1499);
            }
        }
    }

    // Name ok ?
    if (trim($data_user['FIRSTNAME']) == "") {
        $ERROR = $l->g(1391) . ' : ' . $l->g(1366);
    }
    // Password ok ?
    if (trim($data_user['PASSWORD']) == "" && (isset($data_user['MODIF']) && trim($data_user['MODIF']) == "")) {
        $ERROR = $l->g(1391) . ' : ' . $l->g(217);
    }
    // Login ok ?
    if (trim($data_user['ID']) == "") {
        $ERROR = $l->g(997);
    }

    if (is_array($list_profil)) {
        if (!array_key_exists($data_user['ACCESSLVL'], $list_profil)) {
            $ERROR = $l->g(998);
        }
    }
    if(isset($ERROR) && $ERROR == ""){
        unset($ERROR);
    }
    if (!isset($ERROR)) {
        $sql = "select id from operators where id= '%s'";
        $arg = $data_user['ID'];
        $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
        $row = mysqli_fetch_object($res);
        if (isset($row->id)) {
            if ($data_user['MODIF'] != $row->id) {
                return $l->g(999);
            } else {
                $sql_update = "update operators
								set firstname = '%s',
									lastname='%s',
									new_accesslvl='%s',
									email='%s',
									comments='%s',
									user_group='%s'";
                $arg_update = array($data_user['FIRSTNAME'],
                    $data_user['LASTNAME'],
                    $data_user['ACCESSLVL'],
                    $data_user['EMAIL'],
                    $data_user['COMMENTS'],
                    $data_user['USER_GROUP'] ?? '');
                if (is_defined($data_user['PASSWORD'])) {
                    $sql_update .= ", passwd ='%s' , password_version ='%s' ";
                    $arg_update[] = hash(PASSWORD_CRYPT, $password);
                    $arg_update[] = $_SESSION['OCS']['PASSWORD_VERSION'];
                }
                $sql_update .= "	 where ID='%s'";
                $arg_update[] = $row->id;
                mysql2_query_secure($sql_update, $_SESSION['OCS']["writeServer"], $arg_update);
                return $l->g(374);
            }
        } else {
            $sql = " insert into operators (id,firstname,lastname,new_accesslvl,email,comments,user_group";
            if (isset($password)) {
                $sql .= ",passwd";
                $sql .= ",password_version";
            }
            $sql .= ") value ('%s','%s','%s','%s','%s','%s','%s'";

            $arg = array($data_user['ID'], $data_user['FIRSTNAME'],
                $data_user['LASTNAME'],
                $data_user['ACCESSLVL'],
                $data_user['EMAIL'],
                $data_user['COMMENTS'],
                $data_user['USER_GROUP'] ?? '');
            if (isset($password)) {
                $sql .= ",'%s','%s'";
                $arg[] = hash(PASSWORD_CRYPT, $password);
                $arg[] = $_SESSION['OCS']['PASSWORD_VERSION'];
            }
            $sql .= ")";
            mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
            return $l->g(373);
        }
    } else {
        return $ERROR;
    }
}

function admin_user($id_user = null, $is_my_account = false) {
    global $protectedPost, $l, $pages_refs;

    $tab_hidden = array();
    $list_groups = array();

    if ($id_user) {
        $update = 3;
    } else {
        $update = 0;
    }

    if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_USER_GROUP') == 'YES') {
      //search all profil type
      $list_profil = get_profile_labels();
      $list_groups_result = look_config_default_values("USER_GROUP_%", 'LIKE');
      if (!empty($list_groups_result['name']) && is_array($list_groups_result['name'])) {
          foreach ($list_groups_result['name'] as $key => $value) {
              $list_groups[$list_groups_result['ivalue'][$key]] = $list_groups_result['tvalue'][$key];
          }
      }
        $name_field = array("ID", "ACCESSLVL", "USER_GROUP");
        $tab_name = array($l->g(995) . " :", $l->g(66) . " :", $l->g(607) . " :");
        $type_field = array($update, 2, 2);
    }else{
        $name_field = array("ID", "ACCESSLVL");
        $tab_name[]=" ";
        $tab_name[]=" ";
        $type_field = array(3, 3);
    }

    $name_field[] = "FIRSTNAME";
    $name_field[] = "LASTNAME";
    $name_field[] = "EMAIL";
    $name_field[] = "COMMENTS";
    //$name_field[]="USER_GROUP";

    $tab_name[]=$l->g(1366)." :";
    $tab_name[]=$l->g(996)." :";
    $tab_name[]=$l->g(1117)." :";
    $tab_name[]=$l->g(51)." :";
    //$tab_name[]="Groupe de l'utilisateur: ";

    $type_field[]= 0;
    $type_field[]= 0;
    $type_field[]= 0;
    $type_field[]= 0;
    //$type_field[]= 2;

    $tab_hidden['MODIF']=$id_user;
    $sql="select ID,NEW_ACCESSLVL,USER_GROUP,FIRSTNAME,LASTNAME,EMAIL,COMMENTS from operators where id= '%s'";
    $arg=$id_user;
    $res=mysql2_query_secure($sql, $_SESSION['OCS']["readServer"],$arg);
    $row=mysqli_fetch_object($res);
    if ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_USER_GROUP') == 'YES'){
            $protectedPost['ACCESSLVL'] = $row->NEW_ACCESSLVL ?? null;
            $protectedPost['USER_GROUP'] = $row->USER_GROUP ?? null;
            $value_field = array($row->ID ?? null, $list_profil, $list_groups);
    }else{
        $protectedPost['ACCESSLVL'] = $row->NEW_ACCESSLVL ?? null;
        $value_field = array($row->ID ?? null, $protectedPost['ACCESSLVL']);
    }

    $value_field[] = $row->FIRSTNAME ?? "";
    $value_field[] = $row->LASTNAME ?? "";
    $value_field[] = $row->EMAIL ?? "";
    $value_field[] = $row->COMMENTS ?? "";

    $name_field[] = "PASSWORD";
    $type_field[] = 4;
    $tab_name[] = $l->g(217)." :";
    $value_field[] = $protectedPost['PASSWORD'] ?? "";

    $tab_typ_champ = show_field($name_field,$type_field,$value_field);
    foreach ($tab_typ_champ as $id=>$values){
            $tab_typ_champ[$id]['CONFIG']['SIZE']=40;
    }
    if ($_SESSION['OCS']['profile']->getConfigValue('MANAGE_USER_GROUP') == 'YES'){
            $tab_typ_champ[2]["CONFIG"]['DEFAULT']="YES";
    //	$tab_typ_champ[1]['COMMENT_AFTER']="<a href=# onclick=window.open(\"index.php?".PAG_INDEX."=".$pages_refs['ms_admin_profil']."&head=1\",\"admin_profil\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")><img src=image/plus.png></a>";
            $tab_typ_champ[2]['COMMENT_AFTER']="<a href=\"index.php?".PAG_INDEX."=".$pages_refs['ms_adminvalues']."&head=1&tag=USER_GROUP\",\"admin_user_group\",\"location=0,status=0,scrollbars=0,menubar=0,resizable=0,width=550,height=450\")><img src=image/plus.png></a>";
    }

    if (isset($tab_typ_champ)) {

        foreach ($tab_name as $index => $fields){

            $indexType = $tab_typ_champ[$index]['INPUT_TYPE'];

            $selectValues = '';

            $inputName = $tab_typ_champ[$index]['INPUT_NAME'];
            $inputValue = $protectedPost[$inputName] ?? null;

            if($indexType == 0 ||
                    $indexType == 1 ||
                    $indexType == 6 ||
                    $indexType == 10
            ){
                    $inputType = 'text';
            } else if($indexType == 2){
                    $inputType = 'select';
                    $selectValues = $tab_typ_champ[$index]['DEFAULT_VALUE'];
                    $inputValue = $protectedPost;
                    // If data is sended with post
                    if(isset($protectedPost[$inputName])){
                        $tab_typ_champ[$index]['DEFAULT_VALUE'] = array($inputName => $protectedPost[$inputName]);
                    }
            } else if($indexType == 3){
                    $inputType = 'hidden';
            } else if($indexType == 4){
                    $inputType = 'password';
            } else if($indexType == 5){
                    $inputType = 'checkbox';
            } else if($indexType == 8){
                    $inputType = 'button';
            } else if($indexType == 9){
                    $inputType = 'link';
            } else {
                    $inputType = 'hidden';
            }

            if($id_user != null){
            formGroup('hidden', 'MODIF', '', '', '', $id_user, '', '', '', '', '');
            }

            if($inputName == "ID" && $id_user != null && ($_SESSION['OCS']['profile']->getConfigValue('CHANGE_USER_GROUP') == 'YES')){
                formGroup('text', $inputName, $fields, '', '', $id_user, '', $selectValues, $selectValues, 'readonly', '');
              }else{
                if(empty($tab_typ_champ[$index]['COMMENT_AFTER'])){
                    $tab_typ_champ[$index]['COMMENT_AFTER'] = "";
                  }
                  if($inputType != 'select'){
                    formGroup($inputType, $inputName, $fields, '', '', $tab_typ_champ[$index]['DEFAULT_VALUE'], '', $selectValues, $selectValues, '' , $tab_typ_champ[$index]['COMMENT_AFTER']);
                  }else{
                    formGroup($inputType, $inputName, $fields, '', '', $protectedPost[$inputName], '', $selectValues, $selectValues, '' , $tab_typ_champ[$index]['COMMENT_AFTER']);
                  }
              }
        }
    }
}

/**
 * updatePasswordMd5toHash
 *
 * @param  string $login
 * @param  string $mdp
 * @return boolean $result
 */
function updatePasswordMd5toHash($login, $mdp) {
    $sql = "SELECT ID FROM operators WHERE ID = '%s'";
    $arg = array($login);

    $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    $row = mysqli_fetch_object($res);

    if (isset($row->ID)) {
        if (is_defined($mdp)) {
            $sql_update = "UPDATE operators SET PASSWD ='%s', PASSWORD_VERSION ='%s' WHERE ID = '%s'";
            $sql_arg = array(hash(PASSWORD_CRYPT, $mdp), $_SESSION['OCS']['PASSWORD_VERSION'], $row->ID);
            $res = mysql2_query_secure($sql_update, $_SESSION['OCS']["writeServer"], $sql_arg);

            if($res) {
                return true;
            }
        }
    }

    return false;
}

?>
