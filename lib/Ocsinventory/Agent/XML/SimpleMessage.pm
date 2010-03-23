package Ocsinventory::Agent::XML::SimpleMessage;

use strict;
use warnings;

use Data::Dumper; # XXX Debug
use XML::Simple;
use Digest::MD5 qw(md5_base64);

use Ocsinventory::Agent::XML::Prolog;

sub new {
  my (undef, $params) = @_;

  my $self = {};
  $self->{config} = $params->{config};
  $self->{accountinfo} = $params->{accountinfo};
 
  die unless ($self->{config}->{deviceid}); #XXX

  $self->{REQUEST}{QUERY} = ['PROLOG']; 
  $self->{REQUEST}{DEVICEID} = [$self->{config}->{deviceid}];

  bless $self;
}

sub dump {
  my $self = shift;
  print Dumper($self->{REQUEST});

}

sub set {
  my ($self, $args) = @_;

  foreach (keys %$args) {
      $self->{REQUEST}{$_} = [$args->{$_}]; 
  }
}

sub getContent {
  my ($self, $args) = @_;

  $self->{accountinfo}->setAccountInfo($self);
  my $content=XMLout( $self->{REQUEST}, RootName => 'REQUEST', XMLDecl => '<?xml version="1.0" encoding="UTF-8"?>',
    SuppressEmpty => undef );

  return $content;
}



1;
