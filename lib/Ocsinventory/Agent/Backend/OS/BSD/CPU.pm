package Ocsinventory::Agent::Backend::OS::BSD::CPU;
use strict;

sub check {
    return unless -r "/dev/mem";
    1;
}

sub run {
    my $params = shift;
    my $common = $params->{common};
  
    my $os;
  
    my $processort;
    my $processorn;
    my $processors;
    
    my $family;
    my $manufacturer;
    my $serial;
    
    chomp($os = `uname -s`);
    if ($os eq "FreeBSD") {
  	    $processors = `sysctl -n hw.clockrate`;
    } else {
  	    $processors = `sysctl -n hw.cpuspeed`;
    }
    $processorn = `sysctl -n hw.ncpu`;
    $processort = `sysctl -n hw.model`;
    
    $family = `sysctl -n hw.machine`;
    $serial = `sysctl -n hw.serialno`;
  
    if (chomp($processort) =~ /Intel/i) {
  	    $manufacturer = "Intel";
    } 
    if (chomp($processort) =~ /Advanced Micro|AMD/) {
  	    $manufacturer = "AMD";
    }
  
    $common->addCPU({
        FAMILY => $family,
        MANUFACTURER => $manufacturer,
        NUMBER => $processorn,
        TYPE => $processort,
        SPEED => $processors,
        SERIAL => $serial
    });
}
1;
