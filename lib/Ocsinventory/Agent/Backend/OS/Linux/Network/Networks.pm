package Ocsinventory::Agent::Backend::OS::Linux::Network::Networks;

use strict;
use warnings;
use Data::Dumper;
use File::stat;
use Time::Local;

sub check {
  return unless (can_run("ifconfig") || can_run("ip")) && can_run("route") && can_load("Net::IP qw(:PROC)");
  1;
}

sub getLeaseFile {

    my $if = @_; 
    my @directories = qw( 
        /var/db
        /var/lib/dhclient
        /var/lib/dhcp3
        /var/lib/dhcp
        /var/lib/NetworkManager
    );
    my @patterns = ("*$if*.lease", "*.lease", "dhclient.leases.$if");
    my @files;
    
    foreach my $directory (@directories) {
        next unless -d $directory;
        foreach my $pattern (@patterns) {
            push @files,
                 grep { -s $_ }
                 glob("$directory/$pattern");
        }
    }   
    return unless @files;
    @files = 
        map {$_->[0]}
        sort {$a->[1]->ctime()<=>$b->[1]->ctime()}
        map {[$_,stat($_)]}
        @files;
    return $files[-1];

}

sub _ipdhcp {

    my $if = shift;
    my $path;
    my $dhcp;
    my $ipdhcp;
    my $leasepath;

    if( $leasepath = getLeaseFile($if) ) {
        if (open DHCP, $leasepath) {
            my $lease;
            while(<DHCP>){
                $lease = 1 if(/lease\s*{/i);
                $lease = 0 if(/^\s*}\s*$/);
                #Interface name
                if ($lease) { #inside a lease section
                    if (/interface\s+"(.+?)"\s*/){
                        $dhcp = ($1 =~ /^$if$/);
                    }
                    #Server IP
                    if (/option\s+dhcp-server-identifier\s+(\d{1,3}(?:\.\d{1,3}){3})\s*;/ and $dhcp){
                        $ipdhcp = $1;
                    }
                }
            }
            close DHCP or warn;
        } else {
            warn "Can't open $leasepath\n";
        }
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
    my $bitrate;
    my $address;
    my $mask4;
    my $mask6;
    my $binip;
    my $binmask;
    my $binsubnet;

    my %gateway;

    if (can_run("ip")){
        my @ipLink = `ip -o link`;
        foreach my $line (`ip -o addr`) {
            $ipsubnet = $description = $ipaddress = $ipmask = $ipgateway = $macaddr = $status = $type = $speed = $duplex = $driver = $pcislot = $virtualdev = undef;

            if ($line =~ /lo.*(127\.0\.0\.1\/8)/) { # ignore default loopback interface
                   next;
            }   
            if ($line =~ /lo.*(::1\/128)/) { # ignore default loopback interface
                   next;
            }   
            
            $description = $1 if ($line =~ /\d:\s+(\S+)/i);

            if ($line =~ /inet (\S+)\/(\d{2})/i){
                $ipaddress = $1; 
                $mask4 = $2; 
            } elsif ($line =~ /inet6 (\S+)\/(\d{2})/i){
                $ipaddress = $1; 
                $mask6 = $2; 
            }
    
            if (ip_is_ipv4($ipaddress)){
                $ipmask = ip_bintoip(ip_get_mask($mask4,4),4) if $mask4;
                $binip = ip_iptobin($ipaddress,4) if $ipaddress;
                $binmask = ip_iptobin($ipmask,4) if $ipmask;
                $binsubnet = $binip & $binmask if ($binip && $binmask);
                $ipsubnet = ip_bintoip($binsubnet,4) if $binsubnet;
            } elsif (ip_is_ipv6($ipaddress)) {
                $ipmask = ip_bintoip(ip_get_mask($mask6,6),6) if $mask6;;
                $binip = ip_iptobin(ip_expand_address($ipaddress,6),6) if $ipaddress;
                $binmask = ip_iptobin(ip_expand_address($ipmask,6),6) if $ipmask;
                $binsubnet = $binip & $binmask if ($binip && $binmask);
                $ipsubnet = ip_compress_address(ip_bintoip($binsubnet,6),6) if $binsubnet;
            }

            my @line1 = split " ", $line;
            my @linkData = split " ", (grep {/$line1[1]/} @ipLink)[0];
            $macaddr = uc $linkData[-3];

            if ($linkData[2] =~ /,UP/) {
                $status = "UP";
            } else {
                $status = "DOWN";
            }

            $type = $linkData[-4];
            if ($type =~ /ether/){
                $type = "Ethernet";
            } elsif ($type =~ /loopback/) {
                $type = "Loopback";
            }

            $ipgateway = $gateway{$ipsubnet} if $ipsubnet;

            # replace '0.0.0.0' (ie 'default gateway') by the default gateway IP address if it exists
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
            if ( ! -z "/sys/class/net/$description/speed") {
                open SPEED, "</sys/class/net/$description/speed";
                foreach (<SPEED>){
                     $current_speed=$_;
                   }
                close SPEED;
                if (defined $current_speed) {
                    chomp($current_speed);
                    if ($current_speed eq "65535"){
                        $current_speed = 0;
                    } elsif ( $current_speed gt 100 ){
                        $speed = ($current_speed/1000)." Gbps";
                      } else {
                        $speed = $current_speed." Mbps";
                    }
                }
            }
 
            # Retrieve duplex from /sys/class/net/$description/duplex
            if (open DUPLEX, "</sys/class/net/$description/duplex"){
                foreach (<DUPLEX>){
                    $duplex=chomp($_);
                }
                close DUPLEX;
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
                $type = "bridge";
            }

            if (-d "/sys/class/net/$description/wireless"){
                my @wifistatus = `iwconfig $description`;
                foreach my $line (@wifistatus){
                    $ssid = $1 if ($line =~ /ESSID:(\S+)/);
                    $version = $1 if ($line =~ /IEEE (\S+)/);
                    $mode = $1 if ($line =~ /Mode:(\S+)/);
                    $bssid = $1 if ($line =~ /Access Point: (\S+)/);
                    $bitrate = $1 if ($line =~ /Bit\sRate=\s*(\S+\sMb\/s)/i);
                }
                $type = "Wifi";
            }

            if ($type eq "Wifi") {
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
                      STATUS => $status,
                      TYPE => $type,
                      SPEED => $bitrate,
                      SSID => $ssid,
                      BSSID => $bssid,
                      IEEE => $version,
                      MODE => $mode,
                });
            } else { 
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
                      STATUS => $status,
                      TYPE => $type,
                      VIRTUALDEV => $virtualdev,
                      DUPLEX => $duplex?"Full":"Half",
                      SPEED => $speed,
                });
            }
        }
    }

    elsif (can_run("ifconfig")){
        foreach my $line (`ifconfig -a`) {
            if ($line =~ /^$/ && $description && $macaddr) {
            # end of interface section
            # I write the entry
                if (ip_is_ipv4($ipaddress)){
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

                    $binip = ip_iptobin ($ipaddress ,4) if $ipaddress;
                    $binmask = ip_iptobin ($ipmask,4) if $ipmask;
                    $binsubnet = $binip & $binmask if ($binip && $binmask);
                    $ipsubnet = ip_bintoip($binsubnet,4) if $binsubnet;
                } elsif (ip_is_ipv6($ipaddress)) {
                    # Table de routage IPv6 du noyau
                    # Destination                    Next Hop                   Flag Met Ref Use If
                    # fe80::/64                      [::]                       U    256 0     0 wlp6s0
                    # foreach (`route -6`) {
                    #    if (/^(\S+\/\d{2})\s+(\S+)/) {
                    #        $gateway{$1} = $2;
                    #    }
                    #}
                    #if (defined ($gateway{'fe80::/64'})) {
                    #    $common->setHardware({
                    #        DEFAULTGATEWAY => $gateway{'fe80::/64'}
                    #    });
                    #}
                    $ipmask = ip_bintoip(ip_get_mask($mask6,6),6) if $mask6;;
                    $binip = ip_iptobin(ip_expand_address($ipaddress,6),6) if $ipaddress;
                    $binmask = ip_iptobin(ip_expand_address($ipmask,6),6) if $ipmask;
                    $binsubnet = $binip & $binmask if ($binip && $binmask);
                    $ipsubnet = ip_compress_address(ip_bintoip($binsubnet,6),6) if $binsubnet;
                }

                $ipgateway = $gateway{$ipsubnet} if $ipsubnet;

                if (-d "/sys/class/net/$description/wireless"){
                    my @wifistatus = `iwconfig $description`;
                    foreach my $line (@wifistatus){
                        $ssid = $1 if ($line =~ /ESSID:(\S+)/);
                        $version = $1 if ($line =~ /IEEE (\S+)/);
                        $mode = $1 if ($line =~ /Mode:(\S+)/);
                        $bssid = $1 if ($line =~ /Access Point: (\S+)/);
                        $bitrate = $1 if ($line =~ /Bit\sRate=\s*(\S+\sMb\/s)/i);
                    }
                    $type = "Wifi";
                } 

                if (-f "/sys/class/net/$description/mode"){
                    $type = "infiniband";
                }

                # replace '0.0.0.0' (ie 'default gateway') by the default gateway IP address if it exists
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
                if ( ! -z "/sys/class/net/$description/speed") {
                    open SPEED, "</sys/class/net/$description/speed";
                    foreach (<SPEED>){
                        $current_speed=$_;
                    }
                    close SPEED;
                    chomp($current_speed);

                    $current_speed=0 if $current_speed eq "";
                    if ($current_speed eq "65535"){
                        $current_speed = 0;
                    } 
                    if ($current_speed gt 100 ){
                        $speed = ($current_speed/1000)." Gbps";
                    } else {
                        $speed = $current_speed." Mbps";
                    }
                }
 
                # Retrieve duplex from /sys/class/net/$description/duplex
                if (! -z "/sys/class/net/$description/duplex") {
                    open DUPLEX, "</sys/class/net/$description/duplex";
                    foreach (<DUPLEX>){
                        $duplex=chomp($_);
                    }
                    close DUPLEX;
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
                    $type = "bridge";
                }

                if ($type eq "Wifi") {
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
                        STATUS => $status,
                        TYPE => $type,
                        SPEED => $bitrate,
                        SSID => $ssid,
                        BSSID => $bssid,
                        IEEE => $version,
                        MODE => $mode,
                    });
                } else { 
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
                        STATUS => $status,
                        TYPE => $type,
                        VIRTUALDEV => $virtualdev,
                        DUPLEX => $duplex?"Full":"Half",
                        SPEED => $speed,
                    });
                }
            }

            if ($line =~ /^$/) { # End of section
                $description = $driver = $ipaddress = $ipgateway = $ipmask = $ipsubnet = $macaddr = $pcislot = $status = $type = $virtualdev = $speed = $duplex = undef;
            } else { # In a section
                $description = $1 if ($line =~ /^(\S+):/ || $line =~ /^(\S+)/); # Interface name
                if ($line =~ /inet add?r:(\S+)/i || $line =~ /^\s*inet\s+(\S+)/i || $line =~ /inet (\S+)\s+netmask/i){ 
                    $ipaddress=$1;
                } elsif ($line =~ /inet6 (\S+)\s+prefixlen\s+(\d{2})/i){
                    $ipaddress=$1;
                    $mask6=$2;
                }
                if ($line =~ /\S*mask:(\S+)/i || $line =~ /\S*netmask (\S+)\s/i){
                    $ipmask=$1; 
                } 
                $macaddr = $1 if ($line =~ /hwadd?r\s+(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/i || $line =~ /ether\s+(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/i);
                $status = 1 if ($line =~ /^\s+UP\s/ || $line =~ /flags=.*[<,]UP[,>]/);
                $type = $1 if ($line =~ /link encap:(\S+)/i);
                $type = $2 if ($line =~ /^\s+(loop|ether).*\((\S+)\)/i);
            }
        }
    }
}

1;

