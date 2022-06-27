<script type="text/javascript">

    function recharge(modif, origine) {
        document.getElementById('systemid').value = modif;
        document.getElementById('origine').value = origine;
        document.getElementById('modif_param').submit();
    }

</script>
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

//function for erase param values
function erase($NAME) {
    global $protectedGet, $list_hardware_id, $tab_hadware_id;
    // if it's for group or a machine
    if (isset($list_hardware_id)) {
        $sql = "DELETE FROM devices WHERE name='%s' AND hardware_id='%s'";
        $arg = array($NAME, $protectedGet["idchecked"]);
        mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
    } else { //else : request
        $sql = "DELETE FROM devices WHERE name='%s' AND hardware_id in ";
        $arg_sql = array($NAME);
        $arg = mysql2_prepare($sql, $arg_sql, $tab_hadware_id);
        mysql2_query_secure($arg['SQL'], $_SESSION['OCS']["writeServer"], $arg['ARG']);
    }
}

//function for insert param values
function insert($NAME, $IVALUE, $TVALUE = "") {
    global $list_hardware_id, $tab_hadware_id;
    //delete old value before insert new
    erase($NAME);
    // if it's for group or a machine
    if (isset($list_hardware_id)) {
        $arg = array($list_hardware_id, $NAME, $IVALUE);
        if ($TVALUE != "") {
            $sql = "INSERT INTO devices(HARDWARE_ID,NAME,IVALUE,TVALUE) VALUES ('%s', '%s', '%s', '%s')";
            array_push($arg, $TVALUE);
        } else {
            $sql = "INSERT INTO devices(HARDWARE_ID, NAME, IVALUE) VALUES('%s', '%s', '%s')";
        }

        mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
    } else {//else : request
        $i = 0;
        while (isset($tab_hadware_id[$i]) && $tab_hadware_id[$i]) {
            $arg = array($tab_hadware_id[$i], $NAME, $IVALUE);
            if ($TVALUE != "") {
                $sql = "INSERT INTO devices(HARDWARE_ID,NAME,IVALUE,TVALUE) VALUES ('%s', '%s', '%s', '%s')";
                array_push($arg, $TVALUE);
            } else {
                $sql = "INSERT INTO devices(HARDWARE_ID, NAME, IVALUE) VALUES ('%s', '%s', '%s')";
            }

            mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
            $i++;
        }
    }
}

function optperso($lbl, $lblPerso, $helpText, $optPerso, $default_value = '', $end = '') {
    global $l, $td3;
    //FIRST
    ?>
    <div class="row">
        <div class="col col-md-6 text-left">
            <p>
                <?php echo $lblPerso; ?>
                <span class="help-block text-success"><?php echo $helpText; ?></span>
            </p>
        </div>
        <div class="col col-md-6">
            <p>
                <?php
                if(isset($optPerso[$lbl])){

                    echo $optPerso[$lbl]['IVALUE'];

                } else{
                    // TODO: Strange spaces on display page
                    echo $l->g(488). " (".$default_value;
                }

                if(isset($end)){
                    echo " ".$end;
                }
                if(!isset($optPerso[$lbl])){
                    echo ")";
                }

                ?>
            </p>
        </div>
    </div>
    <hr />
<?php
}


function optpersoGroup($lbl, $lblPerso, $helpText, $optPerso, $value = '', $supp = '') {
    global $l;
    ?>
    <div class="row">
        <div class="col col-md-6 text-left">
            <p>
                <?php echo ($supp != '' ? "<span class='roundRed'></span>" : '') ?>
                <?php echo $lblPerso; ?>
                <span class="help-block text-success"><?php echo $helpText; ?></span>
            </p>
        </div>
        <div class="col col-md-6">
            <p>
                <?php

                if($value != ''){
                    echo $l->g(488). " (".$value.")";
                } else{
                    echo $supp;
                }

                ?>
            </p>
        </div>
    </div>
    <hr />
    <?php
}
?>
