<?php

/**
 * This script is used to synchronize SNMP types configuration of the database with an XML file containing types configuration (exported from another OCS server for example)
 * Script takes argument :
 * - --file= the XML file (path)
*/

require_once(__DIR__.'/../var.php');
require_once(CONF_MYSQL);
require_once(ETC_DIR.'/require/snmp/Snmp.php');
require_once(ETC_DIR.'/require/function_commun.php');
require_once(ETC_DIR.'/require/config/include.php');
require_once(ETC_DIR.'/require/fichierConf.class.php');

$_SESSION['OCS']["writeServer"] = dbconnect(SERVER_WRITE, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);


function get_arg_value($arg, $name) {
    if (strpos($arg, $name) !== false) {
        return str_replace($name, '', $arg);
    }
    return null;
}

function load_xml_file($xml_file) {
    if (!isset($xml_file)) {
        die("Error: XML file not provided");
    }

    $xml = simplexml_load_file($xml_file) or die("Error: Cannot create object");
    return $xml;
}

function build_xml_types_array($xml) {
    $xmlTypes = array();

    foreach ($xml->TYPE as $type) {
        // extract attributes
        $typeName = (string)$type['TYPE_NAME'];
        $conditionOid = (string)$type['CONDITION_OID'];
        $conditionValue = (string)$type['CONDITION_VALUE'];
        $tableTypeName = (string)$type['TABLE_TYPE_NAME'];
        $labelName = (string)$type['LABEL_NAME'];
        $oid = (string)$type['OID'];
        $reconciliation = (string)$type['RECONCILIATION'];
    
        // check if type already exists
        if (!isset($xmlTypes[$typeName])) {
            $xmlTypes[$typeName] = array();
        }
        // add this type's data to the array
        $xmlTypes[$typeName][] = array(
            'condition_OID' => $conditionOid,
            'value' => $conditionValue,
            'type_name' => $tableTypeName,
            'label_name' => $labelName,
            'OID' => $oid,
            'reconciliation' => $reconciliation
        );
    }
    return $xmlTypes;
}

function create($toCreate, $l) {
    // $toCreate needs a little reformatting, simplifies the process if multiple conditions are set for the same type
    foreach ($toCreate as $type_name => $type_data) {
        $toCreate[$type_name] = array(
            'TYPE_CONDITIONS' => array(),
            'TYPE_CONFIGS' => array()
        );
    
        foreach ($type_data as $type_config) {
            // check if type condition exists in the array already
            if (isset($toCreate[$type_name]['TYPE_CONDITIONS'][$type_config['condition_OID']])) {
                // check if label with same name already exists in TYPE_CONFIGS array
                $label_exists = false;
                foreach ($toCreate[$type_name]['TYPE_CONFIGS'] as $existing_label) {
                    if ($existing_label['label_name'] == $type_config['label_name']) {
                        $label_exists = true;
                        break;
                    }
                }
    
                if (!$label_exists) {
                    $toCreate[$type_name]['TYPE_CONFIGS'][] = array(
                        'label_name' => $type_config['label_name'],
                        'OID' => $type_config['OID'],
                        'reconciliation' => $type_config['reconciliation']
                    );
                }
            } else {
                // if it doesn't, we create the array
                $toCreate[$type_name]['TYPE_CONDITIONS'][$type_config['condition_OID']] = $type_config['value'];
    
                // if label_name does not already exist in the array, we create it
                $label_exists = false;
                foreach ($toCreate[$type_name]['TYPE_CONFIGS'] as $existing_label) {
                    if ($existing_label['label_name'] == $type_config['label_name']) {
                        $label_exists = true;
                        break;
                    }
                }
    
                if (!$label_exists) {
                    $toCreate[$type_name]['TYPE_CONFIGS'][] = array(
                        'label_name' => $type_config['label_name'],
                        'OID' => $type_config['OID'],
                        'reconciliation' => $type_config['reconciliation']
                    );
                }
            }
        }
    }

    foreach ($toCreate as $type_to_create => $type_data) {
        // check if type already exists with the same name
        $sql = "SELECT ID FROM snmp_types WHERE TYPE_NAME = '%s'";
        $sql_arg = array($type_to_create);
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $sql_arg);
        if (mysqli_num_rows($result) > 0) {
            echo "[".date("Y-m-d H:i:s")."] ################################### Type $type_to_create already exists ###################################\n";
            $create = 0;

        } else {
            ############################################ CREATE TYPE ############################################
            echo "[".date("Y-m-d H:i:s")."] Creating type $type_to_create\n";
            $snmp = new OCSSnmp();
            $create = $snmp->create_type($type_to_create);
        }

        if ($create == 0) {
            // retrieving the id of the type we just created
            $sql = "SELECT ID FROM snmp_types WHERE TYPE_NAME = '%s'";
            $sql_arg = array($type_to_create);
            $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $sql_arg);
            $type_id = mysqli_fetch_assoc($result)['ID'];

            ############################################ CREATE CONDITIONS ############################################
            foreach ($type_data['TYPE_CONDITIONS'] as $type_condition_oid => $type_condition_value) {
                $snmp = new OCSSnmp();
                $create = $snmp->create_type_condition($type_id, $type_condition_oid, $type_condition_value);
                if ($create == 0) {
                    echo "[".date("Y-m-d H:i:s")."] Condition ".$type_condition_oid." = ".$type_condition_value." created successfully\n";
                } else {
                    echo "[".date("Y-m-d H:i:s")."] Error creating condition ".$type_condition_oid." = ".$type_condition_value." : ". $l->g($create). "\n";
                }
            }

            ############################################ CREATE/UPDATE CONFIGS ############################################
            foreach ($type_data['TYPE_CONFIGS'] as $type_config) {                
                $create = create_snmp_configs($type_config, $type_id, $l);

            }
        } else {
            echo "[".date("Y-m-d H:i:s")."] Error creating type $type_to_create : ". $l->g($create). "\n";
        }

    }
}

