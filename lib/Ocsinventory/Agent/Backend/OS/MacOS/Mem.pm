package Ocsinventory::Agent::Backend::OS::MacOS::Mem;
use strict;

sub check {
    return(undef) unless -r '/usr/sbin/system_profiler'; # check perms
    return (undef) unless can_load("Mac::SysProfile");
    return 1;
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    my $PhysicalMemory;

    # create the profile object and return undef unless we get something back
    my $profile = Mac::SysProfile->new();
    my $data = $profile->gettype('SPMemoryDataType');
    return(undef) unless(ref($data) eq 'ARRAY');

    # Workaround for MacOSX 10.5.7
    #if ($h->{'Memory Slots'}) {
    #  $h = $h->{'Memory Slots'};
    #}


    foreach my $memory (@$data){
        next unless $memory->{'_name'} =~ /^BANK|SODIMM|DIMM/;
        # tare out the slot number
        my $slot = $memory->{'_name'};
	# memory in 10.5
        if($slot =~ /^BANK (\d)\/DIMM\d/){
            $slot = $1;
        }
	# 10.4
	if($slot =~ /^SODIMM(\d)\/.*$/){
		$slot = $1;
	}
	# 10.4 PPC
	if($slot =~ /^DIMM(\d)\/.*$/){
		$slot = $1;
	}

        my $size = $memory->{'dimm_size'};

        # if system_profiler lables the size in gigs, we need to trim it down to megs so it's displayed properly
        if($size =~ /GB$/){
                $size =~ s/GB$//;
                $size *= 1024;
        }
        $common->addMemory({
            'CAPACITY'      => $size,
            'SPEED'         => $memory->{'dimm_speed'},
            'TYPE'          => $memory->{'dimm_type'},
            'SERIALNUMBER'  => $memory->{'dimm_serial_number'},
            'DESCRIPTION'   => $memory->{'dimm_part_number'},
            'NUMSLOTS'      => $slot,
            'CAPTION'       => 'Status: '.$memory->{'dimm_status'},
        });
    }
}
1;
