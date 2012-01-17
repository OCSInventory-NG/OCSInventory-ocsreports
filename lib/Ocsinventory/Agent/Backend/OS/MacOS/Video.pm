package Ocsinventory::Agent::Backend::OS::MacOS::Video;
use strict;


sub check {
    # make sure the user has access, cause that's the command that's gonna be run
    return(undef) unless -r '/usr/sbin/system_profiler';
    return(undef) unless can_load("Mac::SysProfile");
    return 1;
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    # run the profiler to get our datatype
    my $profile = Mac::SysProfile->new();
    my $data = $profile->gettype('SPDisplaysDataType');

    # unless we get a valid return, bail out
    return(undef) unless(ref($data) eq 'ARRAY');

    # we get video card because system_profiler XML output does not provide a human readable value
    my $video_names = $common->get_sysprofile_devices_names('SPDisplaysDataType');
    return(undef) unless(ref($video_names) eq 'ARRAY');

    my $count = 0;

    # add the video information
    foreach my $video (@$data){
        my $memory = $video->{'spdisplays_vram'};
        $memory =~ s/ MB$//;

        $common->addVideo({
                'NAME'        => $$video_names[$count],
                'CHIPSET'     => $video->{'sppci_model'},
                'MEMORY'    => $memory,
                'RESOLUTION'    => $video->{'spdisplays_ndrvs'}[0]->{'spdisplays_resolution'},
        });

  
        foreach my $display (@{$video->{'spdisplays_ndrvs'}}){
            next unless(ref($display) eq 'HASH');
            next if($display->{'_name'} eq 'spdisplays_display_connector');

            $common->addMonitor({
                'CAPTION'   => $display->{'_name'},
            })
        }

        $count++;
    }

}
1;
