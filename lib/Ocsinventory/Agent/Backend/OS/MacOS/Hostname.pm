package Ocsinventory::Agent::Backend::OS::MacOS::Hostname;

sub check {
  my $params = shift;
  my $common = $params->{common};
  return 1 if $common->can_load ("Mac::SysProfile");
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
