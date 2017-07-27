package Ocsinventory::Agent::Backend::Virtualization::SolarisLdoms;

use strict;

sub check {
    my $params = shift;
    my $common = $params->{common};
    return unless $common->can_run('ldm');
}

sub run {
  my @ldoms;
  my $ldom;
  my @ldomdetails;
  my $ldomdetail;
  my $ldomname;
  my $ldomstatus;
  my $ldommem;
  my $ldomncpu;
  my $ldomuuid;
  my $ldomsoftstate;
  my $params = shift;
  my $common = $params->{common};
  my $logger = $params->{logger};

  @ldoms = `/usr/sbin/ldm list-domain -p`;

  foreach $ldom (@ldoms) {
    if($ldom =~ /^DOMAIN\|name=(\S+)\|state=(\S+)\|flags=\S+\|cons=\S+\|ncpu=(\d+)\|mem=(\d+)\|.*/) {
        $ldomname=$1;
        $ldomstatus=$2;
        $ldomncpu=$3;
        $ldommem=$4/1024/1024;
        $ldomsoftstate="";

        @ldomdetails = `/usr/sbin/ldm list-domain -o domain -p $ldomname`;

        foreach $ldomdetail (@ldomdetails) {
          if($ldomdetail =~ /^DOMAIN\|.*\|softstate=(.*)$/) {
            $ldomsoftstate=$1;
          } elsif($ldomdetail =~ /^UUID\|uuid=(.*)$/) {
            $ldomuuid=$1;
          }
        }

        my $machine = {

            MEMORY => $ldommem,
            NAME => $ldomname,
            UUID => $ldomuuid,
            STATUS => $ldomstatus,
            SUBSYSTEM => $ldomsoftstate,
            VMTYPE => "Solaris Ldom",
            VCPU => $ldomncpu,

        };

        $common->addVirtualMachine($machine);

    }
  }
}

1;
