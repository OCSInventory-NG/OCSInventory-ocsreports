package Ocsinventory::Agent::Backend::OS::MacOS::Storages;

use strict;

sub check {
  my $params = shift;
  my $common = $params->{common};
  return $common->can_load('Mac::SysProfile');
}

sub getManufacturer {
  my $model = shift;
  if($model =~ /(maxtor|western|sony|compaq|hewlett packard|ibm|seagate|toshiba|fujitsu|lg|samsung|nec|transcend|matshita|pioneer|hitachi)/i) {
    return ucfirst(lc($1));
  }
  elsif ($model =~ /^HP/) {
    return "Hewlett Packard";
  }
  elsif ($model =~ /^WDC/) {
    return "Western Digital";
  }
  elsif ($model =~ /^ST/) {
    return "Seagate";
  }
  elsif ($model =~ /^HD/ or $model =~ /^IC/ or $model =~ /^HU/) {
    return "Hitachi";
  }
}

sub run {

  my $params = shift;
  my $common = $params->{common};
  my $logger = $params->{logger};

  my $devices = {};

  my $profile = Mac::SysProfile->new();

  # Get SATA Drives
  my $sata = $profile->gettype('SPSerialATADataType');

  if ( ref($sata) eq 'ARRAY') {
  
    foreach my $storage ( @$sata ) {
      next unless ( ref($storage) eq 'HASH' );

      my $description;
      if ( $storage->{'_name'} =~ /DVD/i || $storage->{'_name'} =~ /CD/i ) {
        $description = 'CD-ROM Drive';
      }
      else {
        $description = 'Disk drive';
      }

      my $size = $storage->{'size'};
      if ($size =~ /GB/) {
        $size =~ s/ GB//;
        $size *= 1024;
      }
      if ($size =~ /TB/) {
        $size =~ s/ TB//;
        $size *= 1048576;
      }

      my $manufacturer = getManufacturer($storage->{'_name'});

      my $model = $storage->{'device_model'};
      $model =~ s/\s*$manufacturer\s*//i;

      $devices->{$storage->{'_name'}} = {
        NAME => $storage->{'name'},
        SERIALNUMBER => $storage->{'device_serial'},
        DISKSIZE => $size,
        FIRMWARE => $storage->{'device_revision'},
        MANUFACTURER => $manufacturer,
        DESCRIPTION => $description,
        MODEL => $model
      };
    }
  } 

  # Get PATA Drives
  my $pata = $profile->gettype('SPParallelATADataType');
  
  if ( ref($sata) eq 'ARRAY') {
    foreach my $storage ( @$pata ) {
      next unless ( ref($storage) eq 'HASH' );
      
      my $description;
      if ( $storage->{'_name'} =~ /DVD/i || $storage->{'_name'} =~ /CD/i ) {
       $description = 'CD-ROM Drive';
      }
      else {
        $description = 'Disk drive';
      }
      
      my $manufacturer = getManufacturer($storage->{'_name'});
      
      my $model = $storage->{'device_model'};
      
      my $size;
      
      $devices->{$storage->{'_name'}} = {
        NAME => $storage->{'_name'},
        SERIAL => $storage->{'device_serial'},
        DISKSIZE => $size,
        FIRMWARE => $storage->{'device_revision'},
        MANUFACTURER => $manufacturer,
        DESCRIPTION => $description,
        MODEL => $model
      };
    
    }
  }

  foreach my $device ( keys %$devices ) {
    $common->addStorages($devices->{$device});
  }
}

1;
