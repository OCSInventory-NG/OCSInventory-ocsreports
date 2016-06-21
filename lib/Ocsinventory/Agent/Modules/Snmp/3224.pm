###
# SNMP: OID:3224 SYSTEM:Juniper
###

package Ocsinventory::Agent::Modules::Snmp::3224;

use strict;
no strict 'refs';
use warnings;

sub snmp_info {
   return ( { oid_value => "1.3.6.1.4.1.3224.5.1.0", oid_name => "Juniper Mib" } );
}

sub snmp_run() {
    my ($session,$snmp) = @_;
  
    my $common = $snmp->{common};
    my $logger=$snmp->{logger};
  
    $logger->debug("Running Juniper (3224) MIB module");
    $common->setSnmpCommons( {TYPE => "Firewall"} );
    
    my $list_mib=["If_Mib"];
  
    foreach my $mib ( @{$list_mib} ) {
        $snmp->snmp_oid_run($mib);
    }
}

1;
