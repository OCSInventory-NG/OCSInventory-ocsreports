package Ocsinventory::Agent::Backend::OS::Generic::Lsusb::Usb;

use strict;
use Config;

use Data::Dumper;

my $vendor;
my $product;
my $interface;
my $bus;
my $device;
my $serial;
my $protocol;

sub run {

    my $params = shift;
    my $common = $params->{common};

    foreach (`lsusb`) {
        if (/^Bus\s+(\d+)\sDevice\s(\d*):.*/i) {
            $bus = $1;
            $device = $2;
            next if ( grep(/$bus:$device/,qw(001:001 001:002 002:001 002:002 )) );
            if (defined $bus && defined $device) {
                my @detail = `lsusb -v -s $bus:$device`;
                foreach my $d (@detail) {
                    #print Dumper($d);
                    if ($d =~ /^\s*iManufacturer\s*\d\s(\w+)/i) {
                        $vendor = $1;
                    } elsif ($d =~ /^\s*idProduct\s*0x(\w+)\s(.*)/i) {
                        $product = $2;
                    } elsif ($d =~ /^\s*bInterfaceProtocol\s*\d\s(.*)/i) { 
                        $protocol = $1 unless defined $protocol || $1 eq 'None';
                    } elsif ($d =~ /^\s*iSerial\s*\d\s(.*)/i) {
                        $serial = $1;
                    } elsif ($d =~ /^\s*iInterface\s*\d\s(\w+)\s(.*)/i){
                        $interface = $1;
                    }
                }
            }
        }
        # Add information to $current
        $common->addUsb({
            'MANUFACTURER'  =>  $vendor,
            'DESCRIPTION'   =>  $product,
            'TYPE'          =>  $protocol,
            'SERIAL'        => $serial,
            'INTERFACE'     => $interface,
        });
        undef $protocol;
    }
}

1
