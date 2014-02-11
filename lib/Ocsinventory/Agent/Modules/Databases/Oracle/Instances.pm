################################################################################
# Oracle::Instances - Common stuff to get Oracle instances and versions
#
# Author: Linkbynet <SA-DEVSYS@linkbynet.com>
################################################################################

package Ocsinventory::Agent::Modules::Databases::Oracle::Instances;

=head1 NAME

Oracle::Instances - Lib for Oracle instances and versions retrieval

=head1 DESCRIPTION

This module provides a function to retrieve information about Oracle instances
on the current server.

To do so, the function reads the /etc/oratab file to find instances and then:

=over

=item 1

Checks the existence of the ORACLE_HOME directory.

=item 2

Add (ORACLE_SID, ORACLE_HOME, AUTOSTART) to the databases hash.

=item 3

Go and find server version and bundle for each ORACLE_HOME.

This information is found by executing the C<tnsping> command and by reading
the following XML file:

 $ORACLE_HOME/inventory/Components21/oracle.server/*/context.xml

The following bundles are known: SE1 (Standard Edition One), SE (Standard
Edition), EE (Enterprise Edition), XE (Express Edition).

=item 4

Returns the database hash as follows:

 {
     'SID' => {
         'BUNDLE' => 'Standard',
         'AUTOSTART' => 0,
         'ORA_HOME' => '/home/oracle/oracle/product/11.2.0',
         'VERSION' => '11.2.0.4.0'
     }
 };

=back

Optionally, you can call C<getInstances()> to return only the hash of versions
and bundles, instead of the complete instances hash:

 {
     '/home/oracle/oracle/product/11.2.0' => {
         'BUNDLE' => 'Standard',
         'VERSION' => '11.2.0.4.0'
     }
 };

In that case, you just have to pass parameter 1 (integer one) when calling the
function.

=head2 Exports

The module exports the C<getInstances()> function.

=cut

use Exporter;
our @ISA = qw(Exporter);
our @EXPORT = qw(&getInstances);

use strict;
use warnings;

######### BEGIN: Constants ############

# Known Oracle editions. The two-letter codes are the ones which can be
# encountered in the context.xml file.
my %bundles = (
    'SE1' => 'Standard One',
    'SE' => 'Standard',
    'EE' => 'Enterprise',
    'XE' => 'Express'
);

######### END: Constants ############


=head2 getInstances()

=head3 Synopsis

    getInstances($version_only)

where $versions_only is a integer (tested as a bool)

    my $database = getInstances()
    my $versions = getInstances(1)

=head3 Return values

The function returns a hash reference containing the instances or versions.

=cut
sub getInstances {
    my $versions_only = shift;

    my %database;
    my %versions;

    # First, read the /etc/oratab to find out Oracle instances
    # Here we already get the SID, path and autostart
    open(my $ORATAB, '<', '/etc/oratab') or die("Cannot open /etc/oratab $!");
    while (my $oratab_line = <$ORATAB>) {
        chomp $oratab_line;
        next if $oratab_line =~ /^(#|$)/; # skip comments and empty lines
        my ($ora_sid, $ora_home, $dbstart_yn) = split(/:/, $oratab_line);
        # Keep existing instances only
        if (-d $ora_home) {
            $database{$ora_sid} = {
                'ORA_HOME' => $ora_home,
                'AUTOSTART' => $dbstart_yn eq 'Y' ? 1 : 0
            };
        }
    }
    close($ORATAB);

    # Retrieve version and bundle information in %versions
    # and put information in global %database hash too
    foreach my $ora_sid (keys(%database)) {
        my $ora_home = $database{$ora_sid}{'ORA_HOME'};
        unless (exists($versions{$ora_home})) {
            $versions{$ora_home} = _getVersionAndBundle($ora_home);
        }
        my $version_bundle = $versions{$ora_home};
        $database{$ora_sid}{'VERSION'} = $version_bundle->{'VERSION'};
        $database{$ora_sid}{'BUNDLE'} = $version_bundle->{'BUNDLE'};
    }

    # Return complete database hash or only version and bundle, according to
    # $versions_only argument
    if ($versions_only) {
        return \%versions;
    }
    else {
        return \%database;
    }
}

# _getVersionAndBundle($ora_home)
#
# Simple method building a hash with two keys 'VERSION' and 'BUNDLE' and their
# values.
#
# Return: a hash reference
sub _getVersionAndBundle {
    my $ora_home = shift;
    my %version_bundle = (
        'VERSION' => _getVersion($ora_home),
        'BUNDLE' => _getBundle($ora_home)
    );
    return \%version_bundle;
}

# _getVersion($ora_home)
#
# Find Oracle server version installed in ORACLE_HOME.
#
# We are using the tnsping command for that.
# Return: the version string (for example "11.2.0.4.0" or "N/A" by default)
sub _getVersion {
    my $ora_home = shift;
    my $version = 'N/A';

    my $tnsping = "$ora_home/bin/tnsping";
    if (-x $tnsping) {
        open(my $TNSPING_OUT, "$tnsping localhost |")
            or die("Cannot exec tnsping $!");
        while (my $tp_line = <$TNSPING_OUT>) {
            if ($tp_line =~ m/^TNS Ping Utility for Linux: Version ([0-9.]+)/) {
                $version = $1;
            }
        }
        close($TNSPING_OUT);
    }
    return $version;
}

# _getBundle($ora_home)
#
# Find bundle version, reading a context.xml file down below ORACLE_HOME.
#
# Return: the full name of the install type (e.g. Standard or Enterprise) or,
# by default, "N/A"
sub _getBundle {
    my $ora_home = shift;
    my $bundle = 'N/A';

    my $xml_re = qr/^\s*<VAR .*NAME="s_serverInstallType"/i;
    my $type_re = qr/VAL="(.*?)"/;

    my @xmls = glob("$ora_home/inventory/Components21/oracle.server/*/context.xml");
    if (@xmls) {
        my $xml = $xmls[0];
        open(my $CONTEXT_XML, '<', $xml) or die("Cannot open $xml $!");
        while (my $xml_line = <$CONTEXT_XML>) {
            if ($xml_line =~ $xml_re) {
                $xml_line =~ $type_re;
                my $abbr = $1;
                $bundle = $bundles{$abbr} if exists($bundles{$abbr});
                last;
            }
        }
        close($CONTEXT_XML);
    }

    return $bundle;
}

1;
