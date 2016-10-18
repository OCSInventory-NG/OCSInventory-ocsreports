package Ocsinventory::Agent::Backend::OS::Linux::Archs::Alpha::CPU;

use strict;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    $common->can_read("/proc/cpuinfo") 
}

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
                CPUARCH => 'Alpha',
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

1;
