package Ocsinventory::Agent::Backend::OS::Linux::Arachs::Alpha::CPU;

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
        if (/^cpu\s*:/) {
            if ($current) {
                $common->addCPU($current);
            }

            $current = {
                ARCH => 'Alpha',
            };
        } else {

            $current->{SERIAL} = $1 if /^cpu serial number\s+:\s+(\S.*)/;
            $current->{SPEED} = $1 if /cycle frequency \[Hz\]\s+:\s+(\d+)000000/;
            $current->{TYPE} = $1 if /platform string\s+:\s+(\S.*)/;

        }
    }

    # The last one
    $common->addCPU($current);
}

1
