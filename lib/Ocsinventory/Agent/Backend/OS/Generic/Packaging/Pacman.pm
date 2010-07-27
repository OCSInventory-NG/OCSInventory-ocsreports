package Ocsinventory::Agent::Backend::OS::Generic::Packaging::Pacman;

sub check {can_run("pacman")}

sub run {
  my $params = shift;
  my $common = $params->{common};

  foreach(`pacman -Q`){
      /^(\S+)\s+(\S+)/;
      my $name = $1;
      my $version = $2;
     
      $common->addSoftware({
      'NAME' => $name,
      'VERSION' => $version
      });
  }
}

1;
