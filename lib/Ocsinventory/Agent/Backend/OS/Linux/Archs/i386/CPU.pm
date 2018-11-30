package Ocsinventory::Agent::Backend::OS::Linux::Archs::i386::CPU;

use strict;
use warnings;
use Data::Dumper;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    $common->can_run("lscpu"); 
}

sub run {

    my $params = shift;
    my $common = $params->{common};

    my @cpuinfos=`LANG=C lscpu`;
    my $cpu;
    my $nbcpus;

    foreach my $info (@cpuinfos){
        chomp $info;
        $cpu->{CPUARCH}=$1 if ($info =~ /Architecture:\s*(.*)/i);
        $cpu->{NBCPUS}=$1 if ($info =~ /^CPU\(s\):\s*(\d)/i);
        $cpu->{THREADS}=$1 if ($info =~ /Thread\(s\)\sper\score:\s*(\d)/i);
        $cpu->{CORES}=$1 if ($info =~ /Core\(s\)\sper\ssocket:\s*(\d)/i);
        $cpu->{NBSOCKET}=$1 if ($info =~ /Socket\(s\):\s*(\d)/i);
        $cpu->{TYPE}=$1 if ($info =~ /Model\sname:\s*(.*)/i);
        if ($info =~ /Vendor ID:\s*(Authentic|Genuine|)(.+)/i){
            $cpu->{MANUFACTURER} = $2;
            $cpu->{MANUFACTURER} =~ s/(TMx86|TransmetaCPU)/Transmeta/;
            $cpu->{MANUFACTURER} =~ s/CyrixInstead/Cyrix/;
            $cpu->{MANUFACTURER} =~ s/CentaurHauls/VIA/;
        }

        $cpu->{CURRENT_SPEED} = $1 if ($info =~ /CPU\sMHz:\s*(\d+)(|\.\d+)$/i);
        $cpu->{L2CACHESIZE} = $1 if ($info =~ /L2\scache:\s*(.*)/i);
        if ($cpu->{CPUARCH} && $cpu->{CPUARCH} eq 'x86_64'){
            $cpu->{DATA_WIDTH}='64';
        } else {
            $cpu->{DATA_WIDTH}='32';
        }
        
        if ($cpu->{TYPE}) {
            if ($cpu->{TYPE} =~ /([\d\.]+)MHz$/){
                $cpu->{SPEED}=$1;
            } elsif ($cpu->{TYPE} =~ /([\d\.]+)GHz$/){
                $cpu->{SPEED}=$1*1000;
            }
        }
    }

    my $infos=$common->getDmidecodeInfos();
    foreach my $info (@{$infos->{4}}) {
        next if $info->{Status} && $info->{Status} =~ /Unpopulated|Disabled/i;
        $cpu->{SERIAL}=$info->{'Serial Number'};
        $cpu->{VOLTAGE}=$info->{'Voltage'};
    }

    for (my $i=0;$i<$cpu->{NBCPUS};$i++) {
        $common->addCPU($cpu);
    }

}

1;
