package Ocsinventory::Agent::Backend::OS::Generic::Lspci;
use strict;

sub check {
    my $params = shift;
    my $common = $params->{common};
    $common->can_run("lspci")
}

sub run {}
1;
