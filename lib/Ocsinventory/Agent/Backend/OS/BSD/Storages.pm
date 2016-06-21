package Ocsinventory::Agent::Backend::OS::BSD::Storages;

use strict;

sub check { -x '/usr/local/bin/smartctl'; }

sub run {
    my $params = shift;
    my $common = $params->{common};
    my @values;
    my $devlist;
    my $osname = `uname -s`;
    if (chomp($osname) eq "FreeBSD") {
        $devlist = `/sbin/sysctl -n kern.disks`;
    } else {
        $devlist = `/sbin/sysctl -n hw.disknames`;
    }
    chomp($devlist);
    my @devices = split( /\s+/, $devlist );
    for my $dev (@devices) {
        open( CMD, "smartctl -i /dev/$dev |" );
        my ( $manufacturer, $serialnumber, $model, $size, $firmware, $type, $desc, $luwwnid );
        while (<CMD>) {
            chomp();
            if (/^Vendor:\s+|^Model Family:\s+/i) {
                $manufacturer = ( split( /:\s+/, $_ ) )[1];
            }
            if (/^Product:\s+|^Device Model:\s+/i) {
                $model = ( split( /:\s+/, $_ ) )[1];
            }
            if (/^Serial number:\s+/i) {
                $serialnumber = ( split( /:\s+/, $_ ) )[1];
            }
            if (/^User Capacity:\s+/i) {
                s/,//g;
                my $out = ( split( /:\s+/, $_ ) )[1];
                $size = ( split( ' ', $out ) )[0] / ( 1024 * 1024 );
            }
            if (/^Revision:\s+|^Firmware Version:\s+/) {
                $firmware = ( split( /:\s+/, $_ ) )[1];
            }
            if (/^Device type:\s+/) { $type = ( split( /:\s+/, $_ ) )[1]; }
            if (/^Transport protocol:\s+/) {
                $desc = ( split( /:\s+/, $_ ) )[1];
            }
            if (/^LU WWN Device Id:\s+/) {
                $luwwnid = ( split( /:\s+/, $_ ) )[1];
            }
        }
        $common->addStorages({
            NAME         => $dev,
            MANUFACTURER => $manufacturer,
            MODEL        => $model,
            DESCRIPTION  => $desc,
            TYPE         => $type,
            DISKSIZE     => $size,
            SERIALNUMBER => $serialnumber,
            FIRMWARE     => $firmware,
            SCSI_UNID    => $luwwnid,
        });
    }
}

1;
