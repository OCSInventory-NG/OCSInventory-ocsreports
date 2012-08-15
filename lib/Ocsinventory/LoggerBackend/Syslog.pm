package Ocsinventory::LoggerBackend::Syslog;
# Not tested yet!
use Sys::Syslog qw( :DEFAULT setlogsock);

sub new {
  my (undef, $params) = @_;

  my $self = {};

  openlog("ocs-agent",'cons.pid', $params->{config}->{logfacility});
  syslog('debug', 'syslog backend enabled');

  bless $self;
}

sub addMsg {

  my (undef, $args) = @_;

  my $level = $args->{level};
  my $message = $args->{message};

  return if $message =~ /^$/;

  syslog('info', $message);

}

sub destroy {
  closelog();
}

1;
