package Ocsinventory::Agent::XML::Response;

use strict;
use warnings;

use XML::Simple;
use Data::Dumper;

sub new {
    my (undef, $params) = @_;

    my $self = {};

    $self->{accountconfig} = $params->{accountconfig};
    $self->{accountinfo} = $params->{accountinfo};
    $self->{content}  = $params->{content};
    $self->{config} = $params->{config};
    $self->{common} = $params->{common};
    my $logger = $self->{logger}  = $params->{logger};
    $self->{origmsg}  = $params->{origmsg};

    $logger->debug("=BEGIN=SERVER RET======");
    $logger->debug(Dumper($self->{content}));
    $logger->debug("=END=SERVER RET======");

    $self->{parsedcontent}  = undef;

    bless $self;
}

sub getRawXML {
    my $self = shift;

    return $self->{content};
}

sub getParsedContent {
    my ($self,$forcearray) = @_;

    if(!$self->{parsedcontent}) {
	$self->{parsedcontent} = $self->{common}->readXml($self->{content},$forcearray);
    }

    return $self->{parsedcontent};
}

sub origMsgType {
    my ($self, $package) = @_;

    return ref($package);
}

1;
