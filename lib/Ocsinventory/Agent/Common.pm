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
  my (undef, $params) = @_;

  my $self = {};

  $self->{logger} = $params->{logger};
  $self->{config} = $params->{config};

  $self->{xmltags}={};

  bless $self;
}

=item addController()

Add a controller in the inventory.

=cut
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

=item addModem()

Add a modem in the inventory.

=cut
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

# For compatibiliy
sub addModems {
   my $self = shift;
   my $logger = $self->{logger};

   $logger->debug("please rename addModems to addModem()");
   $self->addModem(@_);
}

=item addDrive()

Add a partition in the inventory.

=cut
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
# For compatibiliy
sub addDrives {
   my $self = shift;
   my $logger = $self->{logger};

   $logger->debug("please rename addDrives to addDrive()");
   $self->addDrive(@_);
}

=item addStorages()

Add a storage system (hard drive, USB key, SAN volume, etc) in the inventory.

=cut
sub addStorages {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

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

# For compatibiliy
sub addStorage {
   my $self = shift;
   my $logger = $self->{logger};

   $logger->debug("please rename addStorages to addStorage()");
   $self->addStorage(@_);
}


=item addMemory()

Add a memory module in the inventory.

=cut
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

# For compatibiliy
sub addMemories {
   my $self = shift;
   my $logger = $self->{logger};

   $logger->debug("please rename addMemories to addMemory()");
   $self->addMemory(@_);
}

=item addPort()

Add a port module in the inventory.

=cut
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

# For compatibiliy
sub addPort {
   my $self = shift;
   my $logger = $self->{logger};

   $logger->debug("please rename addPorts to addPort()");
   $self->addPort(@_);
}

=item addSlot()

Add a slot in the inventory. 

=cut
sub addSlot {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  my $description = $args->{DESCRIPTION};
  my $designation = $args->{DESIGNATION};
  my $name = $args->{NAME};
  my $status = $args->{STATUS};


  push @{$xmltags->{SLOTS}},
  {

    DESCRIPTION => [$description?$description:''],
    DESIGNATION => [$designation?$designation:''],
    NAME => [$name?$name:''],
    STATUS => [$status?$status:''],

  };
}

# For compatibiliy
sub addSlots {
   my $self = shift;
   my $logger = $self->{logger};

   $logger->debug("please rename addSlots to addSlot()");
   $self->addSlot(@_);
}

=item addSoftware()

Register a software in the inventory.

=cut
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

# For compatibiliy
sub addSoftwares {
   my $self = shift;
   my $logger = $self->{logger};

   $logger->debug("please rename addSoftwares to addSoftware()");
   $self->addSoftware(@_);
}

=item addMonitor()

Add a monitor (screen) in the inventory.

=cut
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

# For compatibiliy
sub addMonitors {
   my $self = shift;
   my $logger = $self->{logger};

   $logger->debug("please rename addMonitors to addMonitor()");
   $self->addMonitor(@_);
}

=item addVideo()

Add a video card in the inventory.

=cut
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

# For compatibiliy
sub addVideos {
   my $self = shift;
   my $logger = $self->{logger};

   $logger->debug("please rename addVideos to addVideo()");
   $self->addVideo(@_);
}

=item addSound()

Add a sound card in the inventory.

=cut
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

# For compatibiliy
sub addSounds {
   my $self = shift;
   my $logger = $self->{logger};

   $logger->debug("please rename addSounds to addSound()");
   $self->addSound(@_);
}

=item addNetwork()

Register a network in the inventory.

=cut
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
# For compatibiliy
sub addNetworks {
   my $self = shift;
   my $logger = $self->{logger};

   $logger->debug("please rename addNetworks to addNetwork()");
   $self->addNetwork(@_);
}


=item setHardware()

Save global information regarding the machine.

The use of setHardware() to update USERID and PROCESSOR* informations is
deprecated, please, use addUser() and addCPU() instead.

=cut
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


=item setBios()

Set BIOS informations.

=cut
sub setBios {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

  foreach my $key (qw/SMODEL SMANUFACTURER SSN BDATE BVERSION BMANUFACTURER MMANUFACTURER MSN MMODEL ASSETTAG/) {

    if (exists $args->{$key}) {
      $xmltags->{'BIOS'}{$key}[0] = $args->{$key};
    }
  }
}

=item addCPU()

Add a CPU in the inventory.

=cut
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
  my $processorn = int @{$xmltags->{CPUS}};
  my $processors = $xmltags->{CPUS}[0]{SPEED}[0];
  my $processort = $xmltags->{CPUS}[0]{TYPE}[0];

  $self->setHardware ({
    PROCESSORN => $processorn,
    PROCESSORS => $processors,
    PROCESSORT => $processort,
  }, 1);

}

