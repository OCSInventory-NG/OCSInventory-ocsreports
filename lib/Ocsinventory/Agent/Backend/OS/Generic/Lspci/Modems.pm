package Ocsinventory::Agent::Backend::OS::Generic::Lspci::Modems;
use strict;

sub run {
    my $params = shift;
    my $common = $params->{common};
  
    foreach(`lspci`){
        if (/modem/i && /\d+\s(.+):\s*(.+)$/){
            my $name = $1;
            my $description = $2;
            $common->addModems({
  	            'DESCRIPTION'  => $description,
  	            'NAME'          => $name,
  	        });
        }
    }
}

1;
