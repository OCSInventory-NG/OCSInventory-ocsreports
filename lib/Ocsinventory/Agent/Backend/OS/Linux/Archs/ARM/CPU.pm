package Ocsinventory::Agent::Backend::OS::Linux::Archs::ARM::CPU;

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
        if (/^Processor\s+:\s*:/) {

            if ($current) {
                $common->addCPU($current);
            }

            $current = {
                CPUARCH => 'ARM',
            };

        }

        $current->{TYPE} = $1 if /Processor\s+:\s+(\S.*)/;

    }

    # The last one
    $common->addCPU($current);
}

1
