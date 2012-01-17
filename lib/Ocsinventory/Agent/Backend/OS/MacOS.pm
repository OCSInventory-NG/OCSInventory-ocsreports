package Ocsinventory::Agent::Backend::OS::MacOS;

use strict;

require Exporter;
our @ISA = qw /Exporter/;
our @EXPORT = qw /get_sysprofile_devices_names/;


sub check {
	my $r;
	# we check far darwin because that's the _real_ underlying OS
	$r = 1 if (uc($^O) =~ /^DARWIN$/);
	return($r);
}

sub run {
        my $params = shift;
        my $common = $params->{common};

        my $OSName;
        my $OSComment;
        my $OSVersion;
		
		# if we can load the system profiler, gather the information from that
		if(can_load("Mac::SysProfile")){
			my $profile = Mac::SysProfile->new();
			my $data = $profile->gettype('SPSoftwareDataType');
			return(undef) unless(ref($data) eq 'ARRAY');
			
			my $h = $data->[0];
			
			my $SystemVersion = $h->{'os_version'};
			if ($SystemVersion =~ /^(.*?)\s+(\d+.*)/) {
			    $OSName=$1;
			    $OSVersion=$2;
			} else {
			    # Default values
			    $OSName="Mac OS X";
			    $OSVersion="Unknown";
			}

		} else {
			# we can't load the system profiler, use the basic BSD stype information
			# Operating system informations
			chomp($OSName=`uname -s`);
			chomp($OSVersion=`uname -r`);			
		}
		
		# add the uname -v as the comment, not really needed, but extra info never hurt
		chomp($OSComment=`uname -v`);
        $common->setHardware({
                OSNAME		=> $OSName,
                OSCOMMENTS	=> $OSComment,
                OSVERSION	=> $OSVersion,
        });
}

1;
