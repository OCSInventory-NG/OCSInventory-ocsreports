package Ocsinventory::Agent::Backend::OS::Linux::Storages;

use strict;
use XML::Simple;

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
    $cap = int ($cap*1024) if $cap;
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
    #if($model =~ /(maxtor|western|sony|compaq|hewlett packard|ibm|seagate|toshiba|fujitsu|lg|samsung|nec|transcend)/i) {
    #    return ucfirst(lc($1));
    if ($model =~ /^(IBM|LG|NEC$)/){
        return $1;
    } elsif ($model =~ /(maxtor|western|sony|compaq|hewlett packard|ibm|seagate|toshiba|fujitsu|lg|samsung|nec$|transcend)/i) {
        $model = lc($1);
        $model = s/\b(\w)/\u$1/g;
        return $model;
    } elsif ($model =~ /^HP/) {
        return "Hewlett-Packard";
    } elsif ($model =~ /^WD/) {
        return "Western Digital";
    } elsif ($model =~ /^ST/) {
        return "Seagate";
    } elsif ($model =~ /^(HD|IC|HU)/) {
        return "Hitachi";
    } elsif ($model =~ /^NECVMWar/) {
        return "VMware";
    } else {
        return $model;
    }
}

sub getMultipathDisks {
    my $params = shift;
    my $common = $params->{common};
    return unless ($common->can_run("multipath"));
    my @mpList = `multipath -l`;
    my @devs;
	my $volume;
	my $serial;
    my $dm;
	my $manufacturer;
	my $model;
    foreach my $line (@mpList) {
        if($line =~ /^([\w\d]+)\s\((.*)\)\s(dm-\d+)\s(\w+)\s+,([\w\d\s]+)$/i) {
            $volume = $1;
			$serial = $2;
			$dm = $3;
			$manufacturer = $4;
			$model = $5;
        }
		if($line =~ /size=(\d+)(\w+)\s/) {
			my $size = $1;
			my $unit = $2;
			# conversion to mebibyte
			my %conversion = (
				"T" => 1000**4,
				"G" => 1000**3,
				"M" => 1000**2,
				"K" => 1000,
			);
			if($conversion{$unit}) {
				$size = $size / $conversion{$unit} * 2**20;
			}
			else {
				$size = $size." ".$unit;
			}
			push (@devs, {NAME=>$dm, DESCRIPTION=>$volume, TYPE=>"Multipath volume", MODEL=>$model, SERIALNUMBER=>$serial, MANUFACTURER=>$manufacturer});
		}
        if($line =~ /(sd[a-z]+)/i) {
            push (@devs, {NAME=>$1, DESCRIPTION=>"Child of $dm", TYPE=>"Multipath child"});
        }
    }
    return @devs;

}

# some hdparm release generated kernel error if they are
# run on CDROM device
# http://forums.ocsinventory-ng.org/viewtopic.php?pid=20810
sub correctHdparmAvailable {
    my $params = shift;
    my $common = $params->{common};
    return unless $common->can_run("hdparm");

    my $hdparmVersion = `hdparm -V`;
    if ($hdparmVersion =~ /^hdparm v(\d+)\.(\d+)(\.|$)/) {
        return 1 if $1>9;
        return 1 if $1==9 && $2>=15;
    }    
    return;
}

# get available md softraid devices
sub getMdDevices {
    return unless ( open(my $fh, '<:encoding(UTF-8)', '/proc/mdstat') );
    my @lines = <$fh>;
    close($fh);
    my $devName;
    my $raidLevel;
    my @devs;
    foreach (@lines) {
        chomp($_);
        if (/^(md\d*)\s*:\s*\w*\s*(raid\d)/) {
            $devName = $1;
            $raidLevel = $2;
            push (@devs, {NAME => $devName, MODEL => $raidLevel});
        }
    }
    return @devs;
}

# get available block devices from /dev
sub getFromDev {
    my @devs;
    my @disks;
    my $dir = "/dev";

    opendir (my $dh, $dir) or die $!;
    @disks = grep{/^sd[a-z][a-z]?$|^vd[a-z][a-z]?$|^sr\d+$/} readdir($dh);
    foreach (@disks) {
        push(@devs, {NAME => $_});
    }
    return @devs;
}

