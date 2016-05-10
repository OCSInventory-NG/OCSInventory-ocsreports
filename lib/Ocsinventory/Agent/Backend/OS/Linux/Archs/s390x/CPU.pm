package Ocsinventory::Agent::Backend::OS::Linux::Archs::s390x::CPU;

use strict;

sub check { can_read("/proc/cpuinfo") }

sub run {
    my $params = shift;
    my $common = $params->{common};

    my $current;
    my $cpucores;
    open CPUINFO, "</proc/cpuinfo" or warn;
    foreach(<CPUINFO>) {
        print;
        if (/^vendor\s*:/) {
            if ($current) {
                $common->addCPU($current);
              }
        } elsif (/^processor [0-9]:\s+(\S.*)/) {
            $cpucores++;
        }
        $current->{MANUFACTURER} = $1 if /vendor_id\s*:\s+(\S.*)/;
        $current->{SPEED} = $1 if /bogomips per cpu:\s+(\S.*)/;
    }
    $current->{CORES} = $cpucores;
    $common->addCPU($current);
}

1;
