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
require_once('require/function_telediff.php');
require_once('require/teledeploy/Teledeploy.php');
require_once('require/teledeploy/PackageBuilder.php');
require_once('require/teledeploy/PackageBuilderForm.php');
require_once('require/teledeploy/PackageBuilderFormOperatingSystem.php');
require_once('require/teledeploy/PackageBuilderFormInteractions.php');

$teledeploy = new Teledeploy();
$packageBuilderFormOperatingSystem = new PackageBuilderFormOperatingSystem();
$packageBuilderFormInteractions = new PackageBuilderFormInteractions();
$packageBuilderForm = new PackageBuilder($packageBuilderFormInteractions, $packageBuilderFormOperatingSystem);
$packageBuilder = new PackageBuilder($packageBuilderForm);

echo "<div class='container'><div class='col-md-12 col-xs-offset-0'>";

$form_name = "create_pack";
printEnTete($l->g(434));
echo open_form($form_name, '', "enctype='multipart/form-data'", "form-horizontal");
?>

<div class="" style="margin-bottom:50px;margin-top:50px;">
    <ul class="nav nav-pills nav-justified nav-pills-ocs radius-parent">
        <li class="active triangle radius-left" id="operating_system"><a><b>OPERATING SYSTEM</b></a></li>
        <li class="disabled ocs-disabled triangle" id="interactions"><a><b>INTERACTIONS</b></a></li>
        <li class="disabled ocs-disabled triangle" id="options"><a><b>OPTIONS</b></a></li>
        <li class="disabled ocs-disabled triangle" id="fragments"><a><b>FRAGMENTS</b></a></li>
        <li class="disabled ocs-disabled radius-right" id="services"><a><b>SERVICE CATALOG</b></a></li>
    </ul>
</div>

