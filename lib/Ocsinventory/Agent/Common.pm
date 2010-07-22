###############################################################################
## OCSINVENTORY-NG
## Copyleft Guillaume PROTET 2010
## Web : http://www.ocsinventory-ng.org
##
## This code is open source and may be copied and modified as long as the source
## code is always made freely available.
## Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
################################################################################

package Ocsinventory::Agent::Common;

use strict;
no strict 'refs';
use warnings;

##################################################################################
#  Ocsinventory::Agent::Common is use to give common methods to other modules   #
##################################################################################

sub new {

  my $self = {};
  $self->{xmltags}={};

  bless $self;
}

sub setSnmpCommons {
  my ($self,$args)=@_; 
  my $xmltags=$self->{xmltags};

  my $ip = $args->{IP};
  my $name = $args->{NAME};
  my $description = $args->{DESCRIPTION};
  my $contact = $args->{CONTACT};
  my $location = $args->{LOCATION};
  my $uptime = $args->{UPTIME};
  my $domain = $args->{DOMAIN};

  $xmltags->{IP} = [$ip?$ip:''];
  $xmltags->{NAME} = [$name?$name:''];
  $xmltags->{DESCRIPTION}=[$description?$description:''];
  $xmltags->{CONTACT} = [$contact?$contact:''];
  $xmltags->{LOCATION} =[$location?$location:''];
  $xmltags->{UPTIME} = [$uptime?$uptime:''];
  $xmltags->{DOMAIN} = [$domain?$domain:''];
}

sub addController {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $driver = $args->{DRIVER};
  my $name = $args->{NAME};
  my $manufacturer = $args->{MANUFACTURER};
  my $pciid = $args->{PCIID};
  my $pcislot = $args->{PCISLOT};
  my $type = $args->{TYPE};

  push @{$xmltags->{CONTROLLERS}},
  {
    DRIVER => [$driver?$driver:''],
    NAME => [$name],
    MANUFACTURER => [$manufacturer],
    PCIID => [$pciid?$pciid:''],
    PCISLOT => [$pcislot?$pcislot:''],
    TYPE => [$type],
  };
}


sub addModem {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $description = $args->{DESCRIPTION};
  my $name = $args->{NAME};

  push @{$xmltags->{MODEMS}},
  {

    DESCRIPTION => [$description],
    NAME => [$name],

  };
}

sub addDrive {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $createdate = $args->{CREATEDATE};
  my $free = $args->{FREE};
  my $filesystem = $args->{FILESYSTEM};
  my $label = $args->{LABEL};
  my $serial = $args->{SERIAL};
  my $total = $args->{TOTAL};
  my $type = $args->{TYPE};
  my $volumn = $args->{VOLUMN};

  push @{$xmltags->{DRIVES}},
  {
    CREATEDATE => [$createdate?$createdate:''],
    FREE => [$free?$free:''],
    FILESYSTEM => [$filesystem?$filesystem:''],
    LABEL => [$label?$label:''],
    SERIAL => [$serial?$serial:''],
    TOTAL => [$total?$total:''],
    TYPE => [$type?$type:''],
    VOLUMN => [$volumn?$volumn:'']
  };
}

sub addStorages {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $logger = $self->{logger};

  my $description = $args->{DESCRIPTION};
  my $disksize = $args->{DISKSIZE};
  my $manufacturer = $args->{MANUFACTURER};
  my $model = $args->{MODEL};
  my $name = $args->{NAME};
  my $type = $args->{TYPE};
  my $serial = $args->{SERIAL};
  my $serialnumber = $args->{SERIALNUMBER};
  my $firmware = $args->{FIRMWARE};
  my $scsi_coid = $args->{SCSI_COID};
  my $scsi_chid = $args->{SCSI_CHID};
  my $scsi_unid = $args->{SCSI_UNID};
  my $scsi_lun = $args->{SCSI_LUN};

  $serialnumber = $serialnumber?$serialnumber:$serial;

  push @{$xmltags->{STORAGES}},
  {

    DESCRIPTION => [$description?$description:''],
    DISKSIZE => [$disksize?$disksize:''],
    MANUFACTURER => [$manufacturer?$manufacturer:''],
    MODEL => [$model?$model:''],
    NAME => [$name?$name:''],
    TYPE => [$type?$type:''],
    SERIALNUMBER => [$serialnumber?$serialnumber:''],
    FIRMWARE => [$firmware?$firmware:''],
    SCSI_COID => [$scsi_coid?$scsi_coid:''],
    SCSI_CHID => [$scsi_chid?$scsi_chid:''],
    SCSI_UNID => [$scsi_unid?$scsi_unid:''],
    SCSI_LUN => [$scsi_lun?$scsi_lun:''],

  };
}

