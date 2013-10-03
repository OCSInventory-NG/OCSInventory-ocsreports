package Ocsinventory::Agent::Backend::OS::Linux::Archs::i386::CPU;

use strict;

use Config;

sub check { can_read("/proc/cpuinfo") }

sub run {

    my $params = shift;
    my $common = $params->{common};

    my @cpu;
    my $current;
    my $cpuarch;
    my %cpusocket;
    my $siblings;
    my $coreid;

    open CPUINFO, "</proc/cpuinfo" or warn;
    foreach(<CPUINFO>) {
	    if (/^physical\sid\s*:\s*(\d+)/i) {
		    $cpusocket{$1} = $1;
	    }

        if (/^vendor_id\s*:\s*(Authentic|Genuine|)(.+)/i) {
            $current->{MANUFACTURER} = $2;
            $current->{MANUFACTURER} =~ s/(TMx86|TransmetaCPU)/Transmeta/;
            $current->{MANUFACTURER} =~ s/CyrixInstead/Cyrix/;
            $current->{MANUFACTURER} =~ s/CentaurHauls/VIA/;
        }

        $siblings = $1 if /^siblings\s*:\s*(\d+)/i;
		$current->{CURRENT_SPEED} = $1 if /^cpu\sMHz\s*:\s*(\d+)/i;
        $current->{TYPE} = $1 if /^model\sname\s*:\s*(.+)/i;
	    $current->{L2CACHESIZE} = $1 if /^cache\ssize\s*:\s*(\d+)/i;
	    if (/^flags\s*:\s*(.*)/i) {
                my @liste1=split(/ /,$1);
		        if (grep /^lm$/,@liste1) {
                      $current->{CPUARCH}="x86_64";
					  $current->{DATA_WIDTH}=64;
                } else {
                      $current->{CPUARCH}="x86";
					  $current->{DATA_WIDTH}=32;
               	}
        }

    }
    $current->{NBSOCKET}=scalar keys %cpusocket;

   # /proc/cpuinfo provides real time speed processor.
   # Get optimal speed with dmidecode command
   # Get also cpu cores with dmidecode command
   # Get also voltage information with dmidecode command
   @cpu = `dmidecode -t processor`;
   for (@cpu){
        if (/Current\sSpeed:\s*(.*) (|MHz|GHz)/i){
            $current->{SPEED} = $1;
        }
        if (/Core\sCount:\s*(\d+)/i){
            $current->{CORES} = $1;
        }
        if (/Voltage:\s*(.*)V/i){
            $current->{VOLTAGE} = $1;
        }
		if (/Status:\s*(.*),\s(.*)/i){
            $current->{CPUSTATUS} = $2;
        }
        if (/Upgrade:\s*(.*)/i){
            $current->{SOCKET} = $1;
        }
    }

    # Is(Are) CPU(s) hyperthreaded?
    if ($siblings == $current->{CORES}) {
        # Hyperthreading is off
        $current->{HPT}=0;
    } else {
        # Hyperthreading is on
        $current->{HPT}=1;
    }

    # The last one
    $cpusocket{$current->{NBSOCKET}}=$current;
    #print Dumper($current);

    # Add the values to XML
    $common->addCPU($current);
}

1
