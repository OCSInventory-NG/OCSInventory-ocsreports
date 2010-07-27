package Ocsinventory::Agent::XML::Inventory;
# TODO: resort the functions
use strict;
use warnings;

=head1 NAME

Ocsinventory::Agent::XML::Inventory - the XML abstraction layer

=head1 DESCRIPTION

OCS Inventory uses XML for the data transmition. The module is the
abstraction layer. It's mostly used in the backend module where it
called $inventory in general.

=cut

use XML::Simple;
use Digest::MD5 qw(md5_base64);
use Config;

use Ocsinventory::Agent::Backend;

=over 4

=item new()

The usual constructor.

=cut
sub new {
  my (undef, $params) = @_;

  my $self = {};
  $self->{accountinfo} = $params->{context}->{accountinfo};
  $self->{accountconfig} = $params->{context}->{accountconfig};
  $self->{backend} = $params->{backend};
  $self->{common} = $params->{context}->{common};

  my $logger = $self->{logger} = $params->{context}->{logger};
  $self->{config} = $params->{context}->{config};

  if (!($self->{config}{deviceid})) {
    $logger->fault ('deviceid unititalised!');
  }

  $self->{xmlroot}{QUERY} = ['INVENTORY'];
  $self->{xmlroot}{DEVICEID} = [$self->{config}->{deviceid}];

  #$self->{xmlroot}{CONTENT}{HARDWARE} = {
    # TODO move that in a backend module
   # ARCHNAME => [$Config{archname}]
  #};

  # Is the XML centent initialised?
  $self->{isInitialised} = undef;

  bless $self;
}

=item initialise()

Runs the backend modules to initilise the data.

=cut
sub initialise {
  my ($self) = @_;

  return if $self->{isInitialised};

  $self->{backend}->feedInventory ({inventory => $self});

}


=item getContent()

Return the inventory as a XML string.

=cut
sub getContent {
  my ($self, $args) = @_;

  my $logger = $self->{logger};
  my $common = $self->{common};

  $self->initialise();

  $self->processChecksum();

  #  checks for MAC, NAME and SSN presence
  my $macaddr = $self->{xmlroot}->{CONTENT}->{NETWORKS}->[0]->{MACADDR}->[0];
  my $ssn = $self->{xmlroot}->{CONTENT}->{BIOS}->{SSN}->[0];
  my $name = $self->{xmlroot}->{CONTENT}->{HARDWARE}->{NAME}->[0];

  my $missing;

  $missing .= "MAC-address " unless $macaddr;
  $missing .= "SSN " unless $ssn;
  $missing .= "HOSTNAME " unless $name;

  if ($missing) {
    $logger->debug('Missing value(s): '.$missing.'. I will send this inventory to the server BUT important value(s) to identify the computer are missing');
  }

  $self->{accountinfo}->setAccountInfo($self);

  my $content = XMLout( $self->{xmlroot}, RootName => 'REQUEST', XMLDecl => '<?xml version="1.0" encoding="UTF-8"?>', SuppressEmpty => undef );

  my $clean_content;

  # To avoid strange breakage I remove the unprintable caractere in the XML
  foreach (split "\n", $content) {
#      s/[[:cntrl:]]//g;
    if (! m/\A(
      [\x09\x0A\x0D\x20-\x7E]            # ASCII
      | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
      |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
      | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
      |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
      |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
      | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
      |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
      )*\z/x) {
      s/[[:cntrl:]]//g;
      $logger->debug("non utf-8 '".$_."'");
    }

      s/\r|\n//g;

      # Is that a good idea. Intent to drop some nasty char
      # s/[A-z0-9_\-<>\/:\.,#\ \?="'\(\)]//g;
      $clean_content .= $_."\n";
  }

  #Cleaning xmltags content after adding it o inventory
  $common->flushXMLTags();

  return $clean_content;
}

=item printXML()

Only for debugging purpose. Print the inventory on STDOUT.

=cut
sub printXML {
  my ($self, $args) = @_;

  $self->initialise();
  print $self->getContent();
}

=item writeXML()

Save the generated inventory as an XML file. The 'local' key of the config
is used to know where the file as to be saved.

