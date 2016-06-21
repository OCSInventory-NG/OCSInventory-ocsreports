###
# SNMP: Default
###

package Ocsinventory::Agent::Modules::Snmp::Default;

use strict;
no strict 'refs';
use warnings;

sub snmp_run {
    my ($session , $snmp )= @_;
    my $oid_run=$snmp->{snmp_oid_run};

    my $inventory = $snmp->{inventory};
    my $logger=$snmp->{logger};

    my $list_mib=["If_Mib", "Host_Resources_Mib","Printer_Mib"];

    $logger->debug("Running Default MIB module \n");

    foreach my $mib ( @{$list_mib} ) {
        $snmp->snmp_oid_run($mib);
    }
}
1;
