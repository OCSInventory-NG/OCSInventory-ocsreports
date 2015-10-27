package Ocsinventory::Agent::Backend::OS::Generic::Dmidecode::Memory;
use strict;

sub run {
    my $params = shift;
    my $common = $params->{common};

    my $dmidecode = `dmidecode -t memory`; # TODO retrieve error
    # some versions of dmidecode do not separate items with new lines
    # so add a new line before each handle
    $dmidecode =~ s/\nHandle/\n\nHandle/g;
    my @dmidecode = split (/\n/, $dmidecode);
    # add a new line at the end
    push @dmidecode, "\n";


    s/^\s+// for (@dmidecode);

    my $flag;

    my $capacity;
    my $speed;
    my $type;
    my $description;
    my $numslot;
    my $caption;
    my $serialnumber;
    my $manufacturer;

    foreach (@dmidecode) {
        if (/dmi type 17,/i) { # beginning of Memory Device section
            $flag = 1;
            $numslot++;
        } elsif ($flag && /^$/) { # end of section
            $flag = 0;
            if ($capacity ne "No") {
                $common->addMemory({
                    CAPACITY => $capacity,
                    CAPTION => $caption,
                    DESCRIPTION => $description,
                    MANUFACTURER => $manufacturer,
                    NUMSLOTS => $numslot,
                    SERIALNUMBER => $serialnumber,
                    SPEED => $speed,
                    TYPE => $type,
                });
            }
            $capacity = $description = $caption = $type = $type = $serialnumber = $manufacturer = undef;
        } elsif ($flag) { # in the section
            if (/^size:\s*(\d+)\sMB/i) {
                $capacity = $1;
            } elsif (/^size:\s*(\d+)\sGB/i) {
                $capacity = $1*1024;
            } elsif (/^size:\s*(\d+)\sTB/i) {
                $capacity = $1*1024*1024;
            }
            $description = $1 if /^Form Factor:\s*(.+)/i;
            $caption = $1 if /^Locator:\s*(.+)/i;
            $speed = $1 if /^speed:\s*(.+)/i;
            $type = $1 if /^type:\s*(.+)/i;
            $serialnumber = $1 if /^Serial Number:\s*(.+)/i;
            $manufacturer = $1 if /^Manufacturer:\s*(.+)/i;
        }


        if (/dmi type 6,/i) {
            $flag=1;
            $numslot++;
        } elsif ($flag && /^$/) {
            $flag=0;
            if ($capacity ne "No") {
                $common->addMemory({
                    CAPACITY => $capacity,
                    DESCRIPTION => $description,
                    SPEED => $speed,
                    TYPE => $type,
                    NUMSLOTS => $numslot,
                });
            }
            $capacity = $description = $caption = $type = $type = $serialnumber = undef;
        } elsif ($flag) { # in the section
            $capacity = $1 if /^installed\ssize\s*:\s*(\d+)\s*(MB|Mbyte)/i;
            $description = $1 if /^Socket\sDesignation\s*:\s*(.+)/i;
            $speed = $1 if /^current\sspeed\s*:\s*(.+)/i;
            $type = $1 if /^type\s*:\s*(.+)/i;
        } 
    }
}

1;
