package Ocsinventory::Agent::Backend::OS::Linux::Storages;

use strict;
use Data::Dumper;

sub check {1}

######## TODO
# Do not remove, used by other modules
sub getFromUdev {
  	my @devs;

  	foreach (glob ("/dev/.udev/db/*")) {
    	my ($scsi_coid, $scsi_chid, $scsi_unid, $scsi_lun, $path, $device, $vendor, $model, $revision, $serial, $serial_short, $type, $bus, $capacity);
    	if (/^(\/dev\/.udev\/db\/.*)([sh]d[a-z]+)$/) {
      		$path = $1;
      		$device = $2;
      		open (PATH, $1 . $2);
      		while (<PATH>) {
        		if (/^S:.*-scsi-(\d+):(\d+):(\d+):(\d+)/) {
          			$scsi_coid = $1;
          			$scsi_chid = $2;
          			$scsi_unid = $3;
          			$scsi_lun = $4;
        		}
        		$vendor = $1 if /^E:ID_VENDOR=(.*)/; 
        		$model = $1 if /^E:ID_MODEL=(.*)/; 
        		$revision = $1 if /^E:ID_REVISION=(.*)/;
        		$serial = $1 if /^E:ID_SERIAL=(.*)/;
        		$serial_short = $1 if /^E:ID_SERIAL_SHORT=(.*)/;
        		$type = $1 if /^E:ID_TYPE=(.*)/;
        		$bus = $1 if /^E:ID_BUS=(.*)/;
      		}
      		$serial_short = $serial unless $serial_short =~ /\S/;
      		$capacity = getCapacity($device);
      		push (@devs, {NAME => $device, MANUFACTURER => $vendor, MODEL => $model, DESCRIPTION => $bus, TYPE => $type, DISKSIZE => $capacity, SERIALNUMBER => $serial_short, FIRMWARE => $revision, SCSI_COID => $scsi_coid, SCSI_CHID => $scsi_chid, SCSI_UNID => $scsi_unid, SCSI_LUN => $scsi_lun});
      		close (PATH);
    	}
  	}

  	return @devs;
}


sub getFromSysProc {
	my($dev, $file) = @_;
  	my $value;

  	foreach ("/sys/block/$dev/device/$file", "/proc/ide/$dev/$file") {
    	next unless open PATH, $_;
    	chomp(my $value = <PATH>);
    	$value =~ s/^(\w+)\W*/$1/;
    	return $value;
  	}
}


sub getCapacity {
	my ($dev) = @_;
  	my $cap;
  	chomp ($cap = `fdisk -s /dev/$dev 2>/dev/null`); #requires permissions on /dev/$dev
  	$cap = int ($cap/1000) if $cap;
  	return $cap;
}

sub getDescription {
  	my ($name, $manufacturer, $description, $serialnumber) = @_;
	# detected as USB by udev
	# TODO maybe we should trust udev detection by default?
  	return "USB" if (defined ($description) && $description =~ /usb/i);

  	if ($name =~ /^s/) { # /dev/sd* are SCSI _OR_ SATA
    	if ($manufacturer =~ /ATA/ || $serialnumber =~ /ATA/ || $description =~ /ATA/i) {
      	return  "SATA";
    	} else {
      		return "SCSI";
    	}
  	} else {
    	return "IDE";
  	}
}

sub getManufacturer {
  	my ($model) = @_;
  	if($model =~ /(maxtor|western|sony|compaq|hewlett packard|ibm|seagate|toshiba|fujitsu|lg|samsung|nec|transcend)/i) {
    	return ucfirst(lc($1));
  	} elsif ($model =~ /^HP/) {
    	return "Hewlett Packard";
  	} elsif ($model =~ /^WDC/) {
    	return "Western Digital";
  	} elsif ($model =~ /^ST/) {
    	return "Seagate";
  	} elsif ($model =~ /^HD/ or $model =~ /^IC/ or $model =~ /^HU/) {
    	return "Hitachi";
  	}
}

# some hdparm release generated kernel error if they are
# run on CDROM device
# http://forums.ocsinventory-ng.org/viewtopic.php?pid=20810
sub correctHdparmAvailable {
  	return unless can_run("hdparm");
  	my $hdparmVersion = `hdparm -V`;
  	if ($hdparmVersion =~ /^hdparm v(\d+)\.(\d+)(\.|$)/) {
    	return 1 if $1>9;
    	return 1 if $1==9 && $2>=15;
  	}	
  	return;
}


