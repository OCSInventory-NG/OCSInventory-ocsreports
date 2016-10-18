package Ocsinventory::Agent::Backend::OS::MacOS::Controllers;


sub check {
    my $params = shift;
    my $common = $params->{common};
    return(undef) unless -r '/usr/sbin/system_profiler'; # check perms
    return (undef) unless $common->can_load("Mac::SysProfile");
    return 1;
}

sub run {
    my $params = shift;
    my $common = $params->{common};
    my ($caption, $description, $name, $type);
    
    my $datatypes = {
        #usb => 'SPUSBDataType',  TODO: fix problems with SPUSBDataType in Mac::Sysprofile
        firewire => 'SPFireWireDataType',
        thunderbolt => 'SPThunderboltDataType',
        ethernet => 'SPEthernetDataType',
    };

    for my $datatype (keys %$datatypes) {
        # create the profile object and return undef unless we get something back
        my $pro = Mac::SysProfile->new();
        my $data = $pro->gettype($datatypes->{$datatype});
        return(undef) unless(ref($data) eq 'ARRAY');
        
        foreach my $port (@$data) {
            if ($datatype =~ /usb/) {
                $name = $port->{'usb_bus_number'};
                $type = 'USB Bus';
                $description = $port->{'controller_location'} . ", PCI Device ID: " . $port->{'pci_device'};
            } elsif ($datatype =~ /firewire/) {
                $name = 'FireWire';
                $type = 'FireWire Bus';
                $description = 'Max Speed: ' . $port->{'max_device_speed'};
            } elsif ($datatype =~ /thunderbolt/) {
                $name = $port->{'device_name_key'};
                next unless $name;
                $type = 'Thunderbolt';
                $description = 'UID: ' . $port->{'switch_uid_key'};
            } elsif ($datatype =~ /ethernet/) {
                    $name = $port->{'_name'};
                    if ($name ne '') {
                        $name = $port->{'spethernet_device-id'} if ($name eq 'ethernet');
                        $type = 'Ethernet Controller';
                        $description = 'BSD: ' . $port->{'spethernet_BSD_Name'};
                    }
            }
            
            $common->addController({
                CAPTION => $caption,
                DESCRIPTION => $description,
                NAME => $name,
                TYPE => $type,
            });
            $caption = $description = $name = $type = undef;
        }
    }
}

1;