sub addMemory {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $capacity = $args->{CAPACITY};
  my $speed =  $args->{SPEED};
  my $type = $args->{TYPE};
  my $description = $args->{DESCRIPTION};
  my $caption = $args->{CAPTION};
  my $numslots = $args->{NUMSLOTS};

  my $serialnumber = $args->{SERIALNUMBER};

  push @{$xmltags->{MEMORIES}},
  {

    CAPACITY => [$capacity?$capacity:''],
    DESCRIPTION => [$description?$description:''],
    CAPTION => [$caption?$caption:''],
    SPEED => [$speed?$speed:''],
    TYPE => [$type?$type:''],
    NUMSLOTS => [$numslots?$numslots:0],
    SERIALNUMBER => [$serialnumber?$serialnumber:'']

  };
}

sub addPorts{
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $caption = $args->{CAPTION};
  my $description = $args->{DESCRIPTION};
  my $name = $args->{NAME};
  my $type = $args->{TYPE};


  push @{$xmltags->{PORTS}},
  {

    CAPTION => [$caption?$caption:''],
    DESCRIPTION => [$description?$description:''],
    NAME => [$name?$name:''],
    TYPE => [$type?$type:''],

  };
}

sub addSlot {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $description = $args->{DESCRIPTION};
  my $designation = $args->{DESIGNATION};
  my $name = $args->{NAME};
  my $status = $args->{STATUS};


  push @{$xmltags->{xmlroot}{CONTENT}{SLOTS}},
  {

    DESCRIPTION => [$description?$description:''],
    DESIGNATION => [$designation?$designation:''],
    NAME => [$name?$name:''],
    STATUS => [$status?$status:''],

  };
}

sub addSoftware {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $comments = $args->{COMMENTS};
  my $filesize = $args->{FILESIZE};
  my $folder = $args->{FOLDER};
  my $from = $args->{FROM};
  my $installdate = $args->{INSTALLDATE};
  my $name = $args->{NAME};
  my $publisher = $args->{PUBLISHER};
  my $version = $args->{VERSION};


  push @{$xmltags->{SOFTWARES}},
  {

    COMMENTS => [$comments?$comments:''],
    FILESIZE => [$filesize?$filesize:''],
    FOLDER => [$folder?$folder:''],
    FROM => [$from?$from:''],
    INSTALLDATE => [$installdate?$installdate:''],
    NAME => [$name?$name:''],
    PUBLISHER => [$publisher?$publisher:''],
    VERSION => [$version],

  };
}

sub addMonitor {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $base64 = $args->{BASE64};
  my $caption = $args->{CAPTION};
  my $description = $args->{DESCRIPTION};
  my $manufacturer = $args->{MANUFACTURER};
  my $serial = $args->{SERIAL};
  my $uuencode = $args->{UUENCODE};


  push @{$xmltags->{MONITORS}},
  {

    BASE64 => [$base64?$base64:''],
    CAPTION => [$caption?$caption:''],
    DESCRIPTION => [$description?$description:''],
    MANUFACTURER => [$manufacturer?$manufacturer:''],
    SERIAL => [$serial?$serial:''],
    UUENCODE => [$uuencode?$uuencode:''],

  };
}

sub addVideo {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $chipset = $args->{CHIPSET};
  my $memory = $args->{MEMORY};
  my $name = $args->{NAME};
  my $resolution = $args->{RESOLUTION};

  push @{$xmltags->{VIDEOS}},
  {

    CHIPSET => [$chipset?$chipset:''],
    MEMORY => [$memory?$memory:''],
    NAME => [$name?$name:''],
    RESOLUTION => [$resolution?$resolution:''],

  };
}

