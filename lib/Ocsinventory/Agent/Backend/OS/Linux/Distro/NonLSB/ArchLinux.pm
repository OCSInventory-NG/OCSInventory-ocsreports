package Ocsinventory::Agent::Backend::OS::Linux::Distro::NonLSB::ArchLinux;
use strict;

sub check {-f "/etc/arch-release"}

#####
sub findRelease {
    my $v;

    open V, "</etc/arch-release" or warn;
    chomp ($v=<V>);
    close V;
    return "ArchLinux $v";
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
