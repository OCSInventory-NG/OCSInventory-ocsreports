###
# SNMP: OID:7244 SYSTEM:Fujitsu
###

package Ocsinventory::Agent::Modules::Snmp::7244;

use strict;
no strict 'refs';
use warnings;

sub snmp_info {
   return ( { oid_value => "1.3.6.1.4.1.7244.1.1.1.1.1.0", oid_name => "Fujitsu" } );
}

sub snmp_run() {
    my ($session,$snmp) = @_;

    my $common = $snmp->{common};
    my $logger=$snmp->{logger};

    $logger->debug("Running Fujitsu (7244) MIB module");
    $common->setSnmpCommons( {TYPE => "Blade"} );
}

1;
