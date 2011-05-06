package Ocsinventory::Agent::Backend::OS::Solaris::Networks;


#ce5: flags=1000843<UP,BROADCAST,RUNNING,MULTICAST,IPv4> mtu 1500 index 3
#        inet 55.37.101.171 netmask fffffc00 broadcast 55.37.103.255
#        ether 0:3:ba:24:9b:bf

#aggr40001:2: flags=201000843<UP,BROADCAST,RUNNING,MULTICAST,IPv4,CoS> mtu 1500 index 3
#        inet 55.37.101.172 netmask ffffff00 broadcast 223.0.146.255


use strict;

sub check {
  can_run("ifconfig") && can_run("netstat") && can_load ("Net::IP qw(:PROC)") 
}

# Initialise the distro entry
sub run {
  my $params = shift;
  my $common = $params->{common};

  my $description;
  my $ipaddress;
  my $ipgateway;
  my $ipmask;
  my $ipsubnet;
  my $macaddr;
  my $status;
  my $type;
  my $zone;

  my @zoneadmcmd;
  my @zacsplitted;
   
  # get the first field of the first line of "zoneadm list -p"
  @zoneadmcmd = `zoneadm list -p`;
  @zacsplitted = split(/:/, $zoneadmcmd[0]);
  $zone = $zacsplitted[0];
    
  foreach (`netstat -rn`){
    $ipgateway=$1 if /^default\s+(\S+)/i;
  }
  #print "Nom :".$zone."*************************\n";

### STEP 1: get aliases and zones' interfaces

	foreach (`ifconfig -a`){
		# resets if new interface
		$ipaddress = $description = $macaddr = $status = $type = undef if /^(\S+):/;

		# interface name and other data
		$description = $1.":".$2 if /^(\S+):(\S+):/;
		$ipaddress = $1 if /inet\s+(\S+)/i;
		$ipmask = $1 if /\S*netmask\s+(\S+)/i;  

		if (/ether\s+(\S+)/i) {
	# See
	# https://sourceforge.net/tracker/?func=detail&atid=487492&aid=1819948&group_id=58373
		  $macaddr = sprintf "%02x:%02x:%02x:%02x:%02x:%02x" ,
		  map hex, split /\:/, $1;
		}
		$status = 1 if /<UP,/;      

		# debug info
		#print "INFO1 : ".$description."_". $ipaddress."_".$ipmask."_".$macaddr."\n";

		if(($description &&  $ipaddress) ){   
	#HEX TO DEC TO BIN TO IP   	
		  $ipmask = hex($ipmask);
		  $ipmask = sprintf("%d", $ipmask);
		  $ipmask = unpack("B*", pack("N", $ipmask));
		  $ipmask = ip_bintoip($ipmask,4);     
	#print $ipmask."\n";

		  my $binip = &ip_iptobin ($ipaddress ,4);
		  my $binmask = &ip_iptobin ($ipmask ,4);
		  my $binsubnet = $binip & $binmask;
		  $ipsubnet = ip_bintoip($binsubnet,4);     

		  $common->addNetwork({
		  DESCRIPTION => $description,
		  IPADDRESS => $ipaddress,	  
		  IPGATEWAY => $ipgateway,
		  IPMASK => $ipmask,
		  IPSUBNET => $ipsubnet,
		  MACADDR => $macaddr,
		  STATUS => $status?"Up":"Down",
		  TYPE => $type,
		  });
		}
	  }	

### STEP 2: get physical interfaces when not a zone

  if (!$zone){  
		  foreach (`ifconfig -a`){			
			# resets if new interface
			$ipaddress = $description = $macaddr = $status = $type = undef if /^(\S+):/;

			# interface name and other data
			$description = $1 if /^(\S+): /;
			$ipaddress = $1 if /inet\s+(\S+)/i;
			$ipmask = $1 if /\S*netmask\s+(\S+)/i;  

			if (/ether\s+(\S+)/i) {
		# See
		# https://sourceforge.net/tracker/?func=detail&atid=487492&aid=1819948&group_id=58373
			  $macaddr = sprintf "%02x:%02x:%02x:%02x:%02x:%02x" ,
			  map hex, split /\:/, $1;
			}
			$status = 1 if /<UP,/;      

			# debug info
			#print "INFO2 : ".$description."_". $ipaddress."_".$ipmask."_".$macaddr."\n";			  

			if(($description && $macaddr)){
		#HEX TO DEC TO BIN TO IP   	
			  $ipmask = hex($ipmask);
			  $ipmask = sprintf("%d", $ipmask);
			  $ipmask = unpack("B*", pack("N", $ipmask));
			  $ipmask = ip_bintoip($ipmask,4);     
		#print $ipmask."\n";

			  my $binip = &ip_iptobin ($ipaddress ,4);
			  my $binmask = &ip_iptobin ($ipmask ,4);
			  my $binsubnet = $binip & $binmask;
			  $ipsubnet = ip_bintoip($binsubnet,4);     

			  $common->addNetwork({
			  DESCRIPTION => $description,
			  IPADDRESS => $ipaddress,	  
			  IPGATEWAY => $ipgateway,
			  IPMASK => $ipmask,
			  IPSUBNET => $ipsubnet,
			  MACADDR => $macaddr,
			  STATUS => $status?"Up":"Down",
			  TYPE => $type,
			  });
			}
		  }
	}

}

1;
