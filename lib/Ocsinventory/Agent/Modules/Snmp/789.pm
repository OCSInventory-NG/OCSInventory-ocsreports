###
# SNMP: OID:789 SYSTEM:NetApp
###

package Ocsinventory::Agent::Modules::Snmp::789;

use strict;
no strict 'refs';
use warnings;

sub snmp_run() {
    my ($session,$snmp) = @_;

    my $common = $snmp->{common};
    my $logger=$snmp->{logger};

    $logger->debug("Running NetApp (789) MIB module");
    $common->setSnmpCommons( {TYPE => "Storage"} );

    my $list_mib=["If_Mib"];

    foreach my $mib ( @{$list_mib} ) {
        $snmp->snmp_oid_run($mib);
    }

    my $snmp_sysProductName="1.3.6.1.4.1.789.1.1.5.0";
    my $snmp_sysProductVersion="1.3.6.1.4.1.789.1.1.2.0";
    my $snmp_sysProductBuild="1.3.6.1.4.1.789.1.1.3.0";
    my $snmp_volumetable="1.3.6.1.4.1.789.1.5.4.1.2";
    my $snmp_storageSize="1.3.6.1.4.1.789.1.5.4.1.29.";
    my $snmp_storageUsed="1.3.6.1.4.1.789.1.5.4.1.30.";
    
    my $name=$session->get_request ( -varbindlist => [ $snmp_sysProductName ] );
    if ( defined ( $name ) ) {
        $name=$name->{$snmp_sysProductName};
    }
    my $version=$session->get_request ( -varbindlist => [ $snmp_sysProductVersion] );
    if ( defined ( $version ) ) {
        $version=$version->{$snmp_sysProductVersion};
    }

    my $comment=$session->get_request ( -varbindlist => [ $snmp_sysProductBuild] );
    if ( defined ( $comment ) ) {
        $comment=$comment->{$snmp_sysProductBuild};
    }

    $common->addSoftware( { NAME => $name ,
        VERSION => $version ,
        COMMENT => $comment,
    });
    my $result;
    my $result_table;
    my $ref;

    # We look for volumes
    $result_table=$session->get_entries(-columns => [ $snmp_volumetable ]);
    foreach my $result ( keys %{$result_table} ) {
        if ( $result =~ /1\.3\.6\.1\.4\.1\.789\.1\.5\.4\.1\.2\.(\S+)/ ) {
            $ref=$1;
            # Definition Var for disks
            my $FREE="";
            my $FILESYSTEM="";
            my $TOTAL="";
            $TOTAL=$session->get_request(-varbindlist => [ $snmp_storageSize.$ref ]);
            if ( defined ( $TOTAL ) ) {
                $TOTAL=$TOTAL->{$snmp_storageSize.$ref};
            }
            #print $TOTAL, " ";
            $FREE=$session->get_request(-varbindlist => [ $snmp_storageUsed.$ref ]);
            if ( defined ( $FREE ) ) {
                $FREE=$TOTAL - $FREE->{$snmp_storageUsed.$ref};
            }
            #print $FREE, " ";
            $FILESYSTEM=$session->get_request(-varbindlist => [ $snmp_volumetable."\.".$ref ]);
            #print $snmp_volumetable."\.".$ref, " ";
            if ( defined ( $FILESYSTEM) ) {
                $FILESYSTEM=$FILESYSTEM->{$snmp_volumetable."\.".$ref};
            }
            #print $FILESYSTEM, "\n";

            $common->addDrive( {
                FILESYSTEM => $FILESYSTEM ,
                FREE => $FREE,
                TOTAL => $TOTAL,
            }),
        } # End index of the element
    } # End foreach storages
}

1;
