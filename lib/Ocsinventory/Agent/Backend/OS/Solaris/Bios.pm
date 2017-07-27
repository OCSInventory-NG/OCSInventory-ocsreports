package Ocsinventory::Agent::Backend::OS::Solaris::Bios;

#  SPARC
# $ showrev
#Hostname: 157501s021plc
#Hostid: 83249bbf
#Release: 5.10
#Kernel architecture: sun4u
#Application architecture: sparc
#Hardware provider: Sun_Microsystems
#Domain: be.cnamts.fr
#Kernel version: SunOS 5.10 Generic_118833-17
#
# $ prtconf -pv    (-b would be great...but doesn't work before S10)
#System Configuration:  Sun Microsystems  sun4u
#Memory size: 16384 Megabytes
#System Peripherals (PROM Nodes):
#
#Node 0xf0819f00
#    scsi-initiator-id:  00000007
#    node#:  00000000
#    #size-cells:  00000002
#    stick-frequency:  00bebc20
#    clock-frequency:  08f0d180
#    idprom:  01840014.4f4162cb.45255cf4.4162cb16.55555555.55555555.55555555.55555555
#    breakpoint-trap:  0000007f
#    device_type:  'gptwo'
#    banner-name:  'Sun Fire E6900'
#    compatible: 'SUNW,Serengeti'
#    newio-addr:  00000001
#    name:  'SUNW,Sun-Fire'


#  X64
# $ showrev
#Hostname: stlaurent
#Hostid: 403100b
#Release: 5.10
#Kernel architecture: i86pc
#Application architecture: i386
#Hardware provider: 
#Domain: 
#Kernel version: SunOS 5.10 Generic_127112-07
#
# $ smbios -t SMB_TYPE_SYSTEM
#ID    SIZE TYPE
#1     76   SMB_TYPE_SYSTEM (system information)
#
#  Manufacturer: Sun Microsystems, Inc.
#  Product: Sun Fire V40z
#  Version: 00
#  Serial Number: R00T34E0009
#
#  UUID: be1630df-d130-41a4-be32-fd28bb4bd1ac
#  Wake-Up Event: 0x6 (power switch)
#  SKU Number: 
#  Family: 


use strict;

sub run {
  my $params = shift;
  my $common = $params->{common};
  my $zone;
  my( $SystemSerial , $SystemModel, $SystemManufacturer, $ChassisType, $MotherBoardSerial, $MotherBoardModel, $MotherBoardManufacturer, $BiosManufacturer, $BiosVersion, $BiosDate, $aarch);
  
  if ( !$common->can_run("zonename") || `zonename` =~ /global/ ) {
    # Ether pre Sol10 or in Sol10/Sol11 global zone
    $zone = "global";
  } else {
    $zone = "";
  }
    
  if ($zone){   
          chomp($SystemModel = `uname -m`);
          chomp($aarch = `uname -p`);
          if( $aarch eq "i386" ){
            #
            # For a Intel/AMD arch, we're using smbios
            #
            foreach(`/usr/sbin/smbios -t SMB_TYPE_SYSTEM`) {
              if(/^\s*Manufacturer:\s*(.+)$/){$SystemManufacturer = $1};
              if(/^\s*Serial Number:\s*(.+)$/){$SystemSerial = $1;}
              if(/^\s*Product:\s*(.+)$/){$SystemModel = $1;}
            }
            foreach(`/usr/sbin/smbios -t SMB_TYPE_BIOS`) {
              if(/^\s*Vendor:\s*(.+)$/){$BiosManufacturer = $1};
              if(/^\s*Version String:\s*(.+)$/){$BiosVersion = $1};
              if(/^\s*Release Date:\s*(.+)$/){$BiosDate = $1};
            }
            foreach(`/usr/sbin/smbios -t SMB_TYPE_BASEBOARD`) {
              if(/^\s*Product\s*(.+)$/){$MotherBoardModel = $1};
              if(/^\s*Serial Number:\s*(.+)$/){$MotherBoardSerial = $1};
              if(/^\s*Manufacturer:\s*(.+)$/){$MotherBoardManufacturer = $1};
            }
            foreach(`/usr/sbin/smbios -t SMB_TYPE_CHASSIS`) {
              if(/^\s*Chassis Type:.*\((.+)\)$/) {$ChassisType = $1};
            }
          } elsif( $aarch eq "sparc" ) {
            #
            # For a Sparc arch, we're using prtconf
            #
            my $name;
            my $OBPstring;
            my $found=0;
            
            if ( $common->can_run("virtinfo") && `virtinfo -t` =~ /.*LDoms guest.*/ ) {
              foreach(`virtinfo -a`) {
                if(/^Domain role:\s*(.+)$/)      {$ChassisType = $1};
                if(/^Chassis serial.:\s*(.+)$/)  {$SystemSerial = $1};
              }
            }

            foreach(`/usr/sbin/prtconf -pv`) {
              # prtconf is an awful thing to parse
              if(/^System Configuration:\s*(.+)\s+\S+$/) {$SystemManufacturer = $1; $BiosManufacturer = $1; }
              if(/^\s*banner-name:\s*'(.+)'$/){$SystemModel = $1;}
              unless ($name)
                { if(/^\s*name:\s*'(.+)'$/){$name = $1;} }
              unless ($OBPstring) {
            if(/^\s*version:\s*'(.+)'$/){
                  $OBPstring = $1;
              # looks like : "OBP 4.33.6.f 2014/07/10 10:24"
                  #    with further informations sometimes
                  if( $OBPstring =~ m@OBP\s+(\S+)\s+(\d+)/(\d+)/(\d+)@ ){
                    $BiosVersion = "OBP $1";
                    $BiosDate = "$2/$3/$4";
                  } else { $BiosVersion = $OBPstring }
                }
              } 
            }
            $SystemModel .= " ($name)" if( $name );
           
            if ( $common->can_run("ipmitool") ) {
              foreach(`/usr/sbin/ipmitool fru print`) {
                if (/^\s*Chassis Type\s+:\s+(.+)/) {
                  $ChassisType = $1;
                  $found = 1;
                }
                elsif ( $found && /^\s*Product Serial\s+:\s+(.+)/) {
                  $SystemSerial = $1;
                  last;
                }
              }
            }

            if ( !$SystemSerial ) {
              # No serial found via ipmitool, so use sneep
              if( -x "/opt/SUNWsneep/bin/sneep" ) {
                chomp($SystemSerial = `/opt/SUNWsneep/bin/sneep`);
              }else {                
                  foreach(`/bin/find /opt -name sneep`) {    
                      chomp($SystemSerial = `$1`) if /^(\S+)/;                         
                  }
                  if (!$SystemSerial){
                      $SystemSerial = "Please install package SUNWsneep";
                  }  
              }
           }
          } 
    }else{
        foreach(`showrev`){            
            if(/^Hardware provider:\s+(\S+)/){$SystemManufacturer = $1};            
        }
        $SystemModel = "Solaris Containers";
        $SystemSerial = "Solaris Containers";
        
    }
 
  # Writing data
  $common->setBios ({
      BVERSION => $BiosVersion,
      BDATE => $BiosDate,
      BMANUFACTURER => $BiosManufacturer,
      SMANUFACTURER => $SystemManufacturer,
      SMODEL => $SystemModel,
      SSN => $SystemSerial,
      MMANUFACTURER => $MotherBoardManufacturer,
      MSN => $MotherBoardSerial,
      TYPE => $ChassisType,
      MMODEL => $MotherBoardModel     
    });
}

1;
