package Ocsinventory::Agent::Backend::Virtualization::Jails;

use strict;

sub check { 
    return(undef) unless -r '/usr/sbin/jls';
    return 1;
}

my @jail;

sub run {
    my $params = shift;
    my $common = $params->{common};
 
    foreach my $line (`jls -h 2>/dev/null | sed -e "1d"`) {
        push @jail, $line;
    }

    foreach my $j (@jail) {
        my @jparam=split('\s',$j);
        my $vmid=$jparam[6];
        my $name=$jparam[7];
        my $subsystem=$jparam[9];
        my $status="running";
        my $ipaddr=$jparam[36];
		
        $common->addVirtualMachine({
            IPADDR => $ipaddr,
            NAME => $name,
            STATUS => $status,
            SUBSYSTEM => "FreeBSD $subsystem",
            VMID => $vmid,
            VTYPE => "Jail",
        });
    }
}

1;
