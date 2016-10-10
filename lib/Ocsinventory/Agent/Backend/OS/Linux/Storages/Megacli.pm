package Ocsinventory::Agent::Backend::OS::Linux::Storages::Megacli;

use Ocsinventory::Agent::Backend::OS::Linux::Storages;
 
use strict;

sub check { 

    my $ret;
    my $cont;

    if (can_run("megacli")) {
       foreach (`megacli -adpCount 2>/dev/null`) {
           if (/^Controller\sCount:\s(\d+)/) {
               $cont=$1;
               if (defined $cont && $cont == 1) {
                   $ret=1;
                   last;
               }
           }
       }
   }
   return $ret;
}

sub run {
   
    my $params = shift;
    my $common = $params->{common};
    my $logger = $params->{logger};

    my $adpc;
    my $model;
    my $description;
    my $capacity;
    my $firmware;
    my $serial;
    my $manufacturer;
    my $index;
    my @partitions;
    my @sl;

    # Retrieve the partition
    open PARTINFO, '</proc/partitions' or warn;
    foreach(<PARTINFO>){
        if (/^\s*(\d*)\s*(\d*)\s*(\d*)\s*([sh]d[a-z]+)$/i){
            push(@partitions,$4);
        }
    }

    # How adapters are present?
    foreach (`megacli -adpCount 2> /dev/null`){
        $adpc=$1 if (/^Controller Count:\s(\d+)./i);
    }

    # How slot are used on the controller?
    for (my $count=0;$count<$adpc;$count++){
        foreach (`megacli -ShowSummary -a$count`){
            # Slot number : Connector          : 0<Internal>: Slot 1
            if (/Connector\s*:\s*\d+(?:<Internal>): Slot (\d+)/){
                push (@sl, $1);
            }
        }
        # use smartctcl command to retrieve information
        foreach my $dev (@partitions){
            foreach my $slo (@sl){
                # smartctl -i -d megaraid,0 /dev/sda
                my $result=`smartctl -i -d megaraid,$slo /dev/$dev`;
                $model=$1 if ($result =~ /Model Family:\s*(.*)/);
                $description=$1 if ($result =~ /Device Model:\s*(.*)/);
                $manufacturer = Ocsinventory::Agent::Backend::OS::Linux::Storages::getManufacturer($description);
                $serial=$1 if ($result =~ /Serial Number:\s*(.*)/);
                $firmware=$1 if ($result =~ /Firmware Version:\s*(.*)/);
                $capacity=$1 if ($result =~ /User Capacity:\s*.*\[(.*)\]/);
                $common->addStorages({
                    NAME => $description,
                    MANUFACTURER => $manufacturer,
                    MODEL => $model,
                    DESCRIPTION => $description,
                    TYPE => "Disk",
                    DISKSIZE => $capacity,
                    SERIALNUMBER => $serial,
                    FIRMWARE => $firmware
                });
            }
        }
    }
} 

1;
