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
  * Class for teledeploy
  */
class Teledeploy
{

  public function get_package($timestamp){
      $zipfile = new zipArchive();

      $sql_document_root = "SELECT tvalue FROM config WHERE NAME='DOWNLOAD_PACK_DIR'";

      $res_document_root = mysql2_query_secure($sql_document_root, $_SESSION['OCS']["readServer"]);
      while ($val_document_root = $res_document_root->fetch(PDO::FETCH_ASSOC)) {
          $document_root = $val_document_root["tvalue"] . '/download/';
      }
      //echo $document_root;
      //if no directory in base, take $_SERVER["DOCUMENT_ROOT"]
      if (!isset($document_root)) {
          $document_root = VARLIB_DIR . '/download/';
      }

      $rep = $document_root . $timestamp . "/";
      $info = file_get_contents($rep.'info');
      $xml = simplexml_load_string($info);
      $info_data = [];

      foreach($xml->attributes() as $key => $value){
        $info_data[$key] = (string)$value;
      }

      $sql_download = "SELECT * FROM download_available WHERE FILEID = %s";
      $arg_download = array($timestamp);
      $result = mysql2_query_secure($sql_download, $_SESSION['OCS']["readServer"], $arg_download);

      while($val = $result->fetch(PDO::FETCH_ASSOC)){
        $info_data['NAME_PACK'] = $val['NAME'];
        $info_data['OS'] = $val['OSNAME'];
        $info_data['COMMENT'] = $val['COMMENT'];
        $info_data['DOCUMENT'] = $document_root;
        $info_data['FRAGMENTS'] = $val['FRAGMENTS'];
        $info_data['SIZE'] = $val['SIZE'];
      }

      return $info_data;
  }
}
