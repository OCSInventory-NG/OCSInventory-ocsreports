package Ocsinventory::Agent::Backend::OS::Generic::Lspci::Videos;
use strict;

sub run {
  my $params = shift;
  my $common = $params->{common};

  foreach(`lspci`){

    if(/graphics|vga|video/i && /^\S+\s([^:]+):\s*(.+?)(?:\(([^()]+)\))?$/i){

      $common->addVideo({
	  'CHIPSET'  => $1,
	  'NAME'     => $2,
	});

    }
  }
}
1
