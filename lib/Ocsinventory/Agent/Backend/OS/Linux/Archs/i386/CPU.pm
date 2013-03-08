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

    open CPUINFO, "</proc/cpuinfo" or warn;
    foreach(<CPUINFO>) {
        if (/^processor\s*:/) {
            if ($current) {
                $common->addCPU($current);
            }

            $current = {
                MANUFACTURER => 'unknown'
            };

        }

        if (/^cpu\s*:/) {
            if ($current) {
                $common->addCPU($current);
            }

            $current = {
                CORES => '0'
            };

        }

#            $current->{SERIAL} = $1 TODO with dmidecode;
        if (/^vendor_id\s*:\s*(Authentic|Genuine|)(.+)/i) {
            $current->{MANUFACTURER} = $2;
            $current->{MANUFACTURER} =~ s/(TMx86|TransmetaCPU)/Transmeta/;
            $current->{MANUFACTURER} =~ s/CyrixInstead/Cyrix/;
            $current->{MANUFACTURER} =~ s/CentaurHauls/VIA/;
        }
        $current->{SPEED} = $1 if /^cpu\sMHz\s*:\s*(\d+)(|\.\d+)$/i;
        $current->{TYPE} = $1 if /^model\sname\s*:\s*(.+)/i;
	$current->{CORES} = $1 if /^cpu\scores\s*:\s*(\d+)/i;
	$current->{L2CACHESIZE} = $1 if /^cache\ssize\s*:\s*(\d+)/i;
	if (/^flags\s*:\s*(.*)/i) {
                my @liste1=split(/ /,$1);
		if (grep /^lm$/,@liste1) {
                      $current->{CPUARCH}=64;
                } else {
                      $current->{CPUARCH}=32;
               	}
        }
	if (/^physical\sid\s*:\s*(\d+)/i) {
		$cpusocket{$1} = $1;
		$current->{CPUSOCKETS} = scalar keys %cpusocket;
	}
    }

    # The last one
    $common->addCPU($current);
}

1
