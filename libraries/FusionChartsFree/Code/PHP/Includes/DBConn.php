<?php
// In this page, we open the connection to the Database
// Our MySQL database (blueprintdb) for the Blueprint Application
// Function to connect to the DB
function connectToDB() {
    // These four parameters must be changed dependent on your MySQL settings
    $hostdb = 'HostURL'; // MySQl host
    $userdb = 'username';  // MySQL username
    $passdb = 'password';  // MySQL password
    $namedb = 'factorydb'; // MySQL database name

// Please uncomment the appropriate statement
    $link = mysql_connect ("localhost:3306", "root", "");
    //$link = mysql_connect ($hostdb, $userdb, $passdb);
    //$link = mysql_connect ();

    if (!$link) {
        // we should have connected, but if any of the above parameters
        // are incorrect or we can't access the DB for some reason,
        // then we will stop execution here
        die('Could not connect: ' . mysql_error());
    }

    $db_selected = mysql_select_db($namedb);
    if (!$db_selected) {
        die ('Can\'t use database : ' . mysql_error());
    }
    return $link;
}
?>