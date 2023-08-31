#!/usr/bin/php
<?php
require_once(__DIR__.'/../var.php');
require_once(CONF_MYSQL);
require_once(ETC_DIR.'/require/function_commun.php');
require_once(ETC_DIR.'/require/cve/Cve.php');
require_once(ETC_DIR.'/require/config/include.php');
require_once(ETC_DIR.'/require/fichierConf.class.php');

$_SESSION['OCS']["writeServer"] = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);

$cve = new Cve();
$date = null;
$clean = false;

//Check if CVE is activate
if($cve->CVE_ACTIVE == 1) {
    print("[".date("Y-m-d H:i:s"). "] Please wait, CVE computer processing is in progress. It could take a few minutes\n");
    
    $sql = "TRUNCATE TABLE `cve_search_computer`";
    mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"]);

    $configToLookOut = [
        'EXCLUDE_ARCHIVE_COMPUTER' => 'EXCLUDE_ARCHIVE_COMPUTER'
    ];

    $configValues = look_config_default_values($configToLookOut)['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'] ?? '';

    $sql = 'SELECT *, p.PUBLISHER, c.LINK as id, h.NAME as computer, h.ID as computerid, n.NAME as softname
                FROM cve_search c 
                LEFT JOIN software_name n ON n.ID = c.NAME_ID
                LEFT JOIN software_publisher p ON p.ID = c.PUBLISHER_ID
                LEFT JOIN software_version v ON v.ID = c.VERSION_ID
                LEFT JOIN software s ON s.NAME_ID = n.ID AND p.ID = s.PUBLISHER_ID AND v.ID = s.VERSION_ID
                INNER JOIN hardware h ON h.ID = s.HARDWARE_ID';

    if($configValues == 1) {
        $sql .= " WHERE h.ARCHIVE IS NULL";
    }

    $sql .= ' GROUP BY h.ID, c.LINK, c.CVSS, c.NAME_ID, c.CVE';

    $response = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], []);

    $_SESSION['OCS']['DEBUG'] = "ON";

    while ($value = mysqli_fetch_array($response)) {
        $sql_insert = "INSERT INTO `cve_search_computer` (`HARDWARE_ID`, `HARDWARE_NAME`, `PUBLISHER`, `VERSION`, `SOFTWARE_NAME`, `CVSS`, `CVE`, `LINK`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')";
        $arg_sql = array($value['computerid'], $value['computer'], $value['PUBLISHER'], $value['VERSION'], $value['softname'], $value['CVSS'], $value['CVE'], $value['LINK']);

        $res = mysql2_query_secure($sql_insert, $_SESSION['OCS']["writeServer"], $arg_sql);

        if (!$res){
            print("[".date("Y-m-d H:i:s"). "] ERROR when attempt to link ".$value['CVE']." with ".$value['computer']."\n");
        }
    }
    print("[".date("Y-m-d H:i:s"). "] End of process\n");
} else {
    $cve->verbose($cve->CVE_VERBOSE, 3);
    exit();
}

session_destroy();
?>
