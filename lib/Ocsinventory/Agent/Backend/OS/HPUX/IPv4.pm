package Ocsinventory::Agent::Backend::OS::HPUX::IPv4;

sub check {
    `which ifconfig 2>&1`; ($? >> 8)?0:1 
}

# Initialise the distro entry
sub run {
    my $params = shift;
    my $common = $params->{common};
    my $ip;
    my $hostname;

    if ( `hostname` =~ /(\S+)/ ) {
      $hostname=$1;
    }

    for ( `grep $hostname /etc/hosts ` ) {
        if ( /(^\d+\.\d+\.\d+\.\d+)\s+/ ){
            $ip=$1;
            $common->setHardware({IPADDR => $ip});
        }
    }
}

1;
