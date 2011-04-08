package Ocsinventory::Agent::XML::Response::Inventory;

use strict;
use Ocsinventory::Agent::XML::Response;
our @ISA = ('Ocsinventory::Agent::XML::Response');

sub new {
    my ($class, @params) = @_;

    my $this = $class->SUPER::new(@params);
    bless ($this, $class);

    my $parsedContent = $this->getParsedContent(['ACCOUNTINFO']);
    if ($parsedContent && exists ($parsedContent->{RESPONSE}) && $parsedContent->{RESPONSE} =~ /^ACCOUNT_UPDATE$/) {
      $this->{accountinfo}->writeAccountInfoFile($parsedContent->{ACCOUNTINFO});
    }
    return $this;
}

1;
