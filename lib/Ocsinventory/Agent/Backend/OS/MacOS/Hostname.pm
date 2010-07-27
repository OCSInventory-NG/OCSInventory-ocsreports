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

  my $prof = Mac::SysProfile->new();
  my $nfo = $prof->gettype('SPSoftwareDataType');
  
  return undef unless(ref($nfo) eq 'HASH');
  
  $hostname = $nfo->{'System Software Overview'}->{'Computer Name'};
  
  $common->setHardware ({NAME => $hostname}) if $hostname;
}

1;
