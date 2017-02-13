package Ocsinventory::Agent::Backend::OS::Linux::Archs::i386::CPU;

use strict;
use Config;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    $common->can_read("/proc/cpuinfo") 
}

sub run {

    open(my $fh, '<:encoding(UTF-8)', "/proc/cpuinfo") or warn;
    my @cpuinfo         = <$fh>;
    close($fh);
    
    my $params      = shift;
    my $common      = $params->{common};
    my @dmidecode       = `dmidecode -t processor`;
    my $fake_physid = 1;
    my $processors;
    my $processor;
    my $vendor_id;
    my $modelName;
    my $cacheSize;
    my $mhz;
    my $physical_id;
    my $siblings;
    my $core_id;
    my $cpuCores;
    my $arch;
    my $addressWidth;
    my $dataWidth;
    my $voltage;
    my $serial;
    my $maxMhz;
    my $socket;
    my $sockettype;
    my $status;
    $processor = $vendor_id = $modelName = $cacheSize = $mhz = $physical_id = $siblings
    = $core_id = $cpuCores = $arch = $dataWidth = $addressWidth = $voltage = $serial
    = $maxMhz = $socket = $status = $sockettype = "";

    # get data from /proc/cpuinfo
    foreach my $line (@cpuinfo) {
        chomp $line;
        if ($line =~ /^\s*$/) { # empty line, dump what we know
            $physical_id = $fake_physid if ( $physical_id eq "");
            $processors->{$physical_id}->{MANUFACTURER}             = $vendor_id;
            $processors->{$physical_id}->{TYPE}                     = $modelName;
            $processors->{$physical_id}->{CURRENT_SPEED}            = $mhz;
            $processors->{$physical_id}->{L2CACHESIZE}              = $cacheSize;
            $processors->{$physical_id}->{CORES}                    = $cpuCores ? $cpuCores : 1;
            $processors->{$physical_id}->{LOGICAL_CPUS}             = $siblings ? $siblings : $processors->{$physical_id}->{CORES};
            $processors->{$physical_id}->{CPUARCH}                  = $arch;
            $processors->{$physical_id}->{DATA_WIDTH}               = $dataWidth;
            $processors->{$physical_id}->{CURRENT_ADDRESS_WIDTH}    = $addressWidth;
                      
            $processor = $vendor_id = $modelName = $cacheSize = $mhz
            = $physical_id = $siblings = $core_id = $cpuCores = $arch
            = $dataWidth = $addressWidth = $voltage = $sockettype = "";
        }
        $processor = $1 if($line =~ /processor\s*:\s*(\S.*)/i);
        $vendor_id = $2 if($line =~ /^vendor_id\s*:\s*(Authentic|Genuine|)(.+)/i);
        $vendor_id =~ s/(TMx86|TransmetaCPU)/Transmeta/;
        $vendor_id =~ s/CyrixInstead/Cyrix/;
        $vendor_id =~ s/CentaurHauls/VIA/;
        $modelName = $1 if($line =~ /model\sname\s*:\s*(\S.*)/i);
        if($line =~ /cpu\sMHz\s*:\s*(\S.*)/i) {
            $mhz = $1;
            $mhz = sprintf "%i", $mhz;
        }
        if($line =~ /cache\ssize\s*:\s*(\S.*)/i) {
            $cacheSize = $1;
            $cacheSize =~ s/\D+//;
        }
        $physical_id = $1 if($line =~ /physical\sid\s*:\s*(\S.*)/i);
        $siblings = $1 if($line =~ /siblings\s*:\s*(\S.*)/i);
        $core_id = $1 if($line =~ /core\sid\s*:\s*(\S.*)/i);
        $cpuCores = $1 if($line =~ /cpu\scores\s*:\s*(\S.*)/i);
        if($line =~ /address\ssizes\s*:\s*(\S.*)/i) {
            $addressWidth = $1;
            $addressWidth =~ /(\d+)\s+bits\s*physical,\s*(\d+)\s*bits\s*virtual/;
            $addressWidth = $2 ? $2 : ( $1 ? $1 : '' );
        }
        if($line =~ /flags/) {
            if($line =~ /lm/) {
                $arch       = "x86_64";
                $dataWidth  = 64;
            }
            else {
                $arch       = "x86";
                $dataWidth  = 32;
            }       
        }
    }
    $socket = -1;
    foreach my $line (@dmidecode) {
        chomp $line;
        $socket++ if($line =~ /^Handle/);   # handle opens a new processor in dmidecode output
        next if $socket < 0;    # if in preface still
        if($line =~ /^\s*$/ ) { # end of processor/socket found
            if ( $status ne "Unpopulated") {
                if ($socket >= 0) {
                    $processors->{$socket}->{VOLTAGE}          = $voltage;
                    $processors->{$socket}->{SPEED}            = $maxMhz;
                    $processors->{$socket}->{SERIALNUMBER}     = $serial;
                    $processors->{$socket}->{SOCKET}           = $sockettype;
                }   # dmidecode tells about more CPUs than /proc/cpuinfo
            }
            $voltage = $maxMhz = $status = $serial = $sockettype = "";
        }

        $voltage = $1 if ($line =~ /Voltage:\s*(\S.*)/i);
        $maxMhz = $1 if($line =~ /Current\sSpeed:\s*(\d+)/i);
        $status = $1 if($line =~ /Status:\s*(\S.*)/i);
        $serial = $1 if($line =~ /serial\sNumber:\s*(\S.*)/i);
        if($line =~ /Upgrade:\s*(\S.*)/i) {
                        $sockettype = $1 unless ( $1 =~ /Unknown|Other/i );
        }
    }
    foreach (keys %{$processors} ) {
        $common->addCPU($processors->{$_});
    }
}

1
