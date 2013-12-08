package Ocsinventory::Agent::Backend::OS::MacOS::CPU;
use strict;

sub check {
    return(undef) unless -r '/usr/sbin/system_profiler';
    return(undef) unless can_load("Mac::SysProfile");
    return 1;
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    # create sysprofile obj. Return undef unless we get a return value
    my $profile = Mac::SysProfile->new();
    my $data = $profile->gettype('SPHardwareDataType');
    return(undef) unless(ref($data) eq 'ARRAY');

    my $h = $data->[0];

    ######### CPU
    my $processort  = $h->{'processor_name'} | $h->{'cpu_type'}; # 10.5 || 10.4
    my $processorn  = $h->{'number_processors'} || $h->{'number_cpus'};
    my $processors  = $h->{'current_processor_speed'} || $h->{'cpu_speed'};

	my $uuid = $h->{'platform_UUID'}; # 10.5, 10.6, 10.7, 10.8
	chomp($uuid);
	$uuid =~ s/\s+$//g;

    # lamp spits out an sql error if there is something other than an int (MHZ) here....
    if($processors =~ /GHz$/){
            $processors =~ s/ GHz//;
            # French Mac returns 2,60 Ghz instead of
            # 2.60 Ghz :D
            $processors =~ s/,/./;
            $processors = ($processors * 1000);
    }
    if($processors =~ /MHz$/){
            $processors =~ s/ MHz//;
    }

    ### mem convert it to meg's if it comes back in gig's
    my $mem = $h->{'physical_memory'};
    if($mem =~ /GB$/){
        $mem =~ s/\sGB$//;
        $mem = ($mem * 1024);
    }
    if($mem =~ /MB$/){
	$mem =~ s/\sMB$//;
    }


    $common->setHardware({
        PROCESSORT  => $processort,
        PROCESSORN  => $processorn,
        PROCESSORS  => $processors,
        MEMORY      => $mem,
		UUID		=> $uuid,
    });
}

1;
