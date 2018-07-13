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

if (AJAX) {
    parse_str($protectedPost['ocs']['0'], $params);
    $protectedPost += $params;

    ob_start();
}

require_once 'require/extensions/ExtensionManager.php';

printEnTete($l->g(7008));

?>
<div class="container">
    <div class="col col-md-12">
<?php

$extMgr = new ExtensionManager();
if($extMgr->checkPrerequisites()){
    $extMgr->checkInstallableExtensions();
    
    if(empty($extMgr->installableExtensionsList)){
        msg_warning($l->g(7014));
    }else{   
        echo open_form("PluginInstall", '', '', 'form-horizontal');
        ?>
            <div class="form-group">
                <div class="col col-sm-5 col-sm-offset-3">
                    <select class="form-control" name="extensions">
                        <?php
                        foreach ($extMgr->installableExtensionsList as $key => $value) {
                            echo "<option value=$value >$value</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col col-sm-2">
                    <input type="submit" class="form-control btn btn-success" value="Install">
                </div>
            </div>
        <?php
        echo close_form();
    }
    
}else{
    msg_warning($l->g(7014));
}

?>
    </div>
</div>
<?php

var_dump($protectedPost);

echo "WIP";