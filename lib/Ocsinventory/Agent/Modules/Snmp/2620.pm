###
# SNMP: OID:2620 SYSTEM:Checkpoint
###

package Ocsinventory::Agent::Modules::Snmp::2620;

use strict;
no strict 'refs';
use warnings;

sub snmp_info {
    return ( { oid_value => "1.3.6.1.4.1.2620.1.1.1.0", oid_name => "Checkpoint Mib" } );
}

sub snmp_run() {
    my ($session,$snmp) = @_;

    my $common = $snmp->{common};
    my $logger=$snmp->{logger};

    $logger->debug("Running Chekpoint (2620) MIB module");
    $common->setSnmpCommons( {TYPE => "Firewall"} );
}

1;
