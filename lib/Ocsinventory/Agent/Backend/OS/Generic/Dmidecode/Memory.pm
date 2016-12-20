package Ocsinventory::Agent::Backend::OS::Generic::Dmidecode::Memory;
use strict;

sub run {
    my $params = shift;
    my $common = $params->{common};
    my $dmidecode;
    my @dmidecode;
    my %dmidecode;
    my $capacity;
    my $speed;
    my $type;
    my $numslot;
    my $serialnumber;
    my $manufacturer;
    my $caption;
    my $description;
    
    # DMI type 17
    $dmidecode = `dmidecode -t 17`;
    @dmidecode = split (/Handle\s/i, $dmidecode);
    shift @dmidecode;
    $numslot = 0;
    foreach (@dmidecode) {
        $capacity = $speed = $type = $serialnumber = $manufacturer = $caption = $description = 0;
        $caption = $1 if /\s\sLocator:\s([\w\d_\-\s#]+)\n/i;
        $speed = $1 if /Speed:\s([\w\d]+)/i;
        $type = $1 if /Type:\s([\s\w]+)\n/i;
        $description = $1 if /Type\sDetail:\s([\s\w]+)\n/i;
        $manufacturer = $1 if /Manufacturer:\s([\w\d\-\_\s]+)\n/i;
        $serialnumber = $1 if /Serial\sNumber:\s([\w\d\-\_\s]+)\n/i;
        if (/Size:\s(\d+)\s(MB|GB|TB|MByte|GByte|TByte)/i) {
            if($2 eq "MB" or $2 eq "MByte") {
                $capacity = $1;
            }
            elsif($2 eq "GB" or $2 eq "GByte") {
                $capacity = $1*1024;
            }
            elsif($2 eq "TB" or $2 eq "TByte") {
                $capacity = $1*1024*1024;
            }
        }
        
        if (/DMI type 17/i) {
            $dmidecode{$numslot}{caption} = $caption ? $caption : "";
            $dmidecode{$numslot}{description} = $description ? $description : "";
            $dmidecode{$numslot}{speed} = $speed ? $speed : "";
            $dmidecode{$numslot}{type} = $type ? $type : "";
            $dmidecode{$numslot}{manufacturer} = $manufacturer ? $manufacturer : "";
            $dmidecode{$numslot}{serialnumber} = $serialnumber ? $serialnumber : "";
            $dmidecode{$numslot}{capacity} = $capacity ? $capacity : "";
            $numslot++;
        }
    }
    # DMI type 6 if type 17 is not available
    if (!$numslot) {
        $dmidecode = `dmidecode -t 6`; # TODO retrieve error
        @dmidecode = split (/Handle\s/i, $dmidecode);
        shift @dmidecode;
        $numslot = 0;
        foreach (@dmidecode) {
            $capacity = $speed = $type = $caption = 0;
            $caption = $1 if /Socket Designation:\s([\w\d_\-\s#]+)\n/i;
            $capacity = $1 if /Installed\sSize:\s(\d+)/i;
            $speed = $1 if /Speed:\s([\w\d]+)/i;
            $type = $1 if /Type:\s([\s\w]+)\n/i;
            if (/Size:\s(\d+)\s(MB|GB|TB|MByte|GByte|TByte)/i) {
                if($2 eq "MB" or $2 eq "MByte") {
                    $capacity = $1;
                }
                elsif($2 eq "GB" or $2 eq "GByte") {
                    $capacity = $1*1024;
                }
                elsif($2 eq "TB" or $2 eq "TByte") {
                    $capacity = $1*1024*1024;
                }
            }
            
            if (/DMI type 6/i) {
                $dmidecode{$numslot}{caption} = $caption ? $caption : "";
                $dmidecode{$numslot}{description} = $description ? $description : "";
                $dmidecode{$numslot}{speed} = $speed ? $speed : "";
                $dmidecode{$numslot}{type} = $type ? $type : "";
                $dmidecode{$numslot}{capacity} = $capacity ? $capacity : "";
                $numslot++;
            }
        }
    }

    foreach (sort {$a <=> $b} keys %dmidecode) {
        $common->addMemory({
            CAPACITY => $dmidecode{$_}{capacity},
            SPEED => $dmidecode{$_}{speed},
            TYPE => $dmidecode{$_}{type},
            MANUFACTURER => $dmidecode{$_}{manufacturer},
            SERIALNUMBER => $dmidecode{$_}{serialnumber},
            NUMSLOTS => $_,
            CAPTION => $dmidecode{$_}{caption},
            DESCRIPTION => $dmidecode{$_}{description},
        });
    }
}
1;
