###
# SNMP: OID:3375 SYSTEM:F5
###

package Ocsinventory::Agent::Modules::Snmp::3375;

use strict;
no strict 'refs';
use warnings;

sub snmp_info {
   return ( { oid_value => "1.3.6.1.4.1.3375.2.1.1.1.1.1.0",
            oid_name => "F5 Mib" } );
}

sub snmp_run()
{
  my ($session,$snmp) = @_;

  my $common = $snmp->{common};
  my $logger=$snmp->{logger};

  $logger->debug("Execution mib F5 mib");
  $common->setSnmpCommons( {TYPE => "Load Balancer"} );

  my $list_mib=["If_Mib"];

  foreach my $mib ( @{$list_mib} ) {
     $logger->debug("Sub mib $mib");
     $snmp->snmp_oid_run($mib);
  }

  my $snmp_sysProductName="1.3.6.1.4.1.3375.2.1.4.1.0";
  my $snmp_sysProductVersion="1.3.6.1.4.1.3375.2.1.4.2.0";
  my $snmp_sysProductBuild="1.3.6.1.4.1.3375.2.1.4.3.0";
  my $snmp_sysProductDate="1.3.6.1.4.1.3375.2.1.4.5.0";
  my $snmp_sysGeneralHwNumber="1.3.6.1.4.1.3375.2.1.3.3.2.0";
  my $snmp_sysGeneralChassisSerialNum="1.3.6.1.4.1.3375.2.1.3.3.3.0";

  
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

  my $date=$session->get_request ( -varbindlist => [ $snmp_sysProductDate ] );
  if ( defined ( $date ) ) {
     $date=$date->{$snmp_sysProductDate};
  }

  $common->addSoftware( {
    NAME => $name ,
    VERSION => $version ,
    INSTALLDATE => $date ,
    COMMENT => $comment,
  });

  my $type=$session->get_request ( -varbindlist => [ $snmp_sysGeneralHwNumber ] ) ;
  if ( defined ( $type ) ) {
     $type=$type->{$snmp_sysGeneralHwNumber};
  }
  my $serialnumber=$session->get_request ( -varbindlist => [ $snmp_sysGeneralChassisSerialNum ] ) ;
  if ( defined ( $serialnumber ) ) {
     $serialnumber=$serialnumber->{$snmp_sysGeneralChassisSerialNum};
  }
  $common->setSnmpLoadBalancer({
    SERIALNUMBER => $serialnumber ,
    TYPE => $type ,
    MANUFACTURER => "F5" ,
  });

}

1;
