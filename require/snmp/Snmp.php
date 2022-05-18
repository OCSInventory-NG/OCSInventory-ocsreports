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
	public function create_type($typeName) {
		// Verif if type already exists
		$sql_verif = "SELECT * FROM `snmp_types` WHERE `TYPE_NAME` = '%s'";
		$sql_verif_arg = array(addslashes($typeName));
		$verif = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $sql_verif_arg);

		if($verif->num_rows == 0) {
			// Insert info table in type snmp table
			$typeName = str_replace("&#039;", "'", $typeName);
			$tableTypeName = $this->cleanString($typeName);
			$tableTypeName = strtolower($tableTypeName);
			$tableTypeName = "snmp_".$tableTypeName;

			$sql = "INSERT INTO `snmp_types` (`TYPE_NAME`, `TABLE_TYPE_NAME`) VALUES ('%s','%s')";
			$sql_arg = array(addslashes($typeName), $tableTypeName);

			$result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $sql_arg);

			if(!$result) {
				return 9024;
			}

			$sql_create_table =   "CREATE TABLE IF NOT EXISTS `%s` (
										`ID` INT(6) NOT NULL AUTO_INCREMENT,
										PRIMARY KEY (`ID`)
									) ENGINE=InnoDB DEFAULT CHARSET=UTF8;";
			$sql_arg_table = array($tableTypeName);

			$result_create = mysql2_query_secure($sql_create_table, $_SESSION['OCS']["writeServer"], $sql_arg_table);
			
			if($result_create) {
				return 0;
			} else {
				$sql = "DELETE FROM `snmp_types` WHERE `TABLE_TYPE_NAME` = '%s'";
				$sql_arg = array($tableTypeName);

				$result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $sql_arg);
				return 9024; // inserer erreur insertion dans la config
			}
		} else {
			return 9023;
		}
	}

	/**
	 * Insert new type condition into DB
	 *
	 * @param int $typeID
	 * @param string $oid
	 * @param string $oidString
	 * @return boolean
	 */
	public function create_type_condition($typeID, $oid, $oiValue) {

		// Insert condition
		$oiValue = str_replace("&#039;", "'", $oiValue);

		$sql = "INSERT INTO `snmp_types_conditions` (`TYPE_ID`, `CONDITION_OID`, `CONDITION_VALUE`) VALUES (%s,'%s','%s')";
		$sql_arg = array($typeID, addslashes($oid), addslashes($oiValue));

		$result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $sql_arg);

		if(!$result) {
			return 9024;
		}
		
		return 0;
	}

	/**
	 * Insert new label into DB 
	 *
	 * @param string $labelName
	 * @return boolean
	 */
	public function create_label($labelName) {
		$labelName = $this->cleanString($labelName);
		$sql_verif = "SELECT * FROM `snmp_labels` WHERE `LABEL_NAME` = '%s'";
		$sql_verif_arg = array(addslashes($labelName));
		$verif = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $sql_verif_arg);

		if($verif->num_rows == 0) {
			$sql = "INSERT INTO `snmp_labels` (`LABEL_NAME`) VALUES ('%s')";
			$sql_arg = array(addslashes($labelName));

			$result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $sql_arg);

			if($result) {
				return 0;
			} else {
				return 9026;
			}
		} else {
			return 9025;
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
	public function snmp_config($typeID, $labelID, $oid, $reconciliation = null) {
		global $l;
		$result_alter_table = $this->add_label_column($typeID, $labelID, $reconciliation);

		if($result_alter_table) {
			if($reconciliation != null) {
				$sql = "INSERT INTO `snmp_configs` (`TYPE_ID`,`LABEL_ID`,`OID`,`RECONCILIATION`) VALUES (%s,%s,'%s','%s')";
				$sql_arg = array($typeID, $labelID, addslashes($oid), 'Yes');
			} else {
				$sql = "INSERT INTO `snmp_configs` (`TYPE_ID`,`LABEL_ID`,`OID`) VALUES (%s,%s,'%s')";
				$sql_arg = array($typeID, $labelID, addslashes($oid));
			}
			$result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $sql_arg);

			if($result) {
				return 0;
			} else {
				return 9027;
			}
		} else {
			return 9027;
		}
	}

	/**
	 * Alter table type and add column label
	 *
	 * @param int $typeID
	 * @param int $labelID
	 * @return boolean
	 */
	private function add_label_column($typeID, $labelID, $reconciliation) {
		$tableName = $this->get_table_type_drop($typeID);
		$labelName = $this->get_label_drop($labelID);
		
		if($reconciliation != null) {
			$sql_alter = "ALTER TABLE `%s` ADD `%s` VARCHAR(255) NOT NULL";
		} else {
			$sql_alter = "ALTER TABLE `%s` ADD `%s` TEXT NOT NULL";
		}
		
		$arg_alter = array($tableName, $labelName);
		$result_alter = mysql2_query_secure($sql_alter, $_SESSION['OCS']["writeServer"], $arg_alter);

		if($reconciliation != null) {
			$sql_unique = "ALTER TABLE `%s` ADD UNIQUE (`%s`)";
			$arg_unique = array($tableName, $labelName);
			$result_unique = mysql2_query_secure($sql_unique, $_SESSION['OCS']["writeServer"], $arg_unique);
		}
		if($result_alter) {
			return true;
		} else {
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
			mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArg);

			// Remove type conditions associated to this type
			$sqlQuery = "DELETE FROM `snmp_types_conditions` WHERE TYPE_ID = %s";
			$sqlArg = [$id];
			mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArg);

			// Remove config associated to this type
			$sqlQuery = "DELETE FROM `snmp_configs` WHERE TYPE_ID = %s";
			$sqlArg = [$id];
			mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArg);

			return 0;
		}else{
			return 9028;
		}
	}

	/**
	 * Remove type in BDD
	 *
	 * @param int $id
	 * @return boolean
	 */
	public function delete_type_condition($id){
		$sqlQuery = "DELETE FROM `snmp_types_conditions` WHERE ID = %s";
		$sqlArg = [$id];
		mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArg);

		return 0;
	}

	/**
	 * DROP type table
	 *
	 * @param int $id
	 * @return boolean
	 */
	private function drop_table($id){
		$tableName = $this->get_table_type_drop($id);

		$sql_drop_table = "DROP TABLE `%s`";
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
			mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArg);

			// Remove config associated to this label
			$sqlQuery = "DELETE FROM `snmp_configs` WHERE LABEL_ID = %s";
			$sqlArg = [$id];
			$result = mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArg);
			if($result){
				return 0;
			} else {
				return 9029;
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

		foreach($type as $value){
			$tableName[] = $this->get_table_type_drop($value);
		}

		foreach($tableName as $name){
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
			$result = mysql2_query_secure($sqlQuery, $_SESSION['OCS']["writeServer"], $sqlArg);
			if($result){
				return 0;
			}else{
				return 9030;
			}
		} else {
			return 9030;
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

		$mib_files = glob($values['tvalue']['SNMP_MIB_DIRECTORY'].'/*', GLOB_BRACE);
		$mib_files = str_replace($values['tvalue']['SNMP_MIB_DIRECTORY']."/", "", $mib_files);
		
		foreach($mib_files as $mib) {
			$mib_name[$mib] = $mib;
		}



		return $mib_name ?? '';
	}

	public function sort_mib($post) {
		$mib_check = null;
		$config = null;

		foreach($post as $key => $value) {
			if(str_contains($key, "checkbox_")) {
				$mib_check = explode("_", $key);
			}
			
			if($key == "label_".$mib_check[1]) {
				$config[$mib_check[1]]['label'] = $value;
			}
			if($key == "oid_".$mib_check[1]) {
				$config[$mib_check[1]]['oid'] = $value;
			}
			if($key == "reconciliation_".$mib_check[1]) {
				$config[$mib_check[1]]['reconciliation'] = $value;
			}
		}

		foreach($config as $key => $value) {
				if($config[$key]['label'] != null && $config[$key]['oid'] != null) {
					$result = $this->snmp_config($post['type_id'], $config[$key]['label'], $config[$key]['oid'], $config[$key]['reconciliation']);

					if($result != 0) {
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
		$i = 0;
		while($item = mysqli_fetch_array($result)) {
			if($item['Field'] != 'ID') {
				$columns[$i] = $item['Field'];
				$i++;
			}
		}

		return $columns;
	}

	public function get_infos($tablename, $columns) {
		$column = implode(",",$columns);
		$sql = "SELECT ID,".$column." FROM %s";
		$arg = array($tablename);
		$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);

		$infos = [];
		if (!empty($result)) {
			while($item = mysqli_fetch_array($result)) {
				$infos[$item['ID']] = $item;
			}
		}

		return $infos;
	}

	/**
	 * Clean specil characters from string 
	 */
	private function cleanString($text) {
		$utf8 = array(
			'/[áàâãªä]/u'   =>   'a',
			'/[ÁÀÂÃÄ]/u'    =>   'A',
			'/[ÍÌÎÏ]/u'     =>   'I',
			'/[íìîï]/u'     =>   'i',
			'/[éèêë]/u'     =>   'e',
			'/[ÉÈÊË]/u'     =>   'E',
			'/[óòôõºö]/u'   =>   'o',
			'/[ÓÒÔÕÖ]/u'    =>   'O',
			'/[úùûü]/u'     =>   'u',
			'/[ÚÙÛÜ]/u'     =>   'U',
			'/ç/'           =>   'c',
			'/Ç/'           =>   'C',
			'/ñ/'           =>   'n',
			'/Ñ/'           =>   'N',
			'/–/'           =>   '_', // UTF-8 hyphen to "normal" hyphen
			'/[’‘‹›‚]/u'    =>   '_', // Literally a single quote
			'/[“”«»„]/u'    =>   '_', // Double quote
			'/ /'           =>   '_', // nonbreaking space (equiv. to 0x160)
			'/&#039;/'		=>	 '_',
			'/-/'			=>	 '_',
			"/'/"			=>	 '_',
		);
		return preg_replace(array_keys($utf8), array_values($utf8), $text);
	}

}