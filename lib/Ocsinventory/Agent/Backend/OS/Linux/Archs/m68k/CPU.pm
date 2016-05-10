package Ocsinventory::Agent::Backend::OS::Linux::Archs::m68k::CPU;
use strict;

sub check { can_read("/proc/cpuinfo") }

sub run {
    my $params = shift;
    my $common = $params->{common};

    my @cpu;
    my $current;
    open CPUINFO, "</proc/cpuinfo" or warn;
    foreach(<CPUINFO>) {
        print;
        if (/^CPU\s+:\s*:/) {
            if ($current) {
                $common->addCPU($current);
            }
            $current = {
                CPUARCH => 'm68k',
            };
        } else {
            $current->{TYPE} = $1 if /CPU:\s+(\S.*)/;
            $current->{SPEED} = $1 if /Clocking:\s+:\s+(\S.*)/;
        }
    }
    # The last one
    $common->addCPU($current);
}

1;
