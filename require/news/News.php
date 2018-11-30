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
  * Class for news
  */
class News
{

    private $url = 'http://check-version.ocsinventory-ng.org/newsfeed.json';

    /**
     * Get JSON news from url
     * @return object [description]
     */
    public function get_json_news(){
        $json = file_get_contents($this->url);
        $obj = json_decode($json);

        foreach($obj->NEWS as $key => $value){
          if(strlen($value->CONTENT) > 150){
            $value->CONTENTMODIF = (string)substr($value->CONTENT, 0, 150) . " ...";
          }
        }

        return $obj;
    }

}
