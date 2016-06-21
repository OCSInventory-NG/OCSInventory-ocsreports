###
# SNMP: OID:311 SYSTEM:Microsoft
###


package Ocsinventory::Agent::Modules::Snmp::311;

use strict;
no strict 'refs';
use warnings;

sub snmp_info {
   return ( { oid_value => "1.3.6.1.4.1.77.1.2.1.0" , oid_name => "Microsoft" } );
}
sub snmp_run {
    my ($session , $snmp )= @_;
    my $oid_run=$snmp->{snmp_oid_run};

    my $common = $snmp->{common};
    my $inventory = $snmp->{inventory};
    my $logger=$snmp->{logger};
    $common->setSnmpCommons( {TYPE => "Microsoft"} );
    $common->setSnmpComputer({SYSTEM => 'Microsoft'});

    my $list_mib=["If_Mib", "Host_Resources_Mib"];

    $logger->debug("Running Microsoft (311) MIB module");

    foreach my $mib ( @{$list_mib} ) {
        $snmp->snmp_oid_run($mib);
    }
}
1;
