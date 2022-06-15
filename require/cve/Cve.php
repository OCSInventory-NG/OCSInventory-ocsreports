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
 * Class for cve-search
 */
class Cve
{
  public $CVE_SEARCH_URL = '';
  public $CVE_ACTIVE;
  private $CVE_BAN;
  private $CVE_ALL;
  public $CVE_EXPIRE_TIME;
  public $CVE_DELAY_TIME;
  private $publisherName;
  public $cve_attr = [];
  public $cve_history = [
    'FLAG'    => null,
    'CVE_NB'  => 0,
    'PUBLISHER_ID' => null,
    'NAME_ID' => null,
    'VERSION_ID'  => null
  ];
  private $cveNB = 0;

  function __construct(){
    $champs = array('VULN_CVESEARCH_ENABLE' => 'VULN_CVESEARCH_ENABLE',
        'VULN_CVESEARCH_HOST' => 'VULN_CVESEARCH_HOST',
        'VULN_BAN_LIST' => 'VULN_BAN_LIST',
        'VULN_CVESEARCH_VERBOSE' => 'VULN_CVESEARCH_VERBOSE',
        'VULN_CVESEARCH_ALL' => 'VULN_CVESEARCH_ALL',
        'VULN_CVE_EXPIRE_TIME' => 'VULN_CVE_EXPIRE_TIME',
        'VULN_CVE_DELAY_TIME' => 'VULN_CVE_DELAY_TIME');

    // Get configuration values from DB
    $values = look_config_default_values($champs);

    $this->CVE_ACTIVE = $values['ivalue']["VULN_CVESEARCH_ENABLE"] ?? 0;
    $this->CVE_SEARCH_URL = $values['tvalue']['VULN_CVESEARCH_HOST'] ?? "";
    $this->CVE_BAN = $values['tvalue']["VULN_BAN_LIST"] ?? "";
    $this->CVE_VERBOSE = $values['ivalue']["VULN_CVESEARCH_VERBOSE"] ?? 0;
    $this->CVE_ALL = $values['ivalue']["VULN_CVESEARCH_ALL"] ?? 0;
    $this->CVE_EXPIRE_TIME = $values['ivalue']["VULN_CVE_EXPIRE_TIME"] ?? null;
    $this->CVE_DELAY_TIME = $values['ivalue']["VULN_CVE_DELAY_TIME"] ?? null;

  }

