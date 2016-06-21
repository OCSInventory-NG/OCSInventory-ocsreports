###
# SNMP: OID:8072 SYSTEM:Linux
###

package Ocsinventory::Agent::Modules::Snmp::8072;

use strict;
no strict 'refs';
use warnings;

sub snmp_run() {
    my ($session,$snmp) = @_;
  
    my $common = $snmp->{common};
    my $logger=$snmp->{logger};
    my $snmp_nom="1.3.6.1.2.1.1.5.0";
    my $list_mib=["If_Mib", "Host_Resources_Mib"];
  
    $logger->debug("Running Linux (8072) MIB module");
    $common->setSnmpCommons( {TYPE => "Linux"} );
    $common->setSnmpComputer({SYSTEM => 'Linux'});
  
    foreach my $mib ( @{$list_mib} ) {
        $snmp->snmp_oid_run($mib);
    }
  
    my $snmp_oids="1.3.6.1.4.1.8072.1.2.1.1.4";
    my $list_oid_done={8072 => 1};
    
    my $result;
    my $results_oids=$session->get_next_request(-varbindlist => [$snmp_oids]) ;
    while ( defined ($results_oids ) ) {
        foreach $result ( keys %{$results_oids} ) {
            $snmp_oids=$result;
            if ( defined ( $results_oids->{$result} ) ) {
                if ( $results_oids->{$result} =~ /endOfMibView/ ) {
                    $snmp_oids=undef;
                } elsif ( $result =~ /^1\.3\.6\.1\.4\.1\.8072\.1\.2\.1\.1\.4\S+\.1\.3\.6\.1\.4\.1\.(\d+)\./ ) {
                    my $find_oid=$1;
                    if ( ! defined $list_oid_done->{$find_oid} ) {
                        $list_oid_done->{$find_oid}=1;
                        $snmp->snmp_oid_run($find_oid);
                    }
                }
            }
        }
        $results_oids=$session->get_next_request(-varbindlist => [$snmp_oids]) ;
    }
}

1;
