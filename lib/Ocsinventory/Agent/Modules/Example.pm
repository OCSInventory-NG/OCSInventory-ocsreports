###############################################################################
## OCSINVENTORY-NG
## Copyleft Guillaume PROTET 2010
## Web : http://www.ocsinventory-ng.org
##
## This code is open source and may be copied and modified as long as the source
## code is always made freely available.
## Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
################################################################################

package Ocsinventory::Agent::Modules::Example;


sub new {
   my $name="example";   #Set the name of your module here

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
			start_handler => "example_start_handler",    #or undef if don't use this hook 
			prolog_writer => "example_prolog_writer",    #or undef if don't use this hook  
			prolog_reader => "example_prolog_reader",    #or undef if don't use this hook  
			inventory_handler => "example_inventory_handler",    #or undef if don't use this hook 
			end_handler => "example_end_handler"    #or undef if don't use this hook 
   };
 
   bless $self;
}



######### Hook methods ############

sub example_start_handler {
   my $self = shift;
   my $logger = $self->{logger};
   
   $logger->debug("Yeah you are in example_start_handler :)");

}


sub example_prolog_writer {
   my $self = shift;
   my $logger = $self->{logger};
   
   $logger->debug("Yeah you are in example_prolog_writer :)");

}


sub example_prolog_reader {
   my $self = shift;
   my $logger = $self->{logger};
   
   $logger->debug("Yeah you are in example_prolog_reader :)");

}


sub example_inventory_handler {
   my $self = shift;
   my $logger = $self->{logger};
   
   $logger->debug("Yeah you are in example_inventory_handler :)");

}


sub example_end_handler {
   my $self = shift;
   my $logger = $self->{logger};
   
   $logger->debug("Yeah you are in example_end_handler :)");

}

1;