sub run {
	my $params = shift;
  	my $logger = $params->{logger};
  	my $common = $params->{common};

  	my $devices = {};

	my ($serial,$cap,$unit,$model,$manufacturer,$type,$desc,$firmware,$name);
	my @partitions;

  	# Get complementary information in hash tab
  	if (can_run ("lshal")) {
    	my %temp;
    	my $in = 0;
    	my $value;
    	foreach my $line (`lshal`) {
      		chomp $line;
      		if ( $line =~ s{^udi = '/org/freedesktop/Hal/devices/storage.*}{}) {
        		$in = 1;
        		%temp = ();
      		} elsif ($in == 1 and $line =~ s{^\s+(\S+) = (.*) \s*\((int|string|bool|string list|uint64)\)}{} ) {
        		my $key = $1;
        		my $value = $2;
        		$value =~ s/^'(.*)'\s*$/$1/; # Drop the quote
        		$value =~ s/\s+$//; # Drop the trailing white space

        		if ($key eq 'storage.serial') {
          			$temp{SERIALNUMBER} = $value;
        		} elsif ($key eq 'storage.firmware_version') {
          			$temp{FIRMWARE} = $value;
        		} elsif ($key eq 'block.device') {
          			$value =~ s/\/dev\/(\S+)/$1/;
          			$temp{NAME} = $value;
        		} elsif ($key eq 'info.vendor') {
          			$temp{MANUFACTURER} = $value;
        		} elsif ($key eq 'storage.model') {
          			$temp{MODEL} = $value;
        		} elsif ($key eq 'storage.drive_type') {
          			$temp{TYPE} = $value;
        		} elsif ($key eq 'storage.size') {
          			$temp{DISKSIZE} = int($value/(1024*1024) + 0.5);
        		}
      		} elsif ($in== 1 and $line eq '' and $temp{NAME}) {
        		$in = 0 ;
        		$devices->{$temp{NAME}} = {%temp};
      		}
		}
  	}

	if (can_run("smartctl")){
		open PARTINFO,'</proc/partitions' or warn;
	
		foreach(<PARTINFO>){
			if (/^\s*(\d*)\s*(\d*)\s*(\d*)\s*([sh]d[a-z]+)$/i){
				push(@partitions,$4);
			}
		}

		foreach my $dev (@partitions){
			$name = "/dev/$dev";
			my @sm = `smartctl -i $name`;
		
			for (@sm){
				if (/^Model\sFamily:\s*(.*)/i){
        			$manufacturer = $1;
        			if ($manufacturer =~ /(maxtor|western|sony|compaq|hewlett packard|ibm|seagate|toshiba|fujitsu|lg|samsung|nec|transcend)/i){
            			$desc=ucfirst(lc($1));
        			}
        			elsif ($manufacturer =~ /^HP/) {
            			$desc="Hewlett Packard";
        			}
        			elsif ($manufacturer =~ /^WDC/) {
            			$desc="Western Digital";
        			}
        			elsif ($manufacturer =~ /^ST/) {
            			$desc="Seagate";
        			}
        			elsif ($manufacturer =~ /^HD/ or $manufacturer =~ /^IC/ or $manufacturer =~ /^HU/) {
            			$desc="Hitachi";
					}
				}
				if (/^Device\sModel:\s*(.*)/i){
        			$model = $1;
    			}
    			if (/^Serial\sNumber:\s*(.*)/i){
        			$serial = $1;	
				}
				if (/^User\sCapacity:\s*(.*)\sbytes\s\[(.*)\s(.*)\]/i){
					$cap = $1;
					$unit = $3;
					$cap =~ s/,//g;
					if ($unit eq "MB") {
						$cap = int($cap/1024);
					} elsif ($unit eq "GB") {
						$cap = int($cap/1048576);
					} elsif ($unit eq "TB") {
						$cap = int($cap/1073741824);
					} else { 
						$cap = undef; 
					}
				}
				if (/^Firmware\sVersion:\s*(.*)/i){
					$firmware = $1;
				}
			}
			$common->addStorages({
				DESCRIPTION => $desc,
				DISKSIZE => $cap,
				FIRMWARE => $firmware,
				MANUFACTURER => $manufacturer,
				MODEL => $model,
				NAME => $name,
				SERIAL => $serial,
				TYPE => 'Disk',
			});
		}
	}
}

1;
