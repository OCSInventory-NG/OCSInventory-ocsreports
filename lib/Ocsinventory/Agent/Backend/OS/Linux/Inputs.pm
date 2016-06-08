package Ocsinventory::Agent::Backend::OS::Linux::Inputs;

use strict;
use warnings;


sub run {

    my $params = shift;
    my $common = $params->{common};
    my $logger = $params->{logger};
    
    my $in;
    my $vendor;
    my $phys;
    my $name;
    my $type;
    
    if (open INPUTS, "</proc/bus/input/devices") {
        foreach (<INPUTS>) {
            if (/^I: Bus=.*Vendor=(.*) Prod/){
               $in=1;
               $vendor=$1;
            } elsif ($_ =~ /^$/) {
                $in=0;
                if ($phys && $phys =~ "input"){
                    $common->addInput({
                        DESCRIPTION=>$name, 
                        CAPTION=>$name,
                        TYPE=>$type
                    });
                }
            } elsif ($in) {
                if (/^P: Phys=.*(button).*/i) {
                    $phys="nodev";
                } elsif (/^P: Phys=.*(input).*/i) {
                    $phys="input";
                }
                if (/^N: Name=\"(.*)\"/i){
                    $name=$1;
                }
                if (/^H: Handlers=(\w+)/i) {
                    if ($1 =~ ".*kbd.*") {
                       $type="Keyboard";
                    } elsif ($1 =~ ".*mouse.*") {
                       $type="Pointing";
                    } else {
                       $type=$1;
                    }
                }
            }
        }
        close INPUTS;
    }
}

1;