<div class="col col-md-12" >
    <!-- OPERATING SYSTEM -->
    <div id="operatingsystem">
        <div class="row">
            <div class="col-md-4">
                <div class="card_test">
                    <div class="container_test">
                        <a onClick="loadInteractions('Windows')">
                            <img src="image/windows-logo.png" style="margin-top:10px;"/>
                            <h4><b>Windows</b></h4>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card_test">
                    <div class="container_test">
                        <a onClick="loadInteractions('Linux')">
                            <img src="image/linux-logo.png" style="margin-top:10px;"/>
                            <h4><b>Linux</b></h4>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card_test">
                    <div class="container_test">
                        <a onClick="loadInteractions('Macos')">
                            <img src="image/apple-logo.png" style="margin-top:10px;"/>
                            <h4><b>MacOS</b></h4>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- INTERACTIONS WINDOWS -->
    <div id="windowsInteractions" style="display:none;">
        <div class="panel-group" id="accordion">
            <div class="row">
                <div class="col-md-4">
                    <div class="panel panel-default panel-default-ocs-deploy">
                        <div class="panel-heading panel-heading-ocs-deploy">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#installUninstall">
                                    INSTALL / UNINSTALL
                                </a>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default panel-default-ocs-deploy">
                        <div class="panel-heading panel-heading-ocs-deploy">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#scripts">
                                    SCRIPTS
                                </a>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default panel-default-ocs-deploy">
                        <div class="panel-heading panel-heading-ocs-deploy">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#others">
                                    OTHERS
                                </a>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INSTALL / UNINSTALL -->
            <div class="panel panel-default panel-default-ocs-deploy">
                <div id="installUninstall" class="panel-collapse collapse">
                    <div class="panel-body panel-body-ocs-deploy">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Windows','msi')">
                                            <img src="image/windows-logo.png" style="margin-top:10px;"/>
                                            <h4><b>Install MSI application</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Windows','winget')">
                                            <img src="image/powershell.png" style="margin-top:10px;"/>
                                            <h4><b>Install with WinGet (Powershell)</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Windows','exe')">
                                            <img src="image/exe.png" style="margin-top:10px;"/>
                                            <h4><b>Execute an EXE</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Windows','uninstall')">
                                            <img src="image/uninstalling.png" style="margin-top:10px;"/>
                                            <h4><b>Uninstall application</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SCRIPTS -->
            <div class="panel panel-default panel-default-ocs-deploy">
                <div id="scripts" class="panel-collapse collapse">
                    <div class="panel-body panel-body-ocs-deploy">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Windows','powershell')">
                                            <img src="image/powershell.png" style="margin-top:10px;"/>
                                            <h4><b>Powershell script</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Windows','bat')">
                                            <img src="image/windows-logo.png" style="margin-top:10px;"/>
                                            <h4><b>BAT script</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- OTHERS -->
            <div class="panel panel-default panel-default-ocs-deploy">
                <div id="others" class="panel-collapse collapse">
                    <div class="panel-body panel-body-ocs-deploy">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Windows','store')">
                                            <img src="image/folder.png" style="margin-top:10px;"/>
                                            <h4><b>Store file/folder</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Windows','update')">
                                            <img src="image/installing.png" style="margin-top:10px;"/>
                                            <h4><b>Update OCS Agent</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Windows','custom')">
                                            <img src="image/adjustment.png" style="margin-top:10px;"/>
                                            <h4><b>Custom package</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- INTERACTIONS LINUX -->
    <div id="linuxInteractions" style="display:none;">
        <div class="panel-group" id="accordionLinux">
            <div class="row">
                <div class="col-md-4">
                    <div class="panel panel-default panel-default-ocs-deploy">
                        <div class="panel-heading panel-heading-ocs-deploy">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordionLinux" href="#installUninstallLinux">
                                    INSTALL / UNINSTALL
                                </a>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default panel-default-ocs-deploy">
                        <div class="panel-heading panel-heading-ocs-deploy">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordionLinux" href="#scriptsLinux">
                                    SCRIPTS
                                </a>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default panel-default-ocs-deploy">
                        <div class="panel-heading panel-heading-ocs-deploy">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordionLinux" href="#othersLinux">
                                    OTHERS
                                </a>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INSTALL / UNINSTALL -->
            <div class="panel panel-default panel-default-ocs-deploy">
                <div id="installUninstallLinux" class="panel-collapse collapse">
                    <div class="panel-body panel-body-ocs-deploy">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Linux','rpm')">
                                            <img src="image/linux-logo.png" style="margin-top:10px;"/>
                                            <h4><b>Install RPM package</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Linux','debian')">
                                            <img src="image/debian.png" style="margin-top:10px;"/>
                                            <h4><b>Install Debian package</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SCRIPTS -->
            <div class="panel panel-default panel-default-ocs-deploy">
                <div id="scriptsLinux" class="panel-collapse collapse">
                    <div class="panel-body panel-body-ocs-deploy">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Linux','bash')">
                                            <img src="image/console.png" style="margin-top:10px;"/>
                                            <h4><b>Execute a BASH script</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- OTHERS -->
            <div class="panel panel-default panel-default-ocs-deploy">
                <div id="othersLinux" class="panel-collapse collapse">
                    <div class="panel-body panel-body-ocs-deploy">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Linux','store')">
                                            <img src="image/folder.png" style="margin-top:10px;"/>
                                            <h4><b>Store file/folder</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Linux','binary')">
                                            <img src="image/binary.png" style="margin-top:10px;"/>
                                            <h4><b>Launch binary file</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Linux','custom')">
                                            <img src="image/adjustment.png" style="margin-top:10px;"/>
                                            <h4><b>Custompackage</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- INTERACTIONS MACOS -->
    <div id="macosInteractions" style="display:none;">
        <div class="panel-group" id="accordionMacos">
            <div class="row">
                <div class="col-md-4">
                    <div class="panel panel-default panel-default-ocs-deploy">
                        <div class="panel-heading panel-heading-ocs-deploy">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordionMacos" href="#installUninstallMacos">
                                    INSTALL / UNINSTALL
                                </a>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default panel-default-ocs-deploy">
                        <div class="panel-heading panel-heading-ocs-deploy">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordionMacos" href="#scriptsMacos">
                                    SCRIPTS
                                </a>
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-default panel-default-ocs-deploy">
                        <div class="panel-heading panel-heading-ocs-deploy">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordionMacos" href="#othersMacos">
                                    OTHERS
                                </a>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INSTALL / UNINSTALL -->
            <div class="panel panel-default panel-default-ocs-deploy">
                <div id="installUninstallMacos" class="panel-collapse collapse">
                    <div class="panel-body panel-body-ocs-deploy">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Macos','apps')">
                                            <img src="image/apple-logo.png" style="margin-top:10px;"/>
                                            <h4><b>Deploy APPS</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SCRIPTS -->
            <div class="panel panel-default panel-default-ocs-deploy">
                <div id="scriptsMacos" class="panel-collapse collapse">
                    <div class="panel-body panel-body-ocs-deploy">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Macos','bash')">
                                            <img src="image/console.png" style="margin-top:10px;"/>
                                            <h4><b>Execute a BASH script</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- OTHERS -->
            <div class="panel panel-default panel-default-ocs-deploy">
                <div id="othersMacos" class="panel-collapse collapse">
                    <div class="panel-body panel-body-ocs-deploy">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Macos','binary')">
                                            <img src="image/binary.png" style="margin-top:10px;"/>
                                            <h4><b>Launch binary file</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Macos','store')">
                                            <img src="image/folder.png" style="margin-top:10px;"/>
                                            <h4><b>Store file/folder</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card_test">
                                    <div class="container_test">
                                        <a onClick="loadOptions('Macos','custom')">
                                            <img src="image/adjustment.png" style="margin-top:10px;"/>
                                            <h4><b>Custompackage</b></h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="macosInteractions" style="display:none;">
        <div class="row">
            
            
        </div>
    </div>



</div>

<?php
echo close_form();
echo "</div></div>";

