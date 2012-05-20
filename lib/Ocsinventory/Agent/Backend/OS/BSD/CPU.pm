package Ocsinventory::Agent::Backend::OS::BSD::CPU;
use strict;

sub check {
  return unless -r "/dev/mem";

  1;
}

sub run {
  my $params = shift;
  my $common = $params->{common};

  my $processort;
  my $processorn;
  my $processors;
  
  my $family;
  my $manufacturer;
  my $serial;

  $processorn = `sysctl -n hw.ncpu`;
  $processort = `sysctl -n hw.model`;
  $processors = `sysctl -n hw.cpuspeed`;
  $family = `sysctl -n hw.machine`;
  $serial = `sysctl -n hw.serialno`;

  if ( chomp($processort) =~ /Intel/i ) {
	$manufacturer = "Intel";
  } 
  if ( chomp($processort) =~ /Advanced Micro|AMD/ ) {
	$manufacturer = "AMD";
  }

  $common->addCPU({
      FAMILY => $family if $family,
      MANUFACTURER => $manufacturer if $manufacturer,
      NUMBER => $processorn if $processorn,
      TYPE => $processort if $processort,
      SPEED => $processors if $processors,
      SERIAL => $serial if $serial
    });

}
1;
