package Ocsinventory::Agent::Backend::Virtualization::Libvirt;

use strict;

use XML::Simple;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    $common->can_run('virsh') 
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    foreach (`virsh list --all`) {
        if (/^\s*(\d+|\s+\-)\s+(\S+)\s+(\S.+)/){
            my $memory;
            my $vcpu;
            my $name = $2;
            my $status = $3;

            $status =~ s/^shut off/off/;
            my $xml = `virsh dumpxml $name`;
            my $data = XMLin($xml);

            my $vcpu = $data->{vcpu};
            my $uuid = $data->{uuid};
            my $vmtype = $data->{type};

            if ($data->{currentMemory}->{unit}) {
                $memory = $1 if $data->{currentMemory}->{content} =~ /(\d+)\d{3}$/;
                $vcpu = $data->{vcpu}->{content};
            } else {
                $memory = $1 if $data->{currentMemory} =~ /(\d+)\d{3}$/;
                $vcpu = $data->{vcpu};
            }

            my $machine = {
                MEMORY => $memory,
                NAME => $name,
                UUID => $uuid,
                STATUS => $status,
                SUBSYSTEM => "Libvirt",
                VMTYPE => $vmtype,
                VCPU   => $vcpu,
            };

            $common->addVirtualMachine($machine);

        }
    }
}

1;
