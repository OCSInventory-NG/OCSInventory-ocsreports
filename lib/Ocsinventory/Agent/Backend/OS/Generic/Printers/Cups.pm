package Ocsinventory::Agent::Backend::OS::Generic::Printers::Cups;
use strict;

sub check {
    my $params = shift;
    my $common = $params->{common};
    # If we are on a MAC, Mac::SysProfile will do the job
    return if -r '/usr/sbin/system_profiler';
    return unless $common->can_load("Net::CUPS") && $Net::CUPS::VERSION >= 0.60;
    return 1;
}

sub run {

    my $params = shift;
    my $common = $params->{common};

    my $cups = Net::CUPS->new();
    my @destinations = $cups->getDestinations();
    my $printer;
    my $description;
    my $port;
    my $driver;


    foreach (@destinations) {
        $printer = $_->getName() unless $printer;
        $description = $_->getDescription() unless $description;
        $port = $_->getUri() unless $port;
        $driver = $_->getOptionValue("printer-make-and-model") unless $driver;
    
        # Just grab the default printer, if I use getDestinations, CUPS
        # returns all the printer of the local subnet (if it can)
        # TODO There is room for improvement here

        $common->addPrinter({
            NAME    => $printer,
            DESCRIPTION => $description,
            PORT => $port, 
            DRIVER => $driver
        });
        $printer = $description = $port = $driver = undef;
    }
}

1;
