package Ocsinventory::Agent::Backend::OS::MacOS::Bios;
use strict;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    return $common->can_load("Mac::SysProfile") 
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    # use Mac::SysProfile to get the respected datatype
    my $profile = Mac::SysProfile->new();
    my $data = $profile->gettype('SPHardwareDataType');

    # unless we get a real hash value, return with nothing
    return(undef) unless($data && ref($data) eq 'ARRAY');
        
    my $h = $data->[0];

    # set the bios informaiton from the apple system profiler
    $common->setBios({
        SMANUFACTURER   => 'Apple Inc', # duh
        SMODEL          => $h->{'model_identifier'} || $h->{'machine_model'},
        #       SSN             => $h->{'Serial Number'}
        # New method to get the SSN, because of MacOS 10.5.7 update
        # system_profiler gives 'Serial Number (system): XXXXX' where 10.5.6
        # and lower give 'Serial Number: XXXXX'
        SSN             => $h->{'serial_number'},
        BVERSION        => $h->{'boot_rom_version'},
    });
}

1;
