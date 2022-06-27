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

    /**
     * Get JSON news from url
     * @return object [description]
     */
    public function get_json_news(){
        global $l;

        $json = file_get_contents(URL_NEWS);
        $obj = json_decode($json);

        foreach($obj->NEWS as $value){
          if(strlen($value->CONTENT) > 150){
            $value->CONTENTMODIF = substr($value->CONTENT, 0, 150) . " ...";
          }
          $value->CONTENT = nl2br($value->CONTENT);

          if(((string)strtotime($value->DATE) >= mktime(0, 0, 0, date("Y"), date("m"), date("d")-7))){
              $obj->RECENT = $l->g(8028);
          }
        }

        return $obj;
    }

    /**
     * Test connection and json
     * @return string [description]
     */
    public function test_connect(){
        global $l;

        $array = get_headers(URL_NEWS);
        $string = $array[0];
        if(strpos($string,"200")) {
            $json = file_get_contents(URL_NEWS);
            $obj = json_decode($json);
            if($obj != null){
              return 'true';
            }else{
              return $l->g(8027);
            }
        } else {
            return $l->g(8027);
        }
    }

}
