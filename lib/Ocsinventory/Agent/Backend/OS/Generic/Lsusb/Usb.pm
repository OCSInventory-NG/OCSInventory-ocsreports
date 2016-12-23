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
my $id;

sub run {

    my $params = shift;
    my $common = $params->{common};

    foreach (`lsusb`) {
        if (/^Bus\s+(\d+)\sDevice\s(\d*):\sID\s(\d+):(\d+)*/i) {
            next if (grep (/$4/,qw(0001 0002 0024)));
            $bus=$1;
            $device=$2;
            #if (defined $bus && defined $device) {
            my @detail = `lsusb -v -s $bus:$device 2>/dev/null`;
            foreach my $d (@detail) {
                if ($d =~ /^\s*iManufacturer\s*\d+\s*(.*)/i) {
                    $vendor = $1;
                } elsif ($d =~ /^\s*iProduct\s*\d+\s*(.*)/i) {
                    $product = $1;
                } elsif ($d =~ /^\s*iSerial\s*\d+\s(.*)/i) {
                    $serial = $1;
                #} elsif ($d =~ /^\s*bInterfaceProtocol\s*\d\s(.*)/i) { 
                } elsif ($d =~ /^\s*bInterfaceClass\s*\d+\s*(.*)/i) { 
                    #$protocol = $1 unless defined $protocol || $1 eq 'None';
                    $protocol = $1;
                #} elsif ($d =~ /^\s*iInterface\s*\d\s(\w+)\s(.*)/i){
                } elsif ($d =~ /^\s*bInterfaceSubClass\s*\d+\s(.*)/i){
                    $interface = $1;
                }
            }
            # Add information to $current
            $common->addUsb({
                DESCRIPTION   => $product,
                INTERFACE     => $interface,
                MANUFACTURER  => $vendor,
                SERIAL        => $serial,
                TYPE          => $protocol,
           });
        }
    }
}

1;