# get data from lshw
sub getFromLshw {
    my $params = shift;
    my $common = $params->{common};
    return unless $common->can_run("lshw");
    my @devs;
    my @inputlines = `lshw -class disk -xml -quiet`;
    return unless $inputlines[0] =~ /xml/i;
    my $foundcdroms = 0;
    my $input;
    foreach (@inputlines) {
        if ( /\<node id=\"cdrom\"/ ) {
            s/<node id="cdrom"/<node id="cdrom:$foundcdroms"/;
            $foundcdroms++;
        }
        $input .= $_;
    }
    if ($input =~ !/<list>/g) {                                 # adding "<list>" root element because
        $input =~ s/\?>/\?>\n<list>/;                           # prior to version B.02.16, "lshw -class disk -xml" produces xml output
        $input .= "\n</list>";                                  # without the "<list>" root element, which does not parse correctly.
    }
    my $xml = new XML::Simple;
    my $data = $xml->XMLin($input);
    my $nodes = $data->{list}->{node};

    foreach my $device (sort keys %$nodes) {
        my $description = "";
        my $size = 0;
        my $sizeUnits = "";
        my $name = "not set";
        my $type = "";
        my $vendor = "";
        my $model = "";
        my $serial = "";
        my $revision = "";
        
        if($nodes->{$device}->{description}) {
            $description = $nodes->{$device}->{description};
        }
        if($nodes->{$device}->{size}) {
            my %units = ('bytes', 1, 'kilobytes', 10**3, 'megabytes', 10**6, 'gigabytes', 10**9, 'terabytes', 10**12);
            $sizeUnits = $nodes->{$device}->{size}->{units};
            $size = $nodes->{$device}->{size}->{content};
            $size = $size * $units{$sizeUnits};
            $size = sprintf "%i", $size;
        }
        if($nodes->{$device}->{logicalname}) {
            $name = $nodes->{$device}->{logicalname};
            if(ref($name) eq 'ARRAY') {
                foreach (@{$name}) {
                    if(!readlink $_) {
                        $name = $_;
                        last;
                    }
                }
            }
            $name =~ s/\/dev\///;
        }
        if($nodes->{$device}->{type}) {
            $type = $nodes->{$device}->{type};
        }
        if($nodes->{$device}->{vendor}) {
            $vendor = $nodes->{$device}->{vendor};
        }
        if($nodes->{$device}->{model}) {
            $model = $nodes->{$device}->{model};
        }
        if($nodes->{$device}->{serial}) {
            $serial = $nodes->{$device}->{serial};
        }
        push (@devs, {NAME => $name,
                        MANUFACTURER => getManufacturer($vendor),
                        MODEL => $model,
                        DESCRIPTION => $description,
                        TYPE => $type,
                        DISKSIZE => $size,
                        SERIALNUMBER => $serial,
                        FIRMWARE => $revision});
    }
    return @devs;
}

# get data from lsscsi
sub getFromLsscsi {
    my $params = shift;
    my $common = $params->{common};
    return unless ($common->can_run("lsscsi"));
    my @devs;
    my ($id, $type, $vendor, $model, $rev, $device);
    foreach my $line (`lsscsi`)     {
        ($id, $type, $vendor, $model, $rev, $device) = unpack ('a13a8a9a17a6a15', $line);
        $vendor =~ s/\s*$//;
        $type =~ s/\s*$//;
        $model =~ s/\s*$//;
        my @columns     = split /\s+/, $line;
        my $deviceName  = $columns[-1];
        $deviceName     =~ s/\/dev\///;
        #debug print Dumper {NAME => $deviceName, MANUFACTURER => $vendor, TYPE => $type, MODEL => $model};
        if ($type =~ /cd\/dvd|disk/) {
            push (@devs, {NAME => $deviceName, MANUFACTURER => getManufacturer($vendor), TYPE => $type, MODEL => $model});
        }
    }
    return @devs;
}

# get data from lsblk
sub getFromLsblk {
    my $params = shift;
    my $common = $params->{common};
    return unless ($common->can_run("lsblk"));
    my @devs;
    foreach my $line (`lsblk -ldbn`) {
        my @columns     = split /\s+/, $line;
        my $deviceName  = $columns[0];
        my $size                = $columns[3];
        my $type                = $columns[5];
        $size = "" if ($type =~ /rom/);
        push (@devs, {NAME => $deviceName, TYPE => $type, DISKSIZE => $size});
    }
    return @devs;
}

