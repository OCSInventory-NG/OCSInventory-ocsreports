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

/**
 * Class for Accountinfo
*/
class Admininfo
{
	// Config table
	const ACCOUNTINFO_CONFIG = "accountinfo_config";
	// Computers accountinfo table
	const ACCOUNTINFO_COMPUTERS = "accountinfo";
	// Snmp accountinfo table
	const ACCOUNT_INFO_SNMP = "snmp_accountinfo";

	// Matrix between field type and sql type
	private $sql_type_accountinfo = array(
		'0' => 'VARCHAR(255)',
		'1' => 'LONGTEXT',
		'2' => 'VARCHAR(255)',
		'5' => 'VARCHAR(255)',
		'14' => 'DATE',
		'11' => 'VARCHAR(255)'
	);
	// Field type
	public $type_accountinfo = array(
		'0' => 'TEXT',
		'1' => 'TEXTAREA',
		'2' => 'SELECT',
		'5' => 'CHECKBOX',
		'14' => 'DATE',
		'11' => 'RADIOBUTTON'
  	);

	/***************************************
	 *           PUBLIC FUNCTIONS          *
	 ***************************************/
	
	/**
	 * find_info_accountinfo
	 *
	 * @param  mixed $id
	 * @param  mixed $type
	 * @param  mixed $exclu_type
	 * @return array
	 */
	public function find_info_accountinfo($id = "", $type = null, $exclu_type = null) {
		// Initialize values
		$list_field = array('id', 'type', 'name', 'id_tab', 'comment', 'show_order', 'account_type', 'default_value');
		$array_info_account = [];
		$where = null;
		$and = null;

		// If type is specified
		if (!is_null($type)) {
			$where = " WHERE account_type='".$type."' ";
			$and = " AND account_type='".$type."' ";
		}
	
		// If exclu type is specified
		if (!is_null($exclu_type)) {
			if (!is_null($where)) {
				$where .= " AND type NOT IN (".$exclu_type.") ";
				$and .= " AND type NOT IN (".$exclu_type.")";
			}
		}
	
		if (is_array($id)) {
			$sql_info_account = "SELECT ".implode(',', $list_field)." FROM ".self::ACCOUNTINFO_CONFIG." WHERE id IN (%s) ".$and." ORDER BY show_order DESC";
			$arg_info_account = array(implode(',', $id));
		} elseif (!empty($id)) {
			$sql_info_account = "SELECT ".implode(',', $list_field)." FROM ".self::ACCOUNTINFO_CONFIG." WHERE id=%s  ".$and." ORDER BY show_order DESC";
			$arg_info_account = array($id);
		} else {
			$sql_info_account = "SELECT ".implode(',', $list_field)." FROM ".self::ACCOUNTINFO_CONFIG." ".$where." ORDER BY show_order DESC";
			$arg_info_account = array();
		}

		$result_info_account = mysql2_query_secure($sql_info_account, $_SESSION['OCS']["readServer"], $arg_info_account);

		while ($val_info_account = mysqli_fetch_array($result_info_account)) {
			$array_info_account[$val_info_account['id']] = $val_info_account;
		}
		
		return $array_info_account;
	}
	
	/**
	 * accountinfo_tab
	 *
	 * @param  mixed $id
	 * @return boolean
	 */
	public function accountinfo_tab($id) {
		$info_tag = $this->find_info_accountinfo($id);

		if ($info_tag[$id]['type'] == 2 || $info_tag[$id]['type'] == 5 || $info_tag[$id]['type'] == 11) {
			return $this->find_value_field('ACCOUNT_VALUE_' . $info_tag[$id]['name']);
		} elseif ($info_tag[$id]['type'] == 8) {
			return false;
		}
	
		return true;
	}
	
