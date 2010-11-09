###
# SNMP: OID:311 SYSTEM:Microsoft
###


package Ocsinventory::Agent::Modules::Snmp::311;

use strict;
no strict 'refs';
use warnings;
use Data::Dumper;

sub snmp_info {
   return ( { oid_value => "1.3.6.1.4.1.77.1.2.1.0" ,
            oid_name => "Microsoft" } );
}
sub snmp_run {
   my ($session , $snmp )= @_;
   my $oid_run=$snmp->{snmp_oid_run};

   my $inventory = $snmp->{inventory};
   my $logger=$snmp->{logger};


  my $list_mib=["If_Mib", "Host_Resources_Mib"];

  $logger->debug("Execution mib Microsoft:311");

  foreach my $mib ( @{$list_mib} ) {
     $logger->debug("Sub mib $mib");
     $snmp->snmp_oid_run($mib);
  }

  #This device is a computer
  $common->setSnmpComputer({
    SYSTEM => 'Microsoft',
  });

}
1;
