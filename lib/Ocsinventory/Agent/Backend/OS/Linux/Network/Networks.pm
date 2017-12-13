package Ocsinventory::Agent::Backend::OS::Linux::Network::Networks;

use strict;
use warnings;
use Data::Dumper;
use File::stat;
use Time::Local;

sub check {
    my $params = shift;
    my $common = $params->{common};
    return unless (($common->can_run("ip") || $common->can_run("ifconfig")) && $common->can_run("route") && $common->can_load("Net::IP qw(:PROC)"));
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
    my $ipaddress6;
    my $ipgateway6;
    my $ipmask6;
    my $ipsubnet6;
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
    my $mtu;
    my @netsum;
    my $basedev;
    my $slave;
    my %gateway;

    if ($common->can_run("ip")){
        my @netsum = `ip addr show`;
        push @netsum, "\n";
        chomp @netsum;
        for (my $i=0;$i<=$#netsum;$i+=1) {
            my $line=$netsum[$i];
            if ($line =~ /^(\d+(?<!:))/ && $description && $macaddr || $line =~ /^$/ && $description && $macaddr){
                if (open UEVENT, "</sys/class/net/$description/device/uevent") {
                    foreach (<UEVENT>) {
                        $driver = $1 if /^DRIVER=(\S+)/;
                        $pcislot = $1 if /^PCI_SLOT_NAME=(\S+)/;
                    }
                    close UEVENT;
                }

                # Retrieve speed from /sys/class/net/$description/speed
                $speed=getSpeed($description);

                # Retrieve duplex from /sys/class/net/$description/duplex
                $duplex=getDuplex($description);

                # Retrieve mtu from /sys/class/net/$description/mtu
                $mtu=getMTU($description);

                if (-d "/sys/class/net/$description/wireless"){
                    my @wifistatus = `iwconfig $description 2>/dev/null`;
                    foreach my $line (@wifistatus){
                        $ssid = $1 if ($line =~ /ESSID:(\S+)/);
                        $version = $1 if ($line =~ /IEEE (\S+)/);
                        $mode = $1 if ($line =~ /Mode:(\S+)/);
                        $bssid = $1 if ($line =~ /Access Point: (\S+)/);
                        $bitrate = $1 if ($line =~ /Bit\sRate=\s*(\S+\sMb\/s)/i);
                    }
                    $type = "Wifi";
                    $status=1;
                } elsif (-f "/sys/class/net/$description/mode") {
                    $type="infiniband";
                }

                if (defined ($ipgateway)) {
                    $common->setHardware({
                        DEFAULTGATEWAY => $ipgateway
                    });
                } elsif (defined ($ipgateway6)){
                    $common->setHardware({
                        DEFAULTGATEWAY => $ipgateway6
                    });
                }

                # Virtual devices
                # Reliable way to get the info
                if (-d "/sys/devices/virtual/net/") {
                    $virtualdev = (-d "/sys/devices/virtual/net/$description")?"1":"0";
                } elsif ($common->can_run("brctl")) {
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

                # Check if this is dialup interface
                if ($description =~ m/^ppp$/) {
                    $type="dialup";
                    $virtualdev=1;
                }

                # Check if this is an alias or tagged interface
                if ($description =~ m/^([\w\d]+)[:.]\d+$/) {
                    $basedev=$1;
                    $type="alias";
                    $virtualdev=1;
                    $common->addNetwork({
                        BASE => $basedev
                    });
                }

                # Check if this is a bonding slave
                if (-d "/sys/class/net/$description/bonding"){
                    $slave=getslaves($description);
                    $type="aggregate";
                    $virtualdev=1;
                    $common->addNetwork({
                        SLAVE => $slave
                    });
                }

                if ($description && $ipaddress) {
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
                              STATUS => $status?"Up":"Down",
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
                            STATUS => $status?"Up":"Down",
                            TYPE => $type,
                            VIRTUALDEV => $virtualdev,
                            DUPLEX => $duplex?"Full":"Half",
                            SPEED => $speed,
                            MTU => $mtu,
                        });
                    }
                } elsif ($description && $ipaddress6) {
                    $common->addNetwork({
                        DESCRIPTION => $description,
                        DRIVER => $driver,
                        IPADDRESS => $ipaddress6,
                        IPDHCP => _ipdhcp($description),
                        IPGATEWAY => $ipgateway6,
                        IPMASK => $ipmask6,
                        IPSUBNET => $ipsubnet6,
                        MACADDR => $macaddr,
                        PCISLOT => $pcislot,
                        STATUS => $status?"Up":"Down",
                        TYPE => $type,
                        VIRTUALDEV => $virtualdev,
                        DUPLEX => $duplex?"Full":"Half",
                        SPEED => $speed,
                        MTU => $mtu,
                    });
                }
                $description = $driver = $ipaddress = $ipgateway = $ipmask = $ipsubnet = $macaddr = $pcislot = $status = $type = $virtualdev = $speed = $duplex = $mtu = undef;
            }
            $description = $1 if ($line =~ /^\S+\s+(\S+(?<!:))/); # Interface name
            if ($line =~ /inet ((?:\d{1,3}+\.){3}\d{1,3})\/(\d+)/i){
                $ipaddress=$1;
                $ipmask=getIPNetmask($2);
                $ipsubnet=getSubnetAddressIPv4($ipaddress,$ipmask);
                $ipgateway=getIPRoute($ipaddress);
            } elsif ($line =~ /\s+link\/(\S+)\s+((?:\w{2}+\:){5}\w{2}(?=\s)|(?:\w{2}+\:){19}\w{2}(?=\s))/i){
                $type=$1;
                if ($type eq "ether"){
                    $type="ethernet";
                }
                $macaddr=$2;
            } elsif ($line =~ /^.+(?:,|<)UP(?:,|>)/){
                $status=1;
            #} elsif ($line =~ /inet6 ((?:[0-9a-fA-F]{0,4}:|::){0,7}(?:[0-9a-fA-F]{0,4})\/(\d+)(?=\s))/i){
            } elsif ($line =~ /inet6 (\S+)\/(d{1,2})/i){
                $ipaddress6=$1;
                $ipmask6=getIPNetmaskV6($2);
                $ipsubnet6=getSubnetAddressIPv6($ipaddress6,$ipmask6);
                $ipgateway6=getIPRoute($ipaddress6);
            }
            if (!$ipaddress) {
                $ipaddress="0.0.0.0";
                $ipmask="0.0.0.0";
                $ipgateway="0.0.0.0";
                $ipsubnet="0.0.0.0";
                $status=0;
            }
        }
    }  elsif ($common->can_run("ifconfig")){
        foreach my $line (`ifconfig -a`) {
            if ($line =~ /^$/ && $description && $macaddr) {
                # end of interface section
                # I write the entry
                if (defined($ipgateway)){
                    $common->setHardware({
                        DEFAULTGATEWAY => $ipgateway
                    });
                } elsif (defined($ipgateway6)) {
                    $common->setHardware({
                        DEFAULTGATEWAY => $ipgateway6
                    });
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
                } elsif (-f "/sys/class/net/$description/mode") {
                    $type="infiniband";
                }

                if (open UEVENT, "</sys/class/net/$description/device/uevent") {
                    foreach (<UEVENT>) {
                        $driver = $1 if /^DRIVER=(\S+)/;
                        $pcislot = $1 if /^PCI_SLOT_NAME=(\S+)/;
                    }
                    close UEVENT;
                }

                # Retrieve speed from /sys/class/net/$description/speed
                $speed=getSpeed($description);

                # Retrieve duplex from /sys/class/net/$description/duplex
                $duplex=getDuplex($description);

                # Virtual devices
                # Reliable way to get the info
                if (-d "/sys/devices/virtual/net/") {
                    $virtualdev = (-d "/sys/devices/virtual/net/$description")?"1":"0";
                } elsif ($common->can_run("brctl")) {
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

                # Check if this is dialup interface
                if ($description =~ m/^ppp$/) {
                    $type="dialup";
                    $virtualdev=1;
                }

                # Check if this is an alias or tagged interface
                if ($description =~ m/^([\w\d]+)[:.]\d+$/) {
                    $basedev=$1;
                    $type="alias";
                    $virtualdev=1;
                    $common->addNetwork({
                        BASE => $basedev
                    });
                }

                # Check if this is a bonding slave
                if (-d "/sys/class/net/$description/bonding"){
                    $slave=getslaves($description);
                    $type="aggregate";
                    $virtualdev=1;
                    $common->addNetwork({
                        SLAVE => $slave
                    });
                }

                if ($description && $ipaddress) {
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
                            STATUS => $status?"Up":"Down",
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
                            STATUS => $status?"Up":"Down",
                            TYPE => $type,
                            VIRTUALDEV => $virtualdev,
                            DUPLEX => $duplex?"Full":"Half",
                            SPEED => $speed,
                            MTU => $mtu,
                        });
                    }
                } elsif ($description && $ipaddress6) {
                    $common->addNetwork({
                        DESCRIPTION => $description,
                        DRIVER => $driver,
                        IPADDRESS => $ipaddress6,
                        IPDHCP => _ipdhcp($description),
                        IPGATEWAY => $ipgateway6,
                        IPMASK => $ipmask6,
                        IPSUBNET => $ipsubnet6,
                        MACADDR => $macaddr,
                        PCISLOT => $pcislot,
                        STATUS => $status?"Up":"Down",
                        TYPE => $type,
                        VIRTUALDEV => $virtualdev,
                        DUPLEX => $duplex?"Full":"Half",
                        SPEED => $speed,
                        MTU => $mtu,
                    });
                }
            }

            if ($line =~ /^$/) { # End of section
                $description = $driver = $ipaddress = $ipgateway = $ipmask = $ipsubnet = $macaddr = $pcislot = $status = $type = $virtualdev = $speed = $duplex = $mtu = undef;
            } else { # In a section
                if ($line =~ /^(\S+):/) {
                    $description = $1; # Interface name
                }

                # Retrieve mtu from /sys/class/net/$description/mtu
                $mtu=getMTU($description);

                #$description = $1 if($line =~ /^(\w+)\s+/); # Interface name

                # BUG: ipv4 address gets overwritten by ipv6 address but we want all ip addresses
                if ($line =~ /inet add?r:(\S+)/i || $line =~ /^\s*inet\s+(\S+)/i || $line =~ /inet (\S+)\s+netmask/i){
                    $ipaddress=$1;
                    $ipmask=getIPNetmask($ipaddress);
                    $ipsubnet=getSubnetAddressIPv4($ipaddress,$ipmask);
                    $ipgateway=getRouteIfconfig($ipaddress);
                } elsif ($line =~ /inet6 (\S+)\s+prefixlen\s+(\d{2})/i){
                    $ipaddress6=$1;
                    $ipmask6=getIPNetmaskV6($ipaddress6);
                    $ipsubnet6=getSubnetAddressIPv6($ipaddress6,$ipmask6);
                    $ipgateway6=getRouteIfconfig($ipaddress6);
                }

                $macaddr = $1 if ($line =~ /hwadd?r\s+(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/i || $line =~ /ether\s+(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/i);
                $status = 1 if ($line =~ /^\s+UP\s/ || $line =~ /flags=.*[<,]UP[,>]/);
                $type = $1 if ($line =~ /link encap:(\S+)/i);
                $type = $2 if ($line =~ /^\s+(loop|ether).*\((\S+)\)/i);
                if ($type eq "ether" || $type eq "Ethernet") {
                    $type="ethernet";
                }
            }
        }
    }
}

