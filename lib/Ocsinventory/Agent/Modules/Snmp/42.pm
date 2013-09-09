###
# SNMP: OID:42 SYSTEM:Sun
###

package Ocsinventory::Agent::Modules::Snmp::42;

use strict;
no strict 'refs';
use warnings;

sub snmp_run() {
  my ($session,$snmp) = @_;

  my $common = $snmp->{common};
  my $logger=$snmp->{logger};

  $logger->debug("Running Sun (42) MIB module");
  $common->setSnmpCommons( {TYPE => "Sun"} );
  $common->setSnmpComputer({SYSTEM => 'Sun'});
}

1;
