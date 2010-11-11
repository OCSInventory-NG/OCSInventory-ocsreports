###
# SNMP: OID: 9 SYSTEM: Cisco
###
# Version 0.9
###

package Ocsinventory::Agent::Modules::Snmp::9;

use strict;
use warnings;

use Data::Dumper;


sub snmp_run {
   my ($session , $snmp )= @_;
   my $logger=$snmp->{logger};
   my $common=$snmp->{common};

   my $list_mib=["Entity_Mib"];
   foreach my $mib ( @{$list_mib} ) {
      $logger->debug("Sub mib $mib");
      $snmp->snmp_oid_run($mib);
   }

   # OID 
   my $snmp_osversion="1.3.6.1.4.1.9.2.1.73.0";
   my $snmp_dot1dBasePortIfIndex="1.3.6.1.2.1.17.1.4.1.2.";
   my $snmp_ifdescr="1.3.6.1.2.1.2.2.1.2";
   my $snmp_iftype="1.3.6.1.2.1.2.2.1.3.";
   my $snmp_ifspeed="1.3.6.1.2.1.2.2.1.5.";
   my $snmp_physAddr="1.3.6.1.2.1.2.2.1.6.";
   my $snmp_ifadminstatus="1.3.6.1.2.1.2.2.1.7.";
   # Specific Cisco
   my $snmp_description="1.3.6.1.4.1.9.2.2.1.1.28.";
   my $snmp_cisco_deviceAddress="1.3.6.1.4.1.9.9.23.1.2.1.1.4.";
   my $snmp_cisco_deviceId="1.3.6.1.4.1.9.9.23.1.2.1.1.6.";
   my $snmp_cisco_devicePort="1.3.6.1.4.1.9.9.23.1.2.1.1.7.";
   my $snmp_cisco_devicePlatform="1.3.6.1.4.1.9.9.23.1.2.1.1.8.";
   my $snmp_vtp_vlan_state="1.3.6.1.4.1.9.9.46.1.3.1.1.2";
   my $snmp_dot1dTpFdbPort="1.3.6.1.2.1.17.4.3.1.2";


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
   my $index_mac={};
   my $ref_mac={};


   my $DESCRIPTION=undef; 
   my $SPEED=undef; 
   my $MACADDR=undef;
   my $DEVICEMACADDR=undef;
   my $SLOT=undef;
   my $STATUS=undef;
   my $TYPE=undef; 
   my $DEVICENAME=undef;
   my $DEVICEADDRESS=undef;
   my $DEVICEPORT=undef;
   my $DEVICETYPE=undef;
   my $VLAN=undef;

    $common->setSnmpCommons( {TYPE => "Network"} );

 # Info interessante SNMPv2-SMI::enterprises.9.9.23.1.2.1.1.6.10140.1
 # SNMPv2-SMI::enterprises.9.9.25.1.1.1.2.1 

    # version IOS
    $result_snmp=$session->get_request(-varbindlist => [$snmp_osversion]);
    if ( defined($result_snmp->{$snmp_osversion}) ) {
       $osversion=$result_snmp->{$snmp_osversion};
    }
    
    # We are going to look for the vlan existing on this equipment
    $result_snmp=$session->get_entries(-columns => [$snmp_vtp_vlan_state]);
    foreach my $resultmac ( keys  %{$result_snmp} ) {
       if ( $resultmac =~ /1\.3\.6\.1\.4\.1\.9\.9\.46\.1\.3\.1\.1\.2\.1\.(\S+)/ ) {
           my $ref_vlan=$1;
           # Now we can scan this vlan for mac adress
           # We must first open a new session with the index associated with the vlan
           my $sub_session= Net::SNMP->session(
                -retries     => 1 ,
                -timeout     => 3,
                -version     => $session->version,
                -hostname    => $session->hostname,
                -community   => $snmp->{snmp_community}."@".$ref_vlan,
                -translate   => [-nosuchinstance => 0, -nosuchobject => 0],
                #-username      => $comm->{username}, # V3 test after
                #-authkey       => $comm->{authkey},
                #-authpassword  => $comm->{authpasswd},
                #-authprotocol  => $comm->{authproto},
                #-privkey       => $comm->{privkey},
                #-privpassword  => $comm->{privpasswd},
                #-privprotocol  => $comm->{privproto},
          );
          my $result_snmp_mac=$sub_session->get_entries(-columns => [$snmp_dot1dTpFdbPort ]);
          if ( defined ($result_snmp_mac ) ) {
             # We scan all lines 
             for my $ligne_snmp_mac ( keys %{$result_snmp_mac} ) {
                 # We first take in the OID the 6 last numbers indicate in decimal the mac address
                 if ( $ligne_snmp_mac =~ /17\.4\.3\.1\.2\.(\S+)\.(\S+)\.(\S+)\.(\S+)\.(\S+)\.(\S+)$/ ) {
                    my $distant_mac=sprintf("%.2x:%.2x:%.2x:%.2x:%.2x:%.2x",$1,$2,$3,$4,$5,$6);
                    my $data_values={};
                    my $index_bridge=$result_snmp_mac->{$ligne_snmp_mac};

                    # We have no table for this reference
                    if ( ! defined ( $index_mac->{$index_bridge}) ) {
                        # init of the table
                        $index_mac->{$index_bridge}=[];
                        # we take the value gived by the OID
                        my $snmp_intero=$snmp_dot1dBasePortIfIndex.$index_bridge;
                        # We take the index reference for the ifdesc
                        # So when we scan this ifdesc, we can add the vlans and mac 
                        my $ref_snmp_line=$sub_session->get_request(-varbindlist => [ $snmp_intero ]);
                        # We transmit the ointer value to the ref_mac so we can have a double acces for the data
                        # If we have no information: the mac is not associated with a port
                        # It's the switch mac adress
                        if ( defined ( $ref_snmp_line->{$snmp_intero}) ) {
                           $ref_mac->{$ref_snmp_line->{$snmp_intero}}=$index_mac->{$index_bridge};
                        }
                    }
                    $data_values->{MACADRESS}[0]=$distant_mac;
                    $data_values->{VLANID}[0]=$ref_vlan;
                    push(@{$index_mac->{$result_snmp_mac->{$ligne_snmp_mac}}},$data_values);
                 }
             }
          }
          $sub_session->close;
       }
       
   }

    # We look for interfaces
    $result_snmp=$session->get_entries(-columns => [$snmp_ifdescr]);
    foreach my $result ( keys  %{$result_snmp} ) {
        # We work on real interface and no vlan
        if ( $result_snmp->{$result} =~ /Ethernet/ ) {
	   $SLOT=$result_snmp->{$result};
           if ( $result =~ /1\.3\.6\.1\.2\.1\.2\.2\.1\.2\.(\S+)/ ) {
               $ref=$1;

               $TYPE=$session->get_request(-varbindlist => [$snmp_iftype.$ref]);
	       if ( defined( $TYPE->{$snmp_iftype.$ref} ) ) {
                   $TYPE= $TYPE->{$snmp_iftype.$ref};
                   if ( $TYPE == 6 ) {
		      $TYPE="ethernetCsmacd";
                   }
               }

               $SPEED=$session->get_request(-varbindlist => [$snmp_ifspeed.$ref]);
               if ( defined( $SPEED->{$snmp_ifspeed.$ref}) ) {
                   $SPEED=$SPEED->{$snmp_ifspeed.$ref};
               }

               $MACADDR=$session->get_request(-varbindlist => [$snmp_physAddr.$ref]);
               if ( defined( $MACADDR->{$snmp_physAddr.$ref}) ) {
                  # For MACADDR, we need a translation beetween Hexa and string
                  $MACADDR=$MACADDR->{$snmp_physAddr.$ref};
	          if ( length ($MACADDR) == 14 ) {
		     $MACADDR=substr($MACADDR,2,2).":".
			substr($MACADDR,4,2).":".
			substr($MACADDR,6,2).":".
			substr($MACADDR,8,2).":".
			substr($MACADDR,10,2).":".
			substr($MACADDR,12,2);
                  } else {
                     $MACADDR="";
                  }
               }
               if ( defined $ref_mac->{$ref} ) {
                  $VLAN=$ref_mac->{$ref};
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
	       if ( defined( $DEVICEADDRESS ) ) {
                  foreach my $key ( keys %{$DEVICEADDRESS} ) {
		     $DEVICEADDRESS=$DEVICEADDRESS->{$key} ;
                     if ( length ( $DEVICEADDRESS ) == 10 ) {
                        $DEVICEADDRESS=hex(substr($DEVICEADDRESS,2,2)).
					".".hex(substr($DEVICEADDRESS,4,2)).
					".".hex(substr($DEVICEADDRESS,6,2)).
					".".hex(substr($DEVICEADDRESS,8,2));
                     } else {
		        $DEVICEADDRESS="";
                     }
                     
                  }
               }
               $DEVICENAME=$session->get_entries( -columns => [ $snmp_cisco_deviceId.$ref ] );
	       if ( defined( $DEVICENAME ) ) {
                  foreach my $key ( keys %{$DEVICENAME} ) {
		     $DEVICENAME=$DEVICENAME->{$key};
                  }
               # If we have the device name, the cdp can be used for the other informations
                  $DEVICETYPE=$session->get_entries( -columns => [ $snmp_cisco_devicePlatform.$ref ] );
	          if ( defined( $DEVICETYPE ) ) {
                     foreach my $key ( keys %{$DEVICETYPE} ) {
		        $DEVICETYPE=$DEVICETYPE->{$key};
                     }
                  }
                  $DEVICEPORT=$session->get_entries( -columns => [ $snmp_cisco_devicePort.$ref ] );
	          if ( defined( $DEVICEPORT ) ) {
                     foreach my $key ( keys %{$DEVICEPORT} ) {
		        $DEVICEPORT=$DEVICEPORT->{$key};
                     }
                  }
               }
           }

	   $common->addSnmpNetwork( { 
		DESCRIPTION   => $DESCRIPTION,
		SPEED	      => $SPEED,
		MACADDR       => $MACADDR,
		SLOT          => $SLOT,
		STATUS        => $STATUS,
		TYPE          => $TYPE,
                DEVICENAME    => $DEVICENAME,
                DEVICEPORT    => $DEVICEPORT,
                DEVICETYPE    => $DEVICETYPE,
                VLAN	      => $VLAN,
		});
           $DESCRIPTION=undef;
	   $MACADDR=undef;
	   $SLOT=undef;
	   $STATUS=undef;
	   $TYPE=undef;
           $SPEED=undef;
	   $MACADDR=undef;
           $DEVICEADDRESS=undef;
           $DEVICENAME=undef;
           $DEVICEPORT=undef;
	   $DEVICETYPE=undef;
           $VLAN=undef;
        }
    }
    # We have finished for interfaces

} 

1;