sub getslaves{
    my ($name)=@_;
    my @slaves= map{$_ =~/\/lowr_(\w+)$/} glob("/sys/class/net/$name/lower_*");

    return join(",", @slaves);
}

sub getSpeed{
    my ($prefix)=@_;
    my $speed;
    my $current_speed=0;

    return undef unless $prefix;

    if ( ! -z "/sys/class/net/$prefix/speed") {
        open SPEED, "</sys/class/net/$prefix/speed";
        foreach (<SPEED>){
            $current_speed=$_;
        }
        close SPEED;
        chomp($current_speed);

        if ($current_speed eq "65535" || $current_speed eq ""){
            $current_speed = "Unknown";
        }
        if ($current_speed gt 100 ){
            $speed = ($current_speed/1000)." Gbps";
        } else {
            $speed = $current_speed." Mbps";
        }
    }
    return $speed;
}

sub getDuplex{
    my ($prefix)=@_;
    my $duplex;

    return undef unless $prefix;

    if (open DUPLEX, "</sys/class/net/$prefix/duplex"){
        foreach (<DUPLEX>){
            $duplex=chomp($_);
        }
        close DUPLEX;
    }
    return $duplex;
}

sub getMTU {
    my ($prefix)=@_;
    my $mtu;

    return undef unless $prefix;

    if (open MTU, "</sys/class/net/$prefix/mtu"){
        foreach (<MTU>){
            chomp;
            $mtu=$_;
        }
        close MTU;
    }
    return $mtu;
}

