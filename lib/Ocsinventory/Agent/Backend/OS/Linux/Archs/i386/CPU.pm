package Ocsinventory::Agent::Backend::OS::Linux::Archs::i386::CPU;

use strict;

use Config;

sub check { can_read("/proc/cpuinfo"); can_run("arch"); }

sub run {

    my $params = shift;
    my $common = $params->{common};

    my @cpu;
    my $current;
    my $cpuarch = `arch`;
    chomp($cpuarch);
    my $cpusocket;
    my $siblings;
    my $cpucores;
    my $coreid;

    open CPUINFO, "</proc/cpuinfo" or warn;
    foreach(<CPUINFO>) {

        if (/^vendor_id\s*:\s*(Authentic|Genuine|)(.+)/i) {
            $current->{MANUFACTURER} = $2;
            $current->{MANUFACTURER} =~ s/(TMx86|TransmetaCPU)/Transmeta/;
            $current->{MANUFACTURER} =~ s/CyrixInstead/Cyrix/;
            $current->{MANUFACTURER} =~ s/CentaurHauls/VIA/;
        }

        if (/^siblings\s*:\s*(\d+)/i){
			$siblings++;
		}
		$current->{CURRENT_SPEED} = $1 if /^cpu\sMHz\s*:\s*(\d+)/i;
        $current->{TYPE} = $1 if /^model\sname\s*:\s*(.+)/i;
	    $current->{L2CACHESIZE} = $1 if /^cache\ssize\s*:\s*(\d+)/i;
    }

	# /proc/cpuinfo provides real time speed processor.
	# Get optimal speed with dmidecode command
  	# Get also cpu cores with dmidecode command
  	# Get also voltage information with dmidecode command
   	@cpu = `dmidecode -t processor`;
   	for (@cpu){
		if (/Processor\sInformation/i){
			$cpusocket++;
			$common->addCPU($current) if ($current->{SPEED});
		}	
    	if (/Current\sSpeed:\s*(.*) (|MHz|GHz)/i){
            $current->{SPEED} = $1;
		}
		else {
            $current->{SPEED} = $1;
		}
        if (/Core\sCount:\s*(\d+)/i){
            $current->{CORES} = $1;
        }
		else {
			$current->{CORES} = 'Unknown';
		}
        if (/Voltage:\s*(.*)V/i){
            $current->{VOLTAGE} = $1;
        }
		if (/Status:\s*(.*),\s(.*)/i){
            $current->{CPUSTATUS} = $2;
        }
		else {
			$current->{CPUSTATUS} = $1;
		}
        if (/Upgrade:\s*(.*)/i){
            $current->{SOCKET} = $1;
        }
    	# Is(Are) CPU(s) hyperthreaded?
    	if ($siblings = $current->{CORES}) {
        	# Hyperthreading is off
        	$current->{HPT}=0;
    	} else {
        	# Hyperthreading is on
        	$current->{HPT}=1;
    	}

    	$current->{CPUARCH}=$cpuarch;

		if ($cpuarch eq "x86_64"){
			$current->{DATA_WIDTH}=64;
    	} else {
			$current->{DATA_WIDTH}=32;
    	}
    }

    # Add the values to XML
    $common->addCPU($current);
}

1
