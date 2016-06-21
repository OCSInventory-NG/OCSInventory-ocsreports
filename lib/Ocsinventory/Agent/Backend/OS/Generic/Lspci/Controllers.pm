package Ocsinventory::Agent::Backend::OS::Generic::Lspci::Controllers;
use strict;

sub check {can_run("lspci")}

sub run {
    my $params = shift;
    my $common = $params->{common};
  
    my $driver;
    my $name;
    my $manufacturer;
    my $pciid;
    my $pcislot;
    my $type;
    my $lspci_version;
    my $command = "lspci -vvv";
   
    #We get the current lspci version 
    `lspci --version` =~ m/lspci\sversion\s(\d+.*)/ ; 
    $lspci_version=$1;
    $lspci_version = $common->convertVersion($lspci_version,3);
  
    if ($lspci_version >= 224) {    #More informations since version 2.2.4 
        $command = "lspci -vvv -nn";
    }
  
    foreach(`$command`){
        if (/^(\S+)\s+(\w+.*?):\s(.*)/) {
            $pcislot = $1;
            $name = $2;
            $manufacturer = $3;
            if ($manufacturer =~ s/ \((rev \S+)\)//) {
                $type = $1;
            }
            $manufacturer =~ s/\ *$//; # clean up the end of the string
            $manufacturer =~ s/\s+\(prog-if \d+ \[.*?\]\)$//; # clean up the end of the string
            if ($manufacturer =~ s/ \[([A-z\d]+:[A-z\d]+)\]$//) {
                $pciid = $1;
            }
        }
  
        if ($pcislot && /^\s+Kernel driver in use: (\w+)/) {
            $driver = $1;
        }
  
        if ($pcislot && /^$/) {
            $common->addController({
                'DRIVER'        => $driver,
                'NAME'          => $name,
                'MANUFACTURER'  => $manufacturer,
                'PCIID'       => $pciid,
                'PCISLOT'       => $pcislot,
                'TYPE'          => $type,
            });
  
            $driver = $name = $pciid = $pcislot = $manufacturer = $type = undef;
        }
    }
}

1
