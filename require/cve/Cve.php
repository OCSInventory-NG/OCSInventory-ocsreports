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
  public $CVE_ACTIVE = null;
  private $CVE_BAN;
  public $cve_attr = [];

  function __construct(){
    $champs = array('VULN_CVESEARCH_ENABLE' => 'VULN_CVESEARCH_ENABLE',
        'VULN_CVESEARCH_HOST' => 'VULN_CVESEARCH_HOST',
        'VULN_BAN_LIST' => 'VULN_BAN_LIST');

    // Get configuration values from DB
    $values = look_config_default_values($champs);

    $this->CVE_ACTIVE = $values['ivalue']["VULN_CVESEARCH_ENABLE"];
    $this->CVE_SEARCH_URL = $values['tvalue']['VULN_CVESEARCH_HOST'];
    $this->CVE_BAN = $values['tvalue']["VULN_BAN_LIST"];
  }

  /**
   *  Get distinct all software name and publisher
   */
  public function getSoftwareInformations(){
    $sql = 'SELECT DISTINCT name, publisher, version FROM softwares WHERE name != "" AND publisher != "" ';
    if($this->CVE_BAN != ""){
      $sql .= 'AND category NOT IN ('. $this->CVE_BAN .')';
    }
    $sql .= ' ORDER BY publisher';
    $result = mysqli_query($_SESSION['OCS']["readServer"], $sql);

    while ($item_soft = mysqli_fetch_array($result)) {
      if(!preg_match('/[^\x00-\x7F]/', $item_soft['name']) && !preg_match('#\\{([^}]+)\\}#', $item_soft['name'])){
        $soft = $this->cpeNormalizeName($item_soft['name']);
        $vendor = $this->cpeNormalizeVendor($item_soft['publisher'], $soft);

        $this->cve_attr[] = ["NAME" => $soft, "VENDOR" => $vendor, "VERSION" => $item_soft['version'], "REAL_NAME" => $item_soft['name'], "REAL_VENDOR" => $item_soft['publisher']];
      }
    }

    $getcve = $this->get_cve($this->cve_attr);
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
    $name = preg_replace("/\s/", "_", $name);

    return $name;
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
    $vendor = preg_replace("/,?\s*(corporation|gmbh|inc\.|incorporated|LLC|spol\.\ss\sr\.o\.|systems\sinc\.|systems\sincorporated)$/", "", $vendor);
    $vendor = preg_replace("/\s*\(r\)/", "", $vendor);
    $vendor = preg_replace('/[^\x00-\x7F]/', "", $vendor);
    $vendor = preg_replace("/[^A-Za-z0-9\._]/", "", $vendor);
    $vendor = trim($vendor);
    $vendor = preg_replace("/\s/", "_", $vendor);
    
    return $vendor;
  }

  /**
   *  Init curl session for get CVE by call api cve-search server
   */
  public function get_cve($cve_attr){
    $curl = curl_init();

    foreach($cve_attr as $key => $values){
      $url = $this->CVE_SEARCH_URL."/api/search/".$values['VENDOR']."/".$values['NAME'];   
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
      $result = curl_exec ($curl);
      $vars = json_decode($result, true);
      if(!empty($vars)){
        $this->search_by_version($vars, $values);
      }
    }

    curl_close ($curl) ;
  }

  /**
   *  Clean soft version and verif if CVE is for this version or not
   */
  private function search_by_version($vars, $software){
    $software["VERSION_MODIF"] = $software["VERSION"];
    if(preg_match("/[^0-9,.:-]/", $software["VERSION_MODIF"])){
      $software["VERSION_MODIF"] = preg_replace("/[^0-9,.:-]/", "", $software["VERSION_MODIF"]);
      if(preg_match("/:/", $software["VERSION_MODIF"])){
        $sft = explode(":", $software["VERSION_MODIF"]);
        foreach($sft as $num => $cut){
          if(preg_match("/[.]/", $cut)){
            $software["VERSION_MODIF"] = $cut;
          }
        }
      }
      if(preg_match("/-/", $software["VERSION_MODIF"])){
        $sft = explode("-", $software["VERSION_MODIF"]);
        foreach($sft as $num => $cut){
          if(preg_match("/[.]/", $cut)){
            $software["VERSION_MODIF"] = $cut;
          }
        }
      }
    }
    $vuln_conf = "cpe:2.3:a:".$software["VENDOR"].":".$software["NAME"].":".$software["VERSION_MODIF"];
    if($software["NAME"] == "jre" && preg_match("/Update/", $software["REAL_NAME"])){
      $jre = explode(" ", $software["REAL_NAME"]);
      foreach($jre as $keys => $word){
        if($word == "Update"){
          $vuln_conf .= ":".strtolower($word)."_".$jre[$keys+1];
        }
      }
    }   
    foreach($vars as $key => $values){
      foreach($values["vulnerable_configuration"] as $keys => $vuln){
        if($vuln == $vuln_conf){
          $this->get_infos_cve($values['cvss'], $values['id'], $values['references'][0], $software["REAL_VENDOR"], $software["REAL_NAME"], $software["VERSION"]);
        }
      }
    }
  }

  /**
   *  Insert CVE on BDD
   */
  private function get_infos_cve($cvss, $id, $reference, $vendor, $soft, $version){
    $sql = 'INSERT INTO cve_search VALUES("%s", "%s", "%s", %s, "%s", "%s")';
    $arg_sql = array($vendor,$soft, $version, $cvss, $id, $reference);
    $result_verif = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg_sql);
  }  

  /**
   *  Clean CVE and ban software
   */
  public function clean_cve(){
    $sql = 'SELECT DISTINCT cve FROM cve_search';
    $result = mysqli_query($_SESSION['OCS']["readServer"], $sql);
    $curl = curl_init();

    while ($item_cve = mysqli_fetch_array($result)) {
      $url = $this->CVE_SEARCH_URL."/api/cve/".$item_cve["cve"];
      
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
      $result = curl_exec ($curl);
      $vars = json_decode($result, true);
      curl_close ($curl) ;
      if(empty($vars)){
        $sql_clean = "DELETE FROM cve_search WHERE cve = '%s'";
        $sql_arg = array($item_cve["cve"]);
        $result_verif = mysql2_query_secure($sql_clean, $_SESSION['OCS']["writeServer"], $arg_sql);
      }
    }

    curl_close ($curl);

    if($this->CVE_BAN != ""){
      $sql_ban = "SELECT DISTINCT c.soft FROM cve_search c LEFT JOIN softwares as s ON c.soft = s.name WHERE s.category IN (%s)";
      $sql_ban_arg = array($this->CVE_BAN);
      $result_ban = mysql2_query_secure($sql_ban, $_SESSION['OCS']["readServer"], $sql_ban_arg);

      while ($item_ban = mysqli_fetch_array($result_ban)) {
        if($item_ban != null){
          $sql_remove = "DELETE FROM cve_search WHERE soft = '%s'";
          $sql_remove_arg = array($item_ban["soft"]);
          $result = mysql2_query_secure($sql_remove, $_SESSION['OCS']["writeServer"], $sql_remove_arg);
        }
      }
    }
  }
}

?>
