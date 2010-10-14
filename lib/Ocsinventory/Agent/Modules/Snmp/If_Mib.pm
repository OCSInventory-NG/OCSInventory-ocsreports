###
# SNMP: OID: 2 SYSTEM: If_Mib
###
package Ocsinventory::Agent::Modules::Snmp::If_Mib;

use strict;
use warnings;

sub snmp_info {
   return ( { oid_value => "1.3.6.1.2.1.2.2.1.1.1",
            oid_name => "If_Mib" } );
}

sub snmp_run {
   my ($session , $snmp )= @_;
   my $logger=$snmp->{logger};
   my $common=$snmp->{common};
   
   $logger->debug("Execution: If mib");
   # OID a recuperer
   my $snmp_ifdescr="1.3.6.1.2.1.2.2.1.2";
   my $snmp_iftype="1.3.6.1.2.1.2.2.1.3.";
   my $snmp_ifspeed="1.3.6.1.2.1.2.2.1.5.";
   my $snmp_physAddr="1.3.6.1.2.1.2.2.1.6.";
   my $snmp_ifadminstatus="1.3.6.1.2.1.2.2.1.7.";

   my $SPEED=""; 
   my $MACADDR="";
   my $PCISLOT=""; 
   my $STATUS=""; 
   my $TYPE="";

   my $ref;
   my $result_snmp;

   # We look for interfaces
   $result_snmp=$session->get_entries(-columns => [$snmp_ifdescr]);
   foreach my $result ( keys  %{$result_snmp} ) {
      # We work on real interface and no vlan
      if ( $result_snmp->{$result} =~ /[eE]th/ ) {
         if ( $result =~ /1\.3\.6\.1\.2\.1\.2\.2\.1\.2\.(\S+)/ ) {
            $ref=$1;
            $PCISLOT=$result_snmp->{$result};

            $TYPE=$session->get_request(-varbindlist => [$snmp_iftype.$ref]);
            if ( defined( $TYPE->{$snmp_iftype.$ref} ) ) {
               $TYPE= $TYPE->{$snmp_iftype.$ref};
            }

            $SPEED=$session->get_request(-varbindlist => [$snmp_ifspeed.$ref]);
            if ( defined( $SPEED->{$snmp_ifspeed.$ref}) ) {
               $SPEED=$SPEED->{$snmp_ifspeed.$ref};
            }

            $MACADDR=$session->get_request(-varbindlist => [$snmp_physAddr.$ref]);
            if ( defined( $MACADDR->{$snmp_physAddr.$ref}) ) {
               # For MACADDR, we need a translation beetween Hexa and string
               $MACADDR=$MACADDR->{$snmp_physAddr.$ref};
               #$MACADDR= sprintf "%02x:%02x:%02x:%02x:%02x:%02x" ,
               #         map hex, split /\:/, $MACADDR->{$snmp_physAddr.$ref};
            }

            $STATUS=$session->get_request(-varbindlist => [ $snmp_ifadminstatus.$ref ]);
            if ( $STATUS->{$snmp_ifadminstatus.$ref} == 1 ) {
               $STATUS="Up";
            } else {
               $STATUS="Down";
            }


      #print "  mac $MACADDR pci $PCISLOT status $STATUS type $TYPE virtual $VIRTUALDEV speed $SPEED\n";
           $common->addNetwork( {
                TYPE => $TYPE,
                PCISLOT => $PCISLOT,
                SPEED => $SPEED,
                MACADDR => $MACADDR,
                STATUS => $STATUS,
                });
           $MACADDR="";
           $PCISLOT="";
           $STATUS="";
           $TYPE="";
           $SPEED="";
        }
      }
   } # End foreach result
}

1;
