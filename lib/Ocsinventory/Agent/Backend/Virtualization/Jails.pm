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
        my @ip;
        my $ipv4=$jparam[39];
        if ($ipv4 ne '-') {
            my @ipv4=split(",",$ipv4);
            foreach my $i (@ipv4) {
                push @ip, $i;
            }
        }
        my $ipv6=$jparam[41];
        if ($ipv6 ne '-') {
            my @ipv6=split(",",$ipv6);
            foreach my $i (@ipv6) {
                push @ip, $i;
            }
        }

        my $ip=join "/", @ip;
        if (defined $ip) {
            $common->addVirtualMachine({
                IPADDR => $ip,
                NAME => $name,
                STATUS => $status,
                SUBSYSTEM => "FreeBSD $subsystem",
                VMID => $vmid,
                VTYPE => "Jail",
            });
        } else {
            $common->addVirtualMachine({
                NAME => $name,
                STATUS => $status,
                SUBSYSTEM => "FreeBSD $subsystem",
                VMID => $vmid,
                VTYPE => "Jail",
            });
        }
    }
}

1;