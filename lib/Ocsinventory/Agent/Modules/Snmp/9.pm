###
# SNMP: OID:9 SYSTEM:Cisco
###

package Ocsinventory::Agent::Modules::Snmp::9;

use strict;
use warnings;

use Data::Dumper;


sub snmp_run {
   my ($session , $snmp )= @_;
   my $logger=$snmp->{logger};
   my $common=$snmp->{common};

   # lines refs
   my $snmp_osversion="1.3.6.1.4.1.9.2.1.73.0";
   my $snmp_ifdescr="1.3.6.1.2.1.2.2.1.2";
   my $snmp_iftype="1.3.6.1.2.1.2.2.1.3.";
   my $snmp_ifspeed="1.3.6.1.2.1.2.2.1.5.";
   my $snmp_physAddr="1.3.6.1.2.1.2.2.1.6.";
   my $snmp_ifadminstatus="1.3.6.1.2.1.2.2.1.7.";
   # Cisco specific 
   my $snmp_description="1.3.6.1.4.1.9.2.2.1.1.28.";
   my $snmp_cisco_deviceAddress="1.3.6.1.4.1.9.9.23.1.2.1.1.4.";
   my $snmp_cisco_deviceId="1.3.6.1.4.1.9.9.23.1.2.1.1.6.";

   my $snmp_elements="1.3.6.1.2.1.47.1.1.1.1.8";
   my $snmp_info="1.3.6.1.2.1.47.1.1.1.1.2";
   my $snmp_ref="1.3.6.1.2.1.47.1.1.1.1.7";
   my $snmp_serial="1.3.6.1.2.1.47.1.1.1.1.11";
   my $snmp_software="1.3.6.1.2.1.47.1.1.1.1.10";
   my $snmp_firmware="1.3.6.1.2.1.47.1.1.1.1.9";

   my $oid;
   my $oid_complet;

   my $osversion;
   my $ref;
   my $serial;
   my $first_serial=undef;
   my $software;
   my $firmware;
   my $location;
   my $TotalEthernet=0;
   my $result_snmp;
   my $result_sub;

   my $DESCRIPTION=""; #Ok
   my $DRIVER="";
   my $SPEED=""; #Ok
   my $MACADDR="";
   my $PCISLOT=""; #ok
   my $STATUS=""; #Ok
   my $TYPE=""; 
   my $VIRTUALDEV="";
   my $DEVICEID="";
   my $DEVICEADDRESS="";

 # interesting info SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.6.10140.1
 # SNMPv2-SMI::enterprises.9.9.25.1.1.1.2.1 
    # version IOS
    $result_snmp=$session->get_request(-varbindlist => [$snmp_osversion]);
    if ( defined($result_snmp->{$snmp_osversion}) ) {
       $osversion=$result_snmp->{$snmp_osversion};
       print "osversion $osversion\n";
    }
    
    # We look for interfaces
    $result_snmp=$session->get_entries(-columns => [$snmp_ifdescr]);
    foreach my $result ( keys  %{$result_snmp} ) {
        # We work on real interface and no vlan
        if ( $result_snmp->{$result} =~ /Ethernet/ ) {
	   $PCISLOT=$result_snmp->{$result};
           if ( $result =~ /1\.3\.6\.1\.2\.1\.2\.2\.1\.2\.(\S+)/ ) {
               $ref=$1;

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
		  $MACADDR=substr($MACADDR,2,2).":".
			substr($MACADDR,4,2).":".
			substr($MACADDR,6,2).":".
			substr($MACADDR,8,2).":".
			substr($MACADDR,10,2).":".
			substr($MACADDR,12,2);
               }

               $STATUS=$session->get_request(-varbindlist => [ $snmp_ifadminstatus.$ref ]);
               if ( $STATUS->{$snmp_ifadminstatus.$ref} == 1 ) {
                  $STATUS="Up";
               } else {
		  $STATUS="Down";
               }

	       $DESCRIPTION=$session->get_request(-varbindlist => [ $snmp_description.$ref ]);
               if ( defined( $DESCRIPTION ) ) {
		  $DESCRIPTION=$DESCRIPTION->{$snmp_description.$ref};
               }
               $DEVICEADDRESS=$session->get_entries( -columns => [ $snmp_cisco_deviceAddress.$ref ] );
               #print Dumper ( $DEVICEADDRESS ) ;
	       if ( defined( $DEVICEADDRESS ) ) {
                  foreach my $key ( keys %{$DEVICEADDRESS} ) {
		     $DEVICEADDRESS=$DEVICEADDRESS->{$key} ;
                     $DEVICEADDRESS=hex(substr($DEVICEADDRESS,2,2)).
					".".hex(substr($DEVICEADDRESS,4,2)).
					".".hex(substr($DEVICEADDRESS,6,2)).
					".".hex(substr($DEVICEADDRESS,8,2));
                  }
               }
               $DEVICEID=$session->get_entries( -columns => [ $snmp_cisco_deviceId.$ref ] );
	       if ( defined( $DEVICEID ) ) {
                  foreach my $key ( keys %{$DEVICEID} ) {
		     $DEVICEID=$DEVICEID->{$key};
                  }
               }
           }

	   $common->addNetwork( { 
		DESCRIPTION   => $DESCRIPTION,
		DRIVER        => $DRIVER,
		MACADDR       => $MACADDR,
		PCISLOT       => $PCISLOT,
		STATUS        => $STATUS,
		TYPE          => $TYPE,
		VIRTUALDEV    => $VIRTUALDEV,
      DEVICEADDRESS => $DEVICEADDRESS,
      DEVICEID      => $DEVICEID,
		});
           $DESCRIPTION="";
           $DRIVER="";
	   $MACADDR="";
	   $PCISLOT="";
	   $STATUS="";
	   $TYPE="";
	   $VIRTUALDEV="";
           $DEVICEADDRESS="";
           $DEVICEID="";
        }
    }
    # We have finished for interfaces

    # We are looking for cards
    $result_snmp=$session->get_entries(-columns => [$snmp_elements]);
    foreach my $result ( keys  %{$result_snmp} ) {
        if ( $result =~ /1\.3\.6\.1\.2\.1\.47\.1\.1\.1\.1\.8\.(\S+)/ ) {
           $ref=$1;
           my $REF="";
           my $SERIAL="";
           my $SOFTWARE="";
           my $FIRMWARE="";
           if ( $result_snmp->{$snmp_elements.".".$ref} =~ /\S+/ ) {
             # We have a good element
             $DESCRIPTION=$session->get_request(-varbindlist => [$snmp_info.$ref]);
               if ( defined( $DESCRIPTION ) ) {
                   $DESCRIPTION= $DESCRIPTION->{$snmp_info.$ref};
               }
             $REF=$session->get_request(-varbindlist => [$snmp_ref.$ref]);
               if ( defined( $REF ) ) {
                   $REF = $REF->{$snmp_ref.$ref};
               }
             $SERIAL=$session->get_request(-varbindlist => [$snmp_serial.$ref]);
               if ( defined( $SERIAL ) ) {
                   $SERIAL = $SERIAL->{$snmp_serial.$ref};
               }
             $FIRMWARE=$session->get_request(-varbindlist => [$snmp_firmware.$ref]);
               if ( defined( $FIRMWARE ) ) {
                   $FIRMWARE = $FIRMWARE->{$snmp_firmware.$ref};
               }
             $SOFTWARE=$session->get_request(-varbindlist => [$snmp_software.$ref]);
               if ( defined( $SOFTWARE ) ) {
                   $SOFTWARE = $SOFTWARE->{$snmp_software.$ref};
               }
           }
        }
    } # End cards

    
} # end function

1;
