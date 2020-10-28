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
require_once('require/saveQuery/SaveQuery.php');

$saveQuery = new SaveQuery();

if(isset($protectedPost['saved_search']) && $protectedPost['saved_search'] != "0") {
    $search_info = $saveQuery->get_search_info($protectedPost['saved_search']);
}

if(isset($protectedPost['query_name'])){

    $multiSearchParameters = serialize($_SESSION['OCS']['multi_search']);

    $sqlArgs[] = addslashes($protectedPost['query_name']);
    $sqlArgs[] = addslashes($protectedPost['query_description']);
    $sqlArgs[] = $multiSearchParameters;

    if($protectedPost['id_search'] == "") {
        $verif = $saveQuery->create_search($sqlArgs);
    } else {
        $sqlArgs[] = $protectedPost['id_search'];
        $verif = $saveQuery->update_search($sqlArgs);
    }
    

    if(!$verif) {
        msg_error($l->g(344));
    } else {
        msg_success($l->g(2143));
    }
    
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="container">
                <a href="?function=visu_search">
                    <button type="button" class="btn btn-info"><?php echo $l->g(2129) ?></button>
                </a>
            </div>
        </div>
    </div>
    <?php

} else {
    $query = $saveQuery->get_search_name();

    echo open_form('save_query', '', '', '');
    ?>
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo $l->g(2138) ?></h2>
            <div class="panel panel-default col-md-6 col-md-offset-3" style="width:50%;">
                <div class="panel-body"><?php echo $l->g(2139) ?></div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="container">
                <div class="row">
                    <?php   formGroup('text','query_name',$l->g(49),'','',$search_info['NAME'],'','','','',''); ?>
                </div>
                <br/>
                <div class="row">
                    <?php   formGroup('text','query_description',$l->g(53),'','',$search_info['DESCRIPTION'],'','','','',''); ?>
                </div>
                <input type="hidden" id="id_search" name= "id_search" value="<?php echo $search_info['ID'] ?>">
                <br/>
                <a onClick="verif_champ_name('save_query', 'query_name');">
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

    echo open_form('update_query', '', '', '');
    ?>
    <br/><hr><br/>
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo $l->g(2144) ?></h2>
        </div>
        <div class="col-sm-12">
            <div class="container">
                <br/>
                <div class="row">
                    <?php   formGroup('select','saved_search',$l->g(2141),'','',$protectedPost['saved_search'],'',$query,$query,'',''); ?>
                </div>
                <br/>
                <a onClick="$('#update_query').submit();">
                    <button type="button" class="btn btn-success"><?php echo $l->g(455) ?></button>
                </a>
            </div>
        </div>
    </div>
    <?php
    echo close_form();

}