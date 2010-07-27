package Ocsinventory::Agent::Backend::OS::HPUX::Mem;
use strict;

sub check { $^O =~ /hpux/ }

sub run { 
  my $params = shift;
  my $common = $params->{common};

  my $mem;
  my $swap;

  $mem = `grep Physical /var/adm/syslog/syslog.log|tr -s " "|cut -f 7 -d " "` ;
  if ( $mem eq "" ) {
      $mem = `grep Physical /var/adm/syslog/OLDsyslog.log|tr -s " "|cut -f 7 -d " "` ;
  };
  $mem = int ($mem/1024);

  $swap = `swapinfo -mdfq`;


  $common->setHardware({
      MEMORY =>  $mem,
      SWAP =>    $swap,
			 });
}

1;
