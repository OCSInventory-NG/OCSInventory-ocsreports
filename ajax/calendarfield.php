<?php
/*
 * Copyright 2005-2019 OCSInventory-NG/OCSInventory-ocsreports contributors.
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
require_once('../require/function_commun.php');
require_once('../var.php');

if(isset($_GET['fieldid'])){
    $html = get_html($_GET['fieldid']);
    echo $html;
}


function get_html($fieldId) {
    global $l;
    $html = '<div class="input-group date form_datetime">
                <input type="text" class="form-control" name="'.$fieldId.'" id="'.$fieldId.'" value=""/>
                <span class="input-group-addon">
                    '.calendars($fieldId, $_SESSION['OCS']['DATE_FORMAT_LANG']).'
                </span>
            </div>'; 

    return $html;
}
?>