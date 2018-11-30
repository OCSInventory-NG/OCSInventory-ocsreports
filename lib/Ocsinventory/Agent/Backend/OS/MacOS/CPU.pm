package Ocsinventory::Agent::Backend::OS::MacOS::CPU;
use strict;

sub check {
    my $params = shift;
    my $common = $params->{common};
    return(undef) unless -r '/usr/sbin/system_profiler';
    return(undef) unless $common->can_load("Mac::SysProfile");
    return 1;
}

sub run {
    my $params = shift;
    my $common = $params->{common};
    my $processors;
    my $arch;
    my $datawidth;

    $processors->{1}->{MANUFACTURER} = `sysctl -n machdep.cpu.vendor`;
    $processors->{1}->{TYPE} = `sysctl -n machdep.cpu.brand_string`;
    $processors->{1}->{SPEED} = `sysctl -n hw.cpufrequency` / 1000 / 1000;
    $processors->{1}->{L2CACHESIZE} = `sysctl -n hw.l2cachesize` / 1024;
    $processors->{1}->{CORES} = `sysctl -n machdep.cpu.core_count`;
    $processors->{1}->{LOGICAL_CPUS} = `sysctl -n machdep.cpu.thread_count`;

    # 32 or 64 bits arch?
    my $sysctl_arch = `sysctl -n hw.cpu64bit_capable`;
    if ($sysctl_arch == 1){
       $arch = "x86_64";
       $datawidth = 64;
    } else {
       $arch = "x86";
       $datawidth = 32;
    }
    $processors->{1}->{CPUARCH} = $arch;
    $processors->{1}->{DATA_WIDTH} = $datawidth;

    # copy cpu infos to other packages
    my $ncpu=`sysctl -n hw.packages`;
    foreach my $cpu (2..$ncpu) {
        $processors->{$cpu}->{MANUFACTURER} = $processors->{1}->{MANUFACTURER};
        $processors->{$cpu}->{TYPE} = $processors->{1}->{TYPE};
        $processors->{$cpu}->{SPEED} = $processors->{1}->{SPEED};
        $processors->{$cpu}->{L2CACHESIZE} = $processors->{1}->{L2CACHESIZE};
        $processors->{$cpu}->{CORES} = $processors->{1}->{CORES};
        $processors->{$cpu}->{LOGICAL_CPUS} = $processors->{1}->{LOGICAL_CPUS};
        $processors->{$cpu}->{CPUARCH} = $processors->{1}->{CPUARCH};
        $processors->{$cpu}->{DATA_WIDTH} = $processors->{1}->{DATA_WIDTH};
    }
    
    # Add new cpu infos to inventory
    foreach (keys %{$processors}){
	    $common->addCPU($processors->{$_});
    }
}
1;
