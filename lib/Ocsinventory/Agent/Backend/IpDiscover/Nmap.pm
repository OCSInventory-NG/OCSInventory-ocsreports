package Ocsinventory::Agent::Backend::IpDiscover::Nmap;

use vars qw($runMeIfTheseChecksFailed);
$runMeIfTheseChecksFailed = ["Ocsinventory::Agent::Backend::IpDiscover::IpDiscover"];
use strict;
use warnings;

sub check {
  my $params = shift;

  return unless can_run("nmap");
  return unless can_load("Nmap::Parser");

  # Do we have nmap 3.90 (or >) 
  foreach (`nmap -v 2>&1`) {
    if (/^Starting Nmap (\d+)\.(\d+)/) {
      my $release = $1;
      my $minor = $2;

      if ($release > 3 || ($release > 3 && $minor >= 90)) {
        return 1;
      }
    }
  }

  0;
}


sub run {
  my $params = shift;

  my $common = $params->{common};
  my $prologresp = $params->{prologresp};
  my $logger = $params->{logger};

  # Let's find network interfaces and call ipdiscover on it
  my $options = $prologresp->getOptionsInfoByName("IPDISCOVER");

  my $network;
  if ($options->[0] && exists($options->[0]->{content})) {
    $network = $options->[0]->{content};
  } else {
    return;
  }

  unless ($network =~ /^\d+\.\d+\.\d+\.\d+$/) {
    return;
  }

  #Let's go scanning the network and parsing the results
  $logger->debug("Scanning the $network network");
  my $nmaparser = new Nmap::Parser;
  $nmaparser->parsescan("nmap","-sP","-PR","$network/24");

  for my $host ($nmaparser->all_hosts("up")){
    my $ip = $host->addr;
    my $mac = $host->mac_addr;
    my $hostname = $host->hostname;

    if ($hostname eq 0) {
      $hostname = undef;     #it's better to send nothing instead of a '0'
    }

    if ($mac) {
      $logger->debug("Host $ip found using Nmap. Adding informations in XML");

      #Feeding the Inventory XML
      $common->addIpDiscoverEntry({
        IPADDRESS => $ip,
        MACADDR => lc($mac),
        NAME => $hostname,
     });
    }
  }
}


1;
