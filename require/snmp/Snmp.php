<?php
/*
 * Copyright 2005-2019 OCSInventory-NG/OCSInventory-ocsreports contributors.
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

/**
 *  Snmp class
 */
class OCSSnmp
{

	/**
	 * Insert new type into DB
	 *
	 * @param string $typeName
	 * @param string $oid
	 * @param string $oidString
	 * @return boolean
	 */
	public function create_type($typeName, $oid, $oidString) {

		$sql_verif = "SELECT * FROM `snmp_types` WHERE `TYPE_NAME` = '%s'";
		$sql_verif_arg = array(addslashes($typeName));
		$verif = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $sql_verif_arg);

		if($verif->num_rows == 0) {
			$tableTypeName = str_replace(" ", "_", $typeName);
			$tableTypeName = strtolower($tableTypeName);
			$tableTypeName = "snmp_".$tableTypeName;

			$sql_create_table =   "CREATE TABLE IF NOT EXISTS `%s` (
										`ID` INT(6) NOT NULL AUTO_INCREMENT,
										PRIMARY KEY (`ID`)
									) ENGINE=InnoDB DEFAULT CHARSET=UTF8;";
			$sql_arg_table = array($tableTypeName);

			$result_create = mysql2_query_secure($sql_create_table, $_SESSION['OCS']["writeServer"], $sql_arg_table);
			
			if($result_create) {
				$sql = "INSERT INTO `snmp_types` (`TYPE_NAME`,`CONDITION_OID`,`CONDITION_VALUE`, `TABLE_TYPE_NAME`) VALUES ('%s','%s','%s', '%s')";
				$sql_arg = array(addslashes($typeName), addslashes($oid), addslashes($oidString), $tableTypeName);

				$result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $sql_arg);

				if($result) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Insert new label into DB 
	 *
	 * @param string $labelName
	 * @return boolean
	 */
	public function create_label($labelName) {
		$sql_verif = "SELECT * FROM `snmp_labels` WHERE `LABEL_NAME` = '%s'";
		$sql_verif_arg = array(addslashes($labelName));
		$verif = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $sql_verif_arg);

		if($verif->num_rows == 0) {
			$sql = "INSERT INTO `snmp_labels` (`LABEL_NAME`) VALUES ('%s')";
			$sql_arg = array(addslashes($labelName));

			$result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $sql_arg);

			if($result) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Retrieve all snmp type
	 *
	 * @return array
	 */
	public function get_type(){
		$list_type = [];

		$sql = "SELECT DISTINCT `ID`, `TYPE_NAME` FROM `snmp_types`";
		$result = mysqli_query($_SESSION['OCS']["readServer"], $sql);

		while ($item = mysqli_fetch_array($result)) {
			$list_type[$item['ID']] = $item['TYPE_NAME'];
		}

		return ($list_type);
	}

	/**
	 * Retrieve all snmp label
	 *
	 * @return array
	 */
	public function get_label(){
		$list_label = [];

		$sql = "SELECT DISTINCT `ID`, `LABEL_NAME` FROM `snmp_labels`";
		$result = mysqli_query($_SESSION['OCS']["readServer"], $sql);

		while ($item = mysqli_fetch_array($result)) {
			$list_label[$item['ID']] = $item['LABEL_NAME'];
		}

		return ($list_label);
	}

