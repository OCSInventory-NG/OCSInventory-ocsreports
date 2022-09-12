<?php
/*
 * Copyright 2005-2022 OCSInventory-NG/OCSInventory-ocsreports contributors.
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
 * Class for generating and sending dynamic group reports
 */
class GroupReport {
    /**
     * Check which reports are supposed to be run at the time of execution
     */
    public function getScheduledReports() {
        // retrieve all scheduled reports for each recurrence if they do need to be executed : 
        // last_exec exceeds 1day/1week/1month or last_exec equals time_created and end_date is not exceeded 
        $recurrences = array("DAILY"  =>  "((LAST_EXEC <= NOW() - INTERVAL 1 DAY AND LAST_EXEC >= NOW() - INTERVAL 1 WEEK) OR (LAST_EXEC = DATE_CREATED AND RECURRENCE = 'DAILY'))",
                             "WEEKLY"  =>  " WEEKDAY = WEEKDAY(NOW()) AND ((LAST_EXEC <= NOW() - INTERVAL 1 WEEK AND LAST_EXEC >= NOW() - INTERVAL 1 MONTH) OR (LAST_EXEC = DATE_CREATED AND RECURRENCE = 'WEEKLY'))",
                             "MONTHLY"  =>  "((LAST_EXEC <= NOW() - INTERVAL 1 WEEK AND LAST_EXEC <= NOW() - INTERVAL 1 MONTH) OR (LAST_EXEC = DATE_CREATED AND RECURRENCE = 'MONTHLY'))");


        foreach ($recurrences as $key => $recurrence) {
            $sqlRec = "SELECT * FROM `reports_notifications` WHERE (END_DATE = 0 OR END_DATE >= NOW()) AND $recurrence";
            $result = mysql2_query_secure($sqlRec, $_SESSION['OCS']["readServer"]);

            if (isset($result) && !empty($result)) {
                $scheduledReports = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
                foreach ($scheduledReports as $report) {
                    $ids[] = $report['GROUP_ID'];
                    $reportTitles[] = $report['RECURRENCE'].$report['GROUP_ID'];
                }
            }
        }     
        // get group ids associated with reports
        $strIds = implode(",", $ids);
        $groupData = $this->getGroupData($strIds);
        $reports = $this->generateReport($reportTitles, $groupData);         
        
	}

    /**
     * Retrieve the request associated with the scheduled report
     */
    public function getGroupData($ids) {
        $sqlGroup = "SELECT HARDWARE_ID, XMLDEF, hardware.NAME FROM `groups` LEFT JOIN hardware ON `groups`.hardware_id = hardware.ID WHERE hardware_id IN ($ids)";
        $result = mysql2_query_secure($sqlGroup, $_SESSION['OCS']["readServer"]);
        $groupData = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        return $groupData;
    }


    /**
     * Regenarate SQL queries from XMLDEF field
     */
    function regeneration_sql($query) {
        $tab = xml_decode($query);
        $cherche = array("<xmldef>", "</REQUEST>", "</xmldef>");
        $replace = array("", "", "");
        $tab = str_replace($cherche, $replace, $tab);
        $tab_list_sql = explode("<REQUEST>", trim($tab));
        unset($tab_list_sql[0]);
        return($tab_list_sql);
    }


    /**
     * Generate reports file
     */
    public function generateReport($reportTitles, $groupData) {
        $items = array();

        foreach ($reportTitles as $title) {
            $fileName = $title."_".date("Y-m-d H:i:s").".xls";
            $heading = false;
            $fp = fopen("/tmp/$fileName", 'w');

            foreach ($groupData as $data) {

                $query = $this->regeneration_sql($data['XMLDEF']);
                $reportResult = mysql2_query_secure($query[1], $_SESSION['OCS']["readServer"]);

                while ($value = mysqli_fetch_array($reportResult)) {
                    $ids[] = $value["ID"];
                }

                $strIds = implode(",", $ids);
                $deviceQuery = "SELECT SQL_CALC_FOUND_ROWS h.ID,h.DEVICEID,h.name,h.OSNAME,h.OSVERSION,h.OSCOMMENTS,h.PROCESSORT,h.PROCESSORS,h.PROCESSORN,h.MEMORY,h.SWAP,h.LASTDATE,h.LASTCOME,h.QUALITY,h.FIDELITY,h.DESCRIPTION,h.IPADDR,h.userid,b.ssn,h.ID 
                                FROM hardware h LEFT JOIN accountinfo a ON a.hardware_id=h.id LEFT JOIN bios b ON b.hardware_id=h.id where h.id in ($strIds) and deviceid <> '_SYSTEMGROUP_' AND deviceid <> '_DOWNLOADGROUP_'";
                $reportResult = mysql2_query_secure($deviceQuery, $_SESSION['OCS']["readServer"]);

                while( $row = mysqli_fetch_assoc($reportResult)) {
                    $lines[] = $row;
                }

                // write data to the file
                if(!empty($lines)) {
                    foreach($lines as $line) {
                        if(!$heading) {
                            fwrite($fp, implode("\t", array_keys($line)) . "\n");
                            $heading = true;
                        } else {
                            fwrite($fp, implode("\t", array_values($line)) . "\n");
                        }
                    }

                }
            }
            
            fclose($fp);

        }
        

    }
}


