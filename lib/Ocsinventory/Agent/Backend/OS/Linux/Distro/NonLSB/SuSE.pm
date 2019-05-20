package Ocsinventory::Agent::Backend::OS::Linux::Distro::NonLSB::SuSE;
use strict;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    $common->can_read ("/etc/SuSE-release") 
}

sub run {
    my $v;
    my $version;
    my $patchlevel;
  
    my $params = shift;
    my $common = $params->{common};
  
    open V, "</etc/SuSE-release" or warn;
    foreach (<V>) {
        next if (/^#/);
        $version=$1 if (/^VERSION = ([0-9]+)/);
        $patchlevel=$1 if (/^PATCHLEVEL = ([0-9]+)/);
    }
    close V;

    $common->setHardware({ 
        OSNAME => "SUSE Linux Enterprise Server $version SP$patchlevel",
        OSVERSION => $version,
        OSCOMMENTS => $patchlevel
    });
}

1;
