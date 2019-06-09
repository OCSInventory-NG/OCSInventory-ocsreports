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
  * Class for the notification mail
  */
 class Console
 {
 	
//stevenes donato

   /**
    * Get all machine contacted today and sort by Agent
    * @return array $machine
    */
   public function get_machines(){
      $machines = array("windows" => 0, "unix" => 0, "android" => 0, 'all' => 0);

      foreach($machines as $key => $value){
          $sql = "SELECT name, count(id) as nb, USERAGENT FROM hardware WHERE USERAGENT LIKE '%$key%'";
          $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

          while($item = mysqli_fetch_array($result)){
            if(strpos($item['USERAGENT'], 'unix') !== false){
                $machines['unix'] = intval($item['nb']);
                $machines['all'] = $machines['all'] + intval($item['nb']);
            }elseif(strpos($item['USERAGENT'], 'WINDOWS') !== false){
                $machines['windows'] = intval($item['nb']);
                $machines['all'] = $machines['all'] + intval($item['nb']);
            }elseif(strpos($item['USERAGENT'], 'Android') !== false){
                $machines['android'] = intval($item['nb']);
                $machines['all'] = $machines['all'] + intval($item['nb']);
            }
          }
      }

      return $machines;
   }



   /**
    * Get multisearch url for machine contacted today
    * @param  array $machine [description]
    * @return array          [description]
    */
   public function get_urls($machines){
      global $l;

     // $_SESSION['DATE']['HARDWARE-LASTDATE-TALL'] = date($l->g(1242));
      foreach($machines as $key => $value) {
        if($machines[$key] != 0){
          //$machines[$key] = "<a style='font-size:32px; font-weight:bold;' href='index.php?" . PAG_INDEX . "=visu_search&&values2=".$key."&type_field='>".$value."</a>";
          $machines[$key] = "<a style='font-size:32px; font-weight:bold;' href='index.php?function=visu_search&fields=HARDWARE-LASTCOME&comp=tall&values=&values2=".$key."&type_field='>".$value."</a>";
        } else {
          $machines[$key] = "<a style='font-size:32px; font-weight:bold;' href='#'>".$value."</a>";        	
        }
      }

      return $machines;
   } 


   /**
    * Construct table for machine contacted today
    * @return string [description]
    */
   public function html_table_machines(){
       global $l;
      
      //get OS's 
      $sql_os = "SELECT osname, count(osname) FROM `hardware` group by osname";
      $result_os = mysql2_query_secure($sql_os, $_SESSION['OCS']["readServer"]);
      $oss = "<a style='font-size:32px; font-weight:bold;' href='#'>".mysqli_num_rows($result_os)."</a>";


      //get softwares
      $sql = "SELECT name, count(name) FROM `softwares` group by name";
      $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
      $softs = "<a style='font-size:32px; font-weight:bold;' href='index.php?function=visu_all_soft'>".mysqli_num_rows($result)."</a>";

       $machines = $this->get_urls(
         $this->get_machines()
       );

       $table = '
			<style type="text/css">			
				a:focus, a:hover {color: #961b7e !important; text-decoration: none !important; font-weight:normal !important; }			
			</style>       
			
	      <div class="tableContainer">
	      <table id="tab_stats" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; text-align:center; margin:auto; width:100%; margin-top:20px; background:#fff; border: 1px solid #ddd; table-layout: fixed;" >
		  	<tr>
		      <td style="border-right: 1px solid #ddd; padding: 5px;"><span>' . $machines['all'] . '</span> </p><span style="color:#333; font-size:14pt;">'.$l->g(652).'</span></td>
		      <td style="border-right: 1px solid #ddd;"><span>' . $machines['windows']. '</span> </p><span style="color:#333; font-size:14pt;">Windows</span></td>
		      <td style="border-right: 1px solid #ddd;"><span>' . $machines['unix'] . '</span> </p><span style="color:#333; font-size:14pt;"> Unix </span></td>
		      <td style="border-right: 1px solid #ddd;"><span>' . $machines['android']. '</span> </p><span style="color:#333; font-size:14pt;">Android</span></td>
		      <td style="border-right: 1px solid #ddd;"><span>' . $oss . '</span> </p><span style="color:#333; font-size:14pt;">'.$l->g(25).'</span></td>
		      <td style="border-right: 1px solid #ddd;"><span>' . $softs. '</span> </p><span style="color:#333; font-size:14pt;">'.$l->g(20).'</span></td>
		      
		  </tr>';
       $table .= "</table></div>\n";
       //$table .= "</tbody></table></div></div></div></div></div><br><br>';

       return $table;
   }
 	
//stevenes donato 	
 	


   /**
    * Get all machine contacted today and sort by Agent
    * @return array $machine
    */
   public function get_machine_contacted_td(){
      $machine = array("windows" => 0, "unix" => 0, "android" => 0, 'all' => 0);

      foreach($machine as $key => $value){
          $sql = "SELECT DISTINCT name, count(id) as nb, USERAGENT FROM hardware WHERE lastcome >= date_format(sysdate(),'%Y-%m-%d 00:00:00') AND USERAGENT LIKE '%$key%'";
          $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);

          while($item = mysqli_fetch_array($result)){
            if(strpos($item['USERAGENT'], 'unix') !== false){
                $machine['unix'] = intval($item['nb']);
                $machine['all'] = $machine['all'] + intval($item['nb']);
            }elseif(strpos($item['USERAGENT'], 'WINDOWS') !== false){
                $machine['windows'] = intval($item['nb']);
                $machine['all'] = $machine['all'] + intval($item['nb']);
            }elseif(strpos($item['USERAGENT'], 'Android') !== false){
                $machine['android'] = intval($item['nb']);
                $machine['all'] = $machine['all'] + intval($item['nb']);
            }
          }
      }

      return $machine;
   }

   /**
    * Get multisearch url for machine contacted today
    * @param  array $machine [description]
    * @return array          [description]
    */
   public function get_url($machine){
      global $l;

      $_SESSION['DATE']['HARDWARE-LASTDATE-TALL'] = date($l->g(1242));
      foreach($machine as $key => $value) {
        if($machine[$key] != 0){
          $machine[$key] = "<a style='font-size:32px; font-weight:bold;' href='index.php?" . PAG_INDEX . "=visu_search&fields=HARDWARE-LASTCOME&comp=tall&values=".$_SESSION['DATE']['HARDWARE-LASTCOME-TALL']."&values2=".$key."&type_field='>".$value."</a>";
        } else {
          $machine[$key] = "<a style='font-size:32px; font-weight:bold;' href='#'>".$value."</a>";        	
        }
      }

      return $machine;
   }    
   

   /**
    * Construct table for machine contacted today
    * @return string [description]
    */
   public function html_table_machine(){
       global $l;

       $machine = $this->get_url(
         $this->get_machine_contacted_td()
       );

		 $table = '<div class="tableContainer">
	      <table id="tab_stats" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; text-align:center; margin:auto; width:100%; margin-bottom:0px; background:#fff; border: 1px solid #ddd; table-layout: fixed;" >
		  	<tr>
		      <td style="border-right: 1px solid #ddd; padding: 5px;"><span>' . $machine['all'] . '</span> </p><span style="color:#333; font-size:14pt;">'.$l->g(87).'</span></td>
		      <td style="border-right: 1px solid #ddd;"><span>' . $machine['windows']. '</span> </p><span style="color:#333; font-size:14pt;">Windows</span></td>
		      <td style="border-right: 1px solid #ddd;"><span>' . $machine['unix'] . '</span> </p><span style="color:#333; font-size:14pt;"> Unix </span></td>
		      <td style="border-right: 1px solid #ddd;"><span>' . $machine['android']. '</span> </p><span style="color:#333; font-size:14pt;">Android</span></td>
		      
		  </tr>';
       $table .= "</table></div>\n";
       //$table .= "</tbody></table></div></div></div></div></div><br><br>';

       return $table;
   }

   /**
    * Construct table for software category
    * @param  array $cat [description]
    * @return string      [description]
    */
   public function html_software_cat($cat){
        global $l;

        unset($cat[0]);
        if(!empty($cat)){

          foreach($cat as $key => $value){
            $sql = "SELECT count(ID) as nb FROM softwares WHERE CATEGORY = %s";
            $arg = array($key);
            $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);

            while($item = mysqli_fetch_array($result)){
              $category[$key][$value] = $item['nb'];
            }
          }

          $html = "<table class='cell-border' style='width:100%;'>";
          foreach($category as $id => $array){
            foreach($array as $name => $nb){
              if($nb != '0'){
                  $html .= "<tr class='soft-table'><td class='soft-table-td'>".$name."</td><th style='width: 50%;  text-align: center;'><a href='index.php?" . PAG_INDEX . "=visu_all_soft&onglet=".$id."'>".$nb."</a></th></tr>";
              }else{
                  $html .= "<tr class='soft-table'><td class='soft-table-td'>".$name."</td><td style='width: 50%;  text-align: center;'>".$nb."</td></tr>";
              }
            }
          }
          $html .= "</table>";
        }else{
          $html = $l->g(2134);
        }

        return $html;
   }

   /**
    * Get assets category and construct table
    * @return [type] [description]
    */
   public function get_assets(){
       global $l;

       $sql = "SELECT * FROM assets_categories";
       $result = mysqli_query($_SESSION['OCS']["readServer"], $sql);

       while ($item_asset = mysqli_fetch_array($result)) {
           $list_asset[$item_asset['ID']]['CATEGORY_NAME'] = $item_asset['CATEGORY_NAME'];
           $list_asset[$item_asset['ID']]['SQL_QUERY'] = $item_asset['SQL_QUERY'];
           $list_asset[$item_asset['ID']]['SQL_ARGS'] = $item_asset['SQL_ARGS'];
       }

       if(is_array($list_asset)){
         foreach($list_asset as $key => $values){
             $nb = [];
             $asset = explode(",", $list_asset[$key]['SQL_ARGS']);
             $result_computer = mysql2_query_secure($list_asset[$key]['SQL_QUERY'], $_SESSION['OCS']["readServer"], $asset);
             while ($computer = mysqli_fetch_array($result_computer)) {
                 $nb[] = $computer['hardwareID'];
             }
             $nb_computer[$key][$list_asset[$key]['CATEGORY_NAME']] = count($nb);
         }

         $html = "<table class='cell-border' style='width:100%;'>";
         foreach($nb_computer as $key => $cat){
           foreach($cat as $name => $nb){
             if($nb != 0){
                 $html .= "<tr class='soft-table'><td class='soft-table-td'>".$name."</td><th style='width: 50%;  text-align: center;'><a href='index.php?" . PAG_INDEX . "=visu_search&fields=ASSETS&comp=&values=".$key."&values2=&type_field='>".$nb."</a></th></tr>";
             }else{
                 $html .= "<tr class='soft-table'><td class='soft-table-td'>".$name."</td><td style='width: 50%;  text-align: center;'>".$nb."</td></tr>";
             }
           }
         }
         $html .= "</table>";
       }else{
         $html = $l->g(2133);
       }
       
       return $html;
   }

 }
