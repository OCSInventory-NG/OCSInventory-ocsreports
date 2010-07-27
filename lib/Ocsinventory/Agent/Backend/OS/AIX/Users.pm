package Ocsinventory::Agent::Backend::OS::AIX::Users;

sub check {
# Useless check for a posix system i guess
  my @who = `who 2>/dev/null`;
  return 1 if @who;
  return;
}

# Initialise the distro entry
sub run {
  my $params = shift;
  my $common = $params->{common};

  my %user;
  # Logged on users
  for(`who`){
    $user{$1} = 1 if /^(\S+)./;
  }

  my $UsersLoggedIn = join "/", keys %user;

  $common->setHardware ({ USERID => $UsersLoggedIn });

}

1;
