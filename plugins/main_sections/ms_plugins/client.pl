#!/usr/bin/perl

use strict;
use SOAP::Lite +trace => [qw(all)];

my $num_args = $#ARGV + 1;
if ($num_args != 3) {
    print "\nUsage: client.pl Server_Address Plugin_Name Action\n";
    exit;
}

my $serverAddress = $ARGV[0];
my $pluginName = $ARGV[1];
my $action = $ARGV[2];

my $soap = SOAP::Lite->uri("http://$serverAddress/Apache/Ocsinventory/Plugins/Modules");
my $proxy = $soap->proxy("http://$serverAddress/plugins");

if($action == 1){
   my $obj = $proxy->InstallPlugins("$pluginName");
}
elsif($action == 0){
   my $obj = $proxy->DeletePlugins("$pluginName");
}