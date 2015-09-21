package Ocsinventory::Agent::Backend::OS::Linux::Distro::NonLSB::Slackware;
use strict;

sub check {-f "/etc/slackware-version"}

#####
sub findRelease {
    my $v;

    open V, "</etc/slackware-version" or warn;
    #chomp ($v=<V>);
    foreach (<V>){
        $v=$1 if (/Slackware ([\d.]+)/);
        close V;
        return $v;
    }
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    my $OSComment;
    chomp($OSComment =`uname -v`);

    $common->setHardware({ 
        OSNAME => "Slackware",
        OSVERSION => findRelease(),
        OSCOMMENTS => "$OSComment"
    });
}

1;
