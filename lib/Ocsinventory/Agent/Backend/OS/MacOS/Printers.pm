package Ocsinventory::Agent::Backend::OS::MacOS::Printers;
use strict;

sub check {
    return(undef) unless -r '/usr/sbin/system_profiler';
    return(undef) unless can_load("Mac::SysProfile");
    return 1;
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    my $profile = Mac::SysProfile->new();
    my $data = $profile->gettype('SPPrintersDataType');
    return(undef) unless(ref($data) eq 'ARRAY');

    foreach my $printer (@$data){
        next if($printer->{'_name'} =~ /^The\sprinters\slist\sis\sempty\.(.*)$/);
        $common->addPrinter({
                NAME    => $printer->{'_name'},
                DRIVER  => $printer->{'ppd'},
		PORT	=> $printer->{'uri'},
        });
    }

}
1;
