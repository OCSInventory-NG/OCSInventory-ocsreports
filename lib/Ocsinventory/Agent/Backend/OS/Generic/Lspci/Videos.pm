package Ocsinventory::Agent::Backend::OS::Generic::Lspci::Videos;
use strict;

my $memory;
my $resolution;
my $chipset;
my @resolution;
my ($ret,$handle,$i,$count,$clock,$driver_version, $nvml_version, $memtotal, $serial, $bios_version, $uuid, $name);
my $reso;

#sub check {
#    return unless can_run("xrandr");
#    return 1;
#}

sub run {
    my $params = shift;
    my $common = $params->{common};

    if (can_run("xrandr")) {
        if (can_run("nvidia-smi")) {
            if (can_load("nvidia::ml qw(:all)")){
                nvmlInit();
                # Retrieve driver version
                ($ret, $driver_version) = nvmlSystemGetDriverVersion();
                die nvmlErrorString($ret) unless $ret == $nividia::ml::bindings::NVML_SUCCESS;

                # Retrieve NVML version
                ($ret, $nvml_version) = nvmlSystemGetNVMLVersion();
                die nvmlErrorString($ret) unless $ret == $nividia::ml::bindings::NVML_SUCCESS;

                # How many nvidia cards are present?
                ($ret, $count) = nvmlDeviceGetCount();
                die nvmlErrorString($ret) unless $ret == $nividia::ml::bindings::NVML_SUCCESS;

                for ($i=0; $i<$count; $i++) {
                    ($ret, $handle) = nvmlDeviceGetHandleByIndex($i);
                    next if $ret != $nvidia::ml::bindings::NVML_SUCCESS;

                    ($ret, $name) = nvmlDeviceGetName($handle);
                    next if $ret != $nvidia::ml::bindings::NVML_SUCCESS;

                    ($ret, $memtotal) = nvmlDeviceGetMemoryInfo($handle);
                    next if $ret != $nvidia::ml::bindings::NVML_SUCCESS;
                    $memtotal = ($memtotal->{"total"} / 1024 / 1024);

                    ($ret, $serial) = nvmlDeviceGetSerial($handle);
                    next if $ret != $nvidia::ml::bindings::NVML_SUCCESS;

                    ($ret, $bios_version) = nvmlDeviceVBiosVersion($handle);
                    next if $ret != $nvidia::ml::bindings::NVML_SUCCESS;

                    ($ret, $uuid) = nvmlDeviceGetUUID($handle);
                    next if $ret != $nvidia::ml::bindings::NVML_SUCCESS;
                }
                nvmlShutdown();
                my @resol= `xrandr --verbose | grep *current`; 
                foreach my $r (@resol){
                    if ($r =~ /((\d{3,4})x(\d{3,4}))/){
                        push(@resolution,$1);
                    }
                }    
                foreach my $res (@resolution){
                    $reso = $res;
                }
                $common->addVideo({
                    NAME => $name,
                    MEMORY => $memtotal,
                    DRVVERSION => $driver_version,
                    NVMLVERSION => $nvml_version,
                    SPEED => $clock,
                    SERIAL => $serial,
                    VBIOS => $bios_version,
                    UUID => $uuid,
                    RESOLUTION => $reso,
                });
            }
        } else {
            foreach(`lspci`){
                if(/graphics|vga|video/i && /^(\d\d:\d\d.\d)\s([^:]+):\s*(.+?)(?:\(([^()]+)\))?$/i){
                    my $slot = $1;
                    $chipset = $2;
                    $name = $3;
                    if (defined $slot) {
                        my @detail = `lspci -v -s $slot`;
                        foreach my $m (@detail) {
                            if ($m =~ /.*Memory.*\s+\(.*-bit,\sprefetchable\)\s\[size=(\d*)M\]/) {
                                $memory = $1;
                            }
                        }
                    }
                    my @resol= `xrandr --verbose | grep *current`; 
                    foreach my $r (@resol){
                        if ($r =~ /((\d{3,4})x(\d{3,4}))/){
                            $resolution = $1;
                        }
                        $common->addVideo({
                            'CHIPSET'    => $chipset,
                            'NAME'       => $name,
                            'MEMORY'     => $memory,
                            'RESOLUTION' => $resolution,
                        });
                    }    
                }
            }
        }
    }
    else {
        foreach(`lspci`){
            if(/graphics|vga|video/i && /^\S+\s([^:]+):\s*(.+?)(?:\(([^()]+)\))?$/i){
                $common->addVideo({
                'CHIPSET'  => $1,
                'NAME'     => $2,
                });
            }
        }
    }
}

1;
