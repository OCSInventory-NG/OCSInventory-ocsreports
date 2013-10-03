package Ocsinventory::Agent::Backend::OS::Generic::Lspci::Videos;
use strict;

my $memory;
my $resolution;

sub check {can_run("xrandr")};

sub run {
  my $params = shift;
  my $common = $params->{common};

    foreach(`lspci`){

        if(/graphics|vga|video/i && /^(\d\d:\d\d.\d)\s([^:]+):\s*(.+?)(?:\(([^()]+)\))?$/i){
            my $slot = $1;
            if (defined $slot) {
                my @detail = `lspci -v -s $slot`;
                foreach my $m (@detail) {
                    if ($m =~ /.*Memory.*\s+\(.*-bit,\sprefetchable\)\s\[size=(\d*)M\]/) {
                        $memory = $1;
                    }
                }
            }
            my @resol= (`xrandr --verbose | grep *current`); 
            foreach my $r (@resol){
                if ($r =~ /((\d\d\d\d)x(\d\d\d\d))/){
                $resolution = $1;
            }
        }
            $common->addVideo({
	            'CHIPSET'    => $2,
	            'NAME'       => $3,
                'MEMORY'     => $memory,
                'RESOLUTION' => $resolution,
            });
        }
    }
}

1
