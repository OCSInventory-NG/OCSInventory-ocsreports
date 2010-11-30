<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Erwan GOALOU 2010 (erwan(at)ocsinventory-ng(pt)org)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

/* 
 * LDAP custom authentication module
 * 
 * This module will check and report if a LDAP user is valid based on the configuration supplied.
 * Adapted by Erico Mendonca <emendonca@novell.com> from original OCS code.
 * 
 * I'm fetching a few LDAP attributes to fill in the user record, namely sn,cn,givenname and mail.
 * 
 * 
 * 
 **/



connexion_local_read();
$sql="select substr(NAME,7) as NAME,TVALUE from config where NAME like '%s'";
$arg=array('%CONEX%');	
$res=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"],$arg);

while($item = mysql_fetch_object($res)){
	$config[$item->NAME]=$item->TVALUE;
	define ($item->NAME,$item->TVALUE);
}

// copies the config values to the session area
$_SESSION['OCS']['config']=$config;

$login_successful=verif_pw_ldap($login, $mdp);
$cnx_origine="LDAP";
$user_group="LDAP";
 
function verif_pw_ldap($login, $pw) { 
   $info = search_on_loginnt($login); 
   if ($info["nbResultats"]!=1) 
       return ("BAD LOGIN OR PASSWORD"); // login does't exist
   return (ldap_test_pw($info[0]["dn"], $pw) ? "OK" : "BAD LOGIN OR PASSWORD");   
} 
 
function search_on_loginnt($login) { 

    $f1_name=$_SESSION['OCS']['config']['LDAP_CHECK_FIELD1_NAME'];
    $f2_name=$_SESSION['OCS']['config']['LDAP_CHECK_FIELD2_NAME'];

    // default attributes for query 
    $attributs = array("dn", "cn", "givenname", "sn", "mail"); 

    // search for the custom user level attributes if they're defined
    if ($f1_name != '')
    {
        array_push($attributs, $f1_name);
    }

    if ($f2_name != '')
    {
        array_push($attributs, $f2_name);
    }

    $ds = ldap_connection (); 
    $filtre = "(".LOGIN_FIELD."={$login})"; 
    $sr = @ldap_search($ds,DN_BASE_LDAP,$filtre,$attributs); 
    $lce = @ldap_count_entries($ds,$sr); 
    $info = @ldap_get_entries($ds,$sr); 
    @ldap_close($ds); 
    $info["nbResultats"] = $lce;

    // save user fields in session
    $_SESSION['OCS']['details']['givenname']=$info[0]['givenname'][0];
    $_SESSION['OCS']['details']['sn']=$info[0]['sn'][0];
    $_SESSION['OCS']['details']['cn']=$info[0]['cn'][0];
    $_SESSION['OCS']['details']['mail']=$info[0]['mail'][0];

    // if the extra attributes are there, save them as well
    if ($info[0][$f1_name][0] != '') 
    {
        $_SESSION['OCS']['details'][$f1_name]=$info[0][$f1_name][0];
    }

    if ($info[0][$f2_name][0] != '') 
    {
        $_SESSION['OCS']['details'][$f2_name]=$info[0][$f2_name][0];
    }    

    return $info; 
} 
 
 
function ldap_test_pw($dn, $pw) { 
    $ds = ldap_connection (); 
    if (!$ds) { // avec ldap 2.x.x, ldap_connect est tjrs ok. La connection n'est ouverte qu'au bind 
        $r = false; 
    } else { 
        @ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTOCOL_VERSION); 
        $r = @ldap_bind($ds, $dn, $pw); 
        @ldap_close($ds); 
        return $r; 
    } 
} 

function ldap_connection (){
	$ds = ldap_connect(LDAP_SERVEUR,LDAP_PORT); 
	if (ROOT_DN != ''){
        $b = @ldap_bind($ds, ROOT_DN, ROOT_PW);         
    }else //Anonymous bind
        $b = @ldap_bind($ds);
    if (!$b)
        return false;
    else
        return $ds;
}

?>