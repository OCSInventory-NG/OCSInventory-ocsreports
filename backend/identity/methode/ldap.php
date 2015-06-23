<?php
/* This module automatically inserts valid LDAP users into OCS operators table.
 *
 * The userlevel is defined according to conditions defined in the following configuration fields:
 * 
 * - CONEX_LDAP_CHECK_FIELD1_NAME
 * - CONEX_LDAP_CHECK_FIELD1_VALUE
 * - CONEX_LDAP_CHECK_FIELD1_ROLE
 * - CONEX_LDAP_CHECK_FIELD2_NAME
 * - CONEX_LDAP_CHECK_FIELD2_VALUE
 * - CONEX_LDAP_CHECK_FIELD2_ROLE
 * 
 * If any of these attributes are defined (and found on the LDAP query), they're used to determine the correct
 * user level and role.
 * 
 * in case of success, an array is returned with the access data in the following format: 
 * array('accesslvl'=>%%,'tag_show'=>array(%,%,%,%,%...))
 * 
 * else, an error code is returned.
 * 
 * CONEX_LDAP_CHECK_FIELD1_NAME="thisGuyIsAdmin"
 * CONEX_LDAP_CHECK_FIELD1_VALUE="0"
 * CONEX_LDAP_CHECK_FIELD1_ROLE="user"
 * CONEX_LDAP_CHECK_FIELD2_NAME="thisGuyIsAdmin"
 * CONEX_LDAP_CHECK_FIELD2_VALUE="1"
 * CONEX_LDAP_CHECK_FIELD2_ROLE="sadmin"
 * In logical terms:
 * if thisGuyIsAdmin=0 then
 *    role=user
 * else if thisGuyIsAdmin=1 then
 *    role=sadmin
 *    
 *    Note: the default user levels in OCS currently are "admin", "ladmin" and "sadmin". The above is just an example.
 * 
 */
if ($_SESSION['OCS']['cnx_origine'] != "LDAP")
	return false;
	
require_once ('require/function_files.php');
// page name
$name="ldap.php";
connexion_local_read();

// select the main database
mysqli_select_db($link_ocs,$db_ocs);


// retrieve LDAP-related config values into an array
$sql="select substr(NAME,7) as NAME,TVALUE from config where NAME like '%s'";
$arg=array("%CONEX%");
$res=mysql2_query_secure($sql, $link_ocs,$arg);
while($item = mysqli_fetch_object($res)){
    $config[$item->NAME]=$item->TVALUE;
  //  define ($item->NAME,$item->TVALUE);
}

// checks if the user already exists 
$reqOp="SELECT new_accesslvl as accesslvl FROM operators WHERE id='%s'";
$argOp=array($_SESSION['OCS']["loggeduser"]);
$resOp=mysql2_query_secure($reqOp, $link_ocs,$argOp);

// defines the user level according to specific LDAP attributes
// default: normal user
$defaultRole=$config['LDAP_CHECK_DEFAULT_ROLE']; 
// Checks if the custom fields are valid
$f1_name=$config['LDAP_CHECK_FIELD1_NAME'];
$f2_name=$config['LDAP_CHECK_FIELD2_NAME'];
$f1_value=$_SESSION['OCS']['details'][$f1_name];
$f2_value=$_SESSION['OCS']['details'][$f2_name];
if ($f1_value != '')
{
    //NEW CODE BELOW
    //FIXME: casing? -> 'memberOf'
    if ($f1_name == "memberof")
    {
    //the idea here is to iterate through the groups array looking for a match
    //if we find it, unset the array and store only the match, else leave as it is
    foreach($f1_value as $group){
        if ($group == $config['LDAP_CHECK_FIELD1_VALUE'])
        {
        $f1_value=array();
        $f1_value=$group;
        }
    }
    }
    //the if below is now redundant since we already know that we have a match
    //the coding can be improved, but the logic works.
    //END NEW CODE
    if ($f1_value == $config['LDAP_CHECK_FIELD1_VALUE'])
    {
        $defaultRole=$config['LDAP_CHECK_FIELD1_ROLE'];
    }
}

