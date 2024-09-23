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
  public $CVE_LINK;
  public $CVE_VERBOSE;
  public $CVE_EXPIRE_TIME;
  public $CVE_DELAY_TIME;
  private $CVE_DEBUG;
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
  private $softRegex = [];
  private $vars = [];
  private $previousSoftPublisher;
  private $previousSoftName;

  function __construct(){
    $champs = array('VULN_CVESEARCH_ENABLE' => 'VULN_CVESEARCH_ENABLE',
        'VULN_CVESEARCH_HOST' => 'VULN_CVESEARCH_HOST',
        'VULN_BAN_LIST' => 'VULN_BAN_LIST',
        'VULN_CVESEARCH_LINK' => 'VULN_CVESEARCH_LINK',
        'VULN_CVESEARCH_VERBOSE' => 'VULN_CVESEARCH_VERBOSE',
        'VULN_CVE_EXPIRE_TIME' => 'VULN_CVE_EXPIRE_TIME',
        'VULN_CVE_DELAY_TIME' => 'VULN_CVE_DELAY_TIME');

    // Get configuration values from DB
    $values = look_config_default_values($champs);

    $this->CVE_ACTIVE = $values['ivalue']["VULN_CVESEARCH_ENABLE"] ?? 0;
    $this->CVE_SEARCH_URL = $values['tvalue']['VULN_CVESEARCH_HOST'] ?? "";
    $this->CVE_BAN = $values['tvalue']["VULN_BAN_LIST"] ?? "";
    $this->CVE_LINK = $values['ivalue']["VULN_CVESEARCH_LINK"] ?? 0;
    $this->CVE_VERBOSE = $values['ivalue']["VULN_CVESEARCH_VERBOSE"] ?? 0;
    $this->CVE_EXPIRE_TIME = $values['ivalue']["VULN_CVE_EXPIRE_TIME"] ?? null;
    $this->CVE_DELAY_TIME = $values['ivalue']["VULN_CVE_DELAY_TIME"] ?? 0;
    $this->getAllRegex();
  }

  private function getAllRegex() {
    $query = "SELECT DISTINCT `NAME_REG`, `PUBLISH_RESULT`, `NAME_RESULT` FROM `cve_search_correspondance`";
    $result = mysql2_query_secure($query, $_SESSION['OCS']["readServer"]);

    if ($result) {
      while ($item = mysqli_fetch_array($result)) {
        $this->softRegex[] = $item;
      }
    }
  }

  /**
   * Set debug mode
   */
  public function setDebug($debug){
    $this->CVE_DEBUG = $debug;
  }

  public function getNbAdded() {
    return $this->cveNB;
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
  private function getPublisher($date = null, $check_history = false, $offset = null, $limit = null) {
    $sql = 'SELECT DISTINCT p.ID, p.PUBLISHER FROM software_publisher p
            LEFT JOIN cve_search_history h ON h.PUBLISHER_ID = p.ID
            LEFT JOIN software_categories_link scl ON scl.PUBLISHER_ID = p.ID
          WHERE p.ID != 1 AND TRIM(p.PUBLISHER) != ""';
    if($this->CVE_BAN != "" && $this->CVE_BAN != 0){
      // fix cve ban retuns 0 cve -> double condition is necessary
      // bc 'NOT IN' does not apply to softs not referenced in scl table (not in any category)
      $sql .= ' AND (scl.CATEGORY_ID IS NULL OR scl.CATEGORY_ID NOT IN ('. $this->CVE_BAN .'))';
    }
    if($date != null && $check_history != 0) {
      $sql .= ' AND (h.FLAG_DATE <= "'.$date.'" OR p.ID NOT IN (SELECT PUBLISHER_ID FROM cve_search_history))';
    }
    
    if (isset($offset) && isset($limit)) {
      $sql .= " ORDER BY p.PUBLISHER LIMIT $offset, $limit";
    } else {
      $sql .= " ORDER BY p.PUBLISHER";
    }

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
   *  Get distinct software version by name
   */
  private function getSoftwareVersion($name_id) {
    $sql_soft = " SELECT DISTINCT v.VERSION, v.PRETTYVERSION, sl.VERSION_ID FROM software_version v 
                  LEFT JOIN software_link sl ON sl.VERSION_ID = v.ID 
                  WHERE sl.NAME_ID = %s AND sl.VERSION_ID != 1";
    $sql_soft .= " ORDER BY v.PRETTYVERSION";
    $arg_soft = array($name_id);

    return mysql2_query_secure($sql_soft, $_SESSION['OCS']["readServer"], $arg_soft);
  }

  /**
   *  Get distinct all software name and publisher
   */
  public function getSoftwareInformations($date = null, $clean = false, $poolSize){
    if ($this->CVE_BAN != "") {
      // get names of banned categories
      $banned = [];
      $sql = "SELECT CATEGORY_NAME FROM software_categories WHERE ID IN (".$this->CVE_BAN.")";
      $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
      while ($item = mysqli_fetch_array($result)) {
        $banned[] = $item["CATEGORY_NAME"];
      }
      $this->verbose("Banned categories (VULN_BAN_LIST): ".implode(", ", $banned), "DEBUG");
    }
    $this->verbose("Chunk size: $poolSize publishers", "INFO");
    $this->verbose("CVE processing is in progress, this could take a while ...", "INFO");
    // loop on all publishers based on chunk size
    $numRows = $poolSize;
    $chunkIndex = 1;

    for($limit = 0; $poolSize <= $numRows; $limit = $limit+$poolSize) {
      $this->verbose("Processing publisher chunk " .$chunkIndex, "DEBUG");
      $publishers = $this->getPublisher($date, $this->history_is_empty(), $limit, $poolSize);
      $numRows = mysqli_num_rows($publishers);
      if (!$publishers) {
          $this->verbose("Error fetching publishers in chunk " .$chunkIndex. ".", "DEBUG");
          continue;
      }

      while ($item_publisher = mysqli_fetch_array($publishers)) {
        # Reset date
        $this->cve_history['FLAG'] = date('Y-m-d H:i:s');
        # Reset CVE NB
        $this->cve_history['CVE_NB'] = 0;
        $this->cve_history['PUBLISHER_ID'] = $item_publisher['ID'];
        $this->clean_cve($item_publisher['ID']);
        
        $this->publisherName = $item_publisher['PUBLISHER'];

        $result_soft = $this->getSoftwareName($item_publisher['ID']);

        $this->verbose("Processing publisher: ".$item_publisher['PUBLISHER'], "DEBUG");

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
      $chunkIndex += 1;
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
    $name = preg_replace("/\s*(v|version|release)?\s*[\d\.]+(\s+esr)?$/", "", $name);
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
    $vendor = preg_replace("/,?\s*(corporation|gmbh|inc\.|incorporated|llc|spol\.\ss\sr\.o\.|systems\sinc\.|systems\sincorporated|copyright)$/", "", $vendor);
    $vendor = preg_replace("/\s*\(r\)/", "", $vendor);
    $vendor = preg_replace('/[^\x00-\x7F]/', "", $vendor);
    $vendor = preg_replace("/[^A-Za-z0-9\._-]/", "", $vendor);
    $vendor = trim($vendor);
    
    return preg_replace("/\s/", "_", $vendor);
  }

  /**
   *  Init curl session for get CVE by call api cve-search server
   */
  public function get_cve($cve_attr){
    foreach($cve_attr as $values){
      $curl = curl_init();
      $values = $this->match($values);
      $this->verbose("Processing publisher: ".$values['VENDOR']." for software : ".$values['NAME'], "DEBUG");

      if($this->previousSoftName != $values['NAME'] || $this->previousSoftPublisher != $values['VENDOR']) {
        $this->previousSoftName = $values['NAME'];
        $this->previousSoftPublisher = $values['VENDOR'];
        $this->vars = [];
        
        $url = trim($this->CVE_SEARCH_URL)."/api/search/".$values['VENDOR']."/".$values['NAME'];
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('content-type: application/json'));  
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        // Uncomment if using a self-signed certificate on CVE server
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $this->verbose("Sending request to ".$url, "DEBUG");

        $result = curl_exec ($curl);

        // check curl request
        if($result == false) {
          $this->verbose("Error while fetching CVE data from ".$url, "INFO");
          $this->verbose("Curl error code: ".curl_errno($curl)." - ".curl_error($curl), "DEBUG");
          // Reset previous var if curl error
          $this->previousSoftName = null;
          $this->previousSoftPublisher = null;
          // close curl connection before continue
          curl_close ($curl) ;
          sleep($this->CVE_DELAY_TIME);

          continue;
        } else {
          $this->verbose("Data fetched successfully from ".$url, "DEBUG");
        }

        $this->vars = json_decode($result, true);
        curl_close ($curl);
      } else {
        $this->verbose("Software identical to the previous one, use of data already recovered", "DEBUG");
      }

      if(isset($this->vars['total']) && $this->vars['total'] != 0){
        $this->verbose("CVE data found for ".$values['VENDOR']."/".$values['NAME'], "INFO");
        $this->search_by_version($this->vars, $values);
      } else {
        $this->verbose("No CVE data found for ".$values['VENDOR']."/".$values['NAME'], "INFO");
      }

      sleep($this->CVE_DELAY_TIME);
    }
  }

  private function match($values) {
    $values['VENDOR'] = $this->cpeNormalizeVendor($values['VENDOR'], $values['NAME']);
    $values['NAME'] = $this->cpeNormalizeName($values['NAME']);

    if(!empty($this->softRegex)) {
      foreach($this->softRegex as $reg) {
        $reg_name = $this->stringMatchWithWildcard(trim($values['NAME']), $reg['NAME_REG']);
        $reg_publish = $this->stringMatchWithWildcard(trim($values['VENDOR']), $reg['PUBLISH_RESULT'], true);

        if($reg_name && $reg_publish) {
          if($reg['NAME_RESULT'] != "") {
            $values['NAME'] = $reg['NAME_RESULT'];
          }
          if($reg['PUBLISH_RESULT'] != "") {
            $values['VENDOR'] = $reg['PUBLISH_RESULT'];
          }
        }
      }
    }

    $values['NAME'] = $this->cpeNormalizeName($values['NAME']);
    $values['VENDOR'] = $this->cpeNormalizeVendor($values['VENDOR'], $values['NAME']);

    $this->verbose("Software publisher/name after regex processing ".$values['VENDOR']."/".$values['NAME'], "DEBUG");

    return $values;
  }

  private function stringMatchWithWildcard($source,$pattern, $publisher = false) {
    if ($publisher) {
      $pattern = "*".$pattern."*";
    }

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

      if(!is_null($item_soft["PRETTYVERSION"])) {
        $item_soft["PRETTYVERSION"] = str_replace('"', "", $item_soft["PRETTYVERSION"]);
        $vuln_conf = "cpe:2.3:a:".$software["VENDOR"].":".$software["NAME"].":".$item_soft["PRETTYVERSION"];
        $this->verbose("Search CVE for ".$item_soft["PRETTYVERSION"]." software version", "DEBUG");
      } else {
        $vuln_conf = "cpe:2.3:a:".$software["VENDOR"].":".$software["NAME"].":".$item_soft["VERSION"];
        $this->verbose("Search CVE for ".$item_soft["VERSION"]." software version", "DEBUG");
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
                if((!empty(strval($vuln_conf)) && (strpos(strval($vuln), strval($vuln_conf)) !== false))){
                  $result = $this->get_infos_cve($values['cvss'] ?? $values['cvss3'], $values['id'], $values['references'][0]);
                  if($result) {
                    $this->verbose($values['id']." has been referenced for ".$software["REAL_NAME"]." version ".$software["VERSION"], "INFO");
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
    $result = false;

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
      $sql_ban = "SELECT DISTINCT cs.NAME_ID, cs.VERSION_ID FROM cve_search cs LEFT JOIN software_categories_link as scl ON cs.NAME_ID = scl.NAME_ID AND cs.VERSION_ID = scl.VERSION_ID WHERE scl.CATEGORY_ID IN (%s)";
      $sql_ban_arg = array($this->CVE_BAN);
      $result_ban = mysql2_query_secure($sql_ban, $_SESSION['OCS']["readServer"], $sql_ban_arg);

      while ($item_ban = mysqli_fetch_array($result_ban)) {
        if($item_ban != null){
          $sql_remove = "DELETE FROM cve_search WHERE NAME_ID = %s AND VERSION_ID = %s";
          $sql_remove_arg = array($item_ban["NAME_ID"], $item_ban["VERSION_ID"]);
          $result = mysql2_query_secure($sql_remove, $_SESSION['OCS']["writeServer"], $sql_remove_arg);
        }
      }
    }
  }

  /**
   * Print debug statement depending on the level of debug needed
   * If in debug mode, print all messages, if only verbose mode, print only INFO
   * @param string $string
   * @param string $level
   */
  public function verbose($string, $level) {
    if (($level == "DEBUG" && $this->CVE_DEBUG) || ($level == "INFO" && ($this->CVE_VERBOSE || $this->CVE_DEBUG))) {
      print("[".date("Y-m-d H:i:s"). "] [$level] $string\n");
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
