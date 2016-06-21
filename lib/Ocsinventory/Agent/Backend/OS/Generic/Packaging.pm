package Ocsinventory::Agent::Backend::OS::Generic::Packaging;

use strict;

sub check {
    my $params = shift;
  
    # Do not run an package inventory if there is the --nosoftware parameter
    return if ($params->{config}->{nosoftware});
    1;
}

sub run{}

1;
