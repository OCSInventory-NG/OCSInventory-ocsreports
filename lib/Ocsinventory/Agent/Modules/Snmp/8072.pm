###
# SNMP: OID:8072 SYSTEM:Linux
###

package Ocsinventory::Agent::Modules::Snmp::8072;

use strict;
no strict 'refs';
use warnings;

sub snmp_run()
{
  my ($session,$snmp) = @_;

  my $common = $snmp->{common};
  my $logger=$snmp->{logger};
  my $snmp_nom="1.3.6.1.2.1.1.5.0";
  my $list_mib=["If_Mib", "Host_Resources_Mib"];

  $logger->debug("Running linux mib module (8072)");

  foreach my $mib ( @{$list_mib} ) {
     $logger->debug("Sub mib $mib");
     $snmp->snmp_oid_run($mib);
  }


    
}

1;
