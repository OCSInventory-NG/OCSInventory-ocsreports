package Ocsinventory::Agent::Backend::OS::Generic::Lsusb::Usb;

use strict;
use Config;

my $vendor;
my $product;
my $interface;
my $bus;
my $device;
my $serial;
my $protocol;

sub run {

	my $params = shift;
	my $commons = $params->{common};

	foreach (`lsusb`) {
    	if (/^Bus\s+(\d+)\sDevice\s(\d*):.*/i) {
        	$bus = $1;
        	$device = $2;
        	next if ( grep(/$bus:$device/,qw(001:001 001:002 002:001 002:002 )) );
        	if (defined $bus && defined $device) {
            	my @detail = `lsusb -v -s $bus:$device`;
                foreach my $d (@detail) {
                    if ($d =~ /^\s*iManufacturer\s*\d\s(\w+)/i) {
                        $vendor = $1;
                    } elsif ($d =~ /^\s*idProduct\s*0x(\w+)\s(.*)/i) {
                        $product = $2;
                    } elsif ($d =~ /^\s*bInterfaceProtocol\s*\d\s(.*)/i) { 
                        $protocol = $1 unless defined $protocol || $1 eq 'None';
                    } elsif ($d =~ /^\siSerial\s*\d\s(.*)/i) {
                        $serial = $1;
                    }
                    #if (defined $protocol) {
                    #    $interface = $1 if ($protocol =~ /^USB/i);
                    #}
                }
        	}
    	}
		# Add information to $current
		$common->addUsb({
			'MANUFACTURER'	=>	$vendor,
			'CAPTION'	=>	$product,
			'DESCRIPTION'	=>	$protocol,
			'TYPE'		=> $type,
			'POINTTYPE'	=>	$pointtype,
		    'INTERFACE'	=>	$interface,
		});
		undef $protocol;
	}
}

1
