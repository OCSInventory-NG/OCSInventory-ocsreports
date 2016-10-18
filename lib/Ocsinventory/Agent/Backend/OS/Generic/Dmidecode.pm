package Ocsinventory::Agent::Backend::OS::Generic::Dmidecode;
use strict;

sub check {
    my $params = shift;
    my $common = $params->{common};
    return unless -r "/dev/mem";
    return unless $common->can_run("dmidecode");
    1;
}

sub run {}

1;