	/**
	 * add_accountinfo
	 *
	 * @param  mixed $newfield
	 * @param  mixed $newtype
	 * @param  mixed $newlbl
	 * @param  mixed $tab
	 * @param  mixed $type
	 * @param  mixed $default_value
	 * @return array
	 */
	public function add_accountinfo($newfield, $newtype, $newlbl, $tab, $type = 'COMPUTERS', $default_value) {
		global $l;

		if ($type == 'COMPUTERS') {
			$table = self::ACCOUNTINFO_COMPUTERS;
		} elseif ($type == 'SNMP') {
			$table = self::ACCOUNT_INFO_SNMP;
		} else {
			return array('ERROR' => $type);
		}

		//can not contain special characters
		if (preg_match('/[^0-9A-Za-z]/', $newfield)) {
			return array('ERROR' => $l->g(1178) . ' : <i>' . $l->g(1070) . "</i> " . $l->g(1179) . " <br>");
		}
	
		$ERROR = $this->dde_exist($newfield, '', $type);
		$id_order = $this->max_order(self::ACCOUNTINFO_CONFIG, 'SHOW_ORDER');
	
		if ($ERROR == '') {
			$sql_insert_config = "INSERT INTO `%s` (TYPE,NAME,ID_TAB,COMMENT,SHOW_ORDER,ACCOUNT_TYPE,DEFAULT_VALUE) VALUES(%s,'%s',%s,'%s',%s,'%s','%s')";
			$arg_insert_config = array(
				self::ACCOUNTINFO_CONFIG,
				$newtype,
				$newfield,
				$tab,
				$newlbl, 
				$id_order, 
				$type, 
				$default_value
			);
			mysql2_query_secure($sql_insert_config, $_SESSION['OCS']["writeServer"], $arg_insert_config);
	
			$sql_add_column = "ALTER TABLE " . $table . " ADD COLUMN fields_%s %s default NULL";
			$arg_add_column = array(mysqli_insert_id($_SESSION['OCS']["writeServer"]), $this->sql_type_accountinfo[$newtype]);

			mysql2_query_secure($sql_add_column, $_SESSION['OCS']["writeServer"], $arg_add_column);
			unset($newfield, $newlbl, $_SESSION['OCS']['TAG_LBL']);

			return array('SUCCESS' => $l->g(1069));
		} else {
			return array('ERROR' => $ERROR);
		}
	}
	
	/**
	 * del_accountinfo
	 *
	 * @param  mixed $id
	 * @return void
	 */
	public function del_accountinfo($id) {
		//SNMP or COMPUTERS?
		$sql_found_account_type = "SELECT account_type FROM `%s` WHERE id = '%s'";
		$arg_found_account_type = array(self::ACCOUNTINFO_CONFIG, $id);

		$result = mysql2_query_secure($sql_found_account_type, $_SESSION['OCS']["readServer"], $arg_found_account_type);
		$val = mysqli_fetch_array($result);

		if (isset($val['account_type']) && $val['account_type'] == "SNMP") {
			$table = self::ACCOUNT_INFO_SNMP;
		} elseif (isset($val['account_type']) && $val['account_type'] == "COMPUTERS") {
			$table = self::ACCOUNTINFO_COMPUTERS;
		} else {
			return false;
		}
	
		//DELETE INTO CONFIG TABLE
		$sql_delete_config = "DELETE FROM `%s` WHERE ID = '%s'";
		$arg_delete_config = array(self::ACCOUNTINFO_CONFIG, $id);

		mysql2_query_secure($sql_delete_config, $_SESSION['OCS']["writeServer"], $arg_delete_config);
	
		//ALTER TABLE ACCOUNTINFO
		$sql_DEL_column = "ALTER TABLE " . $table . " DROP COLUMN fields_%s";
		$arg_DEL_column = $id;

		mysql2_query_secure($sql_DEL_column, $_SESSION['OCS']["writeServer"], $arg_DEL_column);
		unset($_SESSION['OCS']['TAG_LBL']);
	}
	
	/**
	 * find_all_account_tab
	 *
	 * @param  mixed $tab_value
	 * @param  mixed $onlyactiv
	 * @param  mixed $first
	 * @return array
	 */
	public function find_all_account_tab($tab_value, $onlyactiv = '', $first = '') {
		$sql_tab_account = "SELECT IVALUE,TVALUE FROM `config` ";
	
		if ($onlyactiv != '') {
			$sql_tab_account .= ", ".self::ACCOUNTINFO_CONFIG;
		}
	
		$sql_tab_account .= " WHERE config.name LIKE '%s'";
	
		if ($onlyactiv != '') {
			$sql_tab_account .= " AND ".self::ACCOUNTINFO_CONFIG.".id_tab=config.ivalue AND ".self::ACCOUNTINFO_CONFIG.".account_type='" . $onlyactiv . "'";
		}
	
		$arg_tab_account = $tab_value.'%';
	
		$result_tab_account = mysql2_query_secure($sql_tab_account, $_SESSION['OCS']["readServer"], $arg_tab_account);

		while ($val_tab_account = mysqli_fetch_array($result_tab_account)) {
			if (!isset($array_tab_account['FIRST']) && $first != '') {
				$array_tab_account['FIRST'] = $val_tab_account['IVALUE'];
			}

			$array_tab_account[$val_tab_account['IVALUE']] = $val_tab_account['TVALUE'];
		}
		
		return $array_tab_account ?? '';
	}
	
