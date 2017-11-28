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

  if($i == 12){
    $select_lang .= $list_plugins[$i];
  } else {
    $select_lang .= $list_plugins[$i] .',';
  }

  $show_lang = "<label for='LANGUAGE'>".$l->g(1012)."</label>
                <div class='bfh-selectbox bfh-languages'  data-language='".$protectedPost['LANG']."' data-available='" . $select_lang . "' data-flags='true' data-blank='false'>
                    <input type='hidden' value='".$protectedPost['LANG']."'>
                    <a class='bfh-selectbox-toggle' role='button' data-toggle='bfh-selectbox' href='#'>
                        <span class='bfh-selectbox-option input-medium' data-option=''></span>
                        <b class='caret'></b>
                    </a>
                    <div class='bfh-selectbox-options'>
                        <div role='listbox'>
                            <ul role='option'>
                            </ul>
                        </div>
                    </div>
                </div>";

      $i++;
}

echo $show_lang;
?>

<script>
$('.bfh-selectbox').on('change.bfhselectbox', function() {
  var language = $(this).val();
  pag(language,'LANG','ACTION_CLIC');
});
</script>