# get data from smartctl
sub getFromSmartctl {
    my $params = shift;
    my $common = $params->{common};
    return unless ($common->can_run("smartctl"));
    my ($devices) = @_;
    my @devs;
    my $vendor;
    my $product;
    my $revision;
    my $size;
    my $type;
    my $serialnum;
    my $description;
    foreach my $device (keys %$devices)     {
        $vendor         = "";
        $product        = "";
        $revision       = "";
        $size           = "";
        $type           = "";
        $serialnum      = "";
        $description    = "";
        my $devName = $devices->{$device}->{NAME};
        foreach my $line (`smartctl -i /dev/$devName`) {
            chomp($line);
            if($line =~ m/Vendor:\s+(\S+.*)\s*$/i) {
                $vendor = $1;
            }
            elsif($line =~ m/Product:\s+(\S+.*)\s*$/i) {
                $product = $1;
            }
            elsif($line =~ m/Revision:\s+(\S+.*)\s*$/i) {
                $revision = $1;
            }
            elsif($line =~ m/Firmware Version:\s+(\S+.*)\s*$/i) {
                $revision = $1;
            }
            elsif($line =~ m/Serial Number:\s+(\S+.*)\s*$/i) {
                $serialnum = $1;
            }
            elsif($line =~ m/User Capacity:\s+([\d\.,]+)\s+bytes/i) {
                $size = $1;
                $size =~ s/[\.,]//g;
            }
            elsif($line =~ m/Device type:\s+(\S+.*)\s*$/i) {
                $type = $1;
            }
            elsif($line =~ m/Rotation Rate:\s+(\S.*)\s*/i) {
                $description = $1;
            }
        }
        push (@devs, {NAME => $devName,
                    MANUFACTURER => getManufacturer($vendor),
                    MODEL => $product,
                    FIRMWARE => $revision,
                    TYPE => $type,
                    DISKSIZE => $size,
                    SERIALNUMBER => $serialnum,
                    DESCRIPTION => $description});
    }
    return @devs;
}

# get data from UDEV
sub getFromuDev2 {
    my $params = shift;
    my $common = $params->{common};
    return unless ($common->can_run("udevinfo") or $common->can_run("udevadm"));
    my ($devices) = @_;
    my @input;
    my @devs;
    my $type = "";
    my $model = "";
    my $vendor = "";
    my $firmware = "";
    my $serial = "";
    my $serial_short = "";
    my $serial_scsi = "";
    my $serial_md = "";

    foreach my $device (keys %$devices)     {
        $type = "";
        $model = "";
        $vendor = "";
        $firmware = "";
        $serial = "";
        $serial_short = "";
        $serial_scsi = "";
        $serial_md = "";
        my $devName = $devices->{$device}->{NAME};
        if($common->can_run("udevadm")) {
            @input = `udevadm info -q all -n /dev/$devName`;
        }
        else {
            @input = `udevinfo -q all -n /dev/$devName`;
        }
        foreach my $line (@input) {
            if($line =~ m/ID_TYPE=(\S+.*)\s*$/){
                $type = $1;
            }
            elsif($line =~ m/ID_MODEL=(\S+.*)\s*$/) {
                $model = $1;
                $model =~ s/_/ /g;
            }
            elsif($line =~ m/ID_VENDOR=(\S+.*)\s*$/) {
                $vendor = $1;
            }
            elsif($line =~ m/ID_REVISION=(\S+.*)\s*$/) {
                $firmware = $1;
            }
            elsif($line =~ m/ID_SERIAL_SHORT=(\S+.*)\s*$/) {
                $serial_short = $1;
            }
            elsif($line =~ m/ID_SCSI_SERIAL=(\S+.*)\s*$/) {
                $serial_scsi = $1;
            }
            elsif($line =~ m/ID_SERIAL=(\S+.*)\s*$/) {
                $serial = $1;
            }
            if($line =~ m/MD_LEVEL=(\S+.*)\s*$/) {
                $model = $1;
            }
            elsif($line =~ m/MD_METADATA=(\d\.?\d?)/) {
                $firmware = $1;
                $firmware = "MD METADATA ".$firmware;
            }
             elsif($line =~ m/MD_UUID=(\S+.*)\s*$/) {
                $serial_md = $1;
            }
        }
        $serial = $serial_short unless $serial_short eq ""; # prefer serial_short over serial
        $serial = $serial_scsi unless $serial_scsi eq "";
        $serial = $serial_md unless $serial_md eq "";

        if($devName =~ /md\d+/) { # if device is a multiple disk softraid
            $type = "MD";
            $vendor = "Linux";
        }
        push (@devs, {NAME => $devName,
                    TYPE => $type,
                    MODEL => $model,
                    MANUFACTURER => getManufacturer($vendor),
                    FIRMWARE => $firmware,
                    SERIALNUMBER => $serial});
    }
    return @devs;
}

