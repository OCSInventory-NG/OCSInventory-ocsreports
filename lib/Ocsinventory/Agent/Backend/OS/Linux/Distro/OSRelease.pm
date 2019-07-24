package Ocsinventory::Agent::Backend::OS::Linux::Distro::OSRelease;

use warnings;
use strict;

sub check {
    my $params = shift;
    my $common = $params->{common};
    $common->can_read ("/etc/os-release") 
}

sub run {

    my $v;
    my $name;
    my $version;
    my $description;

    my $params = shift;
    my $common = $params->{common};

    open V, "/etc/os-release" or warn;
    foreach (<V>) {
       next if /^#/;
       $name = $1 if (/^NAME="?([^"]+)"?/);
       $version = $1 if (/^VERSION_ID="?([^"]+)"?/);
       $description=$1 if (/^PRETTY_NAME="?([^"]+)"?/);
    }
    close V;
    chomp($name);

    $common->setHardware({
        OSNAME => $name,
        OSVERSION => $version,
        OSCOMMENTS => $description,
    });

}

1;