=cut
sub writeXML {
  my ($self, $args) = @_;

  my $logger = $self->{logger};

  if ($self->{config}{local} =~ /^$/) {
    $logger->fault ('local path unititalised!');
  }

  $self->initialise();

  my $localfile = $self->{config}{local}."/".$self->{config}{deviceid}.'.ocs';
  $localfile =~ s!(//){1,}!/!;

  # Convert perl data structure into xml strings

  if (open OUT, ">$localfile") {
    print OUT $self->getContent();
    close OUT or warn;
    $logger->info("Inventory saved in $localfile");
  } else {
    warn "Can't open `$localfile': $!"
  }
}

=item processChecksum()

Compute the <CHECKSUM/> field. This information is used by the server to
know which parts of the XML have changed since the last inventory.

The is done thank to the last_file file. It has MD5 prints of the previous
inventory. 

=cut
sub processChecksum {
  my $self = shift;
  my $logger = $self->{logger};
  my $common = $self->{common};

#To apply to $checksum with an OR
  my %mask = (
    'HARDWARE'      => 1,
    'BIOS'          => 2,
    'MEMORIES'      => 4,
    'SLOTS'         => 8,
    'REGISTRY'      => 16,
    'CONTROLLERS'   => 32,
    'MONITORS'      => 64,
    'PORTS'         => 128,
    'STORAGES'      => 256,
    'DRIVES'        => 512,
    'INPUT'         => 1024,
    'MODEMS'        => 2048,
    'NETWORKS'      => 4096,
    'PRINTERS'      => 8192,
    'SOUNDS'        => 16384,
    'VIDEOS'        => 32768,
    'SOFTWARES'     => 65536,
    'VIRTUALMACHINES' => 131072,
  );
  # TODO CPUS is not in the list

  if (!$self->{config}->{vardir}) {
    $logger->fault ("vardir uninitialised!");
  }

  my $checksum = 0;

  if (!$self->{config}{local} && $self->{config}->{last_statefile}) {
    if (-f $self->{config}->{last_statefile}) {
      # TODO: avoid a violant death in case of problem with XML
      $self->{last_state_content} = XML::Simple::XMLin(

        $self->{config}->{last_statefile},
        SuppressEmpty => undef,
        ForceArray => 1

      );
    } else {
      $logger->debug ('last_state file: `'.
  	$self->{config}->{last_statefile}.
  	"' doesn't exist (yet).");
    }
  }

  foreach my $section (keys %mask) {
    #If the checksum has changed...
    my $hash = md5_base64(XML::Simple::XMLout($self->{xmlroot}{'CONTENT'}{$section}));
    if (!$self->{last_state_content}->{$section}[0] || $self->{last_state_content}->{$section}[0] ne $hash ) {
      $logger->debug ("Section $section has changed since last inventory");
      #We make OR on $checksum with the mask of the current section
      $checksum |= $mask{$section};
      # Finally I store the new value.
      $self->{last_state_content}->{$section}[0] = $hash;
    }
  }

  $logger->debug("CHECKSUM=$checksum");
  $common->setHardware({CHECKSUM => $checksum});
}

=item saveLastState()

At the end of the process IF the inventory was saved
correctly, the last_state is saved.

=cut
sub saveLastState {
  my ($self, $args) = @_;

  my $logger = $self->{logger};

  if (!defined($self->{last_state_content})) {
	  $self->processChecksum();
  }

  if (!defined ($self->{config}->{last_statefile})) {
    $logger->debug ("Can't save the last_state file. File path is not initialised.");
    return;
  }

  if (open LAST_STATE, ">".$self->{config}->{last_statefile}) {
    print LAST_STATE my $string = XML::Simple::XMLout( $self->{last_state_content}, RootName => 'LAST_STATE' );;
    close LAST_STATE or warn;
  } else {
    $logger->debug ("Cannot save the checksum values in ".$self->{config}->{last_statefile}."
	(will be synchronized by GLPI!!): $!");
  }
}

=item addSection()

A generic way to save a section in the inventory. Please avoid this
solution.

=cut
sub addSection {
  my ($self, $args) = @_;
  my $logger = $self->{logger};
  my $multi = $args->{multi};
  my $tagname = $args->{tagname};

  for( keys %{$self->{xmlroot}{CONTENT}} ){
    if( $tagname eq $_ ){
      $logger->debug("Tag name `$tagname` already exists - Don't add it");
      return 0;
    }
  }

  if($multi){
    $self->{xmlroot}{CONTENT}{$tagname} = [];
  }
  else{
    $self->{xmlroot}{CONTENT}{$tagname} = {};
  }
  return 1;
}

=item feedSection()

Add information in inventory.

=back
=cut
# Q: is that really useful()? Can't we merge with addSection()?
sub feedSection{
  my ($self, $args) = @_;
  my $tagname = $args->{tagname};
  my $values = $args->{data};
  my $logger = $self->{logger};

  my $found=0;
  for( keys %{$self->{xmlroot}{CONTENT}} ){
    $found = 1 if $tagname eq $_;
  }

  if(!$found){
    $logger->debug("Tag name `$tagname` doesn't exist - Cannot feed it");
    return 0;
  }

  if( $self->{xmlroot}{CONTENT}{$tagname} =~ /ARRAY/ ){
    push @{$self->{xmlroot}{CONTENT}{$tagname}}, $args->{data};
  }
  else{
    $self->{xmlroot}{CONTENT}{$tagname} = $values;
  }

  return 1;
}

1;
