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
    my $fake_procs=1;
    my $model;
    my $mhz;
    my $cpuCores;
    my $serialnumber;
    my $cachesize;
    my $procs;
    my $threads;
    my $logical_cores;
    my $vendor_id;
    my $arch;
    my $datawidth;

    # informations from system_profiler
    my @cpuinfo=`system_profiler SPHardwareDataType`;
    foreach my $line (@cpuinfo){
        chomp $line;
            if ($line =~ /^\s*$/){
            $procs=$fake_procs if ($procs eq "");
            $processors->{$procs}->{MANUFACTURER}=$vendor_id;
            $processors->{$procs}->{TYPE}=$model;
            $processors->{$procs}->{SPEED}=$mhz;
            $processors->{$procs}->{L2CACHESIZE}=$cachesize;
            $processors->{$procs}->{CORES}=$cpuCores ? $cpuCores : 1;
            $processors->{$procs}->{SERIALNUMBER}=$serialnumber;
            $vendor_id=$model=$mhz=$cpuCores=$serialnumber=$procs="";
        }
        $procs=$1 if ($line =~ /Number of Processors:\s(\S.*)/);
        $model=$1 if ($line =~ /Processor Name:\s(.*)/);
        $vendor_id= $model =~ /Intel/i ? "Intel" : undef;
        $mhz=$1 if ($line =~ /Processor Speed:\s(.*)/);
        $cpuCores=$1 if ($line =~ /Total Number of Cores:\s(.*)/);
        $serialnumber=$1 if ($line =~ /Serial Number \(system\):\s(.*)/);
        $cachesize=$1 if ($line =~ /L2 Cache \(per Core\):\s(.*)/);
        if ($cachesize =~ /KB/){
            $cachesize =~ s/ KB//;
            $cachesize = $cachesize*$cpuCores;
        }
        # lamp spits out an sql error if there is something other than an int (MHZ) here....
        if ($mhz =~ /GHz$/){
            $mhz =~ s/ GHz//;
            # French Mac returns 2,60 Ghz instead of
            # 2.60 Ghz :D
            $mhz =~ s/,/./;
            $mhz = ($mhz * 1000);
        }
        if ($mhz =~ /MHz$/){
            $mhz =~ s/ MHz//;
        }
    }

    # more informations from sysctl 
    my @sysctlinfo=`sysctl -a machdep.cpu`;
    
    foreach my $line (@sysctlinfo){
        chomp $line;
        if ($line =~ /^\s*$/){
            $processors->{$procs}->{LOGICAL_CPUS}=$logical_cores;
            $processors->{$procs}->{THREADS}=$threads;
            $threads=$logical_cores="";
        }
        $threads=$1 if ($line =~ /machdep.cpu.thread_count:\s(\d)/);
        $logical_cores=$1 if ($line =~ /machdep.cpu.logical_per_package:\s(\d+)/);
    }

    # 32 or 64 bits arch?
    my $sysctl_arch=`sysctl hw.cpu64bit_capable`;
    if ($sysctl_arch == 1){
       $arch="x86_64";
       $datawidth=64;
    } else {
       $arch="x86";
       $datawidth=32;
    }
    $processors->{$procs}->{CPUARCH}=$arch;
    $processors->{$procs}->{DATA_WIDTH}=$datawidth;
    
    # Add new cpu infos to inventory
    foreach (keys %{$processors}){
	    $common->addCPU($processors->{$_});
    }
}

1;
