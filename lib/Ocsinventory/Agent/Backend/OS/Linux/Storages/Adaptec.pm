package Ocsinventory::Agent::Backend::OS::Linux::Storages::Adaptec;
use Ocsinventory::Agent::Backend::OS::Linux::Storages;

#Function parse_config and parse_config_fh are taken from https://github.com/kumy/Parse-Arcconf
#
#LICENSE AND COPYRIGHT
#
#Copyright (C) 2012 Mathieu Alorent
#
#This program is free software; you can redistribute it and/or modify it
#under the terms of either: the GNU General Public License as published
#by the Free Software Foundation; or the Artistic License.
#
#See http://dev.perl.org/licenses/ for more information.


# Tested on 2.6.* kernels
#
# Cards tested :
#
# Adaptec AAC-RAID

use strict;

my @devices = Ocsinventory::Agent::Backend::OS::Linux::Storages::getFromUdev();

sub check {
    my $params = shift;
    my $common = $params->{common};

    #Do we have arcconf
    if ($common->can_run ('arcconf') ) {
        my $conf = `arcconf GETCONFIG 1`;
        if($conf =~ /Controllers found: (\d+)/) {
            if($1>0) {
                return 1;
            }
        }
    }

    #Do we have smartctl
    if ($common->can_run ('smartctl') ) {
        foreach my $hd (@devices) {
            $hd->{MANUFACTURER} eq 'Adaptec'?return 1:1;
        }
    }
    return 0;
}

sub run {
    my $params = shift;
    my $common = $params->{common};
    my $logger = $params->{logger};

    my ($name,$vendor,$model,$serialnumber,$firmware, $size, $description, $media, $manufacturer);
    my ($conf, $cur_cont,$info,$key, $dev, $devkey,$controller);
    #get infos from arcconf if possible
    if ($common->can_run ('arcconf') ) {
        $conf = `arcconf GETCONFIG 1`;
        if($conf =~ /Controllers found: (\d+)/) {
            if($1>0) {

                $controller = parse_config();

                foreach $cur_cont (keys %{$controller}){    #travers Controller
                    $info =  $controller->{$cur_cont};

                    foreach $key (keys %{$info}){           #travers Infos of Controller

                        #physical drives
                        if ($key=~m/physical drive/) {
                            $dev = $info->{$key};
                            foreach $devkey (keys %{$dev}) {     #get infos

                                $vendor = $dev->{$devkey}->{'Vendor'};
                                $model = $dev->{$devkey}->{'Model'};
                                $serialnumber = $dev->{$devkey}->{'Serial number'};
                                $firmware = $dev->{$devkey}->{'Firmware'};
                                $size = $dev->{$devkey}->{'Size'};
                                $description = $dev->{$devkey}->{'Transfer Speed'};
                                $media = $dev->{$devkey}->{'Device is'};
                                unless ( $media=~m/an Enclosure services device/) { #Dont need services device just drives
                                    if ( $media=~m/a Hard drive/ ){
                                        $media = "HDD";

                                        #try to determine if Drive is a Solid State Disk
                                        if (exists $dev->{$devkey}->{'SSD'}) { #SSD Info is explicit in config
                                            my $ssd = $dev->{$devkey}->{'SSD'};
                                            if ($ssd=~m/Yes/) {
                                                 $media = "SSD";
                                            }
                                        } else { #No explicit info try to get it through model name
                                           if($model =~m/SSD|Solid State|WDS/) {
                                                $media = "SSD";
                                           }
                                        }

                                    }

                                    $manufacturer = Ocsinventory::Agent::Backend::OS::Linux::Storages::getManufacturer($vendor);
                                    $logger->debug("Adaptec: $manufacturer $media $size, $manufacturer, $model, $description, $media, $size , $serialnumber, $firmware");
                                    $common->addStorages({
                                        NAME => "$manufacturer $media $size",
                                        MANUFACTURER => $manufacturer,
                                        MODEL => $model,
                                        DESCRIPTION => $description,
                                        TYPE => $media,
                                        DISKSIZE => $size,
                                        SERIALNUMBER => $serialnumber,
                                        FIRMWARE => $firmware,
                                    });
                               }
                            }
                        }
                    }
                }
            }
        }
    }
    elsif (-r '/proc/scsi/scsi') {
        foreach my $hd (@devices) {
            open (PATH, '/proc/scsi/scsi');
            # Example output:
            #
            # Attached devices:
            # Host: scsi0 Channel: 00 Id: 00 Lun: 00
            #   Vendor: Adaptec  Model: raid10           Rev: V1.0
            #   Type:   Direct-Access                    ANSI  SCSI revision: 02
            # Host: scsi0 Channel: 01 Id: 00 Lun: 00
            #   Vendor: HITACHI  Model: HUS151436VL3800  Rev: S3C0
            #   Type:   Direct-Access                    ANSI  SCSI revision: 03
            # Host: scsi0 Channel: 01 Id: 01 Lun: 00
            #   Vendor: HITACHI  Model: HUS151436VL3800  Rev: S3C0
            #   Type:   Direct-Access                    ANSI  SCSI revision: 03

            my ($host, $model, $firmware, $manufacturer, $size, $serialnumber);
            my $count = -1;
            while (<PATH>) {
                ($host, $count) = (1, $count+1) if /^Host:\sscsi$hd->{SCSI_COID}.*/;
                if ($host) {
                    if ((/.*Model:\s(\S+).*Rev:\s(\S+).*/) and ($1 !~ 'raid.*')) {
                        $model = $1;
                        $firmware = $2;
                        $manufacturer = Ocsinventory::Agent::Backend::OS::Linux::Storages::getManufacturer($model);
                        foreach (`smartctl -i /dev/sg$count`) {
                            $serialnumber = $1 if /^Serial Number:\s+(\S*).*/;
                        }
                        $logger->debug("Adaptec: $hd->{NAME}, $manufacturer, $model, SATA, disk, $hd->{DISKSIZE}, $serialnumber, $firmware");
                        $host = undef;

                        $common->addStorages({
                            NAME => $hd->{NAME},
                            MANUFACTURER => $manufacturer,
                            MODEL => $model,
                            DESCRIPTION => 'SATA',
                            TYPE => 'disk',
                            DISKSIZE => $size,
                            SERIALNUMBER => $serialnumber,
                            FIRMWARE => $firmware,
                        });
                    }
                }
            }
            close (PATH);
        }
    }
}