=item addUser()

Add an user in the list of logged user.

=cut
sub addUser {
  my ($self, $args) = @_; 
  my $xmltags=$self->{xmltags};

#  my $name  = $args->{NAME};
#  my $gid   = $args->{GID};
  my $login = $args->{LOGIN};
#  my $uid   = $args->{UID};

  return unless $login;

  # Is the login, already in the XML ?
  foreach my $user (@{$xmltags->{USERS}}) {
      return if $user->{LOGIN}[0] eq $login;
  }

  push @{$xmltags->{USERS}},
  {

#      NAME => [$name],
#      UID => [$uid],
#      GID => [$gid],
      LOGIN => [$login]

  };

  my $userString = $xmltags->{HARDWARE}->{USERID}[0] || "";

  $userString .= '/' if $userString;
  $userString .= $login;

  $self->setHardware ({
    USERID => $userString,
  }, 1);

}

=item addPrinter()

Add a printer in the inventory.

=cut
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

# For compatibiliy
sub addPrinters {
   my $self = shift;
   my $logger = $self->{logger};

   $logger->debug("please rename addPrinters to addPrinter()");
   $self->addPrinter(@_);
}

=item addVirtualMachine()

Add a Virtual Machine in the inventory.

=cut
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

=item addProcess()

Record a running process in the inventory.

=cut
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


=item addIpDiscoverEntry()

IpDiscover is used to identify network interface on the local network. This
is done on the ARP level.

This function adds a network interface in the inventory.

=cut
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


=item setAccessLog()

What is that for? :)

=cut
sub setAccessLog {
  my ($self, $args) = @_;

  foreach my $key (qw/USERID LOGDATE/) {

    if (exists $args->{$key}) {
      $self->{xmlroot}{'CONTENT'}{'ACCESSLOG'}{$key}[0] = $args->{$key};
    }
  }
}

=item flushXMlTags()

Clear the content of $common->{xmltags} (to use after adding it in XML)

=cut
sub flushXMLTags {
  my $self= shift;
  $self->{xmltags} = {};
}


### SNMP specifics subroutines ####

sub getSnmpTable {
  my ($self,$snmp_table,$baseoid,$snmp_infos) = @_;

  #$snmp_infos is a hash passed for the SNMP informations we want to get
  #It has to be created like this :
  #my $hash = {
  #  INFORMATION => OID,
  #};

  my $results={};  #The final hash wich will contain one key per SNMP reference

  for my $oid ( keys %$snmp_table ) {
    if ( $oid =~ /$baseoid\.\d+\.\d+\.(\S+)/ ) {
      my $reference=$1;    #Getting the last digits of the OID separated by a dot

      #Getting information if one the values from $snmp_infos hash is found for the current OID
      for my $value (keys %$snmp_infos) {
        if ($oid =~ /$snmp_infos->{$value}\.$reference/) {
        $results->{$reference}->{$value}= $snmp_table->{$oid}
        }
      }
    }
  }
  return $results;
}


sub setSnmpCommons {
  my ($self,$args)=@_; 
  my $xmltags=$self->{xmltags};
  
  foreach my $key (qw/IPADDR TYPE MACADDR SNMPDEVICEID NAME DESCRIPTION CONTACT LOCATION UPTIME DOMAIN TYPE / ) {
     if (exists $args->{$key}) {
        $xmltags->{COMMON}[0]{$key}[0]=$args->{$key};
     }
  }
}

sub setSnmpPrinter {
  my ($self,$args)=@_;
  my $xmltags=$self->{xmltags};

  foreach my $key (qw/NAME SERIALNUMBER COUNTER STATUS ERRORSTATE/ ) {
     if (exists $args->{$key}) {
        $xmltags->{PRINTERS}[0]{$key}[0]=$args->{$key};
     }
  }
}


sub setSnmpSwitchInfos {
  my ($self,$args)=@_;
  my $xmltags=$self->{xmltags};

  foreach my $key (qw/TYPE/ ) {
     if (exists $args->{$key}) {
        $xmltags->{SWITCHINFOS}[0]{$key}[0]=$args->{$key};
     }
  }
}

