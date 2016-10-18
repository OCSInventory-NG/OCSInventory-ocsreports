package Ocsinventory::Agent::Backend::Virtualization::XenCitrixServer;

use strict;
use warnings;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    $common->can_run('xe') 
}

sub run {

    my $params = shift;
    my $common = $params->{common};
    my $hostname = `hostname`;
    my $residenton;

    foreach (`xe host-list params=uuid hostname=$hostname`) {
        $residenton = $1 if /:\s+(.+)/;
    }

    foreach (`xe vm-list params=uuid resident-on=$residenton`) {
        if (/:\s+(.+)/) {
            my $uuid = $1 if /:\s+(.+)/;
            my $fname = `xe vm-list params=name-label uuid=$uuid`;
            my $name = $1 if $fname =~ /:\s+(.+)/;
            my $fstatus = `xe vm-list params=power-state uuid=$uuid`;
            my $status = $1 if $fstatus =~ /:\s+(.+)/;
            my $fvcpu = `xe vm-list params=VCPUs-max uuid=$uuid`;
            my $vcpu = $1 if $fvcpu =~ /:\s+(.+)/;
            my $fmemory = `xe vm-list params=memory-actual uuid=$uuid`;
            my $tmemory = $1 if $fmemory =~ /:\s+(.+)/;
            my $memory = $1 if $tmemory =~ /(\d+)\d{6}$/;

            my $machine = {
                MEMORY => $memory,
                NAME => $name,
                UUID => $uuid,
                STATUS => $status,
                SUBSYSTEM => "xe",
                VMTYPE => "XEN",
                VCPU => $vcpu,
            };
            $common->addVirtualMachine($machine);
          }
     }
}

1;