  /**
   * History cve history
   */
  private function verif_history(){
    $sql = "SELECT ID FROM cve_search_history WHERE PUBLISHER_ID = %s";
    $arg = array($this->cve_history['PUBLISHER_ID']);
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);
    return $result->num_rows;
  }

  /**
   * Verif if history is empty
   */
  private function history_is_empty(){
    $sql = "SELECT ID FROM cve_search_history";
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
    return $result->num_rows;
  }

  /**
   * Insert FLAG on cve_history per Publisher
   */
  private function insertFlag() {
    $verif = $this->verif_history();
    if($verif >= 1) {
      $sql = "UPDATE cve_search_history SET FLAG_DATE = '%s', CVE_NB = %s WHERE PUBLISHER_ID = %s";
    } else {
      $sql = "INSERT INTO cve_search_history(FLAG_DATE, CVE_NB, PUBLISHER_ID) VALUES('%s', %s, %s)";
    }
    $sqlarg = array($this->cve_history['FLAG'], $this->cve_history['CVE_NB'], $this->cve_history['PUBLISHER_ID']);
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $sqlarg);
  }

  /**
   * Get all publisher
   */
  private function getPublisher($date, $check_history) {
    $sql = 'SELECT DISTINCT p.ID, p.PUBLISHER FROM software_publisher p
            LEFT JOIN software_link sl ON p.ID = sl.PUBLISHER_ID 
            LEFT JOIN software_name n ON n.ID = sl.NAME_ID 
            LEFT JOIN cve_search_history h ON h.PUBLISHER_ID = p.ID
            LEFT JOIN software_categories_link scl ON scl.PUBLISHER_ID = p.ID
          WHERE p.ID != 1 AND TRIM(p.PUBLISHER) != ""';
    if($this->CVE_BAN != ""){
      // fix cve ban retuns 0 cve -> double condition is necessary
      // bc 'NOT IN' does not apply to softs not referenced in scl table (not in any category)
      $sql .= ' AND (scl.CATEGORY_ID IS NULL OR scl.CATEGORY_ID NOT IN ('. $this->CVE_BAN .'))';
    }
    if($date != null && $check_history != 0) {
      $sql .= ' AND (h.FLAG_DATE <= "'.$date.'" OR p.ID NOT IN (SELECT PUBLISHER_ID FROM cve_search_history))';
    }
    $sql .= " ORDER BY p.PUBLISHER";

    return mysqli_query($_SESSION['OCS']["readServer"], $sql);
  }

  /**
   *  Get distinct software name by publisher
   */
  private function getSoftwareName($publisher_id) {
    $sql_soft = " SELECT DISTINCT n.NAME, sl.NAME_ID FROM software_name n 
                  LEFT JOIN software_link sl ON sl.NAME_ID = n.ID
                  LEFT JOIN software_categories_link scl ON scl.NAME_ID = n.ID
                  WHERE sl.PUBLISHER_ID = %s AND TRIM(n.NAME) != ''";
    if($this->CVE_BAN != ""){
      $sql_soft .= ' AND (scl.CATEGORY_ID IS NULL OR scl.CATEGORY_ID NOT IN ('. $this->CVE_BAN .'))';
    }
    $sql_soft .= " ORDER BY n.NAME";

    $arg_soft = array($publisher_id);

    return mysql2_query_secure($sql_soft, $_SESSION['OCS']["readServer"], $arg_soft);
  }

  /**
   *  Get distinct software name by publisher
   */
  private function getSoftwareVersion($name_id) {
    $sql_soft = " SELECT DISTINCT v.VERSION, sl.VERSION_ID FROM software_version v 
                  LEFT JOIN software_link sl ON sl.VERSION_ID = v.ID 
                  WHERE sl.NAME_ID = %s AND sl.VERSION_ID != 1";
    $sql_soft .= " ORDER BY v.VERSION";
    $arg_soft = array($name_id);

    return mysql2_query_secure($sql_soft, $_SESSION['OCS']["readServer"], $arg_soft);
  }

  /**
   *  Get distinct all software name and publisher
   */
  public function getSoftwareInformations($date = null, $clean = false){

    $this->verbose($this->CVE_VERBOSE, 4);

    $check_history = $this->history_is_empty();
    $publishers = $this->getPublisher($date, $check_history);
    
    $this->verbose($this->CVE_VERBOSE, 5);

    while ($item_publisher = mysqli_fetch_array($publishers)) {
      # Reset date
      $this->cve_history['FLAG'] = date('Y-m-d H:i:s');
      # Reset CVE NB
      $this->cve_history['CVE_NB'] = 0;
      $this->cve_history['PUBLISHER_ID'] = $item_publisher['ID'];
      $this->clean_cve($item_publisher['ID']);
      
      $this->publisherName = $item_publisher['PUBLISHER'];

      $result_soft = $this->getSoftwareName($item_publisher['ID']);

      $this->verbose($this->CVE_VERBOSE, 6);

      while ($item_soft = mysqli_fetch_array($result_soft)) {
        $this->cve_attr = null;
        if(!preg_match('/[^\x00-\x7F]/', $item_soft['NAME']) && !preg_match('#\\{([^}]+)\\}#', $item_soft['NAME'])){
          $this->cve_history['NAME_ID'] = $item_soft['NAME_ID'];
          $this->cve_attr[] = ["NAME" => $item_soft['NAME'], "VENDOR" => $item_publisher['PUBLISHER'], "VERSION" => null, "REAL_NAME" => $item_soft['NAME'], "REAL_VENDOR" => $item_publisher['PUBLISHER']];
          if($this->cve_attr != null) {
            $this->get_cve($this->cve_attr);
          }
        }
      }

      $this->insertFlag();

    }
  }

  /**
   *  Normalize a software name to a CPE software
   */ 
  private function cpeNormalizeName($name){
    if(preg_match("/Java/", $name) && preg_match("/Update/", $name)){
      return "jre";
    }

    $name = strtolower($name);
    $name = preg_replace("/\s*\([^\)]+\)+$/", "", $name);
    $name = preg_replace("/.x86_/", "", $name);
    $name = preg_replace("/\s*\d+-bit$/", "", $name);
    $name = preg_replace("/\s*(v|version|release)?\s*[\d\.]+(\s+ESR)?$/", "", $name);
    $name = preg_replace("/\s*\(r\)/", "", $name);
    $name = preg_replace('/[^\x00-\x7F]/', "", $name);
    $name = trim($name);

    return preg_replace("/\s/", "_", $name);
  }

  /**
   *  Normalize a software vendor to a CPE vendor
   */
  private function cpeNormalizeVendor($vendor, $soft){
    if(preg_match("/^https?:[^\s]+$/", $vendor)){
      return $soft;
    }
    $vendor = strtolower($vendor);
    $vendor = preg_replace("/https?:[^\s]+/", "", $vendor);
    $vendor = preg_replace("/,?\s*(corporation|gmbh|inc\.|incorporated|LLC|spol\.\ss\sr\.o\.|systems\sinc\.|systems\sincorporated|copyright)$/", "", $vendor);
    $vendor = preg_replace("/\s*\(r\)/", "", $vendor);
    $vendor = preg_replace('/[^\x00-\x7F]/', "", $vendor);
    $vendor = preg_replace("/[^A-Za-z0-9\._]/", "", $vendor);
    $vendor = trim($vendor);
    
    return preg_replace("/\s/", "_", $vendor);
  }

  /**
   *  Init curl session for get CVE by call api cve-search server
   */
  public function get_cve($cve_attr){
    $curl = curl_init();
    foreach($cve_attr as $values){
      $values = $this->match($values);
      $url = trim($this->CVE_SEARCH_URL)."/api/search/".$values['VENDOR']."/".$values['NAME'];
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('content-type: application/json'));  
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
      // Uncomment if using a self-signed certificate on CVE server
      //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      $result = curl_exec ($curl);
      $vars = json_decode($result, true);
      if($vars['total'] != 0){
        $this->search_by_version($vars, $values);
      }
    }

    curl_close ($curl) ;
    sleep($this->CVE_DELAY_TIME);
  }

  private function match($values) {
    $new_vendor = $this->cpeNormalizeVendor($values['VENDOR'], $values['NAME']);
    $new_name = $this->cpeNormalizeName($values['NAME']);

    $regs = $this->get_regex($new_vendor, $new_name);

    if(!empty($regs)) {
      foreach($regs as $reg) {
        if(count($regs) == 1) {
          $reg_publish = true;
          $reg_name = true;
        } else {
          $reg_publish = $this->stringMatchWithWildcard(trim($values['VENDOR']), $reg['NAME_REG']);
          $reg_name = $this->stringMatchWithWildcard(trim($values['NAME']), $reg['NAME_REG']);
        }

        if($reg_name || $reg_publish) {
          if($reg['NAME_RESULT'] != "") {
            $values['NAME'] = $reg['NAME_RESULT'];
          }
          if($reg['PUBLISH_RESULT'] != "") {
            $values['VENDOR'] = $reg['PUBLISH_RESULT'];
          }
          break;
        }
      }
    }

    $values['NAME'] = $this->cpeNormalizeName($values['NAME']);
    $values['VENDOR'] = $this->cpeNormalizeVendor($values['VENDOR'], $values['NAME']);
    return $values;
  }

  private function get_regex($vendor, $name) {
    $reg = [];
    $i = 0;

    $sql = "SELECT * FROM cve_search_correspondance
            WHERE (`NAME_REG` LIKE '%".$vendor."%') OR (`NAME_REG` LIKE '%".$name."%')
            OR (`PUBLISH_RESULT` LIKE '%".$vendor."%') OR (`NAME_RESULT` LIKE '%".$name."%')";

    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

    if($result->num_rows != 0) {
      while($item = mysqli_fetch_array($result)) {
        $reg[$i]['NAME_REG'] = $item['NAME_REG'];
        $reg[$i]['PUBLISH_RESULT'] = $item['PUBLISH_RESULT'];
        $reg[$i]['NAME_RESULT'] = $item['NAME_RESULT'];
        $i++;
      }
    }

    return $reg;
  }

  private function stringMatchWithWildcard($source,$pattern) {
    $regex = str_replace(
      array("\*", "\?"), // wildcard chars
      array('.*','.'),   // regexp chars
      preg_quote($pattern, '/')
    );

    return preg_match('/^'.$regex.'$/is', strtolower($source));
  }

  /**
   *  Clean soft version and verif if CVE is for this version or not
   */
  private function search_by_version($vars, $software){
    $softVersion = $this->getSoftwareVersion($this->cve_history['NAME_ID']);

    while ($item_soft = mysqli_fetch_array($softVersion)) {
      $software['VERSION'] = $item_soft['VERSION'];
      $this->cve_history['VERSION_ID'] = $item_soft['VERSION_ID'];

      $software["VERSION_MODIF"] = $software["VERSION"];
      if(preg_match("/[^0-9,.:-]/", $software["VERSION_MODIF"])){
        $software["VERSION_MODIF"] = preg_replace("/[^0-9,.:-]/", "", $software["VERSION_MODIF"]);
        if(preg_match("/:/", $software["VERSION_MODIF"])){
          $sft = explode(":", $software["VERSION_MODIF"]);
          foreach($sft as $cut){
            if(preg_match("/[.]/", $cut)){
              $software["VERSION_MODIF"] = $cut;
            }
          }
        }
        if(preg_match("/-/", $software["VERSION_MODIF"])){
          $sft = explode("-", $software["VERSION_MODIF"]);
          foreach($sft as $cut){
            if(preg_match("/[.]/", $cut)){
              $software["VERSION_MODIF"] = $cut;
              break;
            }
          }
        }
      }
      $vuln_conf = "cpe:2.3:a:".$software["VENDOR"].":".$software["NAME"].":".$software["VERSION_MODIF"];
      $vuln_conf_all = null;

      if($this->CVE_ALL == 1) {
        $vuln_conf_all = "cpe:2.3:a:".$software["VENDOR"].":".$software["NAME"].":*:*:";
      }
      if($software["NAME"] == "jre" && preg_match("/Update/", $software["REAL_NAME"])){
        $jre = explode(" ", $software["REAL_NAME"]);
        foreach($jre as $keys => $word){
          if($word == "Update"){
            $vuln_conf .= ":".strtolower($word)."_".$jre[$keys+1];
          }
        }
      }

      foreach($vars as $array){
        if(is_array($array)){
          foreach($array as $values) {
            if(isset($values["vulnerable_configuration"])) {
              foreach($values["vulnerable_configuration"] as $vuln){
                if((!empty(strval($vuln_conf)) && (str_contains(strval($vuln), strval($vuln_conf)))) || (!empty(strval($vuln_conf_all)) && (str_contains(strval($vuln), strval($vuln_conf_all))))){
                  $result = $this->get_infos_cve($values['cvss'], $values['id'], $values['references'][0]);
                  if($result != null) {
                    if($this->CVE_VERBOSE == 1) {
                      error_log(print_r($values['id']." has been referenced for ".$software["REAL_NAME"], true));
                    }
                    $this->cve_history['CVE_NB'] ++;
                    $this->cveNB ++;
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  /**
   *  Insert CVE on BDD
   */
  private function get_infos_cve($cvss, $id, $reference){
    $sql_verif = "SELECT * FROM cve_search WHERE PUBLISHER_ID = %s AND NAME_ID = %s AND CVE = '%s' AND VERSION_ID = %s";
    $arg_verif = array($this->cve_history['PUBLISHER_ID'], $this->cve_history['NAME_ID'], $id, $this->cve_history['VERSION_ID']);
    $result_verif = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $arg_verif);
    $result = null;

    if($result_verif->num_rows == 0) {
      $sql = 'INSERT INTO cve_search (PUBLISHER_ID, NAME_ID, VERSION_ID, CVSS, CVE, LINK) VALUES(%s, %s, %s, %s, "%s", "%s")';
      $arg_sql = array($this->cve_history['PUBLISHER_ID'], $this->cve_history['NAME_ID'], $this->cve_history['VERSION_ID'], $cvss, $id, $reference);
      $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg_sql);
    }
    return $result;
  }  

  /**
   *  Clean CVE and ban software
   */
  public function clean_cve($publisher){
    // Clean all CVE of this publisher before insert new CVE
    $sql = 'DELETE FROM cve_search WHERE PUBLISHER_ID = %s';
    $arg = array($publisher);
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);

    if($this->CVE_BAN != ""){
      $sql_ban = "SELECT DISTINCT cs.NAME_ID FROM cve_search cs LEFT JOIN software_categories_link as scl ON cs.NAME_ID = scl.NAME_ID WHERE scl.CATEGORY_ID IN (%s)";
      $sql_ban_arg = array($this->CVE_BAN);
      $result_ban = mysql2_query_secure($sql_ban, $_SESSION['OCS']["readServer"], $sql_ban_arg);

      while ($item_ban = mysqli_fetch_array($result_ban)) {
        if($item_ban != null){
          $sql_remove = "DELETE FROM cve_search WHERE NAME_ID = %s";
          $sql_remove_arg = array($item_ban["NAME_ID"]);
          $result = mysql2_query_secure($sql_remove, $_SESSION['OCS']["writeServer"], $sql_remove_arg);
        }
      }
    }
  }

  /**
   * Print verbose
   */
  public function verbose($config, $code) {
    if($config == 1) {
      switch($code) {
        case 1:
          error_log(print_r($this->CVE_SEARCH_URL." is not reachable.",true));
        break;
        case 2:
          error_log(print_r($this->cveNB." CVE has been added to database",true));
        break;
        case 3:
          error_log(print_r("CVE feature isn't enabled", true));
        break;
        case 4:
          error_log(print_r("Get software publisher ...", true));
        break;
        case 5:
          error_log(print_r("Software publisher OK ... \nCVE treatment started ... \nPlease wait, CVE processing is in progress. It could take a few hours", true));
        break;
        case 6:
          error_log(print_r("Processing ".$this->publisherName." softwares ...", true));
        break;
        case 7:
          error_log(print_r($values['id']." has been referenced for ".$software["REAL_NAME"], true));
        break;
      }
    }
  }

  /**
   *  Add regex correspondance
   */
  public function add_regex($regex, $publish = null, $name = null) {
    $sql = "INSERT INTO cve_search_correspondance (NAME_REG, PUBLISH_RESULT, NAME_RESULT) VALUES ('%s','%s','%s')";
    $arg = array($regex, $publish, $name);

    return mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
  }

  public function csv_treatment($file) {
    $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
    $sql = "INSERT INTO cve_search_correspondance(NAME_REG, PUBLISH_RESULT, NAME_RESULT) VALUES('%s', '%s', '%s')";

    if (in_array($_FILES["csv_file"]["type"], $mimes)) {
      if (($handle = fopen($file['csv_file']['tmp_name'], 'r')) !== FALSE) { // Check the resource is valid
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Check opening the file is OK!
          $arg = null;
          if(trim($data[0]) != "" && $data[1] != "PUBLISH_RESULT"){
            $arg = array(trim(addslashes($data[0])), addslashes($data[1]), addslashes($data[2]));
            $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg);
            if($result == false) {
              return false;
            }
          }
        }
        fclose($handle);
        if($result) {
          return true;
        }
      }
    } else {
      return false;
    }
  }

  public function getUrl(){
    return sprintf(
      "%s://%s",
      isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
      $_SERVER['SERVER_NAME']
    );
  }

  public function get_software_infos() {
    $sql = "SELECT c.NAME_ID, c.VERSION_ID, c.CVSS, c.CVE, c.LINK, n.NAME, v.VERSION
            FROM cve_search c 
            LEFT JOIN software_name n ON n.ID = c.NAME_ID 
            LEFT JOIN software_version v ON v.ID = c.VERSION_ID";
    $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

    $list = [];

    while ($item = mysqli_fetch_array($result)) {
      $list[$item['NAME_ID']]['NAME'] = $item['NAME'];
      $list[$item['NAME_ID']][$item['CVE']]['VERSION'] = $item['VERSION'];
      $list[$item['NAME_ID']][$item['CVE']]['CVSS'] = $item['CVSS'];
      $list[$item['NAME_ID']][$item['CVE']]['CVE'] = $item['CVE'];
      $list[$item['NAME_ID']][$item['CVE']]['LINK'] = $item['LINK'];
    }

    return $list;
  }

}

?>
