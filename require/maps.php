<?php
global $l;
/********************************* DEFINE ALL ALIAS FOR ALL TABLES *************************/

$alias_table=array('HARDWARE'=>'h',
				   'BIOS'=>'b',
				   'CONTROLLERS' => 'con',
				   'DRIVES'=> 'dr',
				   'INPUT' => 'in',
				   'MEMORIES' => 'mem',
				   'MODEMS' => 'mod',
				   'MONITORS' => 'mon',
				   'ACCOUNTINFO' => 'a');

/********************************* DEFINE ALL LBL FOR ALL FIELDS **************************/
//search all fields for accountinfo
require_once('require/function_admininfo.php');
$accountinfo_data=witch_field_more('COMPUTERS');
$lbl_column['ACCOUNTINFO']['HARDWARE_ID'] = $l->g(949);
foreach($accountinfo_data['LIST_FIELDS'] as $id=>$id_lbl){
	if ($id != '1')
		$lbl_column['ACCOUNTINFO']['fields_'.$id]=$l->g(1210)." ".$id_lbl;
	else
		$lbl_column['ACCOUNTINFO']['TAG']=$l->g(1210)." ".$id_lbl;
}
$default_column['ACCOUNTINFO']=array('TAG');



//hardware
$lbl_column['HARDWARE'] = array('ID' => $l->g(949),
						     'DEVICEID' => 'DEVICEID',
						     'NAME' => $l->g(729).": ".$l->g(23),
						     'WORKGROUP' => $l->g(33),
						     'USERDOMAIN' => $l->g(82).": ".$l->g(557),
						     'OSNAME' => $l->g(25).": ".$l->g(25),
						     'OSVERSION' => $l->g(25).": ".$l->g(275),
						     'OSCOMMENTS' => $l->g(25).": ".$l->g(286),
						     'PROCESSORT' => $l->g(54).": ".$l->g(350),
						     'PROCESSORS' => $l->g(54).": ".$l->g(569),
						     'PROCESSORN' => $l->g(54).": ".$l->g(351),
						     'MEMORY' => $l->g(568),
						     'SWAP' => $l->g(50),
						     'IPADDR' => $l->g(82).": ".$l->g(34),
						     'DNS' => $l->g(82).": DNS",
						     'DEFAULTGATEWAY' => 'DEFAULTGATEWAY',
						     'ETIME' => 'ETIME',
						     'LASTDATE' => "OCS: ".$l->g(46),
						     'LASTCOME' => "OCS: ".$l->g(352),
						     'QUALITY' => "OCS: ".$l->g(353),
						     'FIDELITY' => "OCS: ".$l->g(354),
						     'USERID' => $l->g(243).": ".$l->g(24) ,
						     'TYPE' => $l->g(66),
						     'DESCRIPTION' => $l->g(25).": ".$l->g(53),
						     'WINCOMPANY' => $l->g(355),
						     'WINOWNER' => $l->g(356),
						     'WINPRODID' => $l->g(111),
						     'WINPRODKEY' => $l->g(553),
						     'USERAGENT' => "OCS: ".$l->g(357),
						     'CHECKSUM' => 'CHECKSUM',
						     'SSTATE' => 'SSTATE',
						     'IPSRC' => 'IPSRC',
						     'UUID' => 'UUID');
$default_column['HARDWARE']=array('NAME','WORKGROUP','OSNAME','USERID','MEMORY','LASTDATE','LASTCOME');


//bios
$lbl_column['BIOS'] = array('HARDWARE_ID' => $l->g(949),
					   'SMANUFACTURER' =>$l->g(273).": ".$l->g(64),
  					   'SMODEL' => $l->g(273).": ".$l->g(284),
  					   'SSN' => $l->g(273).": ".$l->g(36),
  					   'TYPE' => $l->g(273).": ".$l->g(66),
  					   'BMANUFACTURER' =>$l->g(273).": ".$l->g(284),
  					   'BVERSION' => $l->g(273).": ".$l->g(209),
  					   'BDATE' => $l->g(273).": ".$l->g(210) ,
  					   'ASSETTAG' => $l->g(273).": ".$l->g(216));
$default_column['BIOS'] = array('SMANUFACTURER','SSN','BMANUFACTURER');


//controllers
$lbl_column['CONTROLLERS'] = array('HARDWARE_ID' => $l->g(949),
  							    'MANUFACTURER' => $l->g(64),
  							    'NAME' => $l->g(49),
  								'CAPTION' => 'Caption',
  								'DESCRIPTION' => $l->g(53),
  								'VERSION' => $l->g(277),
  								'TYPE' => $l->g(66));
$default_column['CONTROLLERS'] = array('MANUFACTURER','NAME','DESCRIPTION');



//drives
$lbl_column['DRIVES'] = array('LETTER' => $l->g(85),
					   'TYPE' => $l->g(66),
					   'FILESYSTEM' => $l->g(86),
					   'TOTAL' => $l->g(87)." (MB)",
					   'FREE' => $l->g(88)." (MB)",			   
					   'NUMFILES' => 'NUMFILES',
					   'VOLUMN' => $l->g(70),
					   'CREATEDATE'=> 'CREATEDATE');
$default_column['DRIVES'] = array('LETTER','TOTAL','FREE','VOLUMN');


