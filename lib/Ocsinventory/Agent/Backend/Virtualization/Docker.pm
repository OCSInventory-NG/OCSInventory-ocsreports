package Ocsinventory::Agent::Backend::Virtualization::Docker;

use strict;

sub check { 
    return(undef) unless -r '/usr/bin/docker';
    return 1;
}

my @image;

sub run {
    my $params = shift;
    my $common = $params->{common};
 
    foreach my $cont (`docker ps 2>/dev/null`) {
        next if ($cont =~ /^CONTAINER ID/);
        my $container_id=$1 if ($cont =~ /^(\w+)/i);
        push @image, $container_id;
    }

    foreach my $c (@image) {
        my $tab=`docker inspect $c`;
        my $memory=$1 if ($tab =~ /\s+"Memory":\s(\d+),/);
        my $name=$1 if ($tab =~ /\s+"Hostname":\s"(\w+)",/);
        my $status=$1 if ($tab =~ /\s+"Status":\s"(\w+)",/);
        my $vcpu=$1 if ($tab =~ /\s+"CpuShares":\s(\d+),/); 
        my $vmid=$1 if ($tab =~ /\s+"Id":\s"(\w+)",/);
        my $ipaddr=$1 if ($tab =~ /\s+"IPAddress":\s"(.*)"/);
        my $macaddr=$1 if ($tab =~ /\s+"MacAddress":\s"(.*)"/);
        my $gateway=$1 if ($tab =~ /\s+"Gateway":\s"(.*)"/);
        $common->addVirtualMachine({
            CPUSHARES => $vcpu,
            GATEWAY => $gateway,
            IPADDR => $ipaddr,
            MACADDR => $macaddr,
            MEMORY => $memory,
            NAME => $name,
            STATUS => $status,
            SUBSYSTEM => "Docker Container",
            VMID => $vmid,
            VTYPE => "Docker",
        });
    }
}

1;
