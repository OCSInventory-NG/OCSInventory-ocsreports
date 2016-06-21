package Ocsinventory::Agent::Backend::Virtualization::Lxc;

use strict;

sub check { can_run('lxc-ls') && can_run('lxc-info') }

my $memory;
my $status;
my $name;
my $vmtype;
my $vmid;
my $vcpu=0;
my $lstatus="";
my $cpu;
my @lxc_vm;

sub run {

    my $params = shift;
    my $common = $params->{common};

    # Retrieve name and state of the vm
    foreach (`lxc-ls -1`) {
        my $vm = $1 if (/^(\S+)$/);
        #push (@lxc_vm, $vm);
        foreach (`lxc-info -n $vm`){
            $name = $1 if (/^Name:\s*(\S+)$/);
            $vmid = $1 if (/^PID:\s*(\S+)$/); 
            $lstatus = $1 if (/^State:\s*(\S+)$/);

            if ($lstatus eq "RUNNING") {
                $status = "Running";
                $memory = $1 if (`lxc-cgroup -n $name memory.limit_in_bytes` =~ /(\S+)/);
                if (`lxc-cgroup -n $name cpuset.cpus` =~ /(\S+)/) {
                    $cpu = $1;
                    if ($cpu =~ /^(\d+)-(\d+)/){
                        my @tmp = ($1..$2);
                        $vcpu += $#tmp + 1;
                    } else {
                        $vcpu += 1;
                    }
                }
            } elsif ($lstatus eq "FROZEN") {
                $status = "Paused";
            } elsif ($lstatus eq "STOPPED") {
                $status = "Off";
                open LXC, "</var/lib/lxc/$name/config" or warn;
                foreach (<LXC>) {
                    next if (/^#.*/);
                    if (/^lxc.cgroup.memory.limit_in_bytes\s+=\s*(\S+)\s*$/){
                        $memory = $1;
                    }
                    if (/^lxc.cgroup.cpuset.cpus\s+=\s*(\S+)\s*$/){
                        foreach $cpu (split(/,/,$1)){
                            $cpu = $1;
                            if ($cpu =~ /(\d+)-(\d+)/){
                                my @tmp = ($1..$2);
                                $vcpu += $#tmp + 1;
                            } else {
                                $vcpu += 1;
                            }
                        }
                    } 
                }
            }
        }
        
        my $machine = {
            MEMORY => $memory,
            NAME => $name,
            STATUS => $status,
            SUBSYSTEM => "LXC Container",
            VCPU   => $vcpu,
            VMID => $vmid,
            VMTYPE => "LXC",
        };
        $common->addVirtualMachine($machine);    
    }
}

1;