sub addSound {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $description = $args->{DESCRIPTION};
  my $manufacturer = $args->{MANUFACTURER};
  my $name = $args->{NAME};

  push @{$xmltags->{SOUNDS}},
  {

    DESCRIPTION => [$description?$description:''],
    MANUFACTURER => [$manufacturer?$manufacturer:''],
    NAME => [$name?$name:''],

  };
}

sub addNetwork {
  # TODO IPSUBNET, IPMASK IPADDRESS seem to be missing.
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $description = $args->{DESCRIPTION};
  my $driver = $args->{DRIVER};
  my $ipaddress = $args->{IPADDRESS};
  my $ipdhcp = $args->{IPDHCP};
  my $ipgateway = $args->{IPGATEWAY};
  my $ipmask = $args->{IPMASK};
  my $ipsubnet = $args->{IPSUBNET};
  my $macaddr = $args->{MACADDR};
  my $pcislot = $args->{PCISLOT};
  my $status = $args->{STATUS};
  my $type = $args->{TYPE};
  my $virtualdev = $args->{VIRTUALDEV};


#  return unless $ipaddress;

  push @{$xmltags->{NETWORKS}},
  {

    DESCRIPTION => [$description?$description:''],
    DRIVER => [$driver?$driver:''],
    IPADDRESS => [$ipaddress?$ipaddress:''],
    IPDHCP => [$ipdhcp?$ipdhcp:''],
    IPGATEWAY => [$ipgateway?$ipgateway:''],
    IPMASK => [$ipmask?$ipmask:''],
    IPSUBNET => [$ipsubnet?$ipsubnet:''],
    MACADDR => [$macaddr?$macaddr:''],
    PCISLOT => [$pcislot?$pcislot:''],
    STATUS => [$status?$status:''],
    TYPE => [$type?$type:''],
    VIRTUALDEV => [$virtualdev?$virtualdev:''],

  };
}

sub setHardware {
  my ($self, $args, $nonDeprecated) = @_; 
  my $xmltags=$self->{xmltags};

  my $logger = $self->{logger};

  foreach my $key (qw/USERID OSVERSION PROCESSORN OSCOMMENTS CHECKSUM
    PROCESSORT NAME PROCESSORS SWAP ETIME TYPE OSNAME IPADDR WORKGROUP
    DESCRIPTION MEMORY UUID DNS LASTLOGGEDUSER
    DATELASTLOGGEDUSER DEFAULTGATEWAY VMSYSTEM/) {

    if (exists $args->{$key}) {
      if ($key eq 'PROCESSORS' && !$nonDeprecated) {
          $logger->debug("PROCESSORN, PROCESSORS and PROCESSORT shouldn't be set directly anymore. Please use addCPU() method instead.");
      }
      if ($key eq 'USERID' && !$nonDeprecated) {
          $logger->debug("USERID shouldn't be set directly anymore. Please use addCPU() method instead.");
      }

      $xmltags->{'HARDWARE'}{$key}[0] = $args->{$key};
    }
  }
}


sub setBios {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  foreach my $key (qw/SMODEL SMANUFACTURER SSN BDATE BVERSION BMANUFACTURER MMANUFACTURER MSN MMODEL ASSETTAG/) {

    if (exists $args->{$key}) {
      $xmltags->{'BIOS'}{$key}[0] = $args->{$key};
    }
  }
}

sub addCPU {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  # The CPU FLAG
  my $manufacturer = $args->{MANUFACTURER};
  my $type = $args->{TYPE};
  my $serial = $args->{SERIAL};
  my $speed = $args->{SPEED};

  push @{$xmltags->{CPUS}},
  {

    MANUFACTURER => [$manufacturer],
    TYPE => [$type],
    SERIAL => [$serial],
    SPEED => [$speed],

  };

  # For the compatibility with HARDWARE/PROCESSOR*
  my $processorn = int @{$self->{xmlroot}{CONTENT}{CPUS}};
  my $processors = $self->{xmlroot}{CONTENT}{CPUS}[0]{SPEED}[0];
  my $processort = $self->{xmlroot}{CONTENT}{CPUS}[0]{TYPE}[0];

  $self->setHardware ({
    PROCESSORN => $processorn,
    PROCESSORS => $processors,
    PROCESSORT => $processort,
  }, 1);

}

