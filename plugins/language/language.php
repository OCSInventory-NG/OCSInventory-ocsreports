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
require_once('require/function_files.php');
$Directory = PLUGINS_DIR . 'language/';
$ms_cfg_file = $Directory . "/lang_config.txt";
//show only true sections
if (file_exists($ms_cfg_file)) {
    $search = array('ORDER' => 'MULTI2', 'LBL' => 'MULTI');
    $language_data = read_configuration($ms_cfg_file, $search);
    $list_plugins = $language_data['ORDER'];
    $list_lbl = $language_data['LBL'];
}

$i = 0;

while (isset($list_plugins[$i])) {
    if (file_exists($Directory . $list_plugins[$i] . "/" . $list_plugins[$i] . ".png"))
        $show_lang .= "<img src='plugins/language/" . $list_plugins[$i] . "/" . $list_plugins[$i] . ".png' width=\"20\" height=\"15\" OnClick='pag(\"" . $list_plugins[$i] . "\",\"LANG\",\"ACTION_CLIC\");'>&nbsp;";
    else
        $show_lang .= "<a href=# OnClick='pag(\"" . $list_plugins[$i] . "\",\"LANG\",\"ACTION_CLIC\");'>" . $list_lbl[$list_plugins[$i]] . "</a>&nbsp;";
    $i++;
}

echo $show_lang;
?>
