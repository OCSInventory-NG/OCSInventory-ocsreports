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
require_once 'lib/package_functions.php';

// Initialize the timestamp
if (is_defined($_POST['TIMESTAMP']) && is_numeric($_POST['TIMESTAMP'])) {
    $timestamp = (integer) $_POST['TIMESTAMP'];
} else {
    $timestamp = time();
}

// If the package hasn't been created, show the create package form
if (!package_exists($timestamp)) {
    if (AJAX && isset($_FILES['FILE'])) {
        // Handle file upload
        $tmp_file = $_FILES['FILE']['tmp_name'];

        // Check for errors
        $error = null;
        $ext = null;
        switch ($_FILES['FILE']['error']) {
            case UPLOAD_ERR_OK:
                // Check mime type and extension
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $tmp_file);
                finfo_close($finfo);

                if ($mime_type == 'application/zip') {
                    $ext = '.zip';
                } else if ($mime_type == 'application/x-gzip') {
                    $ext = '.tar.gz';
                } else {
                    $error = 'Invalid file type (' . $mime_type . ')';
                }

                if (!$error && strtolower(substr($_FILES['FILE']['name'], -strlen($ext))) !== $ext) {
                    $error = 'Invalid file extension';
                }

                break;
            case UPLOAD_ERR_NO_FILE:
                $error = 'No file sent';
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error = 'Exceeded file size limit';
                break;
            default:
                $error = 'Unknown error';
                break;
        }

        if (!$error) {
            // Move the file in a tmp dir in its future download location
            $package_dir = get_download_root() . $timestamp;

            if (!file_exists($package_dir) && !mkdir($package_dir)) {
                $error = 'Could not create dir ' . $package_dir . ', please fix your filesystem before trying again ';
                // @TODO : buggy code
                break;
            }

            $package_tmp_dir = $package_dir . '/tmp';
            if (!file_exists($package_tmp_dir) && !mkdir($package_tmp_dir)) {
                $error = 'Could not create dir ' . $package_tmp_dir . ', please fix your filesystem before trying again ';
            }

            $package_tmp_file = $package_tmp_dir . '/package';
            if (!move_uploaded_file($tmp_file, $package_tmp_file)) {
                $error = 'Could not create file ' . $package_tmp_file . ', please fix your filesystem before trying again ';
            }

            // Everything went well
            $size = filesize($package_tmp_file);
        }

        if ($error) {
            echo '{"status": "error", "message": "' . $error . '"}';
        } else {
            echo '{"status": "success", "type": "' . substr($ext, 1) . '", "size": "' . $size . '"}';
        }

        exit;
    } else {
        // Default values
        $form_data = array_merge(array(
            'NAME' => null,
            'DESCRIPTION' => null,
            'OS' => 'WINDOWS',
            'ACTION' => 'STORE',
            'ACTION_INPUT' => null,
            'DEPLOY_SPEED' => 'MIDDLE',
            'PRIORITY' => 5,
            'NB_FRAGS' => null,
            'NOTIFY_USER' => null,
            'NOTIFY_TEXT' => null,
            'NOTIFY_COUNTDOWN' => null,
            'NOTIFY_CAN_CANCEL' => null,
            'NOTIFY_CAN_DELAY' => null,
            'NEED_DONE_ACTION' => null,
            'NEED_DONE_ACTION_TEXT' => null,
            'REDISTRIB_USE' => null,
            'REDISTRIB_PRIORITY' => 5,
            'REDISTRIB_NB_FRAGS' => null,
            'DOWNLOAD_SERVER_DOCROOT' => get_redistrib_distant_download_root(),
                ), $_POST);

        $form_data['TIMESTAMP'] = $timestamp;

        $errors = array();
        if (isset($_POST['create_package'])) {
            require_once 'lib/package_forms.php';

            // Perform validation
            $errors = validate_package_form($form_data, $_FILES);

            if (!$errors) {
                require_once('require/function_telediff.php');

                // Create package
                $sql_details = array(
                    'document_root' => get_download_root(),
                    'timestamp' => $timestamp,
                    'nbfrags' => $form_data['NB_FRAGS'],
                    'name' => $form_data['NAME'],
                    'os' => $form_data['OS'],
                    'description' => $form_data['DESCRIPTION'],
                    'size' => 0, //$form_data['SIZE'],
                    'id_wk' => 0//$form_data['LIST_DDE_CREAT']
                );

                $info_details = array(
                    'PRI' => $form_data['PRIORITY'],
                    'ACT' => $form_data['ACTION'],
                    'DIGEST' => 'TODO', //$form_data['digest'],
                    'PROTO' => 'HTTP', //$protectedPost['PROTOCOLE'],
                    'DIGEST_ALGO' => 'MD5', //["digest_algo"],
                    'DIGEST_ENCODE' => 'HEXA', //$protectedPost["digest_encod"],
                    'PATH' => $form_data['ACTION_INPUT'],
                    'NAME' => $form_data['ACTION_INPUT'],
                    'COMMAND' => $form_data['ACTION_INPUT'],
                    'NOTIFY_USER' => $form_data['NOTIFY_USER'],
                    'NOTIFY_TEXT' => $form_data['NOTIFY_TEXT'],
                    'NOTIFY_COUNTDOWN' => $form_data['NOTIFY_COUNTDOWN'],
                    'NOTIFY_CAN_ABORT' => $form_data['NOTIFY_CAN_ABORT'],
                    'NOTIFY_CAN_DELAY' => $form_data['NOTIFY_CAN_DELAY'],
                    'NEED_DONE_ACTION' => $form_data['NEED_DONE_ACTION'],
                    'NEED_DONE_ACTION_TEXT' => $form_data['NEED_DONE_ACTION_TEXT'],
                    'GARDEFOU' => 'rien'
                );

                create_pack($sql_details, $info_details);
            }
        }

        if ($errors || !isset($_POST['create_package'])) {
            require_once 'views/package_form_view.php';
            show_package_form($form_data, $errors);
        }
    }
}

// If the package has been created, show the details of the package
if (package_exists($timestamp)) {
    $form_data = array();
    $errors = array();

    require_once 'views/activate_form_view.php';
    show_activate_form($timestamp, $form_data, $errors);
}
?>
