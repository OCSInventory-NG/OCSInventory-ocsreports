package Ocsinventory::Agent::Backend::OS::MacOS::Video;
use strict;

use Parse::EDID qw(parse_edid);

sub check {
    my $params = shift;
    my $common = $params->{common};
    # make sure the user has access, cause that's the command that's gonna be run
    return(undef) unless -r '/usr/sbin/ioreg';
    return(undef) unless -r '/usr/sbin/system_profiler';
    return(undef) unless $common->can_load("Mac::SysProfile");
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

   #Getting monitor serial number
   #TODO: get serial for multiples monitors
   my $ioreg_binary = `ioreg -lw0 | grep "EDID" | sed "/[^<]*</s///" | xxd -p -r`;
   my $ioreg = parse_edid($ioreg_binary);

   my $ioreg_serial = $ioreg->{'monitor_name'};
   my $ioreg_name = $ioreg->{'serial_number2'};

    # add the video information
    foreach my $video (@$data){
        my $memory = $video->{'spdisplays_vram'};
        $memory =~ s/ MB$//;

        $common->addVideo({
                'NAME'        => $$video_names[$count],
                'CHIPSET'     => $video->{'sppci_model'},
                'MEMORY'      => $memory,
                'RESOLUTION'  => $video->{'spdisplays_ndrvs'}[0]->{'spdisplays_resolution'},
        });


        foreach my $display (@{$video->{'spdisplays_ndrvs'}}){
            my $serial;
            next unless(ref($display) eq 'HASH');
            next if($display->{'_name'} eq 'spdisplays_display_connector');
            
            $serial = $ioreg_serial if ($ioreg_name eq $display->{'_name'});

            $common->addMonitor({
                'CAPTION'   => $display->{'_name'},
                'SERIAL'    => $serial,
            })
        }

        $count++;
    }

}
1;
