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
require_once('require/teledeploy/PackageBuilderFormOptions.php');
require_once('require/teledeploy/PackageBuilderParseXml.php');

$teledeploy = new Teledeploy();
$packageBuilderParseXml = new PackageBuilderParseXml();
$packageBuilderFormOperatingSystem = new PackageBuilderFormOperatingSystem();
$packageBuilderFormInteractions = new PackageBuilderFormInteractions($packageBuilderParseXml);
$packageBuilderFormOptions = new PackageBuilderFormOptions($packageBuilderParseXml);
$packageBuilderForm = new PackageBuilderForm($packageBuilderFormInteractions, $packageBuilderFormOperatingSystem, $packageBuilderParseXml, $packageBuilderFormOptions);
$packageBuilder = new PackageBuilder($packageBuilderForm, $packageBuilderParseXml);

echo "<div class='container'>
        <div class='col-md-12 col-xs-offset-0'>";

$form_name = "generate_build_package";
printEnTete($l->g(434));

// Loading nav
echo '  <div class="nav-ocs-deploy">
            <ul class="nav nav-pills nav-justified nav-pills-ocs radius-parent">
                <li class="active triangle radius-left" id="operating_system"><a><b>'.strtoupper($l->g(25)).'</b></a></li>
                <li class="disabled ocs-disabled triangle" id="interactions"><a><b>'.strtoupper($l->g(9300)).'</b></a></li>
                <li class="disabled ocs-disabled triangle" id="options"><a><b>'.strtoupper($l->g(9301)).'</b></a></li>
                <li class="disabled ocs-disabled triangle" id="fragments"><a><b>'.strtoupper($l->g(480)).'</b></a></li>
                <li class="disabled ocs-disabled radius-right" id="services"><a><b>'.strtoupper($l->g(9302)).'</b></a></li>
            </ul>
        </div>';

echo '<div class="col col-md-12" >';

/******************** OPERATING SYSTEM ********************/
echo $packageBuilderForm->generateOperatingSystem();

/********************** INTERACTIONS **********************/
echo $packageBuilderForm->generateInteractions();

/************************* OPTIONS ************************/

echo open_form($form_name, '', "enctype='multipart/form-data'", "form-horizontal");
echo '<div id="deployment_options">';
// Generate with ajax
echo '</div>';
echo close_form();

echo '</div>';

echo "  </div>
    </div>";