sub setSnmpFirewalls {
  my ($self,$args)=@_; 
  my $xmltags=$self->{xmltags};

  foreach my $key (qw/SERIALNUMBER/ ) {
     if (exists $args->{$key}) {
        $xmltags->{FIREWALLS}[0]{$key}[0]=$args->{$key};
     }
  }
}


sub setSnmpLoadBalancer {
  my ($self,$args)=@_; 
  my $xmltags=$self->{xmltags};

  foreach my $key (qw/SERIALNUMBER/ ) {
     if (exists $args->{$key}) {
        $xmltags->{LOADBALANCERS}[0]{$key}[0]=$args->{$key};
     }
  }
}

sub setSnmpBlade {
  my ($self,$args)=@_; 
  my $xmltags=$self->{xmltags};

  foreach my $key (qw/SERIALNUMBER/ ) {
     if (exists $args->{$key}) {
        $xmltags->{BLADES}[0]{$key}[0]=$args->{$key};
     }
  }
}

sub setSnmpComputer {
  my ($self,$args)=@_; 
  my $xmltags=$self->{xmltags};

  foreach my $key (qw/SYSTEM/ ) {
     if (exists $args->{$key}) {
        $xmltags->{COMPUTERS}[0]{$key}[0]=$args->{$key};
     }
  }
}

sub addSnmpPrinterCartridge {
  my ($self,$args)=@_;
  my $xmltags=$self->{xmltags};
  my $content={};

  if ( ! defined ($xmltags->{CARDS})) {
     $xmltags->{CARDS}=[];
  }

  foreach my $key (qw/DESCRIPTION TYPE LEVEL MAXCAPACITY COLOR/) {
     if (exists $args->{$key}) {
        $content->{$key}[0]=$args->{$key};
     }
  }
  push @{$xmltags->{CARTRIDGES}},$content;

}

sub addSnmpPrinterTray {
  my ($self,$args)=@_;
  my $xmltags=$self->{xmltags};
  my $content={};

  if ( ! defined ($xmltags->{CARDS})) {
     $xmltags->{CARDS}=[];
  }

  foreach my $key (qw/NAME DESCRIPTION LEVEL MAXCAPACITY/) {
     if (exists $args->{$key}) {
        $content->{$key}[0]=$args->{$key};
     }
  }
  push @{$xmltags->{TRAYS}},$content;

}

sub addSnmpNetwork {
  my ($self,$args)=@_;
  my $xmltags=$self->{xmltags};
  my $content={};
  if ( ! defined ($xmltags->{NETWORKS})) {
     $xmltags->{NETWORKS}=[];
  }

  foreach my $key (qw/DESCRIPTION MACADDR DEVICEMACADDR SLOT STATUS SPEED TYPE DEVICEADDRESS DEVICENAME TYPEMIB IPADDR IPMASK IPGATEWAY IPSUBNET IPDHCP DRIVER VIRTUALDEV/) {
     if (exists $args->{$key}) {
        $content->{$key}[0]=$args->{$key};
     }
  }
  #if ( exists $args->{VLAN}) {
  #	$content->{VLAN}=$args->{VLAN};
  #}
  push @{$xmltags->{NETWORKS}},$content;
}

sub addSnmpDrive {
  my ($self,$args)=@_;
  my $xmltags=$self->{xmltags};
  my $content={};

  if ( ! defined ($xmltags->{DRIVES})) {
     $xmltags->{DRIVES}=[];
  }

  foreach my $key (qw/LETTER TYPE FILESYSTEM TOTAL FREE NUMFILES VOLUMN/) {
     if (exists $args->{$key}) {
        $content->{$key}[0]=$args->{$key};
     }
  }
  push @{$xmltags->{DRIVES}},$content;
}

sub addSnmpStorage {
  my ($self,$args)=@_;
  my $xmltags=$self->{xmltags};
  my $content={};

  if ( ! defined ($xmltags->{STORAGES})) {
     $xmltags->{STORAGES}=[];
  }

  foreach my $key (qw/DESCRIPTION MANUFACTURER NAME MODEL DISKSIZE TYPE SERIALNUMBER FIRMWARE/) {
     if (exists $args->{$key}) {
        $content->{$key}[0]=$args->{$key};
     }
  }
  push @{$xmltags->{STORAGES}},$content;

}

