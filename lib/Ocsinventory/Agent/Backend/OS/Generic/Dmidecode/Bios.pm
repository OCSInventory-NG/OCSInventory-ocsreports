package Ocsinventory::Agent::Backend::OS::Generic::Dmidecode::Bios;
use strict;

sub run {
    my $params = shift;
    my $common = $params->{common};
  
    # Parsing dmidecode output
    # Using "type 0" section
    my( $SystemSerial , $SystemModel, $SystemManufacturer, $BiosManufacturer,
      $BiosVersion, $BiosDate, $AssetTag, $MotherboardManufacturer, $MotherboardModel, $MotherboardSerial, $Type );
  
    #System DMI
    $SystemManufacturer = `dmidecode -s system-manufacturer`;
    $SystemModel = `dmidecode -s system-product-name`;
    $SystemSerial = `dmidecode -s system-serial-number`;
    $AssetTag = `dmidecode -s chassis-asset-tag`;
    $Type = `dmidecode -s chassis-type`;
    
    chomp($SystemModel);
    $SystemModel =~ s/^(#.*\n)+//g;
    $SystemModel =~ s/Invalid.*$//g;
    chomp($SystemManufacturer);
    $SystemManufacturer =~ s/^(#.*\n)+//g;
    $SystemManufacturer =~ s/Invalid.*$//g;
    chomp($SystemSerial);
    $SystemSerial =~ s/^(#.*\n)+//g;
    $SystemSerial =~ s/Invalid.*$//g;
    chomp($AssetTag);
    $AssetTag =~ s/^(#.*\n)+//g;
    $AssetTag =~ s/Invalid.*$//g;
    chomp($Type);
    $Type =~ s/^(#.*\n)+//g;
    $Type =~ s/Invalid.*$//g;
    
    #Motherboard DMI
    $MotherboardManufacturer = `dmidecode -s baseboard-manufacturer`;
    $MotherboardModel = `dmidecode -s baseboard-product-name`;
    $MotherboardSerial = `dmidecode -s baseboard-serial-number`;
    
    chomp($MotherboardModel);
    $MotherboardModel =~ s/^(#.*\n)+//g;
    $MotherboardModel =~ s/Invalid.*$//g;
    chomp($MotherboardManufacturer);
    $MotherboardManufacturer =~ s/^(#.*\n)+//g;
    $MotherboardManufacturer =~ s/Invalid.*$//g;
    chomp($MotherboardSerial);
    $MotherboardSerial =~ s/^(#.*\n)+//g;
    $MotherboardSerial =~ s/Invalid.*$//g;
    
    #BIOS DMI
    $BiosManufacturer = `dmidecode -s bios-vendor`;
    $BiosVersion = `dmidecode -s bios-version`;
    $BiosDate = `dmidecode -s bios-release-date`;
    
    chomp($BiosManufacturer);
    $BiosManufacturer =~ s/^(#.*\n)+//g;
    $BiosManufacturer =~ s/Invalid.*$//g;
    chomp($BiosVersion);
    $BiosVersion =~ s/^(#.*\n)+//g;
    $BiosVersion =~ s/Invalid.*$//g;
    chomp($BiosDate);
    $BiosDate =~ s/^(#.*\n)+//g;
    $BiosDate =~ s/Invalid.*$//g;
  
  # Some bioses don't provide a serial number so I check for CPU ID (e.g: server from dedibox.fr)
    my @cpu;
    if (!$SystemSerial || $SystemSerial =~ /^0+$/) {
        @cpu = `dmidecode -t processor`;
        for (@cpu){
            if (/ID:\s*(.*)/i){
                $SystemSerial = $1;
            }
        }
    }
  
    # Writing data
    $common->setBios ({
        ASSETTAG => $AssetTag,
        SMANUFACTURER => $SystemManufacturer,
        SMODEL => $SystemModel,
        SSN => $SystemSerial,
        BMANUFACTURER => $BiosManufacturer,
        BVERSION => $BiosVersion,
        BDATE => $BiosDate,
        MMANUFACTURER => $MotherboardManufacturer,
        MMODEL => $MotherboardModel,
        MSN => $MotherboardSerial,
        TYPE => $Type,
    });
}

1;
