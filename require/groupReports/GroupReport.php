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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

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
            $sqlRec = "SELECT * FROM `reports_notifications` WHERE STATUS = 'ON' AND (END_DATE = 0 OR END_DATE >= NOW()) AND $recurrence";
            $result = mysql2_query_secure($sqlRec, $_SESSION['OCS']["readServer"]);

            if (isset($result) && $result->num_rows > 0) {
                $scheduledReports = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
                foreach ($scheduledReports as $report) {
                    $ids[] = $report['GROUP_ID'];
                    $reportsData[$report['GROUP_ID']] = $report;
                    $reportsData[$report['GROUP_ID']]['TITLE'] = $report['RECURRENCE'].$report['GROUP_ID'];
                }
            }
        }     

        if ((isset($ids) && count($ids) > 0)) {
            // get group ids associated with reports
            $strIds = implode(",", $ids);
            $scheduledCount = count($ids);
            error_log("[".date("Y-m-d H:i:s")."] Found $scheduledCount reports scheduled to run. Now getting related group data ..");
            $groupData = $this->getGroupData($strIds);
            error_log("[".date("Y-m-d H:i:s")."] Now generating $scheduledCount reports");
            $reports = $this->generateReport($reportsData, $groupData);
            error_log("[".date("Y-m-d H:i:s")."] Reports generated to ".VARLIB_DIR."/tmp_dir/, sending notifications .. ");
        } else {
            error_log("[".date("Y-m-d H:i:s")."] No reports scheduled to be sent today, exiting script.");
        }

        return $reports ?? '';
	}

    /**
     * Retrieve the request associated with the scheduled report
     */
    public function getGroupData($ids) {
        $sqlGroup = "SELECT HARDWARE_ID, XMLDEF, hardware.NAME FROM `groups` LEFT JOIN hardware ON `groups`.hardware_id = hardware.ID WHERE hardware_id IN ($ids)";
        $result = mysql2_query_secure($sqlGroup, $_SESSION['OCS']["readServer"]);
        $groupData = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // restructuring the array
        foreach ($groupData as $key => $value) {
            $groupInfo[$value['HARDWARE_ID']] = $value;
        }
        
        return $groupInfo;
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
    public function generateReport($reportsData, $groupData) {
        $reports = array();
        
        foreach ($reportsData as $report) {
            // report title is built from dynamic grp name
            $report['TITLE'] = $groupData[$report['GROUP_ID']]['NAME'];
            $now = date("Y-m-d_H:i:s");
            $fileName = $report['TITLE']."_".$now.".xlsx";
            $filePath = VARLIB_DIR."/tmp_dir/$fileName";
            $heading = false;
            

            if(is_writable(dirname($fileName))) {
                $fp = fopen(VARLIB_DIR."/tmp_dir/$fileName", 'w');
                foreach ($groupData as $data) {
                    $ids = array();
                    $lines = array();
                    $query = $this->regeneration_sql($data['XMLDEF']);
                    $reportResult = mysql2_query_secure($query[1], $_SESSION['OCS']["readServer"]);
    
                    while ($value = mysqli_fetch_array($reportResult)) {
                        $ids[] = $value["ID"];
                    }
                    
                    
                    $strIds = implode(",", $ids);
                    // from list of IDs, get all hardware info for these devices
                    $deviceQuery = "SELECT SQL_CALC_FOUND_ROWS h.ID,h.DEVICEID,h.name,h.OSNAME,h.OSVERSION,h.OSCOMMENTS,h.PROCESSORT,h.PROCESSORS,h.PROCESSORN,h.MEMORY,h.SWAP,h.LASTDATE,h.LASTCOME,h.QUALITY,h.FIDELITY,h.DESCRIPTION,h.IPADDR,h.userid,b.ssn,h.ID 
                                    FROM hardware h LEFT JOIN accountinfo a ON a.hardware_id=h.id LEFT JOIN bios b ON b.hardware_id=h.id where h.id in ($strIds) and deviceid <> '_SYSTEMGROUP_' AND deviceid <> '_DOWNLOADGROUP_'";
                    $reportResult = mysql2_query_secure($deviceQuery, $_SESSION['OCS']["readServer"]);
    
                    while( $row = mysqli_fetch_assoc($reportResult)) {
                        $lines[] = $row;
                    }
                    

                    // fetching the export sep from OCS configuration
                    $sep = look_config_default_values("EXPORT_SEP");
                    $sep = !empty($sep['tvalue']['EXPORT_SEP']) ? $sep['tvalue']['EXPORT_SEP'] : ';';
                    // writing the file
                    if(!empty($lines)) {
                        foreach($lines as $line) {
                            if(!$heading) {
                                fwrite($fp, implode($sep, array_keys($line)) . "\n");
                                $heading = true;
                            } else {
                                fwrite($fp, implode($sep, array_values($line)) . "\n");
                            }
                        }
                    }
                }
                
                fclose($fp);
                $report['FILE'] = $fileName;
                $report['FILEPATH'] = $filePath;
                $report['DATE'] = $now;
                $reports[$report['GROUP_ID']] = $report;

            } else {
                error_log("[".date("Y-m-d H:i:s")."] Error writing file to ".VARLIB_DIR."/tmp_dir/ for report ".$report['TITLE']);
            }

        }

        return $reports;

    }

    /**
     * Update LAST_EXEC field
     */
    public function updateLastExec($id, $date) {
    
        if (isset($id)) {
            $date = (New DateTime(str_replace("_", "", $date)))->format('Y-m-d H:i:s');
            $updateQuery = "UPDATE `reports_notifications` SET LAST_EXEC = '$date' WHERE ID = $id";
            $updateResult = mysql2_query_secure($updateQuery, $_SESSION['OCS']["writeServer"]);
        }
    }



    public function mailTemplate($report, $template) {
        $template = file_get_contents($template);
        $placeholders = array(
                        '/{TITLE}/',
                        '/{DATE}/',
                        '/{GROUP_ID}/',
                    );
            $replacements = array(
                                $report['TITLE'],
                                $report['DATE'],
                                $report['GROUP_ID'],
                            );
        $template = preg_replace($placeholders, $replacements, $template);
        
        return $template;
    }

    public function sendReportNotification($reports, $values, $groupReport) {
    // writing and sending notifications if notifications have been set
    if($values['NOTIF_FOLLOW'] == 'ON'){
            foreach ($reports as $id => $report) {
                $mail = new PHPMailer();
                //$mail->SMTPDebug  = 3; 

                // Set mailer to use SMTP
                $mail->isSMTP();
                $mail->Host = $values['NOTIF_SMTP_HOST'];
                $mail->SMTPSecure = $values['NOTIF_SEND_MODE'];
                $mail->Port = $values['NOTIF_PORT_SMTP'];

                if ($values['NOTIF_USER_SMTP'] != '' && $values['NOTIF_PASSWD_SMTP'] != '') {
                    // Enable SMTP authentication
                    $mail->SMTPAuth = true;
                    // SMTP username
                    $mail->Username = $values['NOTIF_USER_SMTP'];
                    // SMTP password
                    $mail->Password = $values['NOTIF_PASSWD_SMTP'];
                } else {
                    $mail->SMTPAuth = false;
                }

                if($values['NOTIF_MAIL_REPLY'] != '' && $values['NOTIF_NAME_REPLY'] != ''){
                    $mail->addReplyTo($values['NOTIF_MAIL_REPLY'], $values['NOTIF_NAME_REPLY']);
                }

                // set From
                $mail->setFrom($values['NOTIF_MAIL_ADMIN'], $values['NOTIF_NAME_ADMIN']);

                // set recipients
                $recipients = explode(',', json_decode($report['MAIL']));
                $mail->addAddress($recipients[0]);
                unset($recipients[0]);

                foreach ($recipients as $recipient) {
                    $mail->addCC($recipient);
                }

                // set report in attachment
                $mail->addAttachment($report['FILEPATH']);
                $mail->Subject = "OCS Inventory dynamic group report : ".$report['TITLE'];
                // html format
                $mail->isHTML(true); 
                $mail->Body = $groupReport->mailTemplate($report, TEMPLATE.'OCS_group_notif.html');
                $send = $mail->send();
                
                if ($send) {
                    echo "[".date("Y-m-d H:i:s")."] Message has been sent successfully \n";
                    // updating last_exec datetime + removes generated files from tmp_dir
                    $groupReport->updateLastExec($report['ID'], $report['DATE']);
                    echo "[".date("Y-m-d H:i:s")."] Files removed from tmp_dir/ \n";
                } else {
                    echo "[".date("Y-m-d H:i:s")."] Mailer Error: " . $mail->ErrorInfo . "\n";
                }
                unlink($report['FILEPATH']);
            }
        }
    }

}


