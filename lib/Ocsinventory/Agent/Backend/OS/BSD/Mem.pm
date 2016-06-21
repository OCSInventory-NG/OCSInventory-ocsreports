package Ocsinventory::Agent::Backend::OS::BSD::Mem;
use strict;

sub check {     
    `which sysctl 2>&1`;
    return 0 if($? >> 8);
    `which swapctl 2>&1`;
    return 0 if($? >> 8);
    1;
};

sub run {
    my $params = shift;
    my $common = $params->{common};

    my $PhysicalMemory;
    my $SwapFileSize;

    # Swap
    my @bsd_swapctl= `swapctl -sk`;
    for (@bsd_swapctl){
         $SwapFileSize=$1 if /total:\s*(\d+)/i;
    }
    # RAM
    chomp($PhysicalMemory=`sysctl -n hw.physmem`);
    $PhysicalMemory=$PhysicalMemory/1024;
    
    # Send it to inventory object
    $common->setHardware({
        MEMORY =>  sprintf("%i",$PhysicalMemory/1024),
        SWAP =>    sprintf("%i", $SwapFileSize/1024),
    });
}
1;
