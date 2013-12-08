################################################################################
## Author: Linkbynet <SA-DEVSYS@linkbynet.com>
##
## This code is open source and may be copied and modified as long as the source
## code is always made freely available.
## Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
##
## #############################################################################
##
## This module retrieves information about Apache virtualhosts.
##
## It handles setups where Apache was installed by package (Enterprise Linux
## distributionsi family: RHEL, CentOS, Fedora) with configuration in standard
## dir /etc/httpd.
##
## Information gets written to tags at XPaths:
##
## /REQUEST/CONTENT/APACHE_VHOST
## /REQUEST/CONTENT/APACHE_VHOST_CERTIFICATE
##
## Example:
##    <!-- [...] -->
##
##    <APACHE_VHOST>
##      <DIRECTORY>/var/www/example.com</DIRECTORY>
##      <NAME>example.com:443</NAME>
##      <URL>example.com</URL>
##      <PORT_NUMBER>443</PORT_NUMBER>
##    </APACHE_VHOST>
##
##    <APACHE_VHOST_CERTIFICATE>
##      <DOMAINNAME>example.com</DOMAINNAME>
##      <EXPIRATIONDATE>30/06/2023</EXPIRATIONDATE>
##      <REGISTRATIONDATE>01/07/2013</REGISTRATIONDATE>
##      <SITE>example.com:443</SITE>
##      <EMAIL>certmaster@example.com</EMAIL>
##    </APACHE_VHOST_CERTIFICATE>
##
##    <!-- [...] -->
################################################################################

package Ocsinventory::Agent::Modules::Apache::VhostsEL;


use strict;
use warnings;

use Ocsinventory::Apache::Vhosts::Common;

sub new {
    my $name = "apachevhosts_el";   # Set the name of your module here

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

    # Path to httpd bin
    $self->{modinfos}->{httpd_bin} = '/usr/sbin/httpd';
    # Path to httpd dir in /etc
    # (basis for relative paths or SSLCertificateFile, for example)
    $self->{modinfos}->{httpd_basedir} = '/etc/httpd/';
    # Path to httpd conf dir
    $self->{modinfos}->{httpd_confdir} = '/etc/httpd/conf';
    # Path to httpd main conf file
    $self->{modinfos}->{httpd_conf_file} = '/etc/httpd/conf/httpd.conf';

    bless $self;
}

######### Hook methods ############

sub apachevhosts_el_start_handler { # Use this hook to test prerequisites needed by module and disble it if needed
    my $self = shift;
    my $logger = $self->{logger};

    my $prerequisites = 1;

    # TESTS:
    #
    # 1 - Apache HTTPD server is installed the packet way (conf dir present)
    # 2 - We can execute the httpd binary
    # 3 - We can read the main conf file (httpd.conf or equivalent)

    if (! -d $self->{modinfos}->{httpd_confdir}) {
        # No apache found on this server (or not in the expected place)
        $logger->debug("- no $self->{modinfos}->{httpd_confdir} directory found");
        $prerequisites = 0;
    }
    elsif (! -x $self->{modinfos}->{httpd_bin}) {
        $logger->debug("- no executable $self->{modinfos}->{httpd_bin} found");
        $prerequisites = 0;
    }
    elsif (! -f $self->{modinfos}->{httpd_conf_file}) {
        $logger->debug("- did not find $self->{modinfos}->{httpd_conf_file} file");
        $prerequisites = 0;
    }

    if ($prerequisites == 0) {
        $self->{disabled} = 1; # Use this to disable the module
        $logger->debug("Humm my prerequisites are not OK...disabling module :( :( ");
    }
}

sub apachevhosts_el_inventory_handler { # Use this hook to add or modify entries in the inventory XML
    my $self = shift;
    my $logger = $self->{logger};

    my $common = $self->{context}->{common};
    $logger->debug(__PACKAGE__);

    # Reading httpd -S output
    my $vhosts = readVhostsDump($self->{modinfos}->{httpd_bin}, $self->{modinfos}->{httpd_conf_file}, $logger);

    # Enhancing information by parsing each vhost's configuration
    foreach my $vhost (@$vhosts) {
        readVhostConfFile($vhost, $self->{modinfos}->{httpd_basedir});
    }

    # Write OCS XML
    foreach my $vhost (@$vhosts) {
        push @{$common->{'xmltags'}->{'APACHE_VHOST'}}, {
            "NAME" => [$vhost->{'computedname'}],
            "URL" => [$vhost->{'srvname'}],
            "DIRECTORY" => [$vhost->{'docroot'}],
            "PORT_NUMBER" => [$vhost->{'port'}]
        };
        if ($vhost->{'ssl'}) {
            push @{$common->{'xmltags'}->{'APACHE_VHOST_CERTIFICATE'}}, {
                "SITE" => [$vhost->{'computedname'}],
                "DOMAINNAME" => [$vhost->{'sslcertdetails'}->{'cn'}],
                "REGISTRATIONDATE" => [$vhost->{'sslcertdetails'}->{'startdate'}],
                "EXPIRATIONDATE" => [$vhost->{'sslcertdetails'}->{'enddate'}],
                "EMAIL" => [$vhost->{'sslcertdetails'}->{'email'}]
            };
        }
    }
}

1;
