package Ocsinventory::Agent::Backend::OS::Solaris::Ports;

use strict;

sub run {
  my $params = shift;
  my $common = $params->{common};

  my $zone;
  my $SystemModel;
  my $aarch;

  my $flag;
  my $caption;
  my $description;
  my $name;
  my $type;

  if ( !$common->can_run("zonename") || `zonename` =~ /global/ ) {
    # Ether pre Sol10 or in Sol10/Sol11 global zone
    $zone = "global";
  } else {
    $zone = "";
  }

  if ($zone) {
    chomp($SystemModel = `uname -m`);
    chomp($aarch = `uname -p`);
    if( $aarch eq "i386" ){
      #
      # For a Intel/AMD arch, we're using smbios
      #
      foreach(`/usr/sbin/smbios -t SMB_TYPE_PORT`) {
        if(/\s+Internal Reference Designator:\s*(.+)/i ) {
          $flag = 1;
          $name = $1;
        }
        elsif ($flag && /^$/) { # end of section
          $flag = 0;

          $common->addPorts({
            CAPTION => $caption,
            DESCRIPTION => $description,
            NAME => $name,
            TYPE => $type,
          });

          $caption = $description = $name = $type = undef;
        }
        elsif ($flag) {
          $caption = $1 if /\s+External Connector Type:.*\((.+)\)/i;
          $description = $1 if /\s+External Reference Designator:\s*(.+)/i;
          $type = $1 if /\s+Port Type:.*\((.+)\)/i;
        }

      }
    }
    elsif( $aarch eq "sparc" ) {
      #
      # For a Sparc arch, we're done
      #
    }

  }
}

1;

