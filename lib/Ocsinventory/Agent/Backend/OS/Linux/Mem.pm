package Ocsinventory::Agent::Backend::OS::Linux::Mem;
use strict;

sub check { can_read ("/proc/meminfo") }

sub run {
    my $params = shift;
    my $common = $params->{common};
    my $unit = 1024;

    my $PhysicalMemory;
    my $SwapFileSize;

    # Memory informations
    open MEMINFO, "/proc/meminfo";
    while(<MEMINFO>){
        $PhysicalMemory=$1 if /^memtotal\s*:\s*(\S+)/i;
        $SwapFileSize=$1 if /^swaptotal\s*:\s*(\S+)/i;
    }
    # TODO
    $common->setHardware({
        MEMORY =>  sprintf("%i",$PhysicalMemory/$unit),
        SWAP =>    sprintf("%i", $SwapFileSize/$unit),
    });
}

1
