#!/usr/bin/perl -w
# 
# COPYRIGHT:
#
# This software is Copyright (c) 2008 claimid.com/saxjazman9
# 
# (Except where explicitly superseded by other copyright notices)
# 
# Special thanks to Jesse over a best practical for the framework
# from which this script is has been created
# 
# LICENSE:
# 
# This work is made available to you under the terms of Version 2 of
# the GNU General Public License. A copy of that license should have
# been provided with this software, but in any event can be snarfed
# from www.gnu.org.
# 
# This work is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
# 02110-1301 or visit their web page on the internet at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html.
# 

#
# This is just a basic script that checks to make sure that all
# the modules needed by OCS MacOSX agent before you can compile it.
#
# You have to install LWP in your system to use this script
#
# WARNING: Before executing this script please modify your ~/.cpan/CPAN/MyConfig.pm file as follows:
# $> perl -MCPAN -e shell
# cpan> o conf makepl_arg 'LIB=~/darwin-perl-lib PREFIX=--perl-only'
# cpan> o conf commit
# cpan> quit
#
# This will set the CPAN shell up to install the modules in this script to ~/darwin-perl-lib
# it will also cause the man pages and other misc perl stuff to not be installed... we only need the modules anyway
#
# After this script is done, you will take the ~/darwin-perl-lib and move it to the source code directory for
# compiling your application
#
# Once the script has completed and you are confident you have everything, you can reverse the changes to your
# MyConfig.pm by:
#
# # $> perl -MCPAN -e shell
# cpan> o conf makepl_arg ''
# cpan> o conf commit
# cpan> quit
#

#
# THIS IS A BETA SCRIPT! USE AT YOUR OWN RISK
#

use strict;
use warnings;
use Getopt::Long;
use CPAN;
use LWP::Simple qw/getstore/;

my $libwww_tarball = "G/GA/GAAS/libwww-perl-5.837.tar.gz";
my $xmlentities_tarball = "S/SI/SIXTEASE/XML-Entities-1.0000.tar.gz";
my $cryptssleay_tarball = "N/NA/NANIS/Crypt-SSLeay-0.58.tar.gz";
my $netssleay_tarball = "F/FL/FLORA/Net-SSLeay-1.36.tar.gz";


my %args;
my %deps;
GetOptions(
    \%args,
    'install',                            
	'ssl',
);

unless (keys %args) {
    help();
    exit(0);
}

# Set up defaults
my %default = (
    'ssl'		=> 0,
	'CORE'		=> 1,	
);
$args{$_} = $default{$_} foreach grep !exists $args{$_}, keys %default;

#
# Place any core modules (+ versions) that are required in the form: MOD::MOD 0.01
#

$deps{'CORE'} = [ text_to_hash( << ".") ];
$libwww_tarball
XML::SAX
XML::Parser
XML::Simple
URI
XML::NamespaceSupport
File::Listing
Net::IP
Compress::Zlib
Compress::Raw::Zlib
IO::Zlib
Mac::SysProfile
.

# push all the dep's into a @missing array
my @missing;
my @deps = @{ $deps{'CORE'} };
while (@deps) {
	my $module = shift @deps;
	my $version = shift @deps;
	push @missing, $module, $version;
}

# assuming we've passed the --install, proceed with the compiling and install to our 
if ( $args{'install'} ) {
	while( @missing ) {
		resolve_dep(shift @missing, shift @missing);
	}
	#We install XML::Etities manually because of writing rights in /usr/local/bin directory
	&install_tarball("http://search.cpan.org/CPAN/authors/id",$xmlentities_tarball,"XML-Entities"); 
}

# for ssl, include the Crypt::SSLeay library's
# NOTE: YOU NEED OPENSSL pre-compiled on the system for this to work... You've been warned.
if ( $args{'ssl'} ) {
	&install_tarball("http://search.cpan.org/CPAN/authors/id",$cryptssleay_tarball); 
	&install_tarball("http://search.cpan.org/CPAN/authors/id",$netssleay_tarball); 
}

# convert the dep text list to a hash
sub text_to_hash {
    my %hash;
    for my $line ( split /\n/, $_[0] ) {
        my($key, $value) = $line =~ /(\S+)\s*(\S*)/;
        $value ||= '';
        $hash{$key} = $value;
    }
    return %hash;
}

# pull in our local .cpan/CPAN/MyConfig.pm file 
# use the cpan shell to force install the module to our local dir
# force install is used because although we may have the package already up-to-date on our system,
# we want a clean fresh copy installed to our darwin-perl-lib dir.
sub resolve_dep {
    my $module = shift;
    my $version = shift;
	
	local @INC = @INC;
        my $user = `whoami`; chomp $user;
	unshift @INC, "/Users/$user/Library/Application Support/.cpan";
	if ( $ENV{'HOME'} ) {
                unshift @INC, "$ENV{'HOME'}/.cpan";
	}

	#unshift @INC, "/Users/$user/~darwin-perl-lib";

    print "\nInstall module $module\n";
    my $cfg = (eval { require CPAN::MyConfig });
    unless($cfg){ die('CPAN Not configured properly'); }
    CPAN::Shell->force('install',$module);
}

# the help....
sub help {

    print <<'.';

By default, testdeps determine whether you have 
installed all the perl modules OCSNG.app needs to run.

	--install		Install missing modules
	
The following switches will tell the tool to check for specific dependencies

	--ssl		Incorporate SSL for package deployment (requires OPENSSL lib's to be installed)
.
}
sub install_tarball {

    my $cpan_url = shift;
    my $tarball_url = shift;
    my $directory = shift;

    my $mod_dir;

    my $tarball = $tarball_url; $tarball =~ s/(.*)\/(.*)\/(.*)\///;

    if ($directory) {
        $mod_dir = $directory;
    } else {
        $mod_dir = $tarball ; $mod_dir =~ s/\.tar\.gz//;
    }

    print "Getting $cpan_url/$tarball_url file\n";
    my $resp = getstore("$cpan_url/$tarball_url", $tarball);

    die "Couldn't get $cpan_url/$tarball_url -> HTTP response: $resp." unless $resp == 200;

    print "Extracting $tarball file\n";
    open(EXCLUDE,">exclude_hiddens"); print EXCLUDE ".*"; close EXCLUDE;
    system("tar -xvzf $tarball -X exclude_hiddens");  #We exclude hiddens files from extract (mainly for older Mac::Sysprofile package)
    unlink "exclude_hiddens";

    print "Installing $mod_dir module\n";
    chdir($mod_dir); 
    system("env ARCHFLAGS='-arch i386 -arch ppc -arch x86_64' perl Makefile.PL LIB='~/darwin-perl-lib' PREFIX='--perl-only'");    #Multi architectures support
    system("make && make install");
    chdir('..'); 
    system("rm -Rf $mod_dir");

}

1;
