<?php 
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Pierre LEMMET 2005
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================
//Modified on $Date: 2008-02-27 12:34:12 $$Author: hunal $($Revision: 1.4 $)

	
printEnTete($l->g(601));

if( $protectedPost["sub"] ) {
	
	if( ! $_FILES["fichier"]["name"] ) {
		msg_error($l->g(602));
	}
	else {
		$fSize = @filesize( $_FILES["fichier"]["tmp_name"] );
		if( $fSize <= 0 ) {
			msg_error($l->g(436));
		}
		else {
			$filename = $_FILES['fichier']['tmp_name'];
			if( $fd = fopen($filename, "r") ) {
				$okComputers = 0;
				$koComputers = array();
				while( !feof($fd) ) {				
					$line = trim( fgets( $fd, 256 ) );
					if( affectPackage( $line, $protectedPost["id"] ) ) {
						$okComputers++;						
					}
					else if( ! empty($line) ){
						$koComputers[] = $line;
					}
					flush();					
				}				
				fclose( $fd );
				
				if( $okComputers == 0  ) {
					msg_error($l->g(603));
				}
				else {
					msg_success($okComputers." ".$l->g(604).".");
					
					if( ! empty( $koComputers ) ) {
						$msg_error= "<br>".sizeof($koComputers)." ".$l->g(605).": ";
						foreach( $koComputers as $koComputer )
							$msg_error .= "<br>".$koComputer;
						msg_error($msg_error);
					}
				}
			}
			else {
				msg_error($l->g(436));
			}			
		}
	}
}

function affectPackage( $computer, $packageId ) {
		
	//Getting hardware_id from name
	$reqName = "SELECT id FROM hardware WHERE name='$computer'";
	$resName = @mysql_query( $reqName , $_SESSION['OCS']["readServer"] );
	$valName = @mysql_fetch_array( $resName );
	
	if( ! $valName ) {
		return false;
	}
	$computerId = $valName["id"];
	
	//Removing packages already affected
	@mysql_query( "DELETE FROM devices WHERE name='DOWNLOAD' AND IVALUE=$packageId AND hardware_id='".$computerId."'", $_SESSION['OCS']["writeServer"] );
	
	if( ! @mysql_query( "INSERT INTO devices(HARDWARE_ID, NAME, IVALUE) VALUES('$computerId', 'DOWNLOAD', $packageId )", $_SESSION['OCS']["writeServer"] )) {
		return false;
	}	
	addLog("TELEDEPLOIEMENT", "Affectation masse fichier ".$packageId." sur ".$computer );
	return true;				
}

?>
<br><br>
<form id='mass' name='mass' method='post' enctype='multipart/form-data'>
<table BGCOLOR='#C7D9F5' BORDER='0' WIDTH = '600px' ALIGN = 'Center' CELLPADDING='0' BORDERCOLOR='#9894B5'>
	<tr height='30px' bgcolor='white'>
		<td><span id='filetext'><?php echo $l->g(606); ?>:</td>
		<td colspan='2'><input id='fichier' name='fichier' type='file' accept='archive/zip'></td>
	</tr>
	<tr height='20px'><td colspan='2' align='right'><input type='submit' name='sub'></td></tr>
</table>
<input type='hidden' name='id' value='<?php echo $protectedPost["id"]?$protectedPost["id"]:$protectedGet["id"]; ?>'>
</form>
