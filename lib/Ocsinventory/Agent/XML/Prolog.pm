package Ocsinventory::Agent::XML::Prolog;

use strict;
use warnings;

use XML::Simple;
use Digest::MD5 qw(md5_base64);

sub new {
    my (undef, $params) = @_;

    my $self = {};
    $self->{config} = $params->{context}->{config};

    $self->{logger} = $params->{context}->{logger};

    die unless ($self->{config}->{deviceid}); #XXX

    $self->{xmlroot}{QUERY} = ['PROLOG'];
    $self->{xmlroot}{DEVICEID} = [$self->{config}->{deviceid}];

    bless $self;
}

sub dump {
    my $self = shift;
    eval "use Data::Dumper;";
    print Dumper($self->{xmlroot});

}

sub getContent {
    my ($self, $args) = @_;

    my $content=XMLout( $self->{xmlroot}, RootName => 'REQUEST', XMLDecl => '<?xml version="1.0" encoding="UTF-8"?>', SuppressEmpty => undef );

    return $content;
}

1;
