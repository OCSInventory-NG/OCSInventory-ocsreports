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

/*
 * For redistribution's server
 */

//require_once('require/function_server.php');
//view of all group's machin

require_once('require/function_groups.php');
require_once('require/function_machine.php');
require_once('require/function_server.php');

// TELEDEPLOY
print_item_header($l->g(481));
show_redistrib_groups_packages($systemid);
        
show_redis_infos($systemid);

function show_redis_infos($systemid){
    
    global $protectedPost, $l;
    
    if(isset($protectedPost['SUP_PROF']) && $protectedPost['SUP_PROF'] != ""){
        remove_list_serv($systemid, $protectedPost['SUP_PROF']);
    }
    
    if($protectedPost['Valid_modif'] != ""){
        $sql_update = "update download_servers set URL='" . $protectedPost['URL'] . "' ,ADD_REP='" . $protectedPost['REP_STORE'] . "' where hardware_id=" . $protectedPost['MODIF'];
        mysqli_query($_SESSION['OCS']["writeServer"], $sql_update);
        $sql_update = "update download_enable set pack_loc='" . $protectedPost['URL'] . "' where SERVER_ID=" . $protectedPost['MODIF'];
        mysqli_query($_SESSION['OCS']["writeServer"], $sql_update);
        
        unset($protectedPost['URL']);
        unset($protectedPost['REP_STORE']);
        unset($protectedPost['MODIF']);
    }

    if (isset($systemid)) {
    
        $form_name = "srv_redis";
        $table_name = "srv_redis_table";
        $tab_options = array();
        $tab_options = $protectedPost;
        $tab_options['form_name'] = $form_name;
        $tab_options['table_name'] = $table_name;

        echo open_form($form_name, '', '', 'form-horizontal');
        
        $sql = "select d.HARDWARE_ID,
                              h.NAME,
                              h.IPADDR,
                              h.DESCRIPTION,
                              d.URL,
                              d.ADD_REP
                    from hardware h right join download_servers d on h.id=d.hardware_id
                    where d.GROUP_ID=".$systemid ;

        $list_fields=array( 
            $l->g(296) => 'HARDWARE_ID',
            $l->g(49) => 'NAME',
            $l->g(34) => 'IPADDR',
            $l->g(53) => 'DESCRIPTION',
            $l->g(646) => 'URL',
            'ADD_REP' => 'ADD_REP',
            'MODIF' => 'HARDWARE_ID',
            'SUP' => 'HARDWARE_ID'
        );
        
        $list_col_cant_del=array( 
            $l->g(49) => 'NAME',
            $l->g(34) => 'IPADDR',
            'MODIF' => 'MODIF',
            'SUP' => 'SUP'
        );
        
        $default_fields= array( 
            $l->g(49) => 'NAME',
            $l->g(34) => 'IPADDR',
            $l->g(646) => 'URL',
            'ADD_REP' => 'ADD_REP',
        );

        ajaxtab_entete_fixe($list_fields,$default_fields,$tab_options,$list_col_cant_del);
        
        if ($protectedPost['MODIF'] != "" && !isset($protectedPost['Valid_modif']) && !isset($protectedPost['Reset_modif'])) {
        
            $sql = "select URL, ADD_REP from download_servers where HARDWARE_ID = ".$protectedPost['MODIF'] ;
            $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
            $redis_values = mysqli_fetch_array($result);

            echo "<div class='col col-md-8 col-md-offset-2'>";

            echo $l->g(691);
            formGroup('text', 'URL', $l->g(646), '', '', $redis_values['URL'], '', '', '', '');
            formGroup('text', 'REP_STORE', $l->g(648), '', '', $redis_values['ADD_REP'], '', '', '', '');

            echo "<input type='submit' class='btn btn-success' value=".$l->g(13).">";
            formGroup('hidden', 'Valid_modif', '', '', '', 'OK', '', '', '' ,'');
            formGroup('hidden', 'MODIF', '', '', '', $protectedPost['MODIF'], '', '', '' ,'');

            echo "</div>";
        }

        echo close_form();
        
    }

    if (AJAX){
        ob_end_clean();
        tab_req($list_fields,$default_fields,$list_col_cant_del,$sql,$tab_options);
    }
    
}



?>