	/**
	 * find_value_field
	 *
	 * @param  mixed $name
	 * @param  mixed $type
	 * @return array
	 */
	public function find_value_field($name, $type = null) {
		$array_tab_account = array();
		$data = look_config_default_values($name . '\_%', true);

		if (isset($data['name'])) {
			foreach ($data['name'] as $field => $value) {
				if($type == '5') {
					$array_tab_account[$data['tvalue'][$field]] = $data['tvalue'][$field];
				} else {
					$array_tab_account[$data['ivalue'][$field]] = $data['tvalue'][$field];
				}
			}
		}
		
		return $array_tab_account;
	}
	
	/**
	 * witch_field_more
	 *
	 * @param  mixed $account_type
	 * @return array
	 */
	public function witch_field_more($account_type = null) {
		$list_field = array('ID', 'TYPE', 'NAME', 'COMMENT');
		$list_fields = array();
		$list_name = array();
		$list_type = array();
		$sql_accountinfo = "SELECT ".implode(',', $list_field)." from ".self::ACCOUNTINFO_CONFIG;

		if (!is_null($account_type)) {
			$sql_accountinfo .= " WHERE account_type = '".$account_type."'";
		}

		$result_accountinfo = mysql2_query_secure($sql_accountinfo, $_SESSION['OCS']["readServer"]);
	
		while ($item = mysqli_fetch_object($result_accountinfo)) {
			$list_fields[$item->ID] = $item->COMMENT;
			$list_name[$item->ID] = $item->NAME;
			$list_type[$item->ID] = $item->TYPE;
		}

		return array('LIST_FIELDS' => $list_fields, 'LIST_NAME' => $list_name, 'LIST_TYPE' => $list_type);
	}
	
	/**
	 * update_accountinfo_config
	 *
	 * @param  mixed $id
	 * @param  mixed $array_new_values
	 * @return void
	 */
	public function update_accountinfo_config($id, $array_new_values) {
		//Update
		$sql_update_config = "UPDATE ".self::ACCOUNTINFO_CONFIG." SET ";
		$arg_update_config = array();

		foreach ($array_new_values as $field => $value) {
			if ($field == "TYPE") {
				$new_type_field = $this->sql_type_accountinfo[$value];
			}
			$sql_update_config .= "%s='%s', ";
			array_push($arg_update_config, $field);
			array_push($arg_update_config, $value);
		}

		$sql_update_config = substr($sql_update_config, 0, -2);
	
		if (is_numeric($id)) {
			$sql_update_config .= "  WHERE ID = '%s'";
			array_push($arg_update_config, $id);
		} else {
			$temp_id = explode(',', $id);
			$sql_update_config .= "  WHERE ID IN (";

			foreach ($temp_id as $value) {
				$sql_update_config .= $value . "%s,";
				array_push($arg_update_config, $value);
			}

			$sql_update_config = substr($sql_update_config, 0, -1) . ")";
		}
	
		mysql2_query_secure($sql_update_config, $_SESSION['OCS']["writeServer"], $arg_update_config);
		unset($_SESSION['OCS']['TAG_LBL']);

		return $new_type_field;
	}
		
	/**
	 * find_new_order
	 *
	 * @param  mixed $updown
	 * @param  mixed $id
	 * @param  mixed $type
	 * @param  mixed $onglet
	 * @return array
	 */
	public function find_new_order($updown, $id, $type, $onglet) {
		$tab_order = array();

		if (!is_numeric($id) || !is_numeric($onglet)) {
			return false;
		}

		$sql = "SELECT ID,SHOW_ORDER FROM `%s` WHERE account_type='%s' AND id_tab=%s ORDER BY show_order";
		$arg = array(self::ACCOUNTINFO_CONFIG, $type, $onglet);
		$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);

