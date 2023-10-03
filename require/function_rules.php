<script>
    function check() {
        var msg = '';
        if (document.getElementById('RULE_NAME').value == "") {
            document.getElementById('RULE_NAME').style.backgroundColor = "RED";
            msg = 'NULL';
        }
        var nb_lign = (document.getElementsByTagName('select').length - 2) / 3;
        var i = 1;
        while (i < (nb_lign + 1)) {
            champs = ['PRIORITE_' + i, 'CFIELD_' + i, 'OP_' + i, 'COMPTO_' + i];
            for (var n = 0; n < champs.length; n++)
            {
                if (document.getElementById(champs[n]).value == "") {
                    document.getElementById(champs[n]).style.backgroundColor = "RED";
                    msg = 'NULL';
                } else
                    document.getElementById(champs[n]).style.backgroundColor = "";
            }
            i++;

        }
        if (msg == "")
            return(true);
        else {
            return(false);
        }
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

function verify_name($RULE_NAME, $condition = '') {
    //verify this rule name exist
    $sql_exist = "select id from download_affect_rules where rule_name='%s' ";
    if ($condition != "") {
        $sql_exist .= $condition;
    }
    $arg = trim($RULE_NAME);
    $result_rule_exist = mysql2_query_secure($sql_exist, $_SESSION['OCS']["readServer"], $arg);
    $rule_exist = mysqli_fetch_object($result_rule_exist);
    if ($rule_exist->id) {
        return 'NAME_EXIST';
    } else {
        return 'NAME_NOT_EXIST';
    }
}

function verify_rule($rule_or_condition, $ID) {
    $sql = "select id from download_affect_rules where %s='%s'";
    $arg = array($rule_or_condition, $ID);
    $result_id = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    $id_exist = mysqli_fetch_object($result_id);
    if ($id_exist->id) {
        return 'RULE_EXIST';
    } else {
        return 'RULE_NOT_EXIST';
    }
}

function delete_rule($ID_RULE) {
    global $l;
    $id_exist = verify_rule('rule', $ID_RULE);
    if ($id_exist == "RULE_EXIST") {
        $sql_del_rule = "delete from download_affect_rules where rule='%s'";
        $arg = $ID_RULE;
        mysql2_query_secure($sql_del_rule, $_SESSION['OCS']["writeServer"], $arg);
    } else {
        echo msg_error($l->g(672));
    }
}


function add_rule($RULE_NAME, $RULE_VALUES, $ID_RULE = '') {
    global $l, $protectedPost;
    $rule_exist = verify_name($RULE_NAME);
    if ($rule_exist == 'NAME_NOT_EXIST') {
        //verify this id is new
        $sql = "select id from download_affect_rules where id='%s'";
        $arg = $ID_RULE;
        $result_id = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
        $id_exist = mysqli_fetch_object($result_id);
        //generate id
        if (!is_numeric($ID_RULE) || $ID_RULE == '' || isset($id_exist->id)) {
            $sql_new_id = "select max(RULE) as ID_RULE from download_affect_rules";
            $result_new_id = mysql2_query_secure($sql_new_id, $_SESSION['OCS']["readServer"]);
            $new_id = mysqli_fetch_object($result_new_id);
            $ID_RULE = $new_id->ID_RULE;
            $ID_RULE++;
        }
        //insert new rule
        $i = 1;
        while ($RULE_VALUES['PRIORITE_' . $i]) {
            if ($RULE_VALUES['CFIELD_' . $i] != "") {
                $sql_insert_rule = "insert into download_affect_rules (RULE,RULE_NAME,PRIORITY,CFIELD,OP,COMPTO,SERV_VALUE)
				value (%s,'%s',%s,'%s','%s','%s','%s')";
                $arg = array($ID_RULE, $protectedPost['RULE_NAME'],
                    $RULE_VALUES['PRIORITE_' . $i], $RULE_VALUES['CFIELD_' . $i],
                    $RULE_VALUES['OP_' . $i], $RULE_VALUES['COMPTO_' . $i], $RULE_VALUES['COMPTO_TEXT_' . $i]);
                mysql2_query_secure($sql_insert_rule, $_SESSION['OCS']["writeServer"], $arg);
            }
            $i++;
        }
    } else {
        echo msg_error($l->g(670));
    }
}

/*
 * HTML fields for condition of rule
 *
 */

function fields_conditions_rules($num, $entete = 'NO') {
    global $l, $protectedPost;

    $CFIELD = array(
        'NAME' => $l->g(679),
        'IPADDRESS' => '@IP',
        'IPSUBNET' => 'IPSUBNET',
        'WORKGROUP' => $l->g(680),
        'USERID' => $l->g(681)
    );
    $OP = array(
        'EGAL' => "=",
        'DIFF' => "<>",
        'LIKE' => 'LIKE'
    );

    if (!isset($protectedPost["PRIORITE_" . $num])) {
        $protectedPost["PRIORITE_" . $num] = $num;
    }

    formGroup('text', "PRIORITE_" . $num, $l->g(675), '', '', $protectedPost["PRIORITE_" . $num]);
    formGroup('select', "CFIELD_" . $num, $l->g(676), '', '', $protectedPost["PRIORITE_" . $num], '', $CFIELD, $CFIELD);
    formGroup('select', "OP_" . $num, $l->g(677), '', '', $protectedPost["PRIORITE_" . $num], '', $OP, $OP);
    formGroup('select', "COMPTO_" . $num, $l->g(678), '', '', $protectedPost["PRIORITE_" . $num], '', $CFIELD, $CFIELD);

}
?>