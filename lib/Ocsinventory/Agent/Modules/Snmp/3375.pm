###
# SNMP: OID:3375 SYSTEM:F5
###

package Ocsinventory::Agent::Modules::Snmp::3375;

use strict;
no strict 'refs';
use warnings;

sub snmp_info {
    return ( { oid_value => "1.3.6.1.4.1.3375.2.1.1.1.1.1.0", oid_name => "F5 Mib" } );
}

sub snmp_run() {
    my ($session,$snmp) = @_;

    my $common = $snmp->{common};
    my $logger=$snmp->{logger};

    $logger->debug("Running F5 (3375) MIB module");
    $common->setSnmpCommons( {TYPE => "Load Balanceur"} );

    my $list_mib=["If_Mib"];

    foreach my $mib ( @{$list_mib} ) {
        $snmp->snmp_oid_run($mib);
    }

    my $snmp_sysProductName="1.3.6.1.4.1.3375.2.1.4.1.0";
    my $snmp_sysProductVersion="1.3.6.1.4.1.3375.2.1.4.2.0";
    my $snmp_sysProductBuild="1.3.6.1.4.1.3375.2.1.4.3.0";
    my $snmp_sysProductDate="1.3.6.1.4.1.3375.2.1.4.5.0";
    my $snmp_sysGeneralHwNumber="1.3.6.1.4.1.3375.2.1.3.3.2.0";
    my $snmp_sysGeneralChassisSerialNum="1.3.6.1.4.1.3375.2.1.3.3.3.0";

    my $NAME=$session->get_request ( -varbindlist => [ $snmp_sysProductName ] );
    if ( defined ( $NAME ) ) {
        $NAME=$NAME->{$snmp_sysProductName};
    }

    my $VERSION=$session->get_request ( -varbindlist => [ $snmp_sysProductVersion] );
    if ( defined ( $VERSION ) ) {
        $VERSION=$VERSION->{$snmp_sysProductVersion};
    }

    my $COMMENT=$session->get_request ( -varbindlist => [ $snmp_sysProductBuild] );
    if ( defined ( $COMMENT ) ) {
        $COMMENT=$COMMENT->{$snmp_sysProductBuild};
    }

    my $DATE=$session->get_request ( -varbindlist => [ $snmp_sysProductDate ] );
    if ( defined ( $DATE ) ) {
        $DATE=$DATE->{$snmp_sysProductDate};
    }

    $common->addSoftware( { NAME => $NAME ,
        VERSION => $VERSION ,
        INSTALLDATE => $DATE ,
        COMMENT => $COMMENT,
    });

    my $TYPE=$session->get_request ( -varbindlist => [ $snmp_sysGeneralHwNumber ] ) ;
    if ( defined ( $TYPE ) ) {
        $TYPE=$TYPE->{$snmp_sysGeneralHwNumber};
    }
    my $SERIALNUMBER=$session->get_request ( -varbindlist => [ $snmp_sysGeneralChassisSerialNum ] ) ;
    if ( defined ( $SERIALNUMBER ) ) {
        $SERIALNUMBER=$SERIALNUMBER->{$snmp_sysGeneralChassisSerialNum};
    }
    $common->setSnmpLoadBalancer({ 
        SERIALNUMBER => $SERIALNUMBER ,
        TYPE => $TYPE ,
        MANUFACTURER => "F5" ,
    });

}

1;
