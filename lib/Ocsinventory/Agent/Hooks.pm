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
	foreach my $package (searchModules(\%Ocsinventory::Agent::Modules::)){
		my $module = new $package($context);
		my $name = $module->{structure}->{name};
     
      	#Store the reference in a key to access modules easily  
      	$self->{modules}->{$name}=$module;

  	}

  	bless $self;
}

# This function recursively searches for modules in a given namespace
# Param: a hash reference to the namespace
# Returns: an array with modules fully qualified names
sub searchModules {
	my $symbols_href = shift;
	my @modules_list = ();

	my %symbols_h = %{$symbols_href};
	my @symbols_a = sort(keys(%symbols_h));

	foreach(@symbols_a){
		if ($_ eq 'new'){
			# Found a "new" method -> this is a usable module
			my $module_fqn = $symbols_h{$_};
			# Keep the module fqn, without '*' at start
			$module_fqn =~ s/\*?(.+)::new$/$1/;
			push(@modules_list, $module_fqn);
		}
		elsif (substr($_, -2) eq '::') {
			# If we meet a package, continue walking
			push(@modules_list, searchModules($symbols_h{$_}));
	    }
	}
	return @modules_list;
}


sub run {
  my ($self, $args, $moduleparam) = @_;

  return if $self->{dontuse};

  my $name = $args->{name}; #type of hook asked

  my $logger = $self->{logger};

  $logger->debug("Calling handlers : `$name'");

  #Launching hook for modules if not 'undef' and if modules are not disabled by start_handler
  for (keys %{$self->{modules}}) {
	my $module = $self->{modules}->{$_};

	unless ($module->{disabled}) {	
		my $hook = $module->{structure}->{$name};
      		if ($hook) {
           		$module->$hook($moduleparam);
      		}
	}
  }

}

1;
