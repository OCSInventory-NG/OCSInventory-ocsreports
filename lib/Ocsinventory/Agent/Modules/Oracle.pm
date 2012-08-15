###############################################################################
## OCSINVENTORY-NG
## Copyleft Guillaume PROTET 2010
## Web : http://www.ocsinventory-ng.org
##
## This code is open source and may be copied and modified as long as the source
## code is always made freely available.
## Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
################################################################################

package Ocsinventory::Agent::Modules::Oracle;

use strict;
sub new {

   my $name="Oracle"; # Name of the module

   my (undef,$context) = @_;
   my $self = {};

   #Create a special logger for the module
   $self->{logger} = new Ocsinventory::Logger ({
            config => $context->{config}
   });

   $self->{logger}->{header}="[$name]";

   $self->{context}=$context;

   $self->{structure}= {
                        name => $name,
                        start_handler => undef,    #or undef if don't use this hook
                        prolog_writer => undef,    #or undef if don't use this hook
                        prolog_reader => undef,    #or undef if don't use this hook
                        inventory_handler => $name."_inventory_handler",    #or undef if don't use this hook
                        end_handler => undef    #or undef if don't use this hook
   };

   bless $self;
}

######### Hook methods ############

sub oracle_inventory_handler {

    my $self = shift;
    my $logger = $self->{logger};

    my $common = $self->{context}->{common};

    $logger->debug("Yeah you are in oracle_inventory_handler:)");


	my $sid;
	my $version;

	open ORATAB, "/etc/oratab" or die "Oracle product not installed";
	while(<ORATAB>){
		if (!/^#/ && !/^\s*$/) {
			chomp;
			my @liste1=split(/:/,$_);
			$sid=$liste1[0];
			my @liste2=split("/",$liste1[1]);
			foreach my $v (@liste2){
				$version=$v if ($v=~/\d.*\d/);
			}
		
			push @{$common->{xmltags}->{ORACLE}},
            		{		
                    		SID => $sid,
                    		VERSION => $version,
            		};
		}
	}
	close(ORATAB);
}

1;	
