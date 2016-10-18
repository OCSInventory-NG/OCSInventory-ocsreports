package Ocsinventory::Agent::Backend::OS::Linux::Distro::NonLSB::SuSE;
use strict;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    $common->can_read ("/etc/SuSE-release") 
}

#####
sub findRelease {
    my $v;
  
    open V, "</etc/SuSE-release" or warn;
    chomp ($v=<V>);
    close V;
    $v;
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