if ($f2_value != '')
{
    //NEW CODE BELOW
    if ($f2_name == "memberof")
    {
        foreach($f2_value as $group){
            if ($group == $config['LDAP_CHECK_FIELD2_VALUE'])
            {
                $f2_value=array();
                $f2_value=$group;
            }
        }
    }
    //END NEW CODE
    if ($f2_value == $config['LDAP_CHECK_FIELD2_VALUE'])
    {
        $defaultRole=$config['LDAP_CHECK_FIELD2_ROLE'];
    }
}

// uncomment this section for DEBUG
// note: cannot use the global DEBUG variable because this happens before the toggle is available.
/*
    echo ("field1: ".$f1_name." value=".$f1_value." condition: ".$config['LDAP_CHECK_FIELD1_VALUE']." role=".$config['LDAP_CHECK_FIELD1_ROLE']." level=".$config['LDAP_CHECK_FIELD1_USERLEVEL']."<br>");
    echo ("field2: ".$item['CONEX_LDAP_CHECK_FIELD2_NAME']." value=".$f2_value." condition: ".$config['LDAP_CHECK_FIELD2_VALUE']." role=".$config['LDAP_CHECK_FIELD2_ROLE']." level=".$config['LDAP_CHECK_FIELD2_USERLEVEL']."<br>");
    echo ("user: ".$_SESSION['OCS']["loggeduser"]." will have level=".$defaultLevel." and role=".$defaultRole."<br>");
*/
//if defaultRole is define
if (isset($defaultRole) and $defaultRole != ''){
    // if it doesn't exist, create the user record
    if (!mysqli_fetch_object($resOp)) {
    
    
        $reqInsert="INSERT INTO operators (
            ID,
            FIRSTNAME,
            LASTNAME,
            PASSWD,
            COMMENTS,
            NEW_ACCESSLVL,
            EMAIL,
            USER_GROUP
                )
                VALUES ('%s','%s', '%s', '%s','%s', '%s', '%s', '%s')";
    
        $arg_insert=array($_SESSION['OCS']["loggeduser"],
                           $_SESSION['OCS']['details']['givenname'], 
                        $_SESSION['OCS']['details']['sn'], 
                           "",
                           "LDAP",
                           $defaultRole,
                           $_SESSION['OCS']['details']['mail'],
                           "NULL"
                            );
        
    }
    else
    {
    
        // else update it
        $reqInsert="UPDATE operators SET 
                        NEW_ACCESSLVL='%s',
                        EMAIL='%s'
                    WHERE ID='%s'";
        
        $arg_insert=array($defaultRole,
                             $_SESSION['OCS']['details']['mail'],
                             $_SESSION['OCS']["loggeduser"]);
    }
    connexion_local_write();
    // select the main database
    mysqli_select_db($link_ocs,$db_ocs);
    // Execute the query to insert/update the user record
    mysql2_query_secure($reqInsert,$link_ocs,$arg_insert);
    
    
    
    // repeat the query and define the needed OCS variables
    // note: original OCS code below
    connexion_local_read();
    
    // select the main database
    mysqli_select_db($link_ocs,$db_ocs);
    $resOp=mysql2_query_secure($reqOp, $link_ocs,$argOp);
    $rowOp=mysqli_fetch_object($resOp);
    if (isset($rowOp -> accesslvl)){
        $lvluser=$rowOp -> accesslvl;

        $profile_config = DOCUMENT_REAL_ROOT.'/config/profiles/'.$lvluser.'.xml';
		
		if (!file_exists($profile_config)) {
			migrate_config_2_2();
		}
		
        $profile_serializer = new XMLProfileSerializer();
        $profile = $profile_serializer->unserialize($lvluser, file_get_contents($profile_config));
        
        $restriction = $profile->getRestriction('GUI');
        
        //if this user has RESTRICTION
        //search all tag for this user
        if ($restriction == 'YES'){
            $sql="select tag from tags where login='%s'";
            $arg=array($_SESSION['OCS']["loggeduser"]);
            $res=mysql2_query_secure($sql, $link_ocs,$arg);
            while ($row=mysqli_fetch_object($res)){    
                $list_tag[$row->tag]=$row->tag;
            }
            if (!isset($list_tag))
                $ERROR=$l->g(893);
        }elseif (($restriction != 'NO')) 
        $ERROR=$restriction;
    }else
    $ERROR=$l->g(894);
}else
$ERROR=$l->g(1278);

?>