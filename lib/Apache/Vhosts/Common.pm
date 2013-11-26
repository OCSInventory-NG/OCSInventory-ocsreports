###############################################################################
# Apache::Vhosts::Common - Common stuff for apache vhosts inventory
#
# Author: Linkbynet <SA-DEVSYS@linkbynet.com>
###############################################################################

package Apache::Vhosts::Common;

=head1 NAME

Apache::Vhosts::Common - Lib for common operations in vhosts inventory

=head1 DESCRIPTION

This package is meant to contain common functions used by OCS modules for
Apache virtualhosts.

For example, we could have two OCS modules:

=over

=item ApacheVhostsPackaged

which would deal with packaged apache setups

=item ApacheVhostsCompiled

which would deal with compiled apache versions

=back

At different times, these modules still would need to do the same things, such
as parsing apache configuration files, reading and extracting information from
a vhost dump, reading a x509 certificate with openssl, ...

To avoid code duplication, the specific modules can call the functions
contained in this common package.

=head2 Exports

The module exports the following functions:

=over

=item C<readVhostsDump>

=item C<readVhostConfFile>

=back

=cut

use Exporter;
our @ISA = qw(Exporter);
our @EXPORT = qw(&readVhostsDump &readVhostConfFile);

use strict;
use warnings;

#### BEGIN: Define regexes

# Useful lines in vhosts dump (NameVirtualHosts and IP vhosts only)
my $dumpline_name_re = qr/^port ([0-9]+) namevhost (\S+) \((\S+):([0-9]+)\)/;
my $dumpline_ip_re = qr/^([0-9.]+):([0-9]+)\s+(\S+) \((\S+):([0-9]+)\)/;

# "subject=" line of openssl x509 command output (used to extract CN)
my $subject_cn_re = qr/^subject=.*\/CN=([^\s\/]+).*$/;

# Simplistic email address pattern
my $damn_simple_email_re = qr/.+\@.+\..+/;

#### END: Define regexes


=head2 readVhostsDump()

Return an array of hashes with the virtualhosts found thanks to Apache's vhosts
dump (C<httpd -S> command).

=head3 Return type

The function returns a reference to an array of hashes.

=head3 Process

The function's workflow is as follows:

=over

=item 1

Open C<httpd -S> command output, with the current configuration file

=item 2

Read dump line by line to match IP-based or name-based virtualhost information
(both types of lines should be recognized):

 port 80 namevhost mynamevhost.fr (/etc/httpd/.../10-mynamevhost.conf:50)
 10.0.0.1:80 myvhost myipvhost.fr (/etc/httpd/.../20-myipvhost.conf:1)

=item 3

Create a hash with the virtualhost's data

We put the following attributes in it:

 (string) computedname, (int) port, (string) srvname,
 (string) vhostfile, (string) vhostline, (string) docroot, (bool) ssl

At this stage we do not know docroot or ssl, so they are "/nonexistent" and
false (0), respectively.

=item 4

Push the vhost hash to the array.

=back

=head3 Return example

 [
   {
     'computedname' => "[httpd] myvhost.fr:80",
     'port' => 80,
     'srvname' => 'myvhost.fr',
     'vhostfile' => '/etc/httpd/conf.d/10-myvhost.conf',
     'vhostline' => 1,
     'docroot' => '/nonexistent',
     'ssl' => 0
   },
   {
     'computedname' => "[httpd] myvhost.fr:443",
     'port' => 443,
     'srvname' => 'myvhost.fr',
     'vhostfile' => '/etc/httpd/conf.d/10-myvhost.conf',
     'vhostline' => 20,
     'docroot' => '/nonexistent',
     'ssl' => 0
   }
 ]

=head3 Calling

    my $vhosts = readVhostsDump($httpd_bin, $httpd_conf_file, $logger);

=over

=item Parameter: $httpd_bin (string)

Path to the httpd binary to execute (for example: C</usr/sbin/httpd>).
Specific options (such as C<-D> parameters) may be added to the string.

=item Parameter: $httpd_conf_file (string)

Path to the main httpd configuration file (for example:
C</etc/httpd/conf/httpd.conf>).

=item Parameter: $logger (reference to OCS logger instance)

To make use of OCS logging capabilities within the function.

=back

=cut
sub readVhostsDump {
    my ($httpd_bin, $httpd_conf_file, $logger) = @_;

    my @vhosts = ();

    # 2>&1 because some very old Apache versions write the vhosts dump
    # on stderr!
    open(my $DUMPFILE, "$httpd_bin -S -f $httpd_conf_file 2>&1 |")
        or die("Cannot open \$(httpd -S -f $httpd_conf_file) $!");
    while (<$DUMPFILE>) {
        chomp;
        s/^\s+//; # lstrip
        my ($ip, $port, $srvname, $vhostfile, $vhostline);
        if ($_ =~ $dumpline_name_re) {
            ($port, $srvname, $vhostfile, $vhostline) = ($1, $2, $3, $4);
        }
        elsif ($_ =~ $dumpline_ip_re) {
            ($ip, $port, $srvname, $vhostfile, $vhostline) = ($1, $2, $3, $4, $5);
        }
        if (defined($port)) { # sufficient test to know if line was found
            my %vhost = (
                'computedname' => "$srvname:$port",
                'port' => int($port),
                'srvname' => $srvname,
                'vhostfile' => $vhostfile,
                'vhostline' => int($vhostline),
                'docroot' => '/nonexistent',
                'ssl' => 0
            );

            push(@vhosts, \%vhost);
        }
    }
    close($DUMPFILE);
    return \@vhosts;
}


=head2 readVhostConfFile()

Enhance a virtualhost's information with elements found when parsing the
vhost's configuration file.

=head3 Return type

The function returns nothing.

