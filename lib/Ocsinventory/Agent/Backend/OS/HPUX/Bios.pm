package Ocsinventory::Agent::Backend::OS::HPUX::Bios;
use strict;

###
# Version 1.1
# Correction of Bug n 522774
#
# thanks to Marty Riedling for this correction
#
###

sub check { $^O =~ /hpux/ }

sub run { 
    my $params = shift;
    my $common = $params->{common};
  
    my $BiosVersion;
    my $BiosDate;
    my $SystemModel;
    my $SystemSerial;
    
    
    $SystemModel=`model`;
    if (can_run ("machinfo")) {
        foreach (`machinfo`) {
            if (/Firmware\s+revision\s?[:=]\s+(\S+)/) {
                $BiosVersion=$1;
            }
            if (/achine\s+serial\s+number\s?[:=]\s+(\S+)/) {
  	            $SystemSerial=$1;
            }
        }
    } else {
        for (`echo 'sc product cpu;il' | /usr/sbin/cstm | grep "PDC Firmware"`) {
          if (/Revision:\s+(\S+)/){
              $BiosVersion="PDC $1";
          }
        }
       for (`echo 'sc product system;il' | /usr/sbin/cstm | grep "System Serial Number"`) {
           if (/:\s+(\w+)/) {
              $SystemSerial=$1;
           }
       }
    }
  
    $common->setBios ({
        BVERSION => $BiosVersion,
        BDATE => $BiosDate,
        BMANUFACTURER => "HP",
        SMANUFACTURER => "HP",
        SMODEL => $SystemModel,
        SSN => $SystemSerial,
    });
}

1;