function create_snmp_configs($type_config, $type_id, $l) {
    // check every OID from the $toCreate array against existing configs
    $sql = "SELECT * FROM snmp_configs LEFT JOIN snmp_labels ON snmp_configs.LABEL_ID = snmp_labels.ID WHERE TYPE_ID = %d AND OID = '%s'";
    $sql_arg = array($type_id, $type_config['OID']);
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $sql_arg);

    // label already exists
    if (mysqli_num_rows($result) > 0) {
        echo "[".date("Y-m-d H:i:s")."] Config ".$type_config['label_name']." = ".$type_config['OID']." already exists, checking if it needs to be updated\n";
        $data = mysqli_fetch_assoc($result);

        // if label name or reconciliation changed, update the label
        if ($data['LABEL_NAME'] != $type_config['label_name']) {
            echo "[".date("Y-m-d H:i:s")."] Label ".$type_config['label_name']." needs to be updated\n";
            $different_label = true;
        } else {
            echo "[".date("Y-m-d H:i:s")."] Label ".$type_config['label_name']." doesn't need to be updated\n";
            $different_label = false;
        }

        if ($data['RECONCILIATION'] != $type_config['reconciliation']) {
            echo "[".date("Y-m-d H:i:s")."] Reconciliation needs to be updated\n";
            $different_reconciliation = true;
        } else {
            echo "[".date("Y-m-d H:i:s")."] Reconciliation doesn't need to be updated\n";
            $different_reconciliation = false;
        }

        if ($different_label) {
            ############################################ UPDATE LABEL ############################################
            $sql = "UPDATE snmp_labels SET LABEL_NAME = '%s' WHERE ID = %d";
            $sql_arg = array($type_config['label_name'], $data['LABEL_ID']);
            $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $sql_arg);
            if ($result) {
                echo "[".date("Y-m-d H:i:s")."] Label ".$type_config['label_name']." updated successfully\n";
                $alter = alter_table_column($type_config['label_name'], $type_config['reconciliation'], $data['LABEL_NAME'], $data['LABEL_ID']);

            } else {
                echo "[".date("Y-m-d H:i:s")."] Error updating label ".$type_config['label_name']." : ".mysqli_error($_SESSION['OCS']["readServer"])."\n";
            }

        }

        ############################################ UPDATE RECONCILIATION ############################################
        if ($different_reconciliation) {
            $sql = "UPDATE snmp_configs SET RECONCILIATION = '%s' WHERE LABEL_ID = %d AND TYPE_ID = %d";
            $sql_arg = array($type_config['reconciliation'], $data['LABEL_ID'], $type_id);
            $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $sql_arg);
            if ($result) {
                echo "[".date("Y-m-d H:i:s")."] Config reconciliation for ".$type_config['label_name']." = ".$type_config['OID']." updated successfully\n";
            } else {
                echo "[".date("Y-m-d H:i:s")."] Error updating config reconciliation for ".$type_config['label_name']." = ".$type_config['OID']." : ".mysqli_error($_SESSION['OCS']["readServer"])."\n";
            }
        }
    
    ############################################ CREATE LABEL ############################################
    } else {
        $label_id = create_label($type_config, $l);
        echo "[".date("Y-m-d H:i:s")."] Config ".$type_config['label_name']." = ".$type_config['OID']." doesn't exist\n";

        if ($label_id != 0) {
            ############################################ CREATE CONFIG ############################################
            $snmp = new OCSSnmp();
            $create = $snmp->snmp_config($type_id, $label_id, $type_config['OID'], $type_config['reconciliation']);
            if ($create == 0) {
                echo "[".date("Y-m-d H:i:s")."] Config ".$type_config['label_name']." = ".$type_config['OID']." created successfully\n";
            } else {
                echo "[".date("Y-m-d H:i:s")."] Error creating config ".$type_config['label_name']." : ".mysqli_error($_SESSION['OCS']["readServer"])."\n";
            }
        } else {
            echo "[".date("Y-m-d H:i:s")."] Error creating label ".$type_config['label_name']." : ".mysqli_error($_SESSION['OCS']["readServer"])."\n";
        }
    }
}