	/**
	 * Insert snmp config into DB
	 *
	 * @param int $typeID
	 * @param int $labelID
	 * @param string $oid
	 * @return boolean
	 */
	public function snmp_config($typeID, $labelID, $oid) {
		$result_alter_table  = $this->add_label_column($typeID, $labelID);

		if($result_alter_table){
			$sql = "INSERT INTO `snmp_configs` (`TYPE_ID`,`LABEL_ID`,`OID`) VALUES (%s,%s,'%s')";
			$sql_arg = array($typeID, $labelID, addslashes($oid));
		
			$result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $sql_arg);
			if($result) {
				return true;
			} else {
				return false;
			}
		}else{
			return false;
		}
	}

	/**
	 * Alter table type and add column label
	 *
	 * @param int $typeID
	 * @param int $labelID
	 * @return boolean
	 */
	private function add_label_column($typeID, $labelID) {
		$tableName = $this->get_table_type_drop($typeID);
		$labelName = $this->get_label_drop($labelID);

		$sql_alter = "ALTER TABLE `%s` ADD `%s` VARCHAR(255) NOT NULL";
		$arg_alter = array($tableName, $labelName);
		$result_alter = mysql2_query_secure($sql_alter, $_SESSION['OCS']["writeServer"], $arg_alter);

		if($result_alter){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Remove type in BDD
	 *
	 * @param int $id
	 * @return boolean
	 */
	public function delete_type($id){

		$result = $this->drop_table($id);
		if($result){
			$sqlQuery = "DELETE FROM `snmp_types` WHERE ID = %s";
			$sqlArg = [$id];
			mysql2_query_secure($sqlQuery, $_SESSION['OCS']["readServer"], $sqlArg);

			// Remove config associated to this type
			$sqlQuery = "DELETE FROM `snmp_configs` WHERE TYPE_ID = %s";
			$sqlArg = [$id];
			mysql2_query_secure($sqlQuery, $_SESSION['OCS']["readServer"], $sqlArg);

			return true;
		}else{
			return false;
		}
	}

	/**
	 * DROP type table
	 *
	 * @param int $id
	 * @return boolean
	 */
	private function drop_table($id){
		$tableName = $this->get_table_type_drop($id);

		$sql_drop_table = "DROP TABLE %s";
		$arg_drop_table = array($tableName);

		$result_drop = mysql2_query_secure($sql_drop_table, $_SESSION['OCS']["writeServer"], $arg_drop_table);
		if($result_drop){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Delete label
	 *
	 * @param int $id
	 * @return boolean
	 */
	public function delete_label($id){
		$result_drop = $this->drop_column($id);

		if(!$result_drop){
			return false;
		} else {
			$sqlQuery = "DELETE FROM `snmp_labels` WHERE ID = %s";
			$sqlArg = [$id];
			mysql2_query_secure($sqlQuery, $_SESSION['OCS']["readServer"], $sqlArg);

			// Remove config associated to this label
			$sqlQuery = "DELETE FROM `snmp_configs` WHERE LABEL_ID = %s";
			$sqlArg = [$id];
			$result = mysql2_query_secure($sqlQuery, $_SESSION['OCS']["readServer"], $sqlArg);
			if($result){
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * DROP type column
	 *
	 * @param int $id
	 * @return boolean
	 */
	private function drop_column($id){
		$type = $this->get_config($id);
		$label = $this->get_label_drop($id);

		foreach($type as $key => $value){
			$tableName[] = $this->get_table_type_drop($value);
		}

		foreach($tableName as $id => $name){
			$sql_alter_table = "ALTER TABLE `%s` DROP `%s`";
			$arg_alter_table = array($name, $label);
			$result_alter = mysql2_query_secure($sql_alter_table, $_SESSION['OCS']["writeServer"], $arg_alter_table);
			if(!$result_alter){
				return false;
			}
		}
		return true;
	}

	/**
	 * Get label name
	 *
	 * @param int $id
	 * @return string
	 */
	private function get_label_drop($id){
		$sql_label = "SELECT `LABEL_NAME` FROM `snmp_labels` WHERE ID = %s";
		$agr_label = array($id);

		$result_label = mysql2_query_secure($sql_label, $_SESSION['OCS']["readServer"], $agr_label);

		while ($item_label = mysqli_fetch_array($result_label)) {
			$labelName = $item_label['LABEL_NAME'];
		}
		return $labelName;
	}

	/**
	 * Get table type name
	 *
	 * @param int $id
	 * @return string
	 */
	private function get_table_type_drop($id){
		$sql_type = "SELECT `TABLE_TYPE_NAME` FROM `snmp_types` WHERE ID = %s";
		$arg_type = array($id);

		$result_type = mysql2_query_secure($sql_type, $_SESSION['OCS']["readServer"], $arg_type);

		while ($item_type = mysqli_fetch_array($result_type)) {
			$tableName = $item_type['TABLE_TYPE_NAME'];
		}
		return $tableName;
	}

	/**
	 * Get all type id associated to label
	 *
	 * @param int $labelID
	 * @return array
	 */
	private function get_config($labelID){
		$sql = "SELECT DISTINCT `TYPE_ID` FROM `snmp_configs` WHERE `LABEL_ID` = %s";
		$arg = array($labelID);

		$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
		while ($item = mysqli_fetch_array($result)) {
			$type[] = $item['TYPE_ID'];
		}

		return $type;
	}

	/**
	 * Delete snmp config
	 *
	 * @param int $id
	 * @return boolean
	 */
	public function delete_config($id){
		$sql = "SELECT `TYPE_ID`, `LABEL_ID` FROM `snmp_configs` WHERE ID = %s";
		$arg = array($id);

		$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
		while ($item = mysqli_fetch_array($result)) {
			$type = $item['TYPE_ID'];
			$label = $item['LABEL_ID'];
		}

		$tableName = $this->get_table_type_drop($type);
		$labelName = $this->get_label_drop($label);

		$sql_alter_table = "ALTER TABLE `%s` DROP `%s`";
		$arg_alter_table = array($tableName, $labelName);
		$result_alter = mysql2_query_secure($sql_alter_table, $_SESSION['OCS']["writeServer"], $arg_alter_table);
		if($result_alter) {
			$sqlQuery = "DELETE FROM `snmp_configs` WHERE ID = %s";
			$sqlArg = [$id];
			$result = mysql2_query_secure($sqlQuery, $_SESSION['OCS']["readServer"], $sqlArg);
			if($result){
				return true;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Return MIBs files list
	 *
	 * @return array
	 */
	public function get_mib() {
		$champs = array('SNMP_MIB_DIRECTORY' => 'SNMP_MIB_DIRECTORY');
		$values = look_config_default_values($champs);

		$mib_files = glob($values['tvalue']['SNMP_MIB_DIRECTORY'].'/*.{txt,my}', GLOB_BRACE);
		$mib_files = str_replace($values['tvalue']['SNMP_MIB_DIRECTORY']."/", "", $mib_files);
		
		foreach($mib_files as $mib) {
			$mib_name[$mib] = $mib;
		}

		return $mib_name;
	}

	public function sort_mib($post) {
		$mib_check = null;
		$config = null;

		foreach($post as $key => $value) {
			if(strpos($key, "checkbox_") !== false) {
				$mib_check = explode("_", $key);
			}
			if($mib_check != null) {
				if($key == "label_".$mib_check[1]) {
					$config[$mib_check[1]]['label'] = $value;
				}
				if($key == "oid_".$mib_check[1]) {
					$config[$mib_check[1]]['oid'] = $value;
				}
			}

			if(!empty($config) && $config[$mib_check[1]]['label'] != null && $config[$mib_check[1]]['oid'] != null) {
				$result = $this->snmp_config($post['type_id'], $config[$mib_check[1]]['label'], $config[$mib_check[1]]['oid']);
				$mib_check = null;
				$config = null;

				if(!$result) {
					return false;
				}
			}
		}

		return true;
	}

	public function get_all_type() {
		$list = [];

		$sql = 'SELECT * FROM snmp_types';
		$result = mysqli_query($_SESSION['OCS']["readServer"], $sql);

		while($item = mysqli_fetch_array($result)) {
			$list[$item['ID']]['TABLENAME'] = $item['TABLE_TYPE_NAME'];
			$list[$item['ID']]['TYPENAME'] = $item['TYPE_NAME'];
		}

		return $list;
	}

	public function show_columns($table) {
		$sql = 'SHOW COLUMNS FROM %s';
		$arg = array($table);
		$result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);

		$columns = [];
		while($item = mysqli_fetch_array($result)) {
			if($item['Field'] != 'ID') {
				$columns[$item['Field']] = $item['Field'];
			}
		}

		return $columns;
	}

}