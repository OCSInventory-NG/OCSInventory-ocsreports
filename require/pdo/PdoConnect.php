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
  * Class for PDO connection
  */
class PdoConnect
{
    private $db;

    function __construct($server, $compte_base, $pswd_base, $db = DB_NAME) {
        $this->dbconnect($server, $compte_base, $pswd_base, $db = DB_NAME);
    }

    public function getInstance() {
        return $this->db;
    }

    private function dbconnect($server, $compte_base, $pswd_base, $db = DB_NAME) {
        error_reporting(E_ALL & ~E_NOTICE);

        $options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\';SET sql_mode=\'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\''
        ];

        if(PATH_SSL_KEY != '' && PATH_SSL_CERT != '' && PATH_CA_CERT != '') {
            $options = [
                PDO::MYSQL_ATTR_SSL_KEY     => PATH_SSL_KEY,
                PDO::MYSQL_ATTR_SSL_CERT    => PATH_SSL_CERT,
                PDO::MYSQL_ATTR_SSL_CA      => PATH_CA_CERT,
            ];
        }
        
        try {
            $this->db = new PDO(
                'mysql:host='.$server.';dbname='.$db,
                $compte_base,
                $pswd_base,
                $options
            );
            
        } catch (PDOException $e) {
            return "ERROR: MySql connection problem " . $e->getCode() . "<br>" . $e->getMessage();
        }
    }

}