//inputs
$lbl_column['INPUTS'] = array('HARDWARE_ID' => $l->g(949),
  						   'TYPE' => $l->g(66),
						   'MANUFACTURER' => $l->g(64),
						   'CAPTION' => $l->g(80),
						   'DESCRIPTION' => $l->g(53),
						   'INTERFACE' => $l->g(84),
						   'POINTTYPE' => 'POINTTYPE');

$lbl_column['MEMORIES'] = array('HARDWARE_ID' => $l->g(949),
  							 'CAPTION' => $l->g(80),
						     'DESCRIPTION' => $l->g(53),
						     'CAPACITY' => $l->g(83)." (MB)",
						     'PURPOSE' => $l->g(283),
						     'TYPE' => $l->g(66),
						     'SPEED' => $l->g(268),
						     'NUMSLOTS' => $l->g(94),
						     'SERIALNUMBER' => 'SERIALNUMBER');

$lbl_column['MODEMS'] = array('HARDWARE_ID' => $l->g(949),
						   'NAME' => $l->g(49),
						   'MODEL' => $l->g(65),
						   'DESCRIPTION' => $l->g(53),
						   'TYPE' => $l->g(66));

$lbl_column['MONITORS'] = array('HARDWARE_ID' => $l->g(949),
  							 'MANUFACTURER' => $l->g(64),
  							 'CAPTION' => 	$l->g(80),
  							 'DESCRIPTION' => $l->g(360),
     						 'TYPE' => $l->g(66),
  							 'SERIAL' => $l->g(36));

$lbl_column['NETWORKS']= array('HARDWARE_ID' => $l->g(949),
						     'DESCRIPTION' => $l->g(53),
						     'TYPE' => $l->g(66),
						     'TYPEMIB' => 'TYPEMIB',
						     'SPEED' => $l->g(268),
						     'MACADDR' => $l->g(95),
						     'STATUS' => $l->g(81),
						     'IPADDRESS' => $l->g(34),
						     'IPMASK' => $l->g(208),
						     'IPGATEWAY' => $l->g(207),
						     'IPSUBNET' => $l->g(331),
						     'IPDHCP' => $l->g(281),
						     'VIRTUALDEV' => 'VIRTUALDEV');

$lbl_column['PORTS'] = array('HARDWARE_ID' => $l->g(949),
						  'TYPE' => $l->g(66),
						  'NAME' => $l->g(49),
						  'CAPTION' => $l->g(84),
						  'DESCRIPTION' => $l->g(53));

$lbl_column['PRINTERS'] = array('HARDWARE_ID' => $l->g(949),
						     'NAME' => $l->g(49),
						     'DRIVER' => $l->g(278),
						     'PORT' => $l->g(279),
						     'DESCRIPTION' => $l->g(53));

$lbl_column['REGISTRY'] = array('HARDWARE_ID' => $l->g(949),
  							 'NAME' => $l->g(212),
  							 'REGVALUE' => $l->g(213));

$lbl_column['SLOTS'] = array('HARDWARE_ID' => $l->g(949),
						  'NAME' => $l->g(49),
						  'DESCRIPTION' => $l->g(53),
						  'DESIGNATION' => $l->g(70),
						  'PURPOSE' => 'PURPOSE',
						  'STATUS' => 'STATUS',
						  'PSHARE' => 'PSHARE');

$lbl_column['SOUNDS'] = array('HARDWARE_ID' => $l->g(949),
						   'MANUFACTURER' => $l->g(64),
						   'NAME' => $l->g(49),
						   'DESCRIPTION' => $l->g(53));


$lbl_column['STORAGES'] = array('HARDWARE_ID' => $l->g(949),
						     'MANUFACTURER' => $l->g(64),
						     'NAME' => $l->g(49),
						     'MODEL' => $l->g(65),
						     'DESCRIPTION' => $l->g(53),
						     'TYPE' => $l->g(66),
						     'DISKSIZE' => $l->g(67)." (MB)",
						     'SERIALNUMBER' => $l->g(36),
						     'FIRMWARE' => 'FIRMWARE');

$lbl_column['VIDEOS'] = array('HARDWARE_ID' => $l->g(949),
						   'NAME' => $l->g(49),
						   'CHIPSET' => $l->g(276),
						   'MEMORY' => $l->g(26)." (MB)",
						   'RESOLUTION' => $l->g(62));

$lbl_column['VIRTUALMACHINES'] = array('HARDWARE_ID' => $l->g(949),
								    'NAME' => $l->g(49),
								    'STATUS' => 'STATUS',
								    'SUBSYSTEM' => 'SUBSYSTEM',
								    'VMTYPE' => 'VMTYPE',
								    'UUID' => 'UUID',
								    'VCPU' => 'VCPU',
								    'MEMORY' => 'MEMORY');

$lbl_column['SNMP_CARDS'] = array( 'SNMP_ID' => 'SNMP_ID',
								'DESCRIPTION' => $l->g(53),
								'REFERENCE' => $l->g(1235),
								'FIRMWARE' => $l->g(1229),
								'SOFTWARE' => $l->g(20),
								'REVISION' => $l->g(277),
								'SERIALNUMBER' => $l->g(36),
								'MANUFACTURER' => $l->g(64),
								'TYPE' => $l->g(66));
?>