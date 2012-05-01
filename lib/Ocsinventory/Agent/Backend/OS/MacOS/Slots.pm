package Ocsinventory::Agent::Backend::OS::MacOS::Slots;

sub check {
    return(undef) unless -r '/usr/sbin/system_profiler'; # check perms
    return (undef) unless can_load("Mac::SysProfile");
    return 1;
}

sub run {
    my $params = shift;
    my $common = $params->{common};
    my ($name, $description, $status);
    
    # create the profile object and return undef unless we get something back
    my $pro = Mac::SysProfile->new();
    my $data = $pro->gettype('SPPCIDataType');
    return(undef) unless(ref($data) eq 'ARRAY');
    
    foreach my $slot (@$data) {
        
        
        $name = $slot->{'_name'};
        $description = $slot->{'sppci_link-width'}." ".$slot->{'sppci_bus'}." ".$slot->{'sppci_slot_name'};
        
        $common->addSlot({
            NAME      	=> $name,
            DESCRIPTION	=> $description,
        });

        $name = $description = $status = undef;
    }
    
}

1;
