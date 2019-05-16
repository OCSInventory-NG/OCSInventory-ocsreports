package Ocsinventory::Agent::Backend::OS::Solaris::CPU;

use strict;
use warnings;
use English qw(-no_match_vars);


sub run {

    my $params = shift;
    my $common = $params->{common};
    my $logger = $params->{logger};
    my (%params) = @_;
    my $current;

    my $cpus=`/usr/bin/kstat -m cpu_info | egrep "chip_id|core_id|module: cpu_info" | grep chip_id  | awk '{ print \$2 }' | sort -u | wc -l | tr -d ' '`;
    my $ncores=`/usr/bin/kstat -m cpu_info | egrep "chip_id|core_id|module: cpu_info" | grep core_id  | awk '{ print \$2 }' | sort -u | wc -l | tr -d ' '`;
    my $vproc=`/usr/bin/kstat -m cpu_info | egrep "chip_id|core_id|module: cpu_info" | grep 'module: cpu_info' | awk '{ print \$4 }' | sort -u | wc -l | tr -d ' '`;

    my $cores=$ncores / $cpus;
    my $threads=$vproc / $cpus;
    my $speed=`/usr/bin/kstat -m cpu_info | grep clock_MHz | awk '{ print \$2 }' | sort -u`;
    my $type=`/usr/bin/kstat -m cpu_info | grep brand | awk '{\$1=""}; { print \$0 }' | sort -u`;
    my $manufacturer =
         $type =~ /SPARC/ ? 'SPARC' :
         $type =~ /Xeon/  ? 'Intel' :
                            undef   ;
    my $cpuarch=`isainfo | tr ' ' '_'`;

    $logger->debug("NB CPUs: $cpus");
    $logger->debug("CORES: $cores");
    $logger->debug("LOGICAL_CPUS: $threads");
    $logger->debug("SPEED: $speed");
    $logger->debug("TYPE: $type");
    $logger->debug("MANUFACTURER: $manufacturer");
    $logger->debug("CPUARCH: $cpuarch");

    for my $i (1 .. $cpus) {
        $current->{MANUFACTURER} = $manufacturer if chomp($manufacturer);
        $current->{SPEED} = $speed if chomp($speed);
        $current->{TYPE} = $type if chomp($type);
        $current->{CORES} = $cores if $cores;
        $current->{LOGICAL_CPUS} = $threads if $threads;
        $current->{CPUARCH} = $cpuarch if chomp($cpuarch);

        $common->addCPU($current);
    }
}

#run();
1;
