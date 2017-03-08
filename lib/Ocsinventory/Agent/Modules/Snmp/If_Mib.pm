###
# SNMP: OID: 2 SYSTEM: If_Mib
###
package Ocsinventory::Agent::Modules::Snmp::If_Mib;

use strict;
use warnings;
use Data::Dumper;

sub snmp_info {
   return ( { oid_value => "1.3.6.1.2.1.2.1.0", oid_name => "If_Mib" } );
}

sub snmp_run {
    my ($session , $snmp )= @_;
    my $logger=$snmp->{logger};
    my $common=$snmp->{common};
    
    $logger->debug("Running If MIB module");   
 
    # OID a recuperer
    my $snmp_ifdescr="1.3.6.1.2.1.2.2.1.2.";
    my $snmp_iftype="1.3.6.1.2.1.2.2.1.3";
    my $snmp_ifspeed="1.3.6.1.2.1.2.2.1.5.";
    my $snmp_physAddr="1.3.6.1.2.1.2.2.1.6.";
    my $snmp_ifadminstatus="1.3.6.1.2.1.2.2.1.7.";
    my $snmp_ipAdEntIfIndex="1.3.6.1.2.1.4.20.1.2";
    my $snmp_ipAdEntNetMask="1.3.6.1.2.1.4.20.1.3.";
    my $snmp_ipRouteIfIndex="1.3.6.1.2.1.4.21.1.2.";
    my $snmp_ipRouteNextHop="1.3.6.1.2.1.4.21.1.7.";
    my $snmp_ipRouteType="1.3.6.1.2.1.4.21.1.8";
 
    my $SPEED=undef; 
    my $MACADDR=undef;
    my $SLOT=undef; 
    my $STATUS=undef; 
    my $TYPE=undef;
    my $IPADDR=undef;
    my $IPMASK=undef;
    my $IPGATEWAY=undef;
    my $IPSUBNET=undef;
 
    my $ref;
    my $result_snmp;
    my $address_index=undef;
    my $netmask_index=undef;
    my $network_index=undef;
    my $gateway_index=undef;
 
    # the informations on ip address and gateway are not indexed on the 
    # interfaces so we must get it before and integrate the informations
    # after for each interface
 
    # We take all the snmp_ifadminstatus in a table if this information exist
    $result_snmp=$session->get_entries(-columns => [$snmp_ipAdEntIfIndex]);
    foreach my $result ( keys %{$result_snmp} ) {
        if ( $result =~ /1\.3\.6\.1\.2\.1\.4\.20\.1\.2\.(\S+)/ ) {
            my $address=$1;
            $address_index->{$result_snmp->{$result}}=$address;
            # We have the address so we can lokk for the netmask associated
            my $netmask=$session->get_request(-varbindlist => [$snmp_ipAdEntNetMask.$address]);
            if ( defined($netmask->{$snmp_ipAdEntNetMask.$address} ) ) {
                $netmask_index->{$result_snmp->{$result}}=$netmask->{$snmp_ipAdEntNetMask.$address};
            }
        }
    }
    # now we can do the same thing for the gateway
    # It is an other index and only indirect information (routing)
    # are required actually
    $result_snmp=$session->get_entries(-columns => [$snmp_ipRouteType]);
    foreach my $result ( keys %{$result_snmp} ) {
        if ( $result =~ /1\.3\.6\.1\.2\.1\.4\.21\.1\.8\.(\S+)/ ) {
            if ( $result_snmp->{$result} == 4  ) {
                my $net_address=$1;
                # We get the index associating the interface with the gateway and subnet
                my $ind=$session->get_request(-varbindlist => [$snmp_ipRouteIfIndex.$net_address] );
                $ind=$ind->{$snmp_ipRouteIfIndex.$net_address};
                # We ave already the network so we can add the information with the index
                $network_index->{$ind}=$net_address;
                # We get the gateway
                my $gateway=$session->get_request(-varbindlist => [$snmp_ipRouteNextHop.$net_address] );
                $gateway_index->{$ind}=$gateway->{$snmp_ipRouteNextHop.$net_address};
            }
        }
    }

    # We look for interfaces
    $result_snmp=$session->get_entries(-columns => [$snmp_iftype]);
    foreach my $result ( keys  %{$result_snmp} ) {
        # We work on real interface and no vlan
        $TYPE=$result_snmp->{$result};
        if ( $TYPE == 6 || $TYPE == 94) {
            if ( $result =~ /1\.3\.6\.1\.2\.1\.2\.2\.1\.3\.(\S+)/ ) {
                $ref=$1;
                if ( $TYPE == 6 ) {
                    $TYPE="ethernetCsmacd";
                } elsif ( $TYPE ==  94 ) {
                    $TYPE="Adsl";
                }
 
                my $intero=$snmp_ifdescr.$ref;
                #$SLOT=$session->get_request(-varbindlist => [$snmp_ifdescr.$ref]);
                $SLOT=$session->get_request(-varbindlist => [$intero]);
                $SLOT=" $SLOT->{$snmp_ifdescr.$ref}";
                #print "Pour $intero Le slot $SLOT\n";
 
                $SPEED=$session->get_request(-varbindlist => [$snmp_ifspeed.$ref]);
                if ( defined( $SPEED->{$snmp_ifspeed.$ref}) ) {
                    $SPEED=$SPEED->{$snmp_ifspeed.$ref};
                    if ( $SPEED / 1000000000000 >= 1 ) {
                        $SPEED=$SPEED / 1000000000000;
                        $SPEED=$SPEED." T";
                    } elsif ( $SPEED / 1000000000 >= 1 ) {
                        $SPEED=$SPEED / 1000000000;
                        $SPEED=$SPEED." G";
                    } elsif ( $SPEED / 1000000 >= 1 ) {
                        $SPEED=$SPEED / 1000000;
                        $SPEED=$SPEED." M";
                    }
                }
 
                $MACADDR=$session->get_request(-varbindlist => [$snmp_physAddr.$ref]);
                if ( defined( $MACADDR->{$snmp_physAddr.$ref}) ) {
                    # For MACADDR, we need a translation beetween Hexa and string
                    $MACADDR=" ".$MACADDR->{$snmp_physAddr.$ref};
                    if ( $MACADDR =~ /^ 0x(\w{2})(\w{2})(\w{2})(\w{2})(\w{2})(\w{2})$/ ) {
                        $MACADDR="$1:$2:$3:$4:$5:$6";
                    }
 
                    $STATUS=$session->get_request(-varbindlist => [ $snmp_ifadminstatus.$ref ]);
                    if ( $STATUS->{$snmp_ifadminstatus.$ref} == 1 ) {
                        $STATUS="Up";
                    } else {
                        $STATUS="Down";
                    }
     
                    # If we have the address ip and netmask we can put it
                    if ( defined ( $address_index ) ) {
                        $IPADDR=$address_index->{$ref};
                        $IPMASK=$netmask_index->{$ref};
                        #if ( defined ($IPADDR ) ) {
                        #    my $local_info=$session->hostname();
                        #    print "$IPADDR et $local_info";
                        #    if ( $IPADDR eq $session->hostname() ) {
                        #        $common->setSnmpCommons({MACADDR => $MACADDR });
                        #    }
                        #}
                    }
                    if ( defined ( $network_index ) ) {
                        $IPGATEWAY=$gateway_index->{$ref};
                        $IPSUBNET=$network_index->{$ref};
                    }
                    $common->addSnmpNetwork( {
                        TYPE      => $TYPE,
                        SLOT      => $SLOT,
                        SPEED     => $SPEED,
                        MACADDR   => $MACADDR,
                        STATUS    => $STATUS,
                        IPADDR    => $IPADDR,
                        IPMASK    => $IPMASK,
                        IPGATEWAY => $IPGATEWAY,
                        IPSUBNET  => $IPSUBNET,
                    });
                    $MACADDR=undef;
                    $SLOT=undef;
                    $STATUS=undef;
                    $SPEED=undef;
                    $IPADDR=undef;
                    $IPMASK=undef;
                    $IPGATEWAY=undef;
                    $IPSUBNET=undef;
                }
            }
        } 
    } # End foreach result
}

1;