sub addUser {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

#  my $name  = $args->{NAME};
#  my $gid   = $args->{GID};
  my $login = $args->{LOGIN};
#  my $uid   = $args->{UID};

  return unless $login;

  # Is the login, already in the XML ?
  foreach my $user (@{$self->{xmlroot}{CONTENT}{USERS}}) {
      return if $user->{LOGIN}[0] eq $login;
  }

  push @{$xmltags->{USERS}},
  {

#      NAME => [$name],
#      UID => [$uid],
#      GID => [$gid],
      LOGIN => [$login]

  };

  my $userString = $self->{xmlroot}{CONTENT}{HARDWARE}{USERID}[0] || "";

  $userString .= '/' if $userString;
  $userString .= $login;

  $self->setHardware ({
    USERID => $userString,
  }, 1);

}

sub addPrinter {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $description = $args->{DESCRIPTION};
  my $driver = $args->{DRIVER};
  my $name = $args->{NAME};
  my $port = $args->{PORT};

  push @{$xmltags->{PRINTERS}},
  {

    DESCRIPTION => [$description?$description:''],
    DRIVER => [$driver?$driver:''],
    NAME => [$name?$name:''],
    PORT => [$port?$port:''],

  };
}

sub addVirtualMachine {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  # The CPU FLAG
  my $memory = $args->{MEMORY};
  my $name = $args->{NAME};
  my $uuid = $args->{UUID};
  my $status = $args->{STATUS};
  my $subsystem = $args->{SUBSYSTEM};
  my $vmtype = $args->{VMTYPE};
  my $vcpu = $args->{VCPU};
  my $vmid = $args->{VMID};

  push @{$xmltags->{VIRTUALMACHINES}},
  {

      MEMORY =>  [$memory],
      NAME => [$name],
      UUID => [$uuid],
      STATUS => [$status],
      SUBSYSTEM => [$subsystem],
      VMTYPE => [$vmtype],
      VCPU => [$vcpu],
      VMID => [$vmid],

  };

}

sub addProcess {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $user = $args->{USER};
  my $pid = $args->{PID};
  my $cpu = $args->{CPUUSAGE};
  my $mem = $args->{MEM};
  my $vsz = $args->{VIRTUALMEMORY};
  my $tty = $args->{TTY};
  my $started = $args->{STARTED};
  my $cmd = $args->{CMD};

  push @{$xmltags->{PROCESSES}},
  {
    USER => [$user?$user:''],
    PID => [$pid?$pid:''],
    CPUUSAGE => [$cpu?$cpu:''],
    MEM => [$mem?$mem:''],
    VIRTUALMEMORY => [$vsz?$vsz:0],
    TTY => [$tty?$tty:''],
    STARTED => [$started?$started:''],
    CMD => [$cmd?$cmd:''],
  };
}



sub addIpDiscoverEntry {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $ipaddress = $args->{IPADDRESS};
  my $macaddr = $args->{MACADDR};
  my $name = $args->{NAME};

  if (!$xmltags->{IPDISCOVER}{H}) {
    $xmltags->{IPDISCOVER}{H} = [];
  }

  push @{$xmltags->{IPDISCOVER}{H}}, {
    # If I or M is undef, the server will ingore the host
    I => [$ipaddress?$ipaddress:""],
    M => [$macaddr?$macaddr:""],
    N => [$name?$name:"-"], # '-' is the default value reteurned by ipdiscover
  };
}


#### Old subroutines from the former Common.pm used by Download.pm #######

sub get_path{
   my $self = shift;
   my $binary = shift;
   my $path;

   my @bin_directories  = qw {   /usr/local/sbin/ /sbin/ /usr/sbin/ /bin/ /usr/bin/
            /usr/local/bin/ /etc/ocsinventory-client/};

   print "\n=> retrieving $binary...\n" if $::debug;
   for(@bin_directories){
      $path = $_.$binary,last if -x $_.$binary;
   }

   #For debbuging purposes
   if($path){
      print "=> $binary is at $path\n\n" if $::debug;
   }else{
      print "$binary not found (Maybe it is not installed ?) - Some functionnalities may lack !!\n\n";
   }

   return $path;
}


sub already_in_array {
   my $self = shift;
   my $lookfor = shift;
   my @array   = @_;
   foreach (@array){
      if($lookfor eq $_){
         return 1 ;
      }
   }
   return 0;
}




1;
