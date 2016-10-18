package Ocsinventory::Agent::Backend::OS::Generic::Lsusb;
use strict;

sub check {
    my $params = shift;
    my $common = $params->{common};
    $common->can_run("lsusb")
}

sub run {}
1;
