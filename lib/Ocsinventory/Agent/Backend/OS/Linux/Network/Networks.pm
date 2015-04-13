package Ocsinventory::Agent::Backend::OS::Linux::Network::Networks;

use strict;
use warnings;
use Data::Dumper;

sub check {
  return unless can_run("ifconfig") && can_run("ip") && can_run("route") && can_load("Net::IP qw(:PROC)");

  1;
}


sub _ipdhcp {

  my $if = shift;
  my $path;
  my $dhcp;
  my $ipdhcp;
  my $leasepath;

  foreach (
    "/var/lib/dhcp3/dhclient.%s.leases",
    "/var/lib/dhcp3/dhclient.%s.leases",
    "/var/lib/dhcp/dhclient.%s.leases",
    "/var/lib/dhcp/dhclient.leases",
    "/var/lib/dhcp/dhclient-%s.leases",
    "/var/lib/dhclient/dhclient-%s.leases",
    "/var/lib/NetworkManager/dhclient-%s.leases" ) {

    $leasepath = sprintf($_,$if);
    last if (-e $leasepath);
  }
  return undef unless -e $leasepath;

  if (open DHCP, $leasepath) {
    my $lease;
    while(<DHCP>){
      $lease = 1 if(/lease\s*{/i);
      $lease = 0 if(/^\s*}\s*$/);
      #Interface name
      if ($lease) { #inside a lease section
        if(/interface\s+"(.+?)"\s*/){
          $dhcp = ($1 =~ /^$if$/);
        }
        #Server IP
        if(/option\s+dhcp-server-identifier\s+(\d{1,3}(?:\.\d{1,3}){3})\s*;/ and $dhcp){
          $ipdhcp = $1;
        }
      }
    }
    close DHCP or warn;
  } else {
    warn "Can't open $leasepath\n";
  }
  return $ipdhcp;
}

