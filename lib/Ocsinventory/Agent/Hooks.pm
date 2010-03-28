package Ocsinventory::Agent::Hooks;
# This package give possibility to use hooks in unified unix agent.

use strict;
use warnings;

#use FindBin qw($Bin);

sub new {
  my (undef, $context) = @_;

  my $self = {};
  $self->{accountinfo} = $context->{accountinfo};
  $self->{accountconfig} = $context->{accountconfig};
  my $logger = $self->{logger}=$context->{logger};

  $self->{config} = $context->{config};

  $self->{dontuse} = 1;

  my $modulefile;
  foreach (@{$self->{config}->{etcdir}}) {
    $modulefile = $_.'/modules.conf';
    if (-f $modulefile) {
      if (do $modulefile) {
	$logger->debug("Turns hooks on for $modulefile");
	$self->{dontuse} = 0;
        last;
      } else {
          $logger->debug("Failed to load `$modulefile': $?");
      }
    }
  }

  if ($self->{dontuse}) {
      $logger->debug("No modules will be used.");
  } else {
      my $ocsAgentServerUri;

      # to avoid a warning if $self->{config}->{server} is not defined
      if ($self->{config}->{server}) {
          $ocsAgentServerUri = "http://".$self->{config}->{server}.$self->{config}->{remotedir};
      }

      if ($self->{config}->{debug}) {
        $::debug = 2;
      }

    
  }

  #Create objects for modules 
  foreach my $mod (keys %Ocsinventory::Agent::Modules::) {
	   $mod =~ s/\:\://;
      my $package ="Ocsinventory::Agent::Modules::".$mod; 
      
	my $module = new $package($context) ;
      my $name= $module->{structure}->{name};
     
      #Store the reference in a key to access modules easily  
      $self->{modules}->{$name}=$module;

  }

  bless $self;
}



sub run {
  my ($self, $args, $moduleparam) = @_;

  return if $self->{dontuse};

  my $name = $args->{name}; #type of hook asked

  my $logger = $self->{logger};

  $logger->debug("Calling handlers : `$name'");

  #Launching hook for modules if not 'undef' 
  for (keys %{$self->{modules}}) {
		my $module = $self->{modules}->{$_};
      my $hook = $module->{structure}->{$name};
      if ($hook) {
           $module->$hook($moduleparam);
      }
  }

}

1;