It only operates on the (referenced) vhost hash it got in parameter.

=head3 Process

The function must read the apache configuration file in which the vhost gets
defined (<VirtualHost> block).

The path to the particular configuration file and the line number of the vhost
declaration are known in the C<vhostfile> and C<vhostline> attributes, thanks
to the vhost dump.

The function's process, for the given vhost, is as follows:

=over

=item 1

Open the configuration file at C<vhostfile>

=item 2

Read line by line, waiting to be at correct line number (C<vhostline>) to
start searching for information.

=item 3

Search for the following information in the <VirtualHost> and enhance the
given vhost hash with:

=over

=item *

docroot (string)

the value of the C<DocumentRoot> directive

=item *

ssl (bool)

we turn it to true if we find a C<SSLEngine on> directive

=item *

sslcertpath (string)

value of the C<SSLCertificateFile> directive, if such a directive is present

=back

=item 4

File reading stops when we find the C<< </VirtualHost> >> closing block
(in case multiple vhosts are declared in the same configuration file).

=back

=head3 Calling

    foreach my $vhost (@$vhosts) # Generally
    {
        readVhostConfFile($vhost, $httpd_basedir);
    }

=over

=item Parameter: $vhost (reference to hash)

The virtualhost hash to enhance.

=item Parameter: $httpd_basedir (string)

The path to base directory of httpd, in case we encounter a relative path
in C<SSLCertificateFile> and need to complete it.

B<IMPORTANT>: the given path is expected to end with a slash '/', for example:

    "/etc/httpd/"

=back

=cut
sub readVhostConfFile {
    my ($vhost, $relpath_prefix) = @_;

    my $nr = 0;
    open(my $VHOST_FILE, '<', $vhost->{'vhostfile'})
        or die ("Cannot open $vhost->{'vhostfile'}");
    while (my $vline = <$VHOST_FILE>) {
        # Waiting the correct line to start reading
        next if ++$nr < $vhost->{'vhostline'};

        $vline =~ s/^\s+//; # lstrip

        # Get various vhost properties

        # -- DocumentRoot
        if ($vline =~ m/^DocumentRoot ["']?([^\s'"]+)["']?/i) {
            $vhost->{'docroot'} = $1;
        }
        # -- SSLEngine (bool)
        elsif ($vline =~ m/^SSLEngine on/i) {
            $vhost->{'ssl'} = 1;
        }
        # -- SSLCertificateFile path
        elsif ($vline =~ m/^SSLCertificateFile ["']?([^\s'"]+)["']?/i) {
            $vhost->{'sslcertpath'} = $1;
            readVhostSSLCert($vhost, $relpath_prefix);
        }

        # Stop reading on </VirtualHost> block closing tag
        last if $vline =~ m@^</VirtualHost>@i;
    }
    close($VHOST_FILE);
}


###############################################################################
# readVhostSSLCert() - read a vhost's SSL certificate to get its details
#
# Invoking:
#   readVhostSSLCert($vhost, $relpath_prefix)
#
# Params:
#   - $vhost          - a reference to the vhost hash
#   - $relpath_prefix - the prefix to use in case the SSLCertificateFile path
#                       is a relative one, in order to make it an absolute path
#
# Returns:
#   nothing.
#
# We read the certificate with an "openssl x509" command.
# We try to retrieve the following fields:
#
# - "subject" (specifically to get the CN)
# - "notBefore" and "notAfter" dates
# - "email" (we catch the first email returned)
#
# The certificate details found here are gently stuffed in
# $vhost->{'sslcertdetails'}, as follows:
#
#   {
#     # (in a vhost here)
#     # [...]
#     'srvname' => 'myvhost.fr',
#     'ssl' => 1,
#     'sslcertpath' => 'conf/ssl/myvhost.fr.crt',
#     'sslcertdetails' => {
#       'cn' => 'myvhost.fr',
#       'startdate' => '31/12/2012',
#       'enddate' => '31/12/2042',
#       'email' => 'certadmin-email@example.com'
#     }
#   }
sub readVhostSSLCert {
    my ($vhost, $relpath_prefix) = @_;

    $vhost->{'sslcertdetails'} = ();

    my $sslcertpath = $vhost->{'sslcertpath'};
    my $absolute = (substr($sslcertpath, 0, 1) eq '/');
    if (!$absolute) {
        $sslcertpath = "${relpath_prefix}${sslcertpath}";
    }

    open(my $OPENSSL_CMD, "openssl x509 -in \"$sslcertpath\" -noout -subject -dates -email |");
    while (my $oline = <$OPENSSL_CMD>) {
        if ($oline =~ $subject_cn_re) {
            $vhost->{'sslcertdetails'}->{'cn'} = $1;
        }
        elsif ($oline =~ m/^notBefore=(.+)$/) {
            $vhost->{'sslcertdetails'}->{'startdate'} = formatDate($1);
        }
        elsif ($oline =~ m/^notAfter=(.+)$/) {
            $vhost->{'sslcertdetails'}->{'enddate'} = formatDate($1);
        }
        elsif ($oline =~ $damn_simple_email_re) {
            chomp $oline;
            $vhost->{'sslcertdetails'}->{'email'} = $oline;
            # stop processing from the first email address
            # (it should be the last line of the command output)
            last;
        }
    }
    close($OPENSSL_CMD);
}


###############################################################################
# formatDate() - reformat date, with help of system command date(1)
#
# Invoking:
#   formatDate($date)
#
# Params:
#   - $date - the date string to reformat, in a format parsable by date(1)
#
# Returns:
#   the ISO 8601 (%Y-%m-%d) formatted date (string)
#
sub formatDate {
    my ($date) = @_;
    my $formattedDate = `date --date="$date" --iso-8601`;
    chomp $formattedDate;
    return $formattedDate;
}

1;
