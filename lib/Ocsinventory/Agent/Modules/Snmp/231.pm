###
# SNMP: OID:231 SYSTEM:Fujistu
###


package Ocsinventory::Agent::Modules::Snmp::231;

use strict;
no strict 'refs';
use warnings;

sub snmp_run {
    my ($session,$snmp) = @_;

    my $common = $snmp->{common};
    my $logger=$snmp->{logger};

    my $list_mib=["If_Mib","Host_Resources_Mib"];

    $logger->debug("Running Fujitsu (231) MIB module");

    foreach my $mib ( @{$list_mib} ) {
        $snmp->snmp_oid_run($mib);
    }
}
1;
