package Ocsinventory::Agent::Backend::OS::BSD::CPU;
use strict;

sub check {
  return unless -r "/dev/mem";

  `which dmidecode 2>&1`;
  return if ($? >> 8)!=0;
  `dmidecode 2>&1`;
  return if ($? >> 8)!=0;
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

# XXX Parsing dmidecode output using "type 4" section
# for nproc type and speed
# because no /proc on *BSD

#TODO: enhance this part to get speed everytime and support for multi CPUs
  my $flag=0;
  my $status=0; ### XXX 0 if Unpopulated
  for(`dmidecode`){
    $status = 1 if $flag && /^\s*status\s*:.*enabled/i;
    $processors = $1 if $flag && /^\s*current speed\s*:\s*(\d+).+/i;
  }
  
  $processorn = `sysctl -n hw.ncpu`;
  $processort = `sysctl -n hw.model`;

  $common->addCPU({
      TYPE => $processort,
      SPEED => $processors
    });

}
1;
