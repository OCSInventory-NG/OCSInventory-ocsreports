package Ocsinventory::Agent::AccountInfo;

use strict;
use warnings;

sub new {
    my (undef,$params) = @_;

    my $self = {};
    bless $self;

    $self->{config} = $params->{config};
    $self->{logger} = $params->{logger};
    $self->{common} = $params->{common};

    my $logger = $self->{logger} = $params->{logger};


    if ($self->{config}->{accountinfofile}) {

        $logger->debug ('Accountinfo file: '. $self->{config}->{accountinfofile});
        if (! -f $self->{config}->{accountinfofile}) {
            $logger->info ("Accountinfo file doesn't exist. I create an empty one.");
            $self->writeAccountInfoFile();
        } else {

            my $xmladm;

            eval {
                $xmladm = $self->{common}->readXml($self->{config}->{accountinfofile}, [ 'ACCOUNTINFO' ]);
            };


            if ($xmladm && exists($xmladm->{ACCOUNTINFO})) {
                # Store the XML content in a local HASH
                for(@{$xmladm->{ACCOUNTINFO}}){
                    if (!$_->{KEYNAME}) {
                        $logger->debug ("Incorrect KEYNAME in ACCOUNTINFO");
                    }
                    $self->{accountinfo}{ $_->{KEYNAME} } = $_->{KEYVALUE};
                }
            }
        }
    } else { 
      $logger->debug("No accountinfo file defined");
    }

    if ($self->{config}->{tag}) {
        if ($self->{accountinfo}->{TAG}) {
            $logger->debug("A TAG seems to already exist in the ocsinv.adm file. ".
                "The -t paramter will be ignored. Don't forget that the TAG value ".
                "will ignored by the server unless it has OCS_OPT_ACCEPT_TAG_UPDATE_FROM_CLIENT=1.");
        } else {
          $self->{accountinfo}->{TAG} = $self->{config}->{tag};
        }
    }
  $self; #Because we have already blessed the object 
}

# Add accountinfo stuff to an inventory
sub setAccountInfo {
    my $self = shift;
    my $inventory = shift;

    #my $ai = $self->getAll();
    $self->{xmlroot}{'CONTENT'}{ACCOUNTINFO} = [];

    my $ai = $self->{accountinfo};
    return unless $ai;

    foreach (keys %$ai) {

    push @{$inventory->{xmlroot}{'CONTENT'}{ACCOUNTINFO}}, {
            KEYNAME => [$_],
            KEYVALUE => [$ai->{$_}],
        };
    }
}

sub writeAccountInfoFile {
    my ($self, $ref) = @_;

    my $logger = $self->{logger};

    my $content;
    $content->{ACCOUNTINFO} = [];

    #We clear accountinfo to store the new one 
    undef $self->{accountinfo};

    #We get values sent by server
    if (ref ($ref) =~ /^ARRAY$/) {
        foreach (@$ref) {
            $self->{accountinfo}->{$_->{KEYNAME}} = $_->{KEYVALUE} if defined ($_->{KEYNAME}) && defined ($_->{KEYVALUE});
        }
    } elsif (ref ($ref) =~ /^HASH$/) {
        $self->{accountinfo}->{$ref->{KEYNAME}} = $ref->{KEYVALUE} if defined ($ref->{KEYNAME}) && defined ($ref->{KEYVALUE});
    } else {
        $logger->debug ("Invalid parameter while writing accountinfo file");
    }

    #We feed our XML for accountinfo file
    foreach (keys %{$self->{accountinfo}}) {
        push @{$content->{ACCOUNTINFO}}, {KEYNAME => [$_], KEYVALUE =>
            [$self->{accountinfo}{$_}]}; 
    }

    my $xml=XML::Simple::XMLout($content, RootName => 'ADM', XMLDecl=> '<?xml version="1.0" encoding="UTF-8"?>');

    #We write accountinfo file
    my $fault;
    if (!open ADM, ">".$self->{config}->{accountinfofile}) {
        $fault = 1;
    } else {
        print ADM $xml;
        $fault = 1 unless close ADM;
    }

    if (!$fault) {
        $logger->debug ("Account info updated successfully");
    } else {
        $logger->error ("Can't save account info in `".
            $self->{config}->{accountinfofile});
    }
}

1;
