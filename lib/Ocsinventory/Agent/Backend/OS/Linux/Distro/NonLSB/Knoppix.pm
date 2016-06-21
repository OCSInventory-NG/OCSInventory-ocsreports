package Ocsinventory::Agent::Backend::OS::Linux::Distro::NonLSB::Knoppix;
use strict;

sub check {-f "/etc/knoppix_version"}

#####
sub findRelease {
    my $v;
  
    open V, "</etc/knoppix_version" or warn;
    chomp ($v=<V>);
    close V;
    print $v."\n";
    return "Knoppix GNU/Linux $v";
}

sub run {
    my $params = shift;
    my $common = $params->{common};
  
    my $OSComment;
    chomp($OSComment =`uname -v`);
  
    $common->setHardware({ 
        OSNAME => findRelease(),
        OSCOMMENTS => "$OSComment"
    });
}

1;