function alter_table_column($label_name, $reconciliation, $label_name_old, $label_id) {
    // retrieve ids of types using this label (we will be updating the table for each type)
    $sql = "SELECT TYPE_ID FROM snmp_configs WHERE LABEL_ID = %d";
    $sql_arg = array($label_id);
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $sql_arg);
    $types = array();
    if (mysqli_num_rows($result) > 0) {
        while ($data = mysqli_fetch_assoc($result)) {
            $types[] = $data['TYPE_ID'];
        }
    }

    foreach ($types as $type_id) {
        // table name to update
        $snmp = new OCSSnmp();
        $table_name = $snmp->get_table_type_drop($type_id);
        ############################################ ALTER TABLE COLUMNS ############################################
        // if reconciliation == 'Yes' then varchar(255) else text
        $sql = "ALTER TABLE %s CHANGE %s %s %s";
        if ($reconciliation == 'Yes') {
            $arg = "VARCHAR(255)";
        } else {
            $arg = "TEXT";
        }

        $sql_arg = array($table_name, $label_name_old, $label_name, $arg);
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $sql_arg);

        if ($result) {
            echo "[".date("Y-m-d H:i:s")."] Column ".$label_name." updated successfully for table ".$table_name."\n";
            
        } else {
            echo "[".date("Y-m-d H:i:s")."] Error updating column ".$label_name." for table ".$table_name." : ".mysqli_error($_SESSION['OCS']["readServer"])."\n";
            
        }
    }
}

function create_label($type_config, $l) {
    $snmp = new OCSSnmp();
    $create = $snmp->create_label($type_config['label_name']);
    if ($create == 0) {
        echo "[".date("Y-m-d H:i:s")."] Label ".$type_config['label_name']." created successfully\n";
        // return label id we created
        $sql = "SELECT ID FROM snmp_labels WHERE LABEL_NAME = '%s'";
        $sql_arg = array($type_config['label_name']);
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $sql_arg);
        return mysqli_fetch_assoc($result)['ID'];

    } else if ($create == 9025) {
        echo "[".date("Y-m-d H:i:s")."] Label ".$type_config['label_name']." already exists\n";
        // return label id we found
        $sql = "SELECT ID FROM snmp_labels WHERE LABEL_NAME = '%s'";
        $sql_arg = array($type_config['label_name']);
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $sql_arg);
        return mysqli_fetch_assoc($result)['ID'];

    } else {
        echo "[".date("Y-m-d H:i:s")."] Error creating label ".$type_config['label_name']." = ".$type_config['OID']." : ". $l->g($create). "\n";
        return 0;
    }
}

function sync_types($xmlTypes, $l) {
    $typeNames = array_keys($xmlTypes);

    // get the IDs of the types that exist in the database
    $sql = "SELECT ID FROM snmp_types WHERE TYPE_NAME IN ('" . implode("','", $typeNames) . "')";
    $result = mysqli_query($_SESSION['OCS']["readServer"], $sql);
    $typeIds = array();
    if (mysqli_num_rows($result) > 0) {
        while ($data = mysqli_fetch_assoc($result)) {
            $typeIds[] = $data['ID'];
        }
    }

    // delete any existing conditions for the types
    if (!empty($typeIds)) {
        $sql = "DELETE FROM snmp_types_conditions WHERE TYPE_ID IN (" . implode(',', $typeIds) . ")";
        $result = mysqli_query($_SESSION['OCS']["writeServer"], $sql);
        if ($result) {
            echo "[" . date("Y-m-d H:i:s") . "] Existing conditions deleted successfully\n";
        } else {
            echo "[" . date("Y-m-d H:i:s") . "] Error deleting existing conditions: " . mysqli_error($_SESSION['OCS']["readServer"]) . "\n";
        }
    }

    // Create the new conditions
    create($xmlTypes, $l);
}


if (isset($argv)) {
    $l = new language("en_GB");
    $xml_file = get_arg_value($argv[1], '--file=');
    $xml = load_xml_file($xml_file);
    $xmlTypes = build_xml_types_array($xml);
    sync_types($xmlTypes, $l);
}
