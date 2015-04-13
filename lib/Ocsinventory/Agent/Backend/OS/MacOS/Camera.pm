package Ocsinventory::Agent::Backend::OS::MacOS::Camera;
use strict;


sub check {
    return(undef) unless -r '/usr/sbin/system_profiler'; # check perms
    return 1;
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    # create profiler obj, bail if datatype fails
    my $data = $common->get_sysprofile_devices_names('SPCameraDataType');

    return(undef) unless(ref($data) eq 'ARRAY');

    # add sound cards
    foreach my $cam (@$data){
        $common->addCamera({
            'MODEL'  => $cam,
            'UUID'  => $cam,
        });
    }
}
1;
