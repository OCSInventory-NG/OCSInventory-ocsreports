<?php
function support(){
	global $l;
	//not search certificat if it's exist in session
	if (isset($_SESSION['OCS']['SUPPORT_KEY']))
		return $_SESSION['OCS']['SUPPORT_KEY'];
		
	$certs=array();
	
	//find all support certificats 
	$sql="select FILE,description from ssl_store where file_type='CERT_SUPPORT'";
	$result=mysql2_query_secure($sql,$_SESSION['OCS']["readServer"]);		
	while($cert = mysql_fetch_array( $result ))
		$certificats[$cert['description']]=$cert['FILE'];
		
	//certificats is ok?
	if(	!isset($certificats['cert']) 
		  or !isset($certificats['pkey'])
		   or !isset($certificats['extracerts-0'])
		   or !isset($certificats['extracerts-1'])){
		if ($_SESSION['OCS']['DEBUG'] == 'ON')
			msg_error('NO CERTIFICAT');
		return false;
	}
	//verify if certificat was signed by AC OCS
	if (!openssl_x509_check_private_key ( $certificats['cert'] , $certificats['pkey'] )){
		if ($_SESSION['OCS']['DEBUG'] == 'ON')
			msg_error('PKEY NOT SIGNED CERT');
		return false;
	}
	//read certificat
	$open_data=openssl_x509_read($certificats['cert']);
	$viewCert = openssl_x509_parse($open_data); 

	//Put on SESSION all information we need
	if ($viewCert['validTo_time_t']> time()){
		$_SESSION['OCS']['SUPPORT_KEY']=$viewCert['serialNumber']."-".$viewCert['hash']."-".$viewCert['validFrom_time_t'];
		$_SESSION['OCS']['SUPPORT_VALIDITYDATE']=date($l->g(1242), $viewCert['validTo_time_t']);
		$email=explode(':',$viewCert['extensions']['subjectAltName']);
		$_SESSION['OCS']['SUPPORT_EMAIL']=$email[1];
		$deliv=explode('/DC=',$viewCert['name']);
		$_SESSION['OCS']['SUPPORT_DELIV']=$deliv[2].".".$deliv[1];
		return $_SESSION['OCS']['SUPPORT_KEY'];
	
	}else{ //certificat out of date
		if ($_SESSION['OCS']['DEBUG'] == 'ON')
			msg_error('CERT OUT OF DATE');
		return false;
	}	
	
}

//parse certificat 
function parse_cert($file,$pass){
	global $l;
	openssl_pkcs12_read ( $file , $certs , $pass );
	if (!is_array($certs) 
		 or !isset($certs['cert']) 
		  or !isset($certs['pkey'])
		   or !isset($certs['extracerts'])){
		   msg_error($l->g(1282));
		   return false;
		   }
	return $certs;
}


?>