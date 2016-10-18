package Ocsinventory::Agent::Backend::OS::AIX::IPv4;

sub check {
    my $params = shift;
    my $common = $params->{common};
    $common->can_run("ifconfig")
}

# Initialise the distro entry
sub run {
    my $params = shift;
    my $common = $params->{common};
    my @ip;

    # Looking for ip addresses with ifconfig, except loopback
    # AIX need -a option
    for (`ifconfig -a`){#ifconfig in the path
        # AIX ligne inet
        if(/^\s*inet\s+(\S+).*/){($1=~/127.+/)?next:push @ip, $1};
    }
    $ip=join "/", @ip;
    $common->setHardware({IPADDR => $ip});
}

1;
