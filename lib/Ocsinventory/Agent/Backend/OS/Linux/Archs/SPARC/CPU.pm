package Ocsinventory::Agent::Backend::OS::Linux::Archs::SPARC::CPU;

use strict;

sub check { can_read ("/proc/cpuinfo") };

sub run {
    my $params = shift;
    my $common = $params->{common};

    my @cpu;
    my $current = { CPUARCH => 'ARM' };
    my $ncpus = 1;
    open CPUINFO, "</proc/cpuinfo" or warn;
    foreach(<CPUINFO>) {
        $current->{TYPE} = $1 if /cpu\s+:\s+(\S.*)/;
        $ncpus = $1 if /ncpus probed\s+:\s+(\d+)/
    }

    foreach (1..$ncpus) {
        $common->addCPU($current);
    }
}

1
