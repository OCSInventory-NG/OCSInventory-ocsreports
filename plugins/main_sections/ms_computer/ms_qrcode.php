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

require_once('require/function_admininfo.php');
if (is_numeric($protectedGet['systemid']) && array_key_exists($protectedGet['default_value'], $array_qr_action)) {
    if ($array_qr_action[$protectedGet['default_value']]['TYPE'] == 'url') {
        $msg = $array_qr_action[$protectedGet['default_value']]['VALUE'];
    } else {
        $fields_info = explode('.', $array_qr_action[$protectedGet['default_value']]['VALUE']);
        if ($fields_info[0] == 'hardware') {
            $hardware_id = 'id';
        } else {
            $hardware_id = 'hardware_id';
        }
        $sql = "select %s from %s where %s='%s'";
        $arg = array($fields_info[1], $fields_info[0], $hardware_id, $protectedGet['systemid']);
        $res = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
        $val = mysqli_fetch_array($res);
        $msg = $val[$fields_info[1]];
    }

    $barcode = new \Com\Tecnick\Barcode\Barcode();
    $qrcode = $barcode->getBarcodeObj('QRCODE,H', $msg, 400, 400, 'black', array(20, 20, 20, 20));
    $qrcode->getPng();
}
