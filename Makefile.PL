BEGIN {
    # Avoid installation issue introduced by perl 5.24.1 removing '.' path from
    # @INC to avoid possible module injection in normal running perl scripts
    push @INC, '.' if ( $^V && $^V gt 'v5.24' );
}

use inc::Module::Install;
use English qw(-no_match_vars);
use Config;
require 'lib/Ocsinventory/Agent/Config.pm';

# Hack to be able to run a post install script
sub MY::postamble
{

    my $re;

    $re = '
install :: all pure_install doc_install
	$(NOECHO) $(NOOP)';

    $re .= "\n\t[ ! -f run-postinst ] || $^X postinst.pl\n";

    return $re;
}

my $version = $Ocsinventory::Agent::Config::VERSION;
if (-d '.git') {
    chomp( my $gitRev = `git rev-parse --short HEAD`);
    $version .= "~git".$gitRev;
}

name 'Ocsinventory-Unix-Agent';
include 'Module::AutoInstall';
abstract 'OCS Inventory unified Agent for UNIX, Linux and MacOSX';
license 'gpl';
repository 'https://github.com/OCSInventory-NG/UnixAgent/';
version $version;
perl_version '5.006';
authors 'OCS Inventory Team';

# mandatory dependencies
requires 'Digest::MD5' => undef;
requires 'XML::Simple' => undef;
requires 'LWP' => undef;
requires 'File::Temp' => undef;
requires 'Data::UUID' => undef;
requires 'Net::IP' => undef;
recommends 'IO::Socket::SSL' => undef;
recommends 'Compress::Zlib' => undef;
recommends 'Proc::Daemon' => undef;
recommends 'Proc::PID::File' => undef;

# deployement  or https communication
recommends 'Crypt::SSLeay' => undef;
recommends 'LWP::Protocol::https' => undef;

# inventory
recommends 'Net::CUPS' => undef;
recommends 'Parse::EDID' => undef;
recommends 'Nvidia::ml' => undef;

# network 
recommends 'Net::SNMP' => undef;
recommends 'Net::Netmask' => undef;
recommends 'Net::Ping' => undef;
recommends 'Nmap::Parser' => undef;

resources
   bugtracker => 'https://github.com/OCSinventory-NG/Unixagent/issues',
   homepage => 'http://www.ocsinventory-ng.org',
   IRC => 'irc://irc.freenode.net/#ocsinventory-ng',
   repository => 'https://github.com/OCSinventory-NG/Unixagent';
   

install_script  'ocsinventory-agent';

# We want a release bundled with the needed dependency to be able to
# prepare a standalone binary with PAR::Packer
if ($ENV{OCS_BUNDLE_RELEASE}) {
  foreach my $module (qw/Archive::Zip HTML::Parser LWP URI XML::NamespaceSupport Net::IP Net::Netmask HTML::Tagset Proc::Daemon Module::ScanDeps PAR::Packer AutoLoader PAR PAR::Dist File::Remove YAML::Tiny Getopt::ArgvFile ExtUtils::Install ExtUtils::ParseXS XML::SAX XML::Simple/) {
    bundle $module;
    requires $module;
  }
}

#Ugly hack to include ipdiscover binary compilation and install for Linux systems
if ($^O =~ /^linux$/i && can_cc()) {
    my $cc=$Config::Config{cc};
    system("$cc resources/ipdiscover/ipdiscover.c -o ipdiscover");
    if (-f 'ipdiscover') {
      install_script 'ipdiscover';
    }
}


if (defined ($ENV{PERL_AUTOINSTALL}) || defined ($ENV{PERL_AUTOINSTALL})) {
    unlink 'run-postinst';
} else {
    open POSTINST, '>run-postinst' or die $!;
    close POSTINST;
}


if (!can_use('Compress::Zlib')) {
    print "Please install Compress::Zlib if you use an OCS server prior 1.02.\n";
}

if (!can_use('Crypt::SSLeay')) {
    print "Please install Crypt::SSLeay if you want to use SSL.\n";
}

if (!can_use('Net::SSLeay')) {
    print "Please install Net::SSLeay if you want to use the software deployment.\n";
}


if (!can_run('ipdiscover') && !can_run('nmap')) {
    print "Please install nmap ";
    print "or ipdiscover " if $^O =~ /^linux$/i;
    print "if you want to use the network ".
    "discover feature.\n";
}

if (!can_use('Net::IP')) {
    print "Net::IP is strongly recommended since it's required to retrieve "
    ."network information\n";
}

if (!can_use('Net::Netmask')) {
    print "Net::Netmask is strongly recommended since it's required to retrieve "
    ."snmp information\n";
}

if (!can_use('Proc::Daemon') || !can_use('Proc::PID::File')) {
    print "Please install Proc::Daemon and Proc::PID::File if you ".
    "want to use the daemon mode.\n";
}

if (!can_use('Nvidia::ml')){
    print "Nvidia::ml is recommended if you want to retrieve "
        ."memory, speed, bios information for Nvidia cards.\n";
}

my $archname;
eval { # config_re is not available on Perl 5.6
    my @t = Config::config_re('archname');
    $archname = $t[0];
};
if ($^O =~ /(linux|bsd)/i && (!$archname ||$archname =~ /(i[3456x]86|x86_64|amd64)/i)) {
    foreach (qw/dmidecode lspci/) {
        if (!can_run($_)) {
            print "\n";
            print "[WARNING] $_ is needed if you want a complete inventory on x86 and AMD64/x86_64.\n";
            print "\n";
        }
    }
}

if ($^O =~ /^darwin$/ && !can_use('Mac::SysProfile')) {
    print "\n";
    print "[WARNING] Mac::SysProfile are needed on MacOS to detect most of the device information.\n";
    print "\n";
    requires 'Mac::SysProfile' => undef;
}

# memconf is needed by Solaris backend module
if ($^O =~ /^solaris$/i) {
    install_script  'memconf';
}

#for i in inc/BUNDLES/* ; do rm -rf `basename $i` ;done`
#auto_install;
WriteAll;
