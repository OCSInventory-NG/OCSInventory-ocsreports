###
# SNMP: OID:311 SYSTEM:Microsoft
###


package Ocsinventory::Agent::Modules::Snmp::311;

use strict;
no strict 'refs';
use warnings;
use Data::Dumper;

sub snmp_run {
   my ($session , $snmp )= @_;
   my $oid_run=$snmp->{snmp_oid_run};

  my $list_mib=["If_Mib", "Host_Resources_Mib"];

  $logger->debug("Running Microsoft mib module (311)");

  foreach my $mib ( @{$list_mib} ) {
     $logger->debug("Sub mib $mib");
     $snmp->snmp_oid_run($mib);
  }


}
1;
