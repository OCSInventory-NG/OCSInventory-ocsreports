package Ocsinventory::Agent::Backend::OS::Generic::Dmidecode::Batteries;
use strict;

sub run {
  my $params = shift;
  my $common = $params->{common};

  my $dmidecode = `dmidecode`; # TODO retrieve error
  # some versions of dmidecode do not separate items with new lines
  # so add a new line before each handle
  $dmidecode =~ s/\nHandle/\n\nHandle/g;
  my @dmidecode = split (/\n/, $dmidecode);
  # add a new line at the end
  push @dmidecode, "\n";

  s/^\s+// for (@dmidecode);

  my $flag;

  my $location;
  my $manufacturer;
  my $manufacturedate;
  my $serialnumber;
  my $name;
  my $chemistry;
  my $designcapacity;
  my $designvoltage;
  my $sbdsversion;
  my $maxerror;
  my $oemspecific;
  my $numslot;

  foreach (@dmidecode) {

    if (/dmi type 22,/i) { # begining of Memory Device section
      $flag = 1;
      $numslot++;
    } elsif ($flag && /^$/) { # end of section
      $flag = 0;

      $common->addBatteries({

	  LOCATION => $location,
	  MANUFACTURER => $manufacturer,
	  MANUFACTUREDATE => $manufacturedate,
	  SERIALNUMBER => $serialnumber,
	  NAME => $name,
	  CHEMISTRY => $chemistry,
	  DESIGNCAPACITY => $designcapacity,
	  DESIGNVOLTAGE => $designvoltage,
	  SBDSVERSION => $sbdsversion,
	  MAXERROR => $maxerror,
	  OEMSPECIFIC => $oemspecific,
	});

      $location = $manufacturer = $manufacturedate = $serialnumber = $name = $chemistry = $designcapacity = $designvoltage = $sbdsversion = $maxerror = $oemspecific = undef;
    } elsif ($flag) { # in the section

      $location = $1 if /^Location:\s*(\S+)/i;
      $manufacturer = $1 if /^Manufacturer:\s*(.+)/i;
      $manufacturedate = $1 if /^Manufacture Date:\s*(.+)/i;
      $serialnumber = $1 if /^Serial Number:\s*(.+)/i;
      $name = $1 if /^Name:\s*(.+)/i;
      $chemistry = $1 if /^Chemistry:\s*(.+)/i;
      $designcapacity = $1 if /^Design Capacity:\s*(.+)/i;
      $designvoltage = $1 if /^Design Voltage:\s*(.+)/i;
      $sbdsversion = $1 if /^SBDS Version:\s*(.+)/i;
      $maxerror = $1 if /^Maximum Error:\s*(.+)/i;
      $oemspecific = $1 if /^OEM-specific Information:\s*(.+)/i;


    }
  }
}

1;
