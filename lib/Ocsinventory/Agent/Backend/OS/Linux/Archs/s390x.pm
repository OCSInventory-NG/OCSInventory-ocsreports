package Ocsinventory::Agent::Backend::OS::Linux::Archs::s390x;

use strict;

use Config;

sub check { 
    return 1 if $Config{'archname'} =~ /^s390/;
    0; 
};

sub run{}

1;