sub parse_config
{
  my $arcconf  = "arcconf";
  my $argument = "GETCONFIG 1";
  my $command = sprintf("%s %s|", $arcconf, $argument);
  my $fh;
  if(open $fh, $command)
  {
    my $c = parse_config_fh($fh);
    close $fh;
    return $c;
  }
  return undef;
}

sub parse_config_fh
{
  my  $fh = $_[0];

  my $controller = {};
  my $total_controller        = 0;
  my $current_controller      = 0;
  my $current_logical_drive   = undef;
  my $current_physical_drive  = undef;
  my $ctrl                    = undef;
  my $line                    = undef;

  LEVEL1: while($line = <$fh>)
  {
    chomp $line;

    next if($line =~ /^$/);
    next if($line =~ /^-+$/);

    if($line =~ /^Controllers found: (\d+)$/) {
      $total_controller = $1;
    }
    if($line =~ /^Controller information/) {
      $current_controller     = $current_controller + 1;
      $current_logical_drive  = undef;
      $current_physical_drive = undef;
      $controller->{$current_controller} = {};
      $ctrl = $controller->{$current_controller};

      while($line = <$fh>) {
        chomp $line;

        if ($line =~ /^\s+(.*\w)\s+:\s+(.*)$/) {
          $ctrl->{$1} = $2;
        } elsif ($line =~ /^\s+-+$/) {
          last;
        }
      }

      LEVEL2: while($line = <$fh>) {
        if ($line =~ /^\s+-+$/) {
          $line = <$fh>;
          chomp $line;
        }
        if($line =~ /^\s+(.*\w)\s*/) {
                my $cat = $1;
                $line = <$fh>;
                LEVEL3: while($line = <$fh>) {
                        chomp $line;

                        if ($line =~ /^\s+(.*\w)\s+:\s+(.*)$/) {
                                $ctrl->{$cat}{$1} = $2;
                        } elsif ($line =~ /^\s+-+$/) {
                                last LEVEL3;
                        } elsif ($line =~ /^$/) {
                                last LEVEL2;
                        }
                }
        }
      }
    }

    next if(!defined($current_controller));

    if($line =~ /^Logical drive information/ or $line =~ /^Logical device information/) {
        LEVEL4: while($line = <$fh>) {
                chomp $line;

                if ($line =~ /^\S+.*\w\s+(\d+)$/) {
                        $current_logical_drive = $1;
                } elsif ($line =~ /^\s+(\S.*\S+)\s+:\s+(.*)$/) {
                        $ctrl->{'logical drive'}{$current_logical_drive}{$1} = $2;
                } elsif ($line =~ /^\s+-+$/) {
                        my $cat = <$fh>;
                        $cat =~ s/^\s+(\S.*\S+)\s+/$1/;
                        chomp $cat;
                        LEVEL5: while($line = <$fh>) {
                                chomp $line;

                                if ($line =~ /^\s+(\S.*\S+)\s+:\s+(.*)$/) {
                                        $ctrl->{'logical drive'}{$current_logical_drive}{$cat}{$1} = $2;
                                } elsif ($line =~ /^\S+.*\w\s+(\d+)$/) {
                                        $current_logical_drive = $1;
                                        last LEVEL5;
                                } elsif ($line =~ /^-+$/) {
                                        last LEVEL4;
                                } elsif ($line =~ /^\s+-+$/) {
                                        next;
                                }
                        }
                }
        }
    }

    if($line =~ /^Physical Device information/) {

        LEVEL2: while($line = <$fh>) {
                if ($line =~ /^\s+-+$/) {
                        $line = <$fh>;
                        chomp $line;
                }
                if ($line =~ /^\s+Device\s+#(\d+)$/) {
                        $current_physical_drive = $1;
                } elsif ($line =~ /^\s+Device is (.*\w)/) {
                        $ctrl->{'physical drive'}{$current_physical_drive}{'Device is'} = $1;
                } elsif ($line =~ /^\s+(.*\w)\s+:\s+(.*)$/) {
                        $ctrl->{'physical drive'}{$current_physical_drive}{$1} = $2;
                } elsif ($line =~ /^\s+-+$/) {
                        last LEVEL3;
                } elsif ($line =~ /^$/) {
                        last LEVEL2;
                }
        }
    }

  }

  return $controller;
}



1;
