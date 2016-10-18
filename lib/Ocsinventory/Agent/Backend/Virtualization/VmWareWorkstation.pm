package Ocsinventory::Agent::Backend::Virtualization::VmWareWorkstation;
#
# initial version: Walid Nouh
# 

use strict;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    return $common->can_run('/bin/vmrun') 
}

sub run {
    my $params = shift;
    my $common = $params->{common};
    my $logger = $params->{logger};

    my $cpu;
    my $cores;
    my $uuid;
    my $mem;
    my $status;
    my $name;
    my $i = 0;

    my $commande = "/bin/vmrun list";
    foreach my $vmxpath ( `$commande` ) {
        next unless $i++ > 0; # Ignore the first line
        if (!open TMP, "<$vmxpath") {
            $logger->debug("Can't open $vmxpath\n");
            next;
        }
        my @vminfos = <TMP>;
        close TMP;

        foreach my $line (@vminfos) {
            if ($line =~ m/^displayName =\s\"+(.*)\"/) {
                $name = $1;
            } elsif ($line =~ m/^numvcpus =\s\"+(.*)\"/){
                $cpu = $1;
            } elsif ($line =~ m/^cpuid.coresPerSocket =\s\"+(.*)\" /){
                $cores = $1;
            } elsif ($line =~ m/^memsize =\s\"+(.*)\"/) {
                $mem = $1;
            } elsif ($line =~ m/^uuid.bios =\s\"+(.*)\"/) {
                $uuid = $1;
            }
        }

        $common->addVirtualMachine ({
            NAME      => $name,
            VCPU      => $cpu,
            CORES      => $cores,
            UUID      => $uuid,
            MEMORY    => $mem,
            STATUS    => "running",
            SUBSYSTEM => "VmWare Workstation",
            VMTYPE    => "VmWare",
        });
    }
}

1;
