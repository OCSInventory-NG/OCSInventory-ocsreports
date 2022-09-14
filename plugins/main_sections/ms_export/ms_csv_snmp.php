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

$values = look_config_default_values(array('EXPORT_SEP'));
if (is_defined($values['tvalue']['EXPORT_SEP'])) {
    $separator = $values['tvalue']['EXPORT_SEP'];
} else {
    $separator = ';';
}

$toBeWritten = "";

// Export all snmp data
if(isset($protectedGet['tablename']) && $protectedGet['tablename'] == "all_snmp_export") {
	require('require/snmp/Snmp.php');
	$snmp = new OCSSnmp();

	// Get all type registered
	$snmpType = $snmp->get_all_type();

    if(!empty($snmpType)) {
        foreach($snmpType as $id => $values) {
            // First line -> type name
            $toBeWritten .= $values['TYPENAME'].$separator."\r\n";
            // Seconde line -> column names
            $colums = $snmp->show_columns($values['TABLENAME']);
            if(!empty($colums)) {
                foreach($colums as $name) {
                    $toBeWritten .= $name.$separator;
                }
                $toBeWritten .= "\r\n";
            }
            // Next lines -> datas
            $details = $snmp->getDetails($values['TABLENAME'], 0, true);
            if(!empty($details)) {
                foreach($details as $detail) {
                    foreach($detail as $columnName => $columnValue) {
                        if($columnName != "ID") {
                            $toBeWritten .= "\"".$columnValue."\"".$separator;
                        }
                    }
                    $toBeWritten .= "\r\n";
                }
            }

            $toBeWritten .= "\r\n";
        }
    }
}

// Get directory of log file
if (isset($protectedGet['log']) && !preg_match("/([^A-Za-z0-9.])/", $protectedGet['log'])) {
    $Directory = $_SESSION['OCS']['LOG_DIR'] . "/";
}

// Generate output page
if ($toBeWritten != "" || (isset($Directory) && file_exists($Directory . $protectedGet['log'])) ) {

    // Work around iexplorer problem
    if (ini_get("zlib.output-compression")) {
        ini_set("zlib.output-compression", "Off");
    }

    // Static headers
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-control: private", false);
    header("Content-type: application/force-download");
    header("Content-Transfer-Encoding: binary");

    if ($toBeWritten != "") {
		// Generate output page for DB data export
        header("Content-Disposition: attachment; filename=\"export.csv\"");
        header("Content-Length: " . strlen($toBeWritten));
        echo $toBeWritten;
    } else {
	    // Generate output page for log export
		$filename = $Directory . $protectedGet['log'];
        header("Content-Disposition: attachment; filename=\"" . $protectedGet['log'] . "\"");
        header("Content-Length: " . filesize($filename));
        readfile($filename);
    }

} else {

    // Nothing to export
    require_once (HEADER_HTML);
    msg_error($l->g(920));
    require_once(FOOTER_HTML);

}

die();

?>