# Initialise the distro entry
sub run {

	my $params = shift;
	my $common = $params->{common};
	my $logger = $params->{logger};

	my $description;
	my $driver;
	my $ipaddress;
	my $ipgateway;
	my $ipmask;
	my $ipsubnet;
	my $macaddr;
	my $pcislot;
	my $status;
	my $type;
	my $virtualdev;
	my $settings;
	my $speed;
	my $current_speed;
	my $duplex;
	my $ssid;
	my $bssid;
	my $mode;
	my $version;
	my (@interfaces,@address,$interface);

	my %gateway;
	foreach (`route -n`) {
		if (/^(\d+\.\d+\.\d+\.\d+)\s+(\d+\.\d+\.\d+\.\d+)/) {
			$gateway{$1} = $2;
		}
	}

	if (defined ($gateway{'0.0.0.0'})) {
		$common->setHardware({
			DEFAULTGATEWAY => $gateway{'0.0.0.0'}
		});
  	}

	if (can_run('ifconfig')){
  		foreach my $line (`ifconfig -a`) {
    		if ($line =~ /^$/ && $description && $macaddr) {
      		# end of interface section
      		# I write the entry
      			my $binip;
      			my $binmask;
      			my $binsubnet;

      			$binip = ip_iptobin ($ipaddress ,4) if $ipaddress;
      			$binmask = ip_iptobin ($ipmask ,4) if $ipmask;
      			$binsubnet = $binip & $binmask if ($binip && $binmask);
      			$ipsubnet = ip_bintoip($binsubnet,4) if $binsubnet;

				if (-d "/sys/class/net/$description/wireless"){
      				my @wifistatus = `iwconfig $description`;
					foreach my $line (@wifistatus){
						$ssid = $1 if ($line =~ /ESSID:(\S+)/);
                        $version = $1 if ($line =~ /IEEE (\S+)/);
                        $mode = $1 if ($line =~ /Mode:(\S+)/);
                        $bssid = $1 if ($line =~ /Access Point: (\S+)/);
      				}
        			$type = "Wifi";
				}

      			$ipgateway = $gateway{$ipsubnet} if $ipsubnet;

      			# replace '0.0.0.0' (ie 'default gateway') by the default gateway IP adress if it exists
      			if (defined($ipgateway) and $ipgateway eq '0.0.0.0' and defined($gateway{'0.0.0.0'})) {
        			$ipgateway = $gateway{'0.0.0.0'};
      			}

      			if (open UEVENT, "</sys/class/net/$description/device/uevent") {
        			foreach (<UEVENT>) {
          				$driver = $1 if /^DRIVER=(\S+)/;
          				$pcislot = $1 if /^PCI_SLOT_NAME=(\S+)/;
        			}
        			close UEVENT;
      			}

				# Retrieve speed from /sys/class/net/$description/speed
				if (open SPEED, "</sys/class/net/$description/speed"){
    				foreach (<SPEED>){
     					$current_speed=$_;
   					}
    				close SPEED;
    				chomp($current_speed);
					if ($current_speed eq "65535"){
						$current_speed = "";
					}
				}
 
   				# Retrieve duplex from /sys/class/net/$description/duplex
   				if (open DUPLEX, "</sys/class/net/$description/duplex"){
   					foreach (<DUPLEX>){
 						$duplex=chomp($_);
   					}
   					close DUPLEX;
				}
 
  				if ($current_speed gt 100 ){
      				$speed = $current_speed." Gbps";
  				} else {
     				$speed = $current_speed." Mbps";
				}

      			# Reliable way to get the info
      			if (-d "/sys/devices/virtual/net/") {
        			$virtualdev = (-d "/sys/devices/virtual/net/$description")?"1":"0";
      			} elsif (can_run("brctl")) {
        			# Let's guess
        			my %bridge;
        			foreach (`brctl show`) {
          				next if /^bridge name/;
          				$bridge{$1} = 1 if /^(\w+)\s/;
        			}
        			if ($pcislot) {
          				$virtualdev = "1";
        			} elsif ($bridge{$description}) {
          				$virtualdev = "0";
        			}
      			}

      			$common->addNetwork({
          			DESCRIPTION => $description,
          			DRIVER => $driver,
          			IPADDRESS => $ipaddress,
          			IPDHCP => _ipdhcp($description),
          			IPGATEWAY => $ipgateway,
          			IPMASK => $ipmask,
          			IPSUBNET => $ipsubnet,
          			MACADDR => $macaddr,
          			PCISLOT => $pcislot,
          			STATUS => $status?"Up":"Down",
          			TYPE => $type,
          			VIRTUALDEV => $virtualdev,
          			SPEED => $speed,
          			DUPLEX => $duplex?"Full":"Half",
					SSID => $ssid,
					BSSID => $bssid,
					IEEE => $version,
					MODE => $mode,
        		});
    		}

    		if ($line =~ /^$/) { # End of section

      			$description = $driver = $ipaddress = $ipgateway = $ipmask = $ipsubnet = $macaddr = $pcislot = $status =  $type = $virtualdev = $speed = $duplex = undef;

    		} else { # In a section
        		$description = $1 if ($line =~ /^(\S+):/ || $line =~ /^(\S+)/); # Interface name
        		$ipaddress = $1 if ($line =~ /inet add?r:(\S+)/i || $line =~ /^\s*inet\s+(\S+)/ || $line =~ /inet (\S+)\s+netmask/i);
        		$ipmask = $1 if ($line =~ /\S*mask:(\S+)/i || $line =~ /\S*netmask (\S+)\s/i);
        		$macaddr = $1 if ($line =~ /hwadd?r\s+(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/i || $line =~ /ether\s+(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/i);
        		$status = 1 if ($line =~ /^\s+UP\s/ || $line =~ /flags=.*(,|<)UP(,|>)/);
        		$type = $1 if ($line =~ /link encap:(\S+)/i);
				$type = $2 if ($line =~ /^\s+(loop|ether).*\((\S+)\)/i);
    		}
  		}
	} elsif (can_run('ip')){
		foreach my $line (`ip addr show`) {
			if ($line =~ /\d+:\s+(\S+):\s+.*state\s+(\S+)/){
				if (@address) {
					push @interfaces, @address;
					undef @address;
				} elsif ($interface){
					push @interfaces, $interface;
				}
				$description = $1;
				$status = $2;
				$interface = { 
					DESCRIPTION => $description, 
					STATUS => $status
				};
			} elsif ($line =~ /link\/(\S+)\s+(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/){
				$type = $1;
    			$macaddr = $2;
			} elsif ($line =~ /inet6 (\S+)\/(\d{1,2})/) {
				$ipaddress = $1;
				$ipmask = ip_bintoip(ip_get_mask($2,6),6);

				push @address,  
				$common->addNetwork({
						DESCRIPTION => $interface->{DESCRIPTION},
						IPADDRESS => $ipaddress,
						IPMASK => $ipmask,
						MACADDRESS => $macaddr,
						STATUS => $interface->{STATUS},
						TYPE => $type,
					});
				
			} elsif ($line =~ /inet\s(\S+)\/(\d{1,3})\s+.*(\S+)/) {
				$ipaddress = $1;
   				$ipmask = ip_bintoip(ip_get_mask($2,4),4);
				$description = $3;
				push @address,  
				$common->addNetwork({
						DESCRIPTION => $interface->{DESCRIPTION},
						IPADDRESS => $ipaddress,
						IPMASK => $ipmask,
						MACADDRESS => $macaddr,
						STATUS => $interface->{STATUS},
						TYPE => $type,
					});
				
				#print Dumper($common);

				if (@address) {
					push @interfaces, @address;
					undef @address;
				} elsif ($interface){
					push @interfaces, $interface;
				}
			}
		}
	}
}
1;

