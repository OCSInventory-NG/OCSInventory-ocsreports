package Ocsinventory::Agent::Backend::OS::MacOS::Hostname;

sub check {
  return 1 if can_load ("Mac::SysProfile");
  0;
}

# Initialise the distro entry
sub run {
  my $params = shift;
  my $common = $params->{common};

  my $hostname;

  my $profile = Mac::SysProfile->new();
  my $data = $profile->gettype('SPSoftwareDataType');
  
  return undef unless(ref($data) eq 'ARRAY');

  my $h = $data->[0];  

  $hostname = $h->{'local_host_name'};
  
  $common->setHardware ({NAME => $hostname}) if $hostname;
}

1;
