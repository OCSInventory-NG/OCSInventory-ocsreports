package Ocsinventory::Agent::Backend::Virtualization::Docker;

use strict;
use warnings;

sub check { can_run('docker') }

my @containers=`docker ps`;
my @image;
my $memory;
my $name;
my $vcpu;
my $status;
my $vmid;

sub run {
	my $params = shift;
	my $common = $params->{common};
 
    foreach my $cont (@containers) {
        next if ($cont =~ /^CONTAINER ID/);
        my $container_id=$1 if ($cont =~ /^(\w+)/i);
        push @image, $container_id;
    }

    foreach my $c (@image) {
        my @tab=`docker inspect $c`;
        foreach my $m (@tab) {
            $memory=$1 if ($m =~ /^\s+"Memory":\s(\d+),/i);
            $name=$1 if ($m =~ /^\s+"Hostname":\s"(\w+)",/i);
            $status=$1 if ($m =~ /^\s+"Status":\s"(\w+)",/i);
            $vcpu=$1 if ($m =~ /^\s+"CpuShares":\s(\d+),/i); 
            $vmid=$1 if ($m =~ /^\s+"Id":\s"(\w+)",/i);
        }
        $common->addVirtualMachine({
            NAME => $name,
            MEMORY => $memory,
            STATUS => $status,
            SUBSYSTEM => "Docker Container",
            VCPU => $vcpu?$vcpu:"Not defined",
            VMID => $vmid,
            VTYPE => "Docker",
        });
    }
}

1;


