################################################################################
## Author: Linkbynet <SA-DEVSYS@linkbynet.com>
##
## This code is open source and may be copied and modified as long as the source
## code is always made freely available.
## Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
##
## #############################################################################
##
## This module finds and retrieves information about Oracle instances.
##
## First elements are taken from the /etc/oratab file. For each instance listed
## in it, we get the precise version number (with tnsping utility) and bundle
## name by reading the file below:
##
## $ORACLE_HOME/inventory/Components21/oracle.server/*/context.xml
##
## Information gets written to tags at XPaths:
##
## /REQUEST/CONTENT/ORACLE_INSTANCE
##
## Example:
##    <!-- [...] -->
##
##    <ORACLE_INSTANCE>
##      <AUTOSTART>0</AUTOSTART>
##      <BUNDLE>Standard</BUNDLE>
##      <NAME>EXDB01</NAME>
##      <VERSION>11.2.0.4.0</VERSION>
##    </ORACLE_INSTANCE>
##
##    <!-- [...] -->
################################################################################

package Ocsinventory::Agent::Modules::Databases::Oracle;


use strict;
use warnings;

use Ocsinventory::Agent::Modules::Databases::Oracle::Instances;

sub new {
    my $name = "oracleinstances";   # Set the name of your module here

    my (undef,$context) = @_;
    my $self = {};

    # Create a special logger for the module
    $self->{logger} = new Ocsinventory::Logger ({
        config => $context->{config}
    });

    $self->{logger}->{header} = "[$name]";

    $self->{context} = $context;

    $self->{structure} = {
        name => $name,
        start_handler => $name."_start_handler",            # or undef if don't use this hook
        prolog_writer => undef,                             # or undef if don't use this hook
        prolog_reader => undef,                             # or undef if don't use this hook
        inventory_handler => $name."_inventory_handler",    # or undef if don't use this hook
        end_handler => undef                                # or undef if don't use this hook
    };

    bless $self;
}



######### Hook methods ############

sub oracleinstances_start_handler { # Use this hook to test prerequisites needed by module and disble it if needed
    my $self = shift;
    my $logger = $self->{logger};

    my $prerequisites = 1;

    if (!(-f '/etc/oratab' && -r '/etc/oratab')) {
        $logger->debug("- no readable /etc/oratab file");
        $prerequisites = 0;
    }

    if ($prerequisites == 0) {
        $self->{disabled} = 1; # Use this to disable the module
        $logger->debug("Humm my prerequisites are not OK...disabling module :( :( ");
    }
}


sub oracleinstances_inventory_handler { # Use this hook to add or modify entries in the inventory XML
    my $self = shift;
    my $logger = $self->{logger};

    my $common = $self->{context}->{common};
    $logger->debug(__PACKAGE__);

    my $database = getInstances();

    # Write to OCS XML
    foreach my $instance (keys(%$database)) {
        push @{$common->{xmltags}->{ORACLE_INSTANCE}}, {
            'NAME' => [$instance],
            'AUTOSTART' => [$database->{$instance}->{'AUTOSTART'}],
            'VERSION' => [$database->{$instance}->{'VERSION'}],
            'BUNDLE' => [$database->{$instance}->{'BUNDLE'}]
        };
    }
}

1;
