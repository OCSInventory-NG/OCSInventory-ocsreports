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

    $self->{xmlroot}{QUERY} = ['PROLOG']; 
    $self->{xmlroot}{DEVICEID} = [$self->{config}->{deviceid}];

    bless $self;
}

sub dump {
    my $self = shift;
    print Dumper($self->{xmlroot});

}

sub set {
    my ($self, $args) = @_;

    foreach (keys %$args) {
        $self->{xmlroot}{$_} = [$args->{$_}]; 
    }
}

sub getContent {
    my ($self, $args) = @_;

    $self->{accountinfo}->setAccountInfo($self);
    my $content=XMLout( $self->{xmlroot}, RootName => 'REQUEST', XMLDecl => '<?xml version="1.0" encoding="UTF-8"?>', SuppressEmpty => undef );

    return $content;
}

1;
