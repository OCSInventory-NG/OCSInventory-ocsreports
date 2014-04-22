package Ocsinventory::Agent::Backend::OS::Linux::Distro::NonLSB::oracle;
use strict;

sub check {-f "/etc/oracle-release"}

#####
sub findRelease {
  my $v;

  open V, "</etc/oracle-release" or warn;
  chomp ($v=<V>);
  close V;
  return $v if $v;

  0;
}

sub run {
  my $params = shift;
  my $common = $params->{common};

  my $OSComment;
  chomp($OSComment =`uname -v`);

  $common->setHardware({ 
      OSNAME => findRelease(),
      OSCOMMENTS => "$OSComment"
    });

}

1;
