package Ocsinventory::Agent::Backend::OS::Generic::Lspci::Videos;
use strict;

my $memory;

sub run {
  	my $params = shift;
  	my $common = $params->{common};

	foreach(`lspci`){

    	if(/graphics|vga|video/i && /\d\d:\d\d.\d\s([^:]+):\s*(.+?)(?:\(([^()]+)\))?$/i){
	  	my $slot = $1;
		if (defined $slot) {
			my @detail = `lspci -v -s $slot`;
			foreach my $m (@detail) {	
				if ($m =~/(.*)Memory(.*=\((\d*)-bit,\sprefetchable\)\s(.*)(\d*)M\]/i) {
					$memory = $1;
					$common->addVideo({
	  					'CHIPSET'  => $1,
	  					'NAME'     => $2,
						'MEMORY'   => $memory,
					});
				}
			}
    	}
  	}
}
1
