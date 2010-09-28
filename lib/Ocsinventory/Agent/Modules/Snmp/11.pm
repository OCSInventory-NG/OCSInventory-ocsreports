package Ocsinventory::Agent::Modules::Snmp::11;

use strict;
no strict 'refs';
use warnings;
use Data::Dumper;

sub snmp_run {
   my ($session , $snmp )= @_;
   my $logger = $snmp->{logger}; 
   my $oid_run=$snmp->{snmp_oid_run};
   my $common=$snmp->{common};

  my $list_mib=["Printer_Mib"];

  $logger->debug("Running HP vendor module (11.pm)");

  foreach my $mib ( @{$list_mib} ) {
     $logger->debug("Sub mib $mib");
     $snmp->snmp_oid_run($mib);
  }
}
1;