		while ($item = mysqli_fetch_object($result)) {
			$array_id[] = $item->ID;
			$array_order[] = $item->SHOW_ORDER;
		}

		foreach ($array_id as $key => $value) {
			if ($array_id[$key] == $id) {
				if ($updown == 'UP') {
					$tab_order['NEW'] = $array_id[$key - 1];
					$tab_order['NEW_VALUE'] = $array_order[$key - 1];
				} else {
					$tab_order['NEW'] = $array_id[$key + 1];
					$tab_order['NEW_VALUE'] = $array_order[$key + 1];
				}

				$tab_order['OLD'] = $value;
				$tab_order['OLD_VALUE'] = $array_order[$key];
			}
		}
	
		return $tab_order;
	}
	
	/**
	 * update_accountinfo
	 *
	 * @param  mixed $id
	 * @param  mixed $array_new_values
	 * @param  mixed $type
	 * @return array
	 */
	public function update_accountinfo($id, $array_new_values, $type) {
		global $l;
		$error = $this->dde_exist($array_new_values['NAME'], $id, $type);

		if ($error == '') {
			//Update
			$new_type_field = $this->update_accountinfo_config($id, $array_new_values);
			//update column type in accountinfo table
			$sql_update_column = "ALTER TABLE `%s` CHANGE fields_%s fields_%s %s";
			$arg_update_column = array(self::ACCOUNTINFO_COMPUTERS, $id, $id, $new_type_field);
			mysql2_query_secure($sql_update_column, $_SESSION['OCS']["writeServer"], $arg_update_column);

			return array('SUCCESS' => $l->g(711));
		} else {
			return array('ERROR' => $error);
		}
	}
	
	/**
	 * admininfo_computer
	 *
	 * @param  mixed $id
	 * @return array
	 */
	public function admininfo_computer($id = "") {
		global $l;

		if (!is_numeric($id) && $id != "") {
			return $l->g(623);
		}

		$arg_account_data = array();
		$sql_account_data = "SELECT * FROM ".self::ACCOUNTINFO_COMPUTERS;

		if (is_numeric($id)) {
			$sql_account_data .= " WHERE hardware_id=%s";
			$arg_account_data = array($id);
		} else {
			$sql_account_data .= " LIMIT 1 ";
		}
	
		$res_account_data = mysql2_query_secure($sql_account_data, $_SESSION['OCS']["writeServer"], $arg_account_data);
		$val_account_data = mysqli_fetch_array($res_account_data);

		if (is_array($val_account_data)) {
			return $val_account_data;
		} else {
			return $l->g(1093);
		}
	}
	
	/**
	 * updateinfo_computer
	 *
	 * @param  mixed $id
	 * @param  mixed $values
	 * @param  mixed $list
	 * @return void
	 */
	public function updateinfo_computer($id, $values, $list = '') {
		global $l;

		if (!is_numeric($id) && $list == '') {
			return $l->g(623);
		}

		$arg_account_data = array();
		$sql_account_data = "UPDATE ".self::ACCOUNTINFO_COMPUTERS." SET ";

		foreach ($values as $field => $val) {
			// Check account info
			$accountinfo_id = explode("_", $field);
			$date_accountinfo = false;
			if($accountinfo_id != false and $field != "TAG"){
				$accountinfo_datas = $this->find_info_accountinfo($accountinfo_id[1]);
	
				if($accountinfo_datas[$accountinfo_id[1]]['type'] === '14'){
					$date_accountinfo = true;
				}
			}
	
			$sql_account_data .= " %s='%s', ";
			array_push($arg_account_data, $field);
			if($date_accountinfo){ // If date
				array_push($arg_account_data, date("Y-m-d", strtotime($this->changeDateFormat($_SESSION["OCS"]["LANGUAGE"], $val))));
			}else{ // If not date
				array_push($arg_account_data, $val);
			}
	
		}

		$sql_account_data = substr($sql_account_data, 0, -2);

		if (is_numeric($id) && $list == '') {
			$sql_account_data .= " WHERE hardware_id=%s";
		}

		if ($list != '') {
			$sql_account_data .= " WHERE hardware_id IN (%s)";
		}
	
		array_push($arg_account_data, $id);
		mysql2_query_secure($sql_account_data, $_SESSION['OCS']["writeServer"], $arg_account_data);

		return $l->g(1121);
	}
	
	/**
	 * updown
	 *
	 * @param  mixed $field
	 * @param  mixed $type
	 * @return string
	 */
	public function updown($field, $type) {
		global $form_name;

		if ($type == 'UP') {
			return "<a href=# OnClick='pag(\"" . $field . "\",\"UP\",\"" . $form_name . "\");'><image src='image/up.png'></a>";
		} elseif ($type == 'DOWN') {
			return "<a href=# OnClick='pag(\"" . $field . "\",\"DOWN\",\"" . $form_name . "\");'><image src='image/down.png'></a>";
		}
	}
	
	/**
	 * show_accountinfo
	 *
	 * @param  mixed $id
	 * @param  mixed $type
	 * @param  mixed $exclu_type
	 * @return array
	 */
	public function show_accountinfo($id = '', $type = '', $exclu_type = '') {
		global $protectedPost;
	
		$data = $this->find_info_accountinfo($id, $type, $exclu_type);
		$i = 0;

		foreach ($data as $v) {
			foreach ($v as $key => $value) {
				switch ($key) {
					case "id":
						if ($v['name'] != 'TAG') {
							$name_field[$i] = 'fields_' . $value;
						} else {
							$name_field[$i] = $v['name'];
							$value_field[$i] = $protectedPost[$v['name']] ?? '';
						}
						break;
					case "type":
						$type_field[$i] = $value;
						switch ($value) {
							case '14':
								$comment_behing[$i] = datePick('fields_' . $v['id']);
								$config[$i]['CONFIG']['JAVASCRIPT'] = "READONLY";
								$config[$i]['CONFIG']['SIZE'] = 7;
								break;
							case '5':
							case '2':
							case '11':
								$value_field[$i] = $this->find_value_field("ACCOUNT_VALUE_" . $v['name']);
								$comment_behing[$i] = '';
								$config[$i]['CONFIG']['DEFAULT'] = 'YES';
								break;
							case '1':
								$config[$i]['CONFIG']['COLS'] = 40;
								$config[$i]['CONFIG']['ROWS'] = 5;
								break;
	
							default:
								$comment_behing[$i] = '';
								$config[$i]['CONFIG']['SIZE'] = 20;
								break;
						}
	
						break;
					case "comment":
						$tab_name[$i] = $value;
						break;
				}
			}

			if (!isset($value_field[$i])) {
				$value_field[$i] = $protectedPost['fields_' . $v['id']] ?? '';
			}

			$i++;
		}
	
		return array(
			'FIELDS' => array(
				'name_field' => $name_field,
				'tab_name' => $tab_name,
				'type_field' => $type_field,
				'value_field' => $value_field
			),
			'CONFIG' => $config,
			'COMMENT_AFTER' => $comment_behing
		);
	}
	
	/**
	 * insertinfo_computer
	 *
	 * @param  mixed $id
	 * @param  mixed $fields
	 * @param  mixed $values
	 * @return void
	 */
	public function insertinfo_computer($id, $fields, $values) {
		array_push($fields, 'hardware_id');
		array_push($values, $id);

		$sql = "INSERT INTO ".self::ACCOUNTINFO_COMPUTERS;
		$arg_sql = array();
		$sql = mysql2_prepare($sql, $arg_sql, $fields, true);
		$sql['SQL'] .= " VALUES ";
		$sql = mysql2_prepare($sql['SQL'], $sql['ARG'], $values);

		mysql2_query_secure($sql['SQL'], $_SESSION['OCS']["writeServer"], $sql['ARG']);
	}
	
	/**
	 * replace_tag_value
	 *
	 * @param  mixed $type
	 * @param  mixed $option
	 * @return array
	 */
	public function replace_tag_value($type = '', $option = array()) {
		$info_tag = $this->find_info_accountinfo('', $type);

		if (is_array($info_tag)) {
			foreach ($info_tag as $value) {
				$info_value_tag = $this->accountinfo_tab($value['id']);

				if (is_array($info_value_tag)) {
					$comment = '';

					if (isset($option['comment_be'])) {
						$comment .= $option['comment_be'];
					}

					$comment .= $value['comment'];

					if (isset($option['comment_aft'])) {
						$comment .= $option['comment_aft'];
					}

					$tab_options[$comment] = $info_value_tag;
				}
			}
		}
	
		if(isset($tab_options)) {
			return $tab_options;
		}	
	}
	
	/**
	 * find_value_in_field
	 *
	 * @param  mixed $tag
	 * @param  mixed $value_2_find
	 * @param  mixed $type
	 * @return array
	 */
	public function find_value_in_field($tag, $value_2_find, $type = 'COMPUTERS') {
		$p = $this->find_info_accountinfo($tag, $type);
		$values = look_config_default_values('ACCOUNT_VALUE_' . $p[$tag]['name'] . "_%", true);

		if (is_array($values['tvalue'])) {
			foreach ($values['tvalue'] as $key => $value) {
				if (stristr($value, $value_2_find)) {
					$decoup = explode('_', $key);
					$fr = array_pop($decoup);
					$list_tag_id[] = $fr;
				}
			}

			return $list_tag_id;
		} else {
			return false;
		}
	}
	
	/**
	 * interprete_accountinfo
	 *
	 * @param  mixed $list_fields
	 * @param  mixed $tab_options
	 * @param  mixed $type
	 * @return array
	 */
	public function interprete_accountinfo($list_fields, $tab_options, $type = 'COMPUTERS') {
		global $l;

		$info_tag = $this->find_info_accountinfo('', $type);

		if (is_array($info_tag)) {
			foreach ($info_tag as $value) {
				$value['comment'] = $l->g(1210) . " " . $value['comment'];
				$info_value_tag = $this->accountinfo_tab($value['id']);

				if (is_array($info_value_tag)) {
					$tab_options['REPLACE_VALUE'][$value['comment']] = $info_value_tag;
				}

				if ($value['name'] != 'TAG' && $info_value_tag) {
					$list_fields[$value['comment']] = 'a.fields_' . $value['id'];
				} elseif ($value['name'] == 'TAG') {
					$list_fields[$value['comment']] = 'a.TAG';
					$default_value[$value['comment']] = $value['comment'];
				}
			}
		}

		return array('TAB_OPTIONS' => $tab_options, 'LIST_FIELDS' => $list_fields, 'DEFAULT_VALUE' => $default_value);
	}
	

	/****************************************
	 *           PRIVATE FUNCTIONS          *
	 ****************************************/
	 
	 /**
	  * max_order
	  *
	  * @param  mixed $table
	  * @param  mixed $field
	  * @return int
	  */
	 private function max_order($table, $field) {
		$sql = "SELECT max(%s) AS max_id FROM %s";
		$arg = array($field, $table);
		$result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
		$val = mysqli_fetch_array($result);

		return $val['max_id'] + 1;
	}
	
	/**
	 * dde_exist
	 *
	 * @param  mixed $name
	 * @param  mixed $id
	 * @param  mixed $type
	 * @return string
	 */
	private function dde_exist($name, $id = '', $type) {
		global $l;
	
		if (trim($name) != '') {
			$sql_verif = "SELECT count(*) c FROM `%s` WHERE NAME = '%s' AND ACCOUNT_TYPE='%s'";
			$arg_verif = array(self::ACCOUNTINFO_CONFIG, $name, $type);

			if ($id != '' && is_numeric($id)) {
				$sql_verif .= " AND ID != %s";
				array_push($arg_verif, $id);
			}

			$res_verif = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $arg_verif);
			$val_verif = mysqli_fetch_array($res_verif);

			//this name is already exist
			if ($val_verif['c'] > 0) {
				return $l->g(1067);
			}
		} else {
			//name can't be null
			return $l->g(1068);
		}
	}
	
	/**
	 * changeDateFormat
	 *
	 * @param  mixed $lang
	 * @param  mixed $val
	 * @return string
	 */
	private function changeDateFormat($lang, $val){
		$tab = array("fr_FR", "br_BR", "it_IT", "pl_PL", "pt_PT", "ru_RU", "si_SI", "es_ES", "tr_TR");

		if(in_array($lang, $tab)) {
			$tab2 = explode("/", $val);

			return $tab2[1]."/".$tab2[0]."/".$tab2[2];
		} else {
			return $val;
		}
	}

	

}