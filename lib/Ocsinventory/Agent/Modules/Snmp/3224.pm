###
# SNMP: OID:3224 SYSTEM:Juniper
###

package Ocsinventory::Agent::Modules::Snmp::3224;

use strict;
no strict 'refs';
use warnings;

sub snmp_info {
   return ( { oid_value => "1.3.6.1.4.1.3224.1.1.1.0",
            oid_name => "Juniper Mib" } );
}

sub snmp_run()
{
  my ($session,$snmp) = @_;

  my $common = $snmp->{common};
  my $logger=$snmp->{logger};

  $logger->debug("Running Juniper (3224) MIB module");
  $common->setSnmpCommons( {TYPE => "Firewall"} );
  
}

1;
