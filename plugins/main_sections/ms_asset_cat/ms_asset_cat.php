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

if(isset($protectedPost['cat_name'])){

    $sqlQuery = "INSERT INTO `assets_categories`(`CATEGORY_NAME`, `CATEGORY_DESC`, `SQL_QUERY`, `SQL_ARGS`) VALUES ('%s','%s','%s','%s')";
    $sqlArgs = [];

    $multiSearchSqlQuery = $_SESSION['OCS']['multi_search_query'];
    $multiSearchSqlArgs = implode(',', $_SESSION['OCS']['multi_search_args']);

    $sqlArgs[] = $protectedPost['cat_name'];
    $sqlArgs[] = $protectedPost['cat_desc'];
    $sqlArgs[] = $multiSearchSqlQuery;
    $sqlArgs[] = $multiSearchSqlArgs;

    mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArgs);

    msg_success($l->g(388)." ".$l->g(234));
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="container">
                <a href="?function=visu_search">
                    <button type="button" class="btn btn-info"><?php echo $l->g(2129) ?></button>
                </a>
        </div>
    </div>
    <?php

}else{

    msg_info($l->g(901));
    echo open_form('asset_cat', '', '', '');
    ?>
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo $l->g(2126) ?></h2>
            <div class="panel panel-info">
                <div class="panel-heading"><?php echo $l->g(2128) ?></div>
                <div class="panel-body"><?php echo $l->g(2127) ?></div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="container">
                <div class="row">
                    <?php formGroup('text','cat_name',$l->g(49),'','','','','','','',''); ?>
                </div>
                <br/>
                <div class="row">
                    <?php formGroup('text','cat_desc',$l->g(53),'','','','','','','',''); ?>
                </div>
                <br/>
                <a onClick="$('#asset_cat').submit();">
                    <button type="button" class="btn btn-success"><?php echo $l->g(455) ?></button>
                </a>
                <a href="?function=visu_search">
                    <button type="button" class="btn btn-danger"><?php echo $l->g(454) ?></button>
                </a>
            </div>
        </div>
    </div>
    <?php
    echo close_form();

}