sub run {
    my $params = shift;
    my $common = $params->{common};
    my $logger = $params->{logger};
    my $devices = {};
    my ($serial,$cap,$unit,$model,$manufacturer,$type,$desc,$firmware,$name);
    my @partitions;

    # Get complementary information in hash tab
    if ($common->can_run ("lshal")) {
        my %temp;
        my $in = 0;
        my $value;
        foreach my $line (`lshal`) {
            chomp $line;
            if ($line =~ s{^udi = '/org/freedesktop/Hal/devices/storage.*}{}) {
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
                      $temp{MANUFACTURER} = getManufacturer($value);
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

    foreach my $device (getMultipathDisks($params)) {
        my $name = $device->{NAME};
        foreach my $f ("NAME", "MANUFACTURER", "MODEL", "SERIALNUMBER", "DESCRIPTION", "TYPE") {
            $devices->{$name}->{$f} = $device->{$f};
        }
    }
    
    foreach my $device (getFromDev($params)) {
        my $name = $device->{NAME};
        foreach my $f ("NAME") {
            if($devices->{$name}->{$f} eq "") {
                #debug print "getFromDev $name $f device->{\$f} $device->{$f}\n";
                $devices->{$name}->{$f} = $device->{$f};
            }
        }
    }
    
    foreach my $device (getMdDevices($params)) {
        my $name = $device->{NAME};
        foreach my $f ("NAME", "MODEL") {
            if ($devices->{$name}->{$f} eq "") {
                $devices->{$name}->{$f} = $device->{$f};
            }
        }
    }
    
    foreach my $device (getFromSmartctl($params,$devices)) {
        my $name = $device->{NAME};
        foreach my $f ("NAME", "MANUFACTURER", "TYPE", "MODEL", "DISKSIZE", "FIRMWARE", "SERIALNUMBER", "DESCRIPTION") {
            if ($devices->{$name}->{$f} eq "") {
                #debug print "getFromSmartctl $name $f device->{\$f} $device->{$f}\n";
                $devices->{$name}->{$f} = $device->{$f};
            }
        }
    }
    
    foreach my $device (getFromuDev2($params,$devices)) {
        my $name = $device->{NAME};
        foreach my $f ("NAME", "MANUFACTURER", "TYPE", "MODEL", "FIRMWARE", "SERIALNUMBER") {
            if  ($devices->{$name}->{$f} eq "") {
                #debug print "getFromuDev2 $name $f device->{\$f} $device->{$f}\n";
                $devices->{$name}->{$f} = $device->{$f};
            }
        }
    }
    
    foreach my $device (getFromLshw($params)) {
        my $name = $device->{NAME};
        foreach my $f ("NAME", "MANUFACTURER", "MODEL", "DESCRIPTION", "TYPE", "DISKSIZE", "SERIALNUMBER", "FIRMWARE") {
            if ($devices->{$name}->{$f} eq "") {
                #debug print "getFromLshw $name $f device->{\$f} $device->{$f}\n";
                $devices->{$name}->{$f} = $device->{$f};
            }
        }
    }
    
    foreach my $device (getFromLsblk($params)) {
        my $name = $device->{NAME};
        foreach my $f ("NAME", "DISKSIZE", "TYPE") {
            if ($devices->{$name}->{$f} eq "") {
                #debug print "getFromLsblk $name $f device->{\$f} $device->{$f}\n";
                $devices->{$name}->{$f} = $device->{$f};
            }
        }
    }
    
    foreach my $device (getFromUdev($params)) {
        my $name = $device->{NAME};
        foreach my $f ("NAME", "MANUFACTURER", "MODEL", "DESCRIPTION", "TYPE", "DISKSIZE", "SERIALNUMBER", "FIRMWARE", "SCSI_COID", "SCSI_CHID", "SCSI_UNID", "SCSI_LUN") {
            if ($devices->{$name}->{$f} eq "") {
                #debug print "getFromuDev $name $f device->{\$f} $device->{$f}\n";
                $devices->{$name}->{$f} = $device->{$f};
            }
        }
    }
    
    foreach my $device (getFromLsscsi($params)) {
        my $name = $device->{NAME};
        foreach my $f ("NAME", "MANUFACTURER", "TYPE", "MODEL") {
            if ($devices->{$name}->{$f} eq "") {
                #debug print "getFromLsscsi $name $f device->{\$f} $device->{$f}\n";
                $devices->{$name}->{$f} = $device->{$f};
            }
        }
    }
    
    

    # if (can_run("smartctl")){
        # open PARTINFO,'</proc/partitions' or warn;
        # foreach(<PARTINFO>){
            # if (/^\s*(\d*)\s*(\d*)\s*(\d*)\s*([sh]d[a-z]+)$/i){
                # push(@partitions,$4);
            # }
        # }

        # foreach my $dev (@partitions){
            # $name = "/dev/$dev";
            # my @sm = `smartctl -i $name`;
            # for (@sm){
                # if (/^Model\sFamily:\s*(.*)/i){
                    # $desc = $1;
                    # if ($desc =~ /^(IBM|LG|NEC)/){
                        # $manufacturer = $1;
                    # } elsif ($desc =~ /^(HP|Hewlett[ -]Packard)/) {
                        # $manufacturer="Hewlett-Packard";
                    # } elsif ($desc =~ /(maxtor|western digital|sony|compaq|seagate|toshiba|fujitsu|lg|samsung|nec|transcend)/i){
                        # $manufacturer = lc($1);
                        # $manufacturer =~ s/\b(\w)/\u$1/g;
                    # } elsif ($desc =~ /^WD/) {
                        # $manufacturer="Western Digital";
                    # } elsif ($desc =~ /^ST/) {
                        # $manufacturer="Seagate";
                    # } elsif ($desc =~ /^(HD|IC|HU)/) {
                        # $manufacturer="Hitachi";
                    # } elsif ($desc =~ /^SandForce/) {
                        # $manufacturer="Corsair";
                    # }
                # }

                # if (/^Device\sModel:\s*(.*)/i){
                    # $model = $1;
                    # if (!defined $manufacturer){
                        # if ($model =~ /^(hitachi|toshiba|samsung)/) {
                           # $manufacturer = lc($1);
                           # $manufacturer = s/\b(\w)/\u$1/g;
                        # } elsif ($model =~ /^ST/) {
                           # $manufacturer = "Seagate";
                        # } elsif ($model =~ /^HD/) {
                           # $manufacturer = "Hitachi";
                        # }
                    # }
                # }

                # if (/^Serial\sNumber:\s*(.*)/i){
                    # $serial = $1;
                # }

                # if (/^User\sCapacity:\s*(.*)\sbytes\s\[(.*)\s(.*)\]/i){
                    # $cap = $1;
                    # $unit = $3;
                    # $cap =~ s/,//g;
                    # if ($unit eq "MB") {
                        # $cap = int($cap/1024);
                    # } elsif ($unit eq "GB") {
                        # $cap = int($cap/1048576);
                    # } elsif ($unit eq "TB") {
                        # $cap = int($cap/1073741824);
                    # } else { 
                        # $cap = undef; 
                    # }
                # }
                # if (/^Firmware\sVersion:\s*(.*)/i){
                    # $firmware = $1;
                # }
            # }
            # $devices->{}
            # $common->addStorages({
                # DESCRIPTION => $desc,
                # DISKSIZE => $cap,
                # FIRMWARE => $firmware,
                # MANUFACTURER => $manufacturer,
                # MODEL => $model,
                # NAME => $name,
                # SERIALNUMBER => $serial,
                # TYPE => 'Disk',
            # });
        # }
    # }
    
    foreach my $device (sort (keys %$devices)) {
        if($devices->{$device}->{TYPE} =~ /(CD)|(DVD)|(BD)/i) {
                $devices->{$device}->{DISKSIZE} = "0";
          }
        elsif($devices->{$device}->{DISKSIZE}) {
                        $devices->{$device}->{DISKSIZE} = $devices->{$device}->{DISKSIZE} * 10**-6; # we need MB for the view
        }
        if(!$devices->{$device}->{DESCRIPTION}) {
                $devices->{$device}->{DESCRIPTION} = getFromSysProc($device, "description");
        }
      if (!$devices->{$device}->{MANUFACTURER} or $devices->{$device}->{MANUFACTURER} eq 'ATA'or $devices->{$device}->{MANUFACTURER} eq '') {
        $devices->{$device}->{MANUFACTURER} = getManufacturer($devices->{$device}->{MODEL});
      }

      if ( ! $devices->{$device}->{DISKSIZE} ) {
        $devices->{$device}->{DISKSIZE} = getCapacity($devices->{$device}->{NAME}) * 10**-6;
      }
      if ($devices->{$device}->{CAPACITY} =~ /^cdrom$/) {
        $devices->{$device}->{CAPACITY} = getCapacity($devices->{$device}->{NAME}) * 10**-6;
      }
      $common->addStorages($devices->{$device});
    }
}

1;
