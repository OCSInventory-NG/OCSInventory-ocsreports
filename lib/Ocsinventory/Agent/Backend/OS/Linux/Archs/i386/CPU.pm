package Ocsinventory::Agent::Backend::OS::Linux::Archs::i386::CPU;

use strict;
use warnings;
use Data::Dumper;

use Config;

sub check { can_read("/proc/cpuinfo"); can_run("arch"); }

sub run {

    my $params = shift;
    my $common = $params->{common};
    
    my @cpu;
    my @cache;
    my $current;
    my $cpuarch = `arch`;
    chomp($cpuarch);
    my $datawidth;
    my $index;
    my $cpucount = 0;
    my $l2cacheid;
    my $l2cachesection;
    my @dmiinfo;

    if ($cpuarch eq "x86_64"){
        $datawidth = 64;
    } else {
        $datawidth = 32;
    }
    
    open CPUINFO, "</proc/cpuinfo" or warn;
    for (<CPUINFO>) {
        if (/^vendor_id\s*:\s*(Authentic|Genuine|)(.+)/i) {
            $current->{MANUFACTURER} = $2;
            $current->{MANUFACTURER} =~ s/(TMx86|TransmetaCPU)/Transmeta/;
            $current->{MANUFACTURER} =~ s/CyrixInstead/Cyrix/;
            $current->{MANUFACTURER} =~ s/CentaurHauls/VIA/;
        }
        $current->{CORES} = $1 if /^cpu\scores\s*:\s*(\d+)/i;
        $current->{LOGICAL_CPUS} = $1 if /^siblings\s*:\s*(\d+)/i;
        $current->{SPEED} = $current->{CURRENT_SPEED} = $1 if /^cpu\sMHz\s*:\s*(\d+)/i;
        $current->{TYPE} = $1 if /^model\sname\s*:\s*(.+)/i;
        $current->{HPT} = 'yes' if /^flags\s*:.*\bht\b/i;
        $current->{L2CACHESIZE}=$1 if (/^cache\ssize\s*:\s*(\d+)/i);
        # if "cpu cores" or "siblings" are missing in /proc/cpuinfo (seen on several 1 vCPU configurations)
        if ($current->{CORES} == 0) {
            $current->{CORES} = 1;
        }
        if ($current->{LOGICAL_CPUS} == 0) {
            $current->{LOGICAL_CPUS} = 1;
        }
        $index = $1 if ! defined $index && /^processor\s*:\s*(\d+)/i;
        $index = $1 if /^physical\sid\s*:\s*(\d+)/i;
        if (/^\s*$/) {
            $current->{HPT} = 'no' if $current->{HPT} ne 'yes';
            $current->{CPUARCH} = $cpuarch;
            $current->{DATA_WIDTH} = $datawidth;
            $current->{TYPE} =~ s/\s{2,}/ /g;
        }
        $current->{NBSOCKET}=$index+1;
    }
    @dmiinfo=`dmidecode -t processor`;
    for (@dmiinfo){
        $current->{VOLTAGE}=$1 if (/Voltage:\s*(.*)V/i);
        $current->{SOCKET} = $1 if (/Upgrade:\s*(.*)/i);
        $current->{CPUSTATUS} = $2 if (/Status:\s*(.*),\s(.*)/i);
    }
    $cpu[$index] = $current;
    $current = $index = undef;

    for my $c (@cpu) {
        # sometimes "hardware id" are not contiguous (first hardware id = 0, second hardware id = 2)
        if (defined $c) {
            $common->addCPU($c);
        }
    }
}
    
1;
