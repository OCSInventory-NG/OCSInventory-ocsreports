package Ocsinventory::Agent::Backend::OS::MacOS::UUID;

use strict;
use warnings;
 
sub check {
    my $params = shift;
    my $common = $params->{common};
    return(undef) unless -r '/usr/sbin/system_profiler';
    return 1;
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    my @sphardware=`system_profiler SPHardwareDataType`;
    my $uuid;

    foreach my $line (@sphardware){
        chomp $line;
        $uuid = $1 if ($line =~ /Hardware UUID:\s(.*)/i);
    }

    $common->setHardware({
        UUID => $uuid,
    });
}

1;