sub getSubnetAddressIPv4 {
    my ($address,$mask)=@_;

    return undef unless $address && $mask;

    my $binaddress=ip_iptobin($address, 4);
    my $binmask=ip_iptobin($mask, 4);
    my $binsubnet=$binaddress & $binmask;

    return ip_bintoip($binsubnet, 4);
}

sub getIPNetmask {
    my ($prefix) = @_;

    return undef unless $prefix;
    return ip_bintoip(ip_get_mask($prefix, 4), 4);
}

sub getSubnetAddressIPv6 {
    my ($address,$mask)=@_;

    return undef unless $address && $mask;

    my $binaddress = ip_iptobin($address, 6);
    my $binmask    = ip_iptobin($mask, 6);
    my $binsubnet  = $binaddress & $binmask;

    return ip_compress_address(ip_bintoip($binsubnet, 6),6);
}

sub getIPNetmaskV6 {
    my ($prefix) = @_;

    return undef unless $prefix;
    return ip_compress_address(ip_bintoip(ip_get_mask($prefix, 6), 6),6);
}

sub getIPRoute {
    my ($prefix) = @_;
    my $route;

    return undef unless $prefix;

    if (ip_is_ipv4($prefix)) {
        foreach my $line (`ip route`){
            $route = $1 if $line =~ /^default via\s+(\S+)/;
        }
    } elsif (ip_is_ipv6($prefix)) {
        foreach my $line (`ip -6 route`){
            next if $line =~ /^Unreachable/;
            $route = $1 if $line =~ /^(.*)\/.*/;
        }
    }
    return $route;
}

sub getRouteIfconfig {
    my ($prefix) = @_;
    my $route;

    return undef unless $prefix;

    if (ip_is_ipv4($prefix)) {
        foreach my $line (`route -n`){
            next if $line =~ /^Default/;
            $route = $1 if $line =~ /^0.0.0.0\s+(\S+)/;
        }
    } elsif (ip_is_ipv6($prefix)) {
        foreach my $line (`route -6n`){
            next if $line =~ /^2002/;
            $route = $1 if $line =~ /^(.*)\/.*/;
        }
    }
    return $route;
}
1;
