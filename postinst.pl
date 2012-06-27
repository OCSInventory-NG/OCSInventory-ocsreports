#!/usr/bin/perl -w

use strict;

use lib 'lib';

use Ocsinventory::Agent::Config;


my $old_linux_agent_dir = "/etc/ocsinventory-client";

my $config;
my @cacert;
my $binpath;
my $randomtime;
my $crontab;
my $cron_line;
my $option;
my $nowizard;
my $configdir;
my $remove_old_linux;
my $old_linux_config;
my $nossl;
my $download;
my $snmp;
my $now;


for $option (@ARGV){
  if($option=~/--nowizard$/){
    $nowizard = 1;
  }elsif($option=~/--server=(\S*)$/){
    $config->{server} = $1;
  }elsif($option=~/--basevardir=(\S*)$/){
    $config->{basevardir} = $1;
  }elsif($option=~/--configdir=(\S*)$/){
    $configdir = $1;
  }elsif($option=~/--user=(\S*)$/){
    $config->{user} = $1;
  }elsif($option=~/--password=(\S*)$/){
    $config->{password} = $1;
  }elsif($option=~/--realm=(\S*)$/){
    $config->{realm} = $1;
  }elsif($option=~/--tag=(\S*)$/){
    $config->{tag} = $1;
  }elsif($option=~/--crontab$/){
    $crontab = 1;
  }elsif($option=~/--get-old-linux-agent-config$/){
    $old_linux_config = 1;
  }elsif($option=~/--remove-old-linux-agent$/){
    $remove_old_linux = 1;
  }elsif($option=~/--debug$/){
    $config->{debug} = 1;
  }elsif($option=~/--logfile=(\S*)$/){
    $config->{logfile} = $1;
  }elsif($option=~/--nossl$/){
    $nossl = 1;
  }elsif($option=~/--ca=(\S*)$/){
    $config->{ca} = $1;
  }elsif($option=~/--download$/){
    $download = 1;
  }elsif($option=~/--snmp$/){
    $snmp = 1;
  }elsif($option=~/--now$/){
    $now = 1;
  }elsif($option=~/--help/ || $option=~/-h/) {
  print STDERR <<EOF;
Usage :
\t--nowizard                    launch this script without interaction
\t--server=url                  set OCS Inventory NG server address (e.g: http://ocsinventory-ng/ocsinventory) 
\t--basevardir=path             set OCS Inventory NG Unix Unified agent variables directory (e.g: /var/lib/ocsinventory-agent)	
\t--configdir=path              set OCS Inventory NG Unix Unified configuration directory (e.g: /etc/ocsinventory-agent)	
\t--user=username               set username for OCS Inventory server Apache authentication (if needed)
\t--password=password           set password for OCS Inventory NG server Apache authentication (if needed)
\t--realm=realm                 set realm name for OCS Inventory NG server Apache authentication (if needed)
\t--crontab                     set a crontab while installing OCS Inventory NG Unix Unified agent
\t--get-old-linux-agent-config  retrieve old OCS Inventory NG Linux agent configuration (if needed)
\t--remove-old-linux-agent      remove old OCS Inventory NG Linux agent from system (if needed)
\t--debug                       activate debug mode configuration option while installing OCS Inventory NG Unix Unified agent
\t--logfile=path                set OCS Inventory NG Unix Unified agent log file path (if needed) 
\t--nossl                       disable SSL CA verification configuration option while installing OCS Inventory NG Unix Unified agent (not recommended)
\t--ca=path                     set OCS Inventory NG Unix Unified agent CA certificate chain file path
\t--download                    activate package deployment feature while installing OCS Inventory NG Unix Unified agent
\t--snmp                        activate SNMP scans feature while installing OCS Inventory NG Unix Unified agent
\t--now                         launch OCS Inventory NG Unix Unified agent after installation
\t-h --help                     display this help
EOF

  exit 0;
  }
}


loadModules (qw/XML::Simple ExtUtils::MakeMaker/);

############ Setting default values ############

my @default_configdirs = ("/etc/ocsinventory", "/usr/local/etc/ocsinventory", "/etc/ocsinventory-agent");


unless ($config->{basevardir}) {
  if ($^O =~ /solaris/) {
    $config->{basevardir} = '/var/opt/ocsinventory-agent';
  } else { 
    $config->{basevardir} = '/var/lib/ocsinventory-agent'
  }
}


############ Asking for questions ##############
unless ($nowizard) {
  if (!ask_yn("Do you want to configure the agent", 'y')) {
    exit 0;
  }
  
  unless ($configdir) {
    $configdir = getConfigDir (@default_configdirs);

    #If not found
    unless (-d $configdir) {
      $configdir = askConfigDir (@default_configdirs);
      unless (-d $configdir) {
        unless (ask_yn ("Do you want to create the directory ".$configdir."?", 'y')) {
           die("Please create  ".$configdir." directory first.\n");
        }
      }
    }
  }

  #Old linux agent
  if (ask_yn("Should the old linux_agent settings be imported ?", 'y')) {
   $old_linux_config=1;
  }

  #Getting agent configuration if exists
  if (-f $configdir."/ocsinventory-agent.cfg") {
    open (CONFIG, "<".$configdir."/ocsinventory-agent.cfg") or
    die "Can't open ".$configdir."/ocsinventory-agent.cfg: ".$!;

    foreach (<CONFIG>) {
      s/#.+//;
      if (/(\w+)\s*=\s*(.+)/) {
        my $key = $1;
        my $val = $2;
        # Remove the quotes
        $val =~ s/\s+$//;
        $val =~ s/^'(.*)'$/$1/;
        $val =~ s/^"(.*)"$/$1/;
        $config->{$key} = $val unless $config->{$key};
      }
    }
    close CONFIG;
  }

  #Getting server name 
  print "[info] The config file will be written in $configdir/ocsinventory-agent.cfg,\n";
  $config->{server} = promptUser('What is the address of your ocs server',$config->{server});
  #$config->{server} = promptUser('What is the address of your ocs server', exists ($config->{server})?$config->{server}:'ocsinventory-ng');


  #Getting credentials if needed
  if (ask_yn ("Do you need credential for the server? (You probably don't)", 'n')) {
    $config->{user} = promptUser("user", $config->{user});
    $config->{password} = promptUser("password");
    print "[info] The realm can be found in the login popup of your Internet browser.\n[info] In general, it's something like 'Restricted Area'.\n";
    $config->{realm} = promptUser("realm");
  }

  #Getting tag
  unless ($config->{tag}){
    if (ask_yn('Do you want to apply an administrative tag on this machine', 'y')) {
      $config->{tag} = promptUser("tag", $config->{tag});
    }
  }

  #Getting crontab
  if ($^O =~ /solaris/) {
    if (ask_yn("Do yo want to install the cron task in current user crontab ?", 'y')) {
       $crontab = 1;
    }
  } elsif (-d "/etc/cron.d") {
    if (ask_yn("Do yo want to install the cron task in /etc/cron.d", 'y')) {
       $crontab = 1;
    }
  }

  #Getting basevardir
  $config->{basevardir} = promptUser('Where do you want the agent to store its files? (You probably don\'t need to change it)', $config->{basevardir}, '^\/\w+', 'The location must begin with /');

  unless (-d $config->{basevardir}) {
    unless (ask_yn ("Do you want to create the ".$config->{basevardir}." directory?\n", 'y')) {
      die("Please create the ".$config->{basevardir}." directory manually and relaunch postinst.pl script\n");
    }
  }

  #Remove old linux agent ?
  $remove_old_linux = ask_yn ("Should I remove the old linux_agent", 'n') unless $remove_old_linux;

  #Enable debug option ?
  $config->{debug} = ask_yn("Do you want to activate debug configuration option ?", 'y') unless $config->{debug};

  #Enable log file ?
  unless ($config->{logfile}) {
    if (ask_yn("Do you want to use OCS Inventory NG UNix Unified agent log file ?", 'y')){ 
      $config->{logfile} = promptUser('Specify log file path you want to use', $config->{logfile}, '^\/\w+', 'The location must begin with /');
    }
  }

  #Disable SSL option ?
  unless ($nossl) {
     $nossl = ask_yn("Do you want disable SSL CA verification configuration option (not recommended) ?", 'n');
  }

  #Set CA certificate path ?
  unless ($config->{ca}) {
    if (ask_yn("Do you want to set CA certificate chain file path ?", 'y')){ 
      $config->{ca} = promptUser('Specify CA certificate chain file path', $config->{ca}, '^\/\w+', 'The location must begin with /');
    }
  }


  #Enable download feature ?
  $download = ask_yn("Do you want to use OCS-Inventory software deployment feature?", 'y') unless $download;

  #Enable SNMP feature ?
  $snmp = ask_yn("Do you want to use OCS-Inventory SNMP scans feature?", 'y') unless $snmp;

  #Run agent after configuration ?
  $now = ask_yn("Do you want to send an inventory of this machine?", 'y') unless $now;

}


################ Here we go... ##############

#Old linux agent
if (-f $old_linux_agent_dir.'/ocsinv.conf' && $old_linux_config) {
  
  print STDERR "Getting old OCS Inventory NG Linux agent configuration...\n";
  my $ocsinv = XMLin($old_linux_agent_dir.'/ocsinv.conf');
  $config->{server} = mkFullServerUrl($ocsinv->{'OCSFSERVER'});

  if (-f $old_linux_agent_dir.'/cacert.pem') {
    open CACERT, $old_linux_agent_dir.'/cacert.pem' or die "Can'i import the CA certificat: ".$!;
    @cacert = <CACERT>;
    close CACERT;
  }

  my $admcontent = '';


  if (-f "$old_linux_agent_dir/ocsinv.adm") {
    if (!open(ADM, "<:encoding(iso-8859-1)", "$old_linux_agent_dir/ocsinv.adm")) {
      warn "Can't open $old_linux_agent_dir/ocsinv.adm";
    } else {
      $admcontent .= $_ foreach (<ADM>);
      close ADM;
      my $admdata = XMLin($admcontent) or die;
      if (ref ($admdata->{ACCOUNTINFO}) eq 'ARRAY') {
        foreach (@{$admdata->{ACCOUNTINFO}}) {
          $config->{tag} = $_->{KEYVALUE} if $_->{KEYNAME} =~ /^TAG$/;
        }
      } elsif (
        exists($admdata->{ACCOUNTINFO}->{KEYNAME}) &&
        exists($admdata->{ACCOUNTINFO}->{KEYVALUE}) &&
        $admdata->{ACCOUNTINFO}->{KEYNAME} eq 'TAG'
        ) {
          print $admdata->{ACCOUNTINFO}->{KEYVALUE}."\n";
          $config->{tag} = $admdata->{ACCOUNTINFO}->{KEYVALUE};
      }
    }
  }
}



#Setting server uri
print STDERR "Setting OCS Inventory NG server address...\n";

$config->{server}="ocsinventory-ng" unless $config->{server};
$config->{server} = mkFullServerUrl($config->{server});

if (!$config->{server}) {
    print "Server is empty. Leaving...\n";
    exit 1;
}
my $uri;
if ($config->{server} =~ /^http(|s):\/\//) {
    $uri = $config->{server};
} else { # just the hostname
    $uri = "http://".$config->{server}."/ocsinventory"
}



#Is OCS agent well installed ?
print STDERR "Looking for OCS Invetory NG Unix Unified agent installation...\n";
chomp($binpath = `which ocsinventory-agent 2>/dev/null`);
if (! -x $binpath) {
	# Packaged version with perl and agent ?
	$binpath = $^X;
	$binpath =~ s/perl/ocsinventory-agent/;
}

if (! -x $binpath) {
    print "sorry, can't find ocsinventory-agent in \$PATH\n";
    exit 1;
} else {
    print "ocsinventory agent presents: $binpath\n";
}


#Setting crontab
$randomtime = int(rand(60)).' '.int(rand(24));
$cron_line = $randomtime." * * * root $binpath --lazy > /dev/null 2>&1\n";

if ($crontab) {

  print STDERR "Setting crontab...\n";

  if ($^O =~ /solaris/) {
    my $cron = `crontab -l`;

    # Let's suppress Linux cron/anacron user column
    $cron_line =~ s/ root /  /;
    $cron .= $cron_line;

    open CRONP, "| crontab" || die "Can't run crontab: $!";
    print CRONP $crontab;
    close(CRONP);

  } elsif (-d "/etc/cron.d") {
    open DEST, '>/etc/cron.d/ocsinventory-agent' or die $!;
    # Save the root PATH
    print DEST "PATH=".$ENV{PATH}."\n";
    print DEST $randomtime." * * * root $binpath --lazy > /dev/null 2>&1\n";
    close DEST;
  }
}

	
#Creating basevardir
if (!-d $config->{basevardir}) {
  print STDERR "Creating $config->{basevardir} directory...\n";
  mkdir $config->{basevardir} or die $!;
}

#Disabling SSL verification if asked
$config->{ssl} = 0 if $nossl;

#Creating configuration directory 
$configdir = "/etc/ocsinventory-agent" unless $configdir;  #If not set in command line

if (grep (/$configdir/,@default_configdirs)) {
  $configdir = '/etc/ocsinventory-agent' unless $configdir;

  print STDERR "Creating $configdir directory...\n";

  unless (-d $configdir) {
    unless (mkdir $configdir) {
      die("Failed to create ".$configdir.". Are you root?\n");
    }
  }

  print STDERR "Writing OCS Inventory NG Unix Unified agent configuration\n";
  open CONFIG, ">$configdir/ocsinventory-agent.cfg" or die "Can't write the config file in $configdir: ".$!;
  print CONFIG $_."=".$config->{$_}."\n" foreach (keys %$config);
  close CONFIG;
  chmod 0600, "$configdir/ocsinventory-agent.cfg";


} else {
  die("Wrong configuration directory...please choose a directory supported by OCS Inventory NG agent !!!\n");
}



#Removing old linux agent if needed
if ($remove_old_linux) {
    print STDERR "Removing old OCS Inventory Linux agent...\n";
    foreach (qw#
        /etc/ocsinventory-client
        /etc/logrotate.d/ocsinventory-client
        /usr/sbin/ocsinventory-client.pl
        /etc/cron.d/ocsinventory-client
        /bin/ocsinv
        #) {
        print $_."\n";
        next;
        rmdir if -d;
        unlink if -f || -l;
    }
    print "done\n"
}

# Creating vardirectory for this server
my $dir = $config->{server};
$dir =~ s/\//_/g;
my $vardir = $config->{basevardir}."/".$dir;
print STDERR "Creating $vardir directory...\n";
recMkdir($vardir) or die "Can't create $vardir!";

if (@cacert) { # we need to migrate the certificate
    print STDERR "Copying cacert.pem in $vardir...\n";

    open CACERT, ">".$vardir."/cacert.pem" or die "Can't open ".$vardir.'/cacert.pem: '.$!;
    print CACERT foreach (@cacert);
    close CACERT;
    print "Certificate copied in ".$vardir."/cacert.pem\n";
}


print STDERR "Activating modules if needed...\n";

open MODULE, ">$configdir/modules.conf" or die "Can't write modules.conf in $configdir: ".$!;
print MODULE "# this list of module will be load by the at run time\n";
print MODULE "# to check its syntax do:\n";
print MODULE "# #perl modules.conf\n";
print MODULE "# You must have NO error. Else the content will be ignored\n";
print MODULE "# This mechanism goal is to launch agent extension modules\n";
print MODULE "\n";
print MODULE ($download?'':'#');
print MODULE "use Ocsinventory::Agent::Modules::Download;\n";
print MODULE ($snmp?'':'#');
print MODULE "use Ocsinventory::Agent::Modules::Snmp;\n";
print MODULE "\n";
print MODULE "# DO NOT REMOVE THE 1;\n";
print MODULE "1;\n";
close MODULE;


#Prevent security risks by removing existing snmp_com.txt file which is no longer used
my $snmp_com_file = $vardir."/snmp/snmp_com.txt";
if ( -f $snmp_com_file ) {
    print STDERR "$snmp_com_file seems to exists...removing it to prevent security risks !\n";
    unlink $snmp_com_file;
}

#Launch agent if asked
if ($now) {
    print STDERR "Launching OCS Inventory NG Unix Unified agent...\n";

    system("$binpath --force");
    if (($? >> 8)==0) {
        print "   -> Success!\n";
    } else {
        print "   -> Failed!\n";
	print "You may want to launch the agent with the --verbose or --debug flag.\n";
    }
}

#End
print "New settings written! Thank you for using OCS Inventory\n";

######## Subroutines ################

sub loadModules {
    my @modules = @_;

    foreach (@modules) {
        eval "use $_;";
        if ($@) {
            print STDERR "Failed to load $_. Please install it and restart the postinst.pl script ( ./postinst.pl ).\n";
            exit 1;

        }
    }

}

sub ask_yn {
    my $promptUser = shift;
    my $default = shift;

    die unless $default =~ /^(y|n)$/;

    my $cpt = 5;
    while (1) {
        my $line = prompt("$promptUser\nPlease enter 'y' or 'n'?>", $default);
        return 1 if $line =~ /^y$/;
        return if $line =~ /^n$/;
        if ($cpt-- < 0) {
            print STDERR "to much user input, exit...\n";
            exit(0);
        }
    }
}

sub promptUser {
    my ($promptUser, $default, $regex, $notice) = @_;

    my $string = $promptUser;
    $string .= "?>";

    my $line;
    my $cpt = 5;
    while (1) {

        $line = prompt($string, $default);

        if ($regex && $line !~ /$regex/) {
            print STDERR $notice."\n";
        } else {
            last;
        }

        if ($cpt-- < 0) {
            print STDERR "to much user input, exit...\n";
            exit(0);
        }

    }

    return $line;
}

sub getConfigDir {
    my @choices = @_;

    foreach (@choices) {

        my $t = $_.'/ocsinventory-agent.cfg';

        if (-f $t) {
            print "Config file found are $t! Reusing it.\n";
            return $_; 
        }
    }
}

sub askConfigDir {
    my @choices = @_;

    print STDERR "Where do you want to write the configuration file?\n";
    foreach (0..$#choices) {
        print STDERR " ".$_." -> ".$choices[$_]."\n";
    }
    my $input = -1;
    my $configdir;
    while (1) {
        $input = prompt("?>");
        if ($input =~ /^\d+$/ && $input >= 0 && $input <= $#choices) {
            last;
        } else {
            print STDERR "Value must be between 0 and ".$#choices."\n";
        }
    }

    return $choices[$input];
}

sub recMkdir {
  my $dir = shift;

  my @t = split /\//, $dir;
  shift @t;
  return unless @t;

  my $t;
  foreach (@t) {
    $t .= '/'.$_;
    if ((!-d $t) && (!mkdir $t)) {
      return;
    }
  }
  1;
}

sub mkFullServerUrl {

    my $server = shift;

    my $ret = 'http://' unless $server =~ /^http(s|):\/\//;
    $ret .= $server;
   
    if ($server !~ /http(|s):\/\/\S+\/\S+/) {
        $ret .= '/ocsinventory';
    }

    return $ret;

}
