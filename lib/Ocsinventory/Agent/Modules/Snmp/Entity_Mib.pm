###
# SNMP: OID: 47 SYSTEM: Entity_Mib
###
# Version 0.5
###
package Ocsinventory::Agent::Modules::Snmp::Entity_Mib;

use strict;
use warnings;

use Data::Dumper;

sub snmp_run {
   my ($session , $snmp )= @_;
   my $logger=$snmp->{logger};
   my $common=$snmp->{common};

   $logger->debug("Execution: Entity mib");

   # OID
   my $snmp_entPhysicalClass="1.3.6.1.2.1.47.1.1.1.1.5";

   my $result_snmp;
   my $result;

   # We are looking for physical elements 
   $result_snmp=$session->get_entries(-columns => [$snmp_entPhysicalClass]);
   foreach my $result ( keys  %{$result_snmp} ) {
      if ( $result =~ /1\.3\.6\.1\.2\.1\.47\.1\.1\.1\.1\.5\.(\S+)/ ) {
         my $ref=$1;
         my $PhysicalClass=$result_snmp->{$snmp_entPhysicalClass.".".$ref};

         my $info = {};
         if ( $PhysicalClass =~ /^[3,6,7,9,11]$/ ) {
             info_element($info,$session,$ref,$logger);
         #print "Phys $PhysicalClass\n";
         #print Dumper ($info);
         }
         if ( $PhysicalClass == 3 ) {
             # We have a switch
             $common->addSnmpSwitch( $info ); 
             # Infos for a switch: DESCRIPTION, REF, HARDWARE_REV, FIRMWARE, SERIAL, MANUFACTURER, TYPE
         } elsif ( $PhysicalClass == 6 ) {
             # Infos for an alimentation DESCRIPTION, REF, HARDWARE_REV, SERIAL, MANUFACTURER, TYPE
             $common->addSnmpPowerSupply( $info );
         } elsif ( $PhysicalClass == 7 ) {
             # Infos for a Fan: DESCRIPTION, REF, HARDWARE_REV, SERIAL, MANUFACTURER, TYPE
             $common->addsnmpFan( $info );
         } elsif ( $PhysicalClass == 9 && defined ($info->{SERIAL}) ) {
             # Infor for a card: DESCRIPTION, REF,  HARDWARE_REV, FIRMWARE, SOFTWARE, SERIAL, MANUFACTURER, TYPE
             $common->addSnmpCard( $info );
         }
      }
   } 
}


sub info_element()
{
   my ($info ,$session, $ref, $logger)=@_;

   my $snmp_info="1.3.6.1.2.1.47.1.1.1.1.2.";
   my $snmp_ref="1.3.6.1.2.1.47.1.1.1.1.7.";
   my $snmp_hardware="1.3.6.1.2.1.47.1.1.1.1.8.";
   my $snmp_firmware="1.3.6.1.2.1.47.1.1.1.1.9.";
   my $snmp_software="1.3.6.1.2.1.47.1.1.1.1.10.";
   my $snmp_serial="1.3.6.1.2.1.47.1.1.1.1.11.";
   my $snmp_entPhysicalMfgName="1.3.6.1.2.1.47.1.1.1.1.12.";
   my $snmp_entPhysicalModelName="1.3.6.1.2.1.47.1.1.1.1.13.";

   my $result;
   # We have a good element
   $result=$session->get_request(-varbindlist => [$snmp_info.$ref]);
   if ( defined( $result) ) {
      $info->{DESCRIPTION} = $result->{$snmp_info.$ref};
   }
   $result=$session->get_request(-varbindlist => [$snmp_ref.$ref]);
   if ( defined( $result ) ) {
      $info->{REF} = $result->{$snmp_ref.$ref};
   }
   $result=$session->get_request(-varbindlist => [$snmp_hardware.$ref]);
   if ( defined( $result ) ) {
      $info->{HARDWARE_REV} = $result->{$snmp_hardware.$ref};
   }
   $result=$session->get_request(-varbindlist => [$snmp_serial.$ref]);
   if ( defined( $result ) ) {
      $info->{SERIAL} = $result->{$snmp_serial.$ref};
   }
   $result=$session->get_request(-varbindlist => [$snmp_firmware.$ref]);
   if ( defined( $result) ) {
      $info->{FIRMWARE} = $result->{$snmp_firmware.$ref};
   }
   $result=$session->get_request(-varbindlist => [$snmp_software.$ref]);
   if ( defined( $result ) ) {
      $info->{SOFTWARE} = $result->{$snmp_software.$ref};
   }
   $result=$session->get_request(-varbindlist => [$snmp_entPhysicalMfgName.$ref]);
   if ( defined( $result ) ) {
      $info->{MANUFACTURER} = $result->{$snmp_entPhysicalMfgName.$ref};
   }
   $result=$session->get_request(-varbindlist => [$snmp_entPhysicalModelName.$ref]);
   if ( defined( $result ) ) {
      $info->{TYPE} = $result->{$snmp_entPhysicalModelName.$ref};
   }
}
1;
