package Ocsinventory::Agent::Backend::Virtualization::Libvirt;

use strict;

use XML::Simple;

sub check { can_run('virsh') }

sub run {
    my $params = shift;
    my $common = $params->{common};


    foreach (`virsh list --all`) {
        if (/^(\d+|\s+\-)\s+(\S+)\s+(\S.+)/){
            my $name = $2;
            my $status = $3;

            $status =~ s/^shut off/off/;
            my $xml = `virsh dumpxml $name`;
            my $data = XMLin($xml);

            my $vcpu = $data->{vcpu};
            my $uuid = $data->{uuid};
            my $vmtype = $data->{type};

            if ($data->{currentMemory}->{unit}) {
                my $memory = $1 if $data->{currentMemory}->{content} =~ /(\d+)\d{3}$/;
                my $vcpu = $data->{vcpu}->{content};
            } else {
                my $memory = $1 if $data->{currentMemory} =~ /(\d+)\d{3}$/;
                my $vcpu = $data->{vcpu};
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
