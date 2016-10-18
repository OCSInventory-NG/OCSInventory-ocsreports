package Ocsinventory::Agent::Backend::OS::Linux::Storages::FusionIO;

use Ocsinventory::Agent::Backend::OS::Linux::Storages;
use strict;

sub trim { my $s = shift; $s =~ s/^\s+|\s+$//g; return $s };

sub check {
    my $params = shift;
    my $common = $params->{common};

	my $ret;
	# Do we have fio-status?
	if ($common->can_run("fio-status")) {
		foreach (`fio-status 2> /dev/null`) {
			if (/^fct(\d*).*/) {
				$ret=1;
				last;
			}
		}
	}
	return $ret;
}

sub run {
	
	my $params = shift;
	my $common = $params->{common};
	my $logger = $params->{logger};

	my ($pd, $serialnumber, $model, $capacity, $firmware, $description, $media, $manufacturer);

	foreach (`fio-status 2> /dev/null`) {
        if (/^fct(\d*).*/) {

            my $slot = $1;
            my $cmd = "fio-status /dev/fct$slot --field";
            $model = trim(`$cmd iom.board_name`);
            $description = trim(`$cmd adapter.product_name`);
            $media = trim('disk');
            $capacity = trim(`$cmd iom.size_bytes`);
            $serialnumber = 'OEM:'.trim(`$cmd adapter.oem_serial_number`).' FIO:'.trim(`$cmd adapter.fio_serial_number`).' IOM:'.trim(`$cmd iom.serial_number`);
            $firmware = trim(`$cmd iom.fw_current_version`.' rev '.`$cmd iom.fw_current_revision`);
            $logger->debug("Fusion-io: N/A, $manufacturer, $model, $description, $media, $capacity, $serialnumber, $firmware");
            
            $common->addStorages({
                NAME => $model,
                MANUFACTURER => 'Fusion-io',
                MODEL => $model,
                DESCRIPTION => $description,
                TYPE => $media,
                DISKSIZE => $capacity,
                SERIALNUMBER => $serialnumber,
                FIRMWARE => $firmware
            }); 
        }
    }
}
1;
