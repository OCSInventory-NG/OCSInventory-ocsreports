###############################################################################
## OCSINVENTORY-NG
## Copyleft Guillaume PROTET 2010
## Web : http://www.ocsinventory-ng.org
##
## This code is open source and may be copied and modified as long as the source
## code is always made freely available.
## Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
################################################################################
# Function by hook:
# -download_prolog_reader, download_message, download
# -download_inventory_handler
# -download_end_handler, begin, done, clean, finish, period, download, execute,
#   check_signature and build_package
package Ocsinventory::Agent::Option::Example;

use strict;

require Exporter;

our @EXPORT = qw/
	example_start_handler
	example_prolog_writer
        example_prolog_reader
        example_inventory_handler
        example_end_handler
/;



sub new {
   my (undef,$params) = @_;
   my $self = {};  
 
   my $self->{logger}=$params->{logger};   



   bless $self;
}



######### Hook subroutines ############

sub example_start_handler {


}

sub example_prolog_writer {


}

sub example_prolog_reader {


}

sub example_inventory_handler {


}


sub example_end_handler {


}