sub addSnmpCard {
  my ($self,$args)=@_; 
  my $xmltags=$self->{xmltags};
  my $content={};

  if ( ! defined ($xmltags->{CARDS})) {
     $xmltags->{CARDS}=[];
  }

  foreach my $key (qw/DESCRIPTION REFERENCE FIRMWARE SOFTWARE REVISION SERIALNUMBER MANUFACTURER TYPE/) {
     if (exists $args->{$key}) {
        $content->{$key}[0]=$args->{$key};
     }
  }
  push @{$xmltags->{CARDS}},$content;

}

sub addSnmpFan {
  my ($self,$args)=@_; 
  my $xmltags=$self->{xmltags};
  my $content={};

  if ( ! defined ($xmltags->{FANS})) {
     $xmltags->{FANS}=[];
  }

  foreach my $key (qw/DESCRIPTION REFERENCE REVISION SERIALNUMBER MANUFACTURER TYPE/) {
     if (exists $args->{$key}) {
        $content->{$key}[0]=$args->{$key};
     }
  }
  push @{$xmltags->{FANS}},$content;
}

sub addSnmpPowerSupply {
  my ($self,$args)=@_; 
  my $xmltags=$self->{xmltags};
  my $content={};

  if ( ! defined ($xmltags->{POWERSUPPLIES})) {
     $xmltags->{POWERSUPPLIES}=[];
  }

  foreach my $key (qw/MANUFACTURER REFERENCE TYPE SERIALNUMBER DESCRIPTION REVISION/ ) {
     if (exists $args->{$key}) {
        $content->{$key}[0]=$args->{$key};
     }
  }
  push @{$xmltags->{POWERSUPPLIES}},$content;
}

sub addSnmpSwitch {
  my ($self,$args)=@_; 
  my $xmltags=$self->{xmltags};
  my $content={};
  if ( ! defined ($xmltags->{SWITCHS})) {
     $xmltags->{SWITCHS}=[];
  }

  foreach my $key (qw/MANUFACTURER REFERENCE TYPE SOTVERSION FIRMVERSION SERIALNUMBER REVISION DESCRIPTION/) {
     if (exists $args->{$key}) {
        $content->{$key}[0]=$args->{$key};
     }
  }
  push @{$xmltags->{SWITCHS}},$content;
}

sub addSnmpLocalPrinter {
  my ($self,$args)=@_; 
  my $xmltags=$self->{xmltags};
  my $content={};
  if ( ! defined ($xmltags->{LOCALPRINTERS})) {
     $xmltags->{LOCALPRINTERS}=[];
  }

  foreach my $key (qw/NAME/) {
     if (exists $args->{$key}) {
        $content->{$key}[0]=$args->{$key};
     }
  }
  push @{$xmltags->{LOCALPRINTERS}},$content;

}

sub addSnmpInput {
  my ($self,$args)=@_; 
  my $xmltags=$self->{xmltags};
  my $content={};
  if ( ! defined ($xmltags->{INPUTS})) {
     $xmltags->{INPUTS}=[];
  }

  foreach my $key (qw/DESCRIPTION TYPE/) {
     if (exists $args->{$key}) {
        $content->{$key}[0]=$args->{$key};
     }
  }
  push @{$xmltags->{INPUTS}},$content;

}


#Subroutinne to add 0 in 'Sun like' MAC adress if needed
sub padSnmpMacAddress {
  my ($self,$mac) = @_;

  my @splitedAddr = split(':', $mac);

  for (@splitedAddr) {
    unless ($_ =~ /\w{2}/) {
       $_ = sprintf("%02s", $_);
    }
  }

  $mac=join (':', @splitedAddr);
  return $mac;
}


### Generic shared subroutines #####

sub can_run {
  my $self = shift;
  my $binary = shift;

  my $logger = $self->{logger};

  my $calling_namespace = caller(0);
  chomp(my $binpath=`which $binary 2>/dev/null`);
  return unless -x $binpath;
  $self->{logger}->debug(" - $binary found");
  1;
}

sub can_load {
  my $self = shift;
  my $module = shift;

  my $logger = $self->{logger};

  my $calling_namespace = caller(0);
  eval "package $calling_namespace; use $module;";
  return if $@;
  $self->{logger}->debug(" - $module loaded");
  1;
}


sub can_read {
  my $self = shift;
  my $file = shift;

  my $logger = $self->{logger};

  return unless -r $file;
  $self->{logger}->debug(" - $file can be read");
  1;
}

sub runcmd {
  my $self = shift;
  my $cmd = shift;

  my $logger = $self->{logger};

  return unless $cmd;

  # $self->{logger}->debug(" - run $cmd");
  return `$cmd`;
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
