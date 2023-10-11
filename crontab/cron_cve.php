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

$shortOptions = "c:h:d::";
$longOptions = array("chunk:","help::","debug::");

$options = getopt($shortOptions, $longOptions);

$chunk = 5000;
$debug = false;

if(array_key_exists("h", $options) || array_key_exists("help", $options)) {
    echo "Usage: php cron_cve.php [--] [args...]\n\n";
    echo "  -c, --chunk <number>    Process software publishers by pool of <number>, default 5000\n\n";
    echo "  -d, --debug             Display debug messages\n\n";
    exit();
}

if(array_key_exists("c", $options) || array_key_exists("chunk", $options)) {
    $chunk = $options["c"] ?? $options["chunk"];
}

if (array_key_exists("d", $options) || array_key_exists("debug", $options)) {
    $debug = true;
}

$cve = new Cve();
$cve->setDebug($debug);

$date = null;
$clean = false;

//Check if CVE is activate
if($cve->CVE_ACTIVE == 1) {

    if($cve->CVE_EXPIRE_TIME != null && $cve->CVE_EXPIRE_TIME != "" && $cve->CVE_EXPIRE_TIME != "0") {
        $date = date('Y/m/d H:i:s', time() - (3600 * $cve->CVE_EXPIRE_TIME));
        $clean = true;
        $cve->verbose("CVE_EXPIRE_TIME is set to ".$cve->CVE_EXPIRE_TIME." hour(s)", "DEBUG");
    }

    $curl = curl_init($cve->CVE_SEARCH_URL);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Uncomment if using a self-signed certificate on CVE server
    //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    curl_exec($curl);

    // Check if any error occured on cve-search server
    if(curl_errno($curl)) {
        $info = curl_getinfo($curl);
        $cve->verbose("Error when connecting to cve-search server: ".$cve->CVE_SEARCH_URL." (".$info['http_code'].")", "INFO");
        $cve->verbose("Curl error: ".curl_error($curl), "DEBUG");
        curl_close($curl);
        exit();
    } else {
        curl_close($curl);
        $cve->verbose("Connected to cve-search server: ".$cve->CVE_SEARCH_URL, "INFO");
        $cve->verbose("When using a self-signed certificate, you can disable SSL verification in cron_cve.php", "INFO");
        $cve->verbose("Debug mode is ".($debug ? "enabled" : "disabled"), "INFO");
        $cve->getSoftwareInformations($date, $clean, $chunk);
        $cve->verbose($this->cveNB." CVE have been added to database.", "INFO");
    }
} else {
    $cve->verbose("CVE feature isn't enabled.", "INFO");
    exit();
}

session_destroy();
?>
