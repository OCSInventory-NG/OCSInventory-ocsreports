package Ocsinventory::Agent::Backend::OS::Generic::Lspci::Sounds;
use strict;

sub run {
  my $params = shift;
  my $common = $params->{common};

  foreach(`lspci`){

    if(/audio/i && /^\S+\s([^:]+):\s*(.+?)(?:\(([^()]+)\))?$/i){

      $common->addSound({
	  'DESCRIPTION'  => $3,
	  'MANUFACTURER' => $2,
	  'NAME'     => $1,
	});
    
    }
  }
}
1
