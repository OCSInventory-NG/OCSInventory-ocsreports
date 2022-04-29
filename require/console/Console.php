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
  * Class for the console
  */
 class Console
 {

  private $excludeArchived = null;

  function __construct() {
    $configToLookOut = [
      'EXCLUDE_ARCHIVE_COMPUTER' => 'EXCLUDE_ARCHIVE_COMPUTER'
    ];

    $this->excludeArchived = look_config_default_values($configToLookOut)['ivalue']['EXCLUDE_ARCHIVE_COMPUTER'];
  } 

   /**
    * Get all machine contacted todayand all machines and sort by Agent
    * @param  string $title [table name]
    * @return array $machine
    */
   public function get_machine_contacted_td($title){
      $machine = array("windows" => 0, "unix" => 0, "android" => 0, 'others' => 0, 'all' => 0);

      if($title == "CONTACTED"){
        foreach($machine as $key => $value){
          $sql = "SELECT DISTINCT h.name, count(h.id) as nb, h.USERAGENT FROM hardware h LEFT JOIN accountinfo a ON a.HARDWARE_ID = h.id WHERE h.lastcome >= date_format(sysdate(),'%Y-%m-%d 00:00:00') AND h.USERAGENT LIKE '%$key%'";
          if (is_defined($_SESSION['OCS']["mesmachines"])) {
            $sql .= " AND " . $_SESSION['OCS']["mesmachines"];
          }
          $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
          
          while($item = mysqli_fetch_array($result)) {
            if(strpos($item['USERAGENT'], 'unix') !== false) {
              $machine['unix'] = intval($item['nb']);
              $machine['all'] = $machine['all'] + intval($item['nb']);
            } elseif(strpos(strtoupper($item['USERAGENT']), 'WINDOWS') !== false) {
              $machine['windows'] = intval($item['nb']);
              $machine['all'] = $machine['all'] + intval($item['nb']);
            } elseif(strpos($item['USERAGENT'], 'Android') !== false) {
              $machine['android'] = intval($item['nb']);
              $machine['all'] = $machine['all'] + intval($item['nb']);
            }
          }
        }
      }
      if($title == "ALL COMPUTER"){
        $sql = "SELECT DISTINCT h.ID, count(h.ID) as nb, h.USERAGENT FROM hardware h LEFT JOIN accountinfo a ON a.HARDWARE_ID = h.id WHERE h.USERAGENT IS NOT NULL";
        if (is_defined($_SESSION['OCS']["mesmachines"])) {
          $sql .= " AND " . $_SESSION['OCS']["mesmachines"];
        }
        if($this->excludeArchived == 1) {
          $sql .= " AND h.ARCHIVE IS NULL";
        }
        $sql .= " GROUP BY h.USERAGENT";
        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
        while($item = mysqli_fetch_array($result)){
          if(strpos($item['USERAGENT'], 'unix') !== false) {
            $machine['unix'] = $machine['unix'] + intval($item['nb']);
            $machine['all'] = $machine['all'] + intval($item['nb']);
          } elseif(strpos($item['USERAGENT'], 'WINDOWS') !== false) {
            $machine['windows'] = $machine['windows'] + intval($item['nb']);
            $machine['all'] = $machine['all'] + intval($item['nb']);
          } elseif(strpos($item['USERAGENT'], 'Android') !== false) {
            $machine['android'] = $machine['android'] + intval($item['nb']);
            $machine['all'] = $machine['all'] + intval($item['nb']);
          }else{
            $machine['others'] = $machine['others'] + intval($item['nb']);
            $machine['all'] = $machine['all'] + intval($item['nb']);
          }
        }
      }  
      
      return $machine;
   }

   /**
    * Get multisearch url for machine tables
    * @param  array $machine [description]
    * @param  string $title [table name]
    * @return array  [description]
    */
   public function get_url($machine, $title){
      global $l;

      $_SESSION['DATE']['HARDWARE-LASTDATE-TALL'] = date($l->g(1242));
      foreach($machine as $key => $value) {
        if($machine[$key] != 0){
          if($title == "CONTACTED"){
            $machine[$key] = "<a style='font-size:32px; font-weight:bold;' href='index.php?" . PAG_INDEX . "=visu_search&fields=HARDWARE-LASTCOME&comp=tall&values=".$_SESSION['DATE']['HARDWARE-LASTCOME-TALL']."&values2=".$key."&type_field='>".$value."</a>";
          }elseif($title == "ALL COMPUTER"){
            if($key == 'others') {
              $machine[$key] = "<p style='font-size:32px; font-weight:bold;'>".$value."</p>";
            } else {
              $machine[$key] = "<a style='font-size:32px; font-weight:bold;' href='index.php?function=visu_search&fields=HARDWARE-LASTCOME&comp=tall&values=&values2=".$key."&type_field='>".$value."</a>";
            }
          }
        } else {
          $machine[$key] = "<p style='font-size:32px; font-weight:bold;'>".$value."</p>";
        }
      }

      return $machine;
   }

   /**
    * Construct table for machine tables
    * @param  string $title [table name]
    * @return string [description]
    */
   public function html_table_machine($title){
      global $l;

      $machine = $this->get_url(
        $this->get_machine_contacted_td($title), $title
      );

      
      $table = '<style type="text/css">			
                  a:focus, a:hover {color: #961b7e !important; text-decoration: none !important; font-weight:normal !important; }			
                </style> 
    
      <div class="tableContainer">';

      if($title == "CONTACTED"){
        $table .= '<table id="tab_stats" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; text-align:center; margin:auto; width:100%; margin-bottom:0px; background:#fff; border: 1px solid #ddd; table-layout: fixed;" >
                    <tr>
                      <td style="border-right: 1px solid #ddd; padding: 5px;"><span>' . $machine['all'] . '</span> </p><span style="color:#333; font-size:13pt;">'.$l->g(87).'</span></td>
                      <td style="border-right: 1px solid #ddd;"><span>' . $machine['windows']. '</span> </p><span style="color:#333; font-size:13pt;">Windows</span></td>
                      <td style="border-right: 1px solid #ddd;"><span>' . $machine['unix'] . '</span> </p><span style="color:#333; font-size:13pt;"> Unix </span></td>
                      <td style="border-right: 1px solid #ddd;"><span>' . $machine['android']. '</span> </p><span style="color:#333; font-size:13pt;">Android</span></td>    
                    </tr>';
      }elseif($title == "ALL COMPUTER"){

        //get OS's 
        $sql_os = "SELECT h.osname, count(h.osname) FROM `hardware` h LEFT JOIN accountinfo a ON a.HARDWARE_ID = h.id WHERE h.OSNAME IS NOT NULL";
        if (is_defined($_SESSION['OCS']["mesmachines"])) {
          $sql_os .= " AND " . $_SESSION['OCS']["mesmachines"];
        }
        $sql_os .= " GROUP BY h.osname";

        $result_os = mysql2_query_secure($sql_os, $_SESSION['OCS']["readServer"]);
        $oss = "<p style='font-size:32px; font-weight:bold;'>".mysqli_num_rows($result_os)."</p>";
        //get softwares
        $sql = "SELECT count(sl.ID) as nb FROM software_link sl";

        if (is_defined($_SESSION['OCS']["mesmachines"])) {
          $sql .= " LEFT JOIN software s ON s.NAME_ID = sl.NAME_ID AND s.VERSION_ID = sl.VERSION_ID AND s.PUBLISHER_ID = sl.PUBLISHER_ID 
                    LEFT JOIN accountinfo AS a ON a.HARDWARE_ID = s.HARDWARE_ID
                    WHERE " . $_SESSION['OCS']["mesmachines"];
        }

        $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"]);
        $resultSoft = mysqli_fetch_object($result);

        $softs = "<a style='font-size:32px; font-weight:bold;' href='index.php?function=visu_all_soft'>".$resultSoft->nb."</a>";

        $table .= '<table id="tab_stats" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; text-align:center; margin:auto; width:100%; margin-top:20px; background:#fff; border: 1px solid #ddd; table-layout: fixed;" >
                    <tr>
                      <td style="border-right: 1px solid #ddd; padding: 5px;"><span>' . $machine['all'] . '</span> </p><span style="color:#333; font-size:13pt;">'.$l->g(652).'</span></td>
                      <td style="border-right: 1px solid #ddd;"><span>' . $machine['windows']. '</span> </p><span style="color:#333; font-size:13pt;">Windows</span></td>
                      <td style="border-right: 1px solid #ddd;"><span>' . $machine['unix'] . '</span> </p><span style="color:#333; font-size:13pt;"> Unix </span></td>
                      <td style="border-right: 1px solid #ddd;"><span>' . $machine['android']. '</span> </p><span style="color:#333; font-size:13pt;">Android</span></td>
                      <td style="border-right: 1px solid #ddd;"><span>' . $machine['others']. '</span> </p><span style="color:#333; font-size:13pt;">'.$l->g(1605).'</span></td>
                      <td style="border-right: 1px solid #ddd;"><span>' . $oss . '</span> </p><span style="color:#333; font-size:13pt;">'.$l->g(25).'</span></td>
                      <td style="border-right: 1px solid #ddd;"><span>' . $softs. '</span> </p><span style="color:#333; font-size:13pt;">'.$l->g(20).'</span></td>                   
                    </tr>';
      }
        
       $table .= "</table></div>\n";

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
            $sql = "SELECT count(scl.ID) as nb FROM software_categories_link scl";
                    
            if (is_defined($_SESSION['OCS']["mesmachines"])) {
              $sql .= " LEFT JOIN software s ON s.NAME_ID = scl.NAME_ID AND s.VERSION_ID = scl.VERSION_ID AND s.PUBLISHER_ID = scl.PUBLISHER_ID 
                        LEFT JOIN accountinfo AS a ON a.HARDWARE_ID = s.HARDWARE_ID
                        WHERE ".$_SESSION['OCS']["mesmachines"]." AND scl.CATEGORY_ID = %s";
            } else {
              $sql .= " WHERE scl.CATEGORY_ID = %s";
            }

            $arg = array($key);
            $result = mysql2_query_secure($sql, $_SESSION['OCS']["readServer"], $arg);

            while($item = mysqli_fetch_array($result)){
              $category[$key][$value] = $item['nb'];
            }
          }

          $html = "<table class='cell-border' style='width:100%;'>";
          if($category != null){
            foreach($category as $id => $array){
              foreach($array as $name => $nb){
                if($nb != '0'){
                    $html .= "<tr class='soft-table'><td class='soft-table-td'>".$name."</td><th style='width: 50%;  text-align: center;'><a href='index.php?" . PAG_INDEX . "=visu_all_soft&onglet=".$id."'>".$nb."</a></th></tr>";
                }else{
                    $html .= "<tr class='soft-table'><td class='soft-table-td'>".$name."</td><td style='width: 50%;  text-align: center;'>".$nb."</td></tr>";
                }
              }
            }
          }else{
            foreach($cat as $key => $value){
              $html .= "<tr class='soft-table'><td class='soft-table-td'>".$value."</td><td style='width: 50%;  text-align: center;'>0</td></tr>";
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
			$list_asset_id[$item_asset['ID']] = $item_asset['ID'];
		}

		if(is_array($list_asset_id)){
			foreach($list_asset_id as $key => $values){
        $sql_assets = "SELECT h.ID as hardwareID FROM hardware h LEFT JOIN accountinfo a ON a.HARDWARE_ID = h.ID WHERE h.CATEGORY_ID = %s";
        if (is_defined($_SESSION['OCS']["mesmachines"])) {
          $sql_assets .= " AND " . $_SESSION['OCS']["mesmachines"];
        }
				$nb = [];
				$result_computer = mysql2_query_secure($sql_assets, $_SESSION['OCS']["readServer"], $values);
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
