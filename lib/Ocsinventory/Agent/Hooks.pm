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

    #$self->{current_context} = {
    #  OCS_AGENT_LOG_PATH => $self->{config}->{logdir}."modexec.log",
    #  OCS_AGENT_SERVER_URI => $ocsAgentServerUri,
    #  OCS_AGENT_INSTALL_PATH => $self->{config}->{vardir},
    #  OCS_AGENT_DEBUG_LEVEL => $::debug,
    #  OCS_AGENT_EXE_PATH => $Bin,
    #  OCS_AGENT_SERVER_NAME => $self->{config}->{server},
    #  OCS_AGENT_AUTH_USER => $self->{config}->{user},
    #  OCS_AGENT_AUTH_PWD => $self->{config}->{password},
    #  OCS_AGENT_AUTH_REALM => $self->{config}->{realm},
    #  OCS_AGENT_DEVICEID => $self->{config}->{deviceid},
    #  OCS_AGENT_VERSION => $self->{config}->{VERSION},
    #  OCS_AGENT_CMDL => "TOTO", # TODO cmd line parameter changed with the unified agent
    #  OCS_AGENT_CONFIG => $self->{config}->{accountconfig},
      # The prefered way to log message
    #  OCS_AGENT_LOGGER => $self->{logger},
    #};

    $self->{current_context} = $context 
    
  }

  #Create object for modules
  foreach my $mod (keys %Ocsinventory::Agent::Modules::) {
	   $mod =~ s/\:\://;
      my $package ="Ocsinventory::Agent::Modules::".$mod; 
      my $module = new $package($context) ;
 
      my $name= $module->{structure}->{name};
      
      $self->{modules}->{$name}=$module;
     
      #$module->example_end_handler($context);
      #$logger->debug("Nom du module charge=$name");

      #my $method="$mod"."_end_handler";


  }



  bless $self;


}


sub run {
  my ($self, $args, $optparam) = @_;

  return if $self->{dontuse};
  my $name = $args->{name};

  my $logger = $self->{logger};
  my $context = $self->{context};

  $logger->debug("Calling handlers : `$name'");

  for (keys %{$self->{modules}}) {
		my $module = $self->{modules}->{$_};
      my $hook = $module->{structure}->{$name};
      if ($hook) {
           $module->$hook($context);
      }
  }


  #my @f = get_symbols($name);

  #foreach (@f) {
    #$logger->debug(" run func: `$_'");
    #no strict 'refs';
    ##eval { &$_($self->{current_context}, $optparam); };
    #$self->{example}->$_($self->{current_context}, $optparam); 
    #if ($@) {$logger->error("$_ > exec failed: $@")}
  #}

}


#sub get_symbols {
#  my $suffix = shift;
#  my @ret;

#  no strict 'refs';
#  foreach my $mod (keys %Ocsinventory::Agent::Option::) {

#    foreach (@{"Ocsinventory::Agent::Modules::".$mod."EXPORT"}) {
#      next unless $_ =~ /$suffix$/;
#      push @ret, "Ocsinventory::Agent::Modules::".$mod."$_";
#    }
#  }

#  return @ret;
#}

1;
