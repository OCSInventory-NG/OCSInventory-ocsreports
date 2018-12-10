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
 * Class for Software as a Service
 */
class Saas
{

  /**
   * Add SAAS exp in BDD
   * @param string $saasName [description]
   * @param string $dnsName  [description]
   */
  public function add_saas($saasName, $dnsName){
      $sql_verif = "SELECT * FROM saas_exp WHERE NAME = '%s' OR DNS_EXP = '%s'";
      $arg_verif = array($saasName, $dnsName);
      $result_verif = mysql2_query_secure($sql_verif, $_SESSION['OCS']["readServer"], $arg_verif);
      $verif = mysqli_fetch_array($result_verif);

      if($verif == null) {
        $sql = "INSERT INTO `saas_exp` (`NAME`, `DNS_EXP`) VALUES ('%s', '%s')";
        $arg_sql = array($saasName, $dnsName);

        $result = mysql2_query_secure($sql, $_SESSION['OCS']["writeServer"], $arg_sql);
      } else {
        $result = false;
      }
      return ($result);
  }

}
