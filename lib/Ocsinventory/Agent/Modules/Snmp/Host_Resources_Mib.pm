package Ocsinventory::Agent::Modules::Snmp::Host_Resources_Mib;

use strict;
use warnings;

sub snmp_info {
    return ( { oid_value => "1.3.6.1.2.1.25.1.1.0", oid_name => "Host_Resources_Mib" } );
}

sub snmp_run {
    my ($session , $snmp )= @_;
    my $logger=$snmp->{logger};
    my $common=$snmp->{common};
    
    $logger->debug("Running Host Resources MIB module");

    # OID 
    my $soft="1.3.6.1.2.1.25.6.3.1.";
    my $memory="1.3.6.1.2.1.25.2.2.0";
    my $snmp_storageType="1.3.6.1.2.1.25.2.3.1.2";
    my $snmp_storageDescr="1.3.6.1.2.1.25.2.3.1.3.";
    my $snmp_storageSize="1.3.6.1.2.1.25.2.3.1.5.";
    my $snmp_storageUsed="1.3.6.1.2.1.25.2.3.1.6.";
    my $snmp_storageAllocationUnits="1.3.6.1.2.1.25.2.3.1.4.";

    my $snmp_hrDeviceType="1.3.6.1.2.1.25.3.2.1.2";
    my $snmp_cpu="1.3.6.1.2.1.25.3.2.1.3.";
    my $snmp_Printer="1.3.6.1.2.1.25.3.2.1.5.";
    my $snmp_Video="1.3.6.1.2.1.25.3.2.1.10.";
    my $snmp_Audio="1.3.6.1.2.1.25.3.2.1.11.";
    my $snmp_Keyboard="1.3.6.1.2.1.25.3.2.1.13.";
    my $snmp_Modem="1.3.6.1.2.1.25.3.2.1.14.";
    my $snmp_ParallelPort="1.3.6.1.2.1.25.3.2.1.15.";
    my $snmp_Pointing="1.3.6.1.2.1.25.3.2.1.16.";
    my $snmp_SerialPort="1.3.6.1.2.1.25.3.2.1.17.";

    my $DATE;

    my $result;
    my $result_table;
    my $ref;

    $result_table=$session->get_entries(-columns => [ $soft."2" ] );
    foreach my $result ( keys %{$result_table} ) {
        if ( $result =~ /1\.3\.6\.1\.2\.1\.25\.6\.3\.1\.2\.(\S+)/ ) {
            $ref=$1;
            $DATE=$session->get_request ( -varbindlist => [ $soft."5.".$ref ]);
            if ( defined ( $DATE ) ) {
                $DATE=$DATE->{$soft."5.".$ref};
                 if ( defined ($DATE ) ) {
                     if (length($DATE)>0) {
                         $DATE=sprintf("%d/%d/%d",hex(substr($DATE,2,4)),hex(substr($DATE,6,2)),hex(substr($DATE,8,2)));
                     }
                 }
            }
            $common->addSoftware( { 
                NAME=>$result_table->{ $result } ,
                INSTALLDATE=>$DATE
            } );
        }
    }
    
    # Memory
    $result=$session->get_request(-varbindlist => [ $memory ]);
    if ( defined ($result) ) {
        $common->addMemory( {CAPACITY => $result->{$memory} } );
    }

    # We look for disk
    $result_table=$session->get_entries(-columns => [ $snmp_storageType ]);
    foreach my $result ( keys %{$result_table} ) {
        if ( $result =~ /1\.3\.6\.1\.2\.1\.25\.2\.3\.1\.2\.(\S+)/ ) {
            $ref=$1;
            # We look if the storage is a fixed disk
            if ( $result_table->{$result} =~ /2\.1\.4$/ ) {
                # Definition Var for disks
                my $FREE="";
                my $FILESYSTEM="";
                my $LABEL="";
                my $SERIAL="";
                my $TOTAL="";
                my $TYPE="HardDisk";
                my $VOLUMN="";
                my $AllocUnits=$session->get_request(-varbindlist => [ $snmp_storageAllocationUnits.$ref ]);
                if ( defined ( $AllocUnits ) ) {
                    $AllocUnits=$AllocUnits->{$snmp_storageAllocationUnits.$ref};
                    # Allocation Units are in Bytes, but we want KB
                    if ( $AllocUnits )  {
                        $AllocUnits/=1024;
                    }
                } else {
                    $AllocUnits=1;
                }
                $TOTAL=$session->get_request(-varbindlist => [ $snmp_storageSize.$ref ]);
                if ( defined ( $TOTAL ) ) {
                    $TOTAL=$TOTAL->{$snmp_storageSize.$ref};
                    $TOTAL*=$AllocUnits;
                } else {
                    $TOTAL=0;
                }
                $FREE=$session->get_request(-varbindlist => [ $snmp_storageUsed.$ref ]);
                if ( defined ( $FREE ) ) {
                    $FREE=$TOTAL - $FREE->{$snmp_storageUsed.$ref}* $AllocUnits;
                } else {
                    $FREE=0;
                }
                $FILESYSTEM=$session->get_request(-varbindlist => [ $snmp_storageDescr.$ref ]);
                if ( defined ( $FILESYSTEM) ) {
                    $FILESYSTEM=$FILESYSTEM->{$snmp_storageDescr.$ref};
                }

                if ( $FILESYSTEM =~ /(\S+)\s+Label:(\S+)\s+Serial Number\s+(\S+)/ ) {
                    $VOLUMN=$1;
                    $LABEL=$2;
                    $SERIAL=$3;
                    $FILESYSTEM="";
                } elsif ( $FILESYSTEM =~ /(\S+)\s+Label:\s+Serial Number\s+(\S+)/ ) {
                    $VOLUMN=$1;
                    $SERIAL=$2;
                    $FILESYSTEM="";
                } 
                $common->addDrive( { 
                    FILESYSTEM => $FILESYSTEM ,
                    VOLUMN => $VOLUMN,
                    TYPE => $TYPE,
                    LABEL => $LABEL,
                    SERIAL => $SERIAL,
                    FREE => $FREE,
                    TOTAL => $TOTAL,
                }),
            } # End looking for fixed disk
        } # End index of the element
    } # End foreach storages
    $result_table=$session->get_entries(-columns => [ $snmp_hrDeviceType ]);
    foreach my $result ( keys %{$result_table} ) {
        if ( $result =~ /1\.3\.6\.1\.2\.1\.25\.3\.2\.1\.2\.(\S+)/ ) {
            $ref=$1;
            if  ( $result_table->{$result} =~ /3\.1\.3$/ ) {
                # We have a CPU
                my $CPU=$session->get_request(-varbindlist => [ $snmp_cpu.$ref ] );
                if ( defined ( $CPU ) ) {
                    $CPU=$CPU->{$snmp_cpu.$ref};
                    my $TYPE=$CPU;
                    my $MANUFACTURER="";
                    my $SPEED="";
                    if ( $CPU =~ /^Intel$/ ) {
                        # We have a windows system and for all processor 
                        # it say Intel even if the proc is an AMD
                          $MANUFACTURER="x86 Manufacturer";
                        $TYPE="x86";
                    } elsif ( $CPU =~ /[Ii]nte/ ) {
                          $MANUFACTURER="Intel";
                        if ( $CPU =~ /\s+(\S+[GM][Hh][zZ])/ ) {
                              $SPEED=$1;
                        }
                    } elsif ( $CPU =~ /AMD/ ) {
                        $MANUFACTURER="AMD";
                        if ( $CPU =~ /\s+(\S+\+)/ ) {
                              $SPEED=$1;
                        }
                    }
                    $logger->debug("Speed $SPEED Manufacturer $MANUFACTURER Type $TYPE");
                    $common->addSnmpCPU( { 
                        SPEED => $SPEED,
                        TYPE => $TYPE,
                        MANUFACTURER => $MANUFACTURER,
                    } );
                }
            }
        } #end CPU
        elsif ( $result_table->{$result} =~ /3\.1\.5$/ ) {
            # We have a Printer
            my $NAME=$session->get_request(-varbindlist => [ $snmp_Printer.$ref ] );
            if ( defined ($NAME) ) {
                 $NAME=$NAME->{$snmp_Printer.$ref };
                 $common->addSnmpLocalPrinter( { NAME => $NAME } );
            }
        } # End Printer
        elsif  ( $result_table->{$result} =~ /3\.1\.13$/ ) {
            # We have a Keyboard
            my $DESCRIPTION=$session->get_request(-varbindlist => [ $snmp_Keyboard.$ref ] );
            if ( defined ($DESCRIPTION) ) {
                 $DESCRIPTION=$DESCRIPTION->{$snmp_Keyboard.$ref };
                 $common->addSnmpInput( { DESCRIPTION => $DESCRIPTION , TYPE => "Keyboard" } );
            }
        } # End Keyboard
        elsif  ( $result_table->{$result} =~ /3\.1\.16$/ ) {
            # We have a Pointing
            my $DESCRIPTION=$session->get_request(-varbindlist => [ $snmp_Pointing.$ref ] );
            if ( defined ($DESCRIPTION) ) {
                 $DESCRIPTION=$DESCRIPTION->{$snmp_Pointing.$ref };
                 $common->addSnmpInput( { DESCRIPTION => $DESCRIPTION , TYPE => "Mouse" } );
            }
        } # End Pointing
        elsif  ( $result_table->{$result} =~ /3\.1\.11$/ ) {
            # We have a Audio
            my $NAME=$session->get_request(-varbindlist => [ $snmp_Audio.$ref ] );
            if ( defined ($NAME) ) {
                 $NAME=$NAME->{$snmp_Audio.$ref };
                 $common->addSound( { NAME => $NAME } );
            }
        } # End Audio
        elsif  ( $result_table->{$result} =~ /3\.1\.10$/ ) {
            # We have a Video
            my $NAME=$session->get_request(-varbindlist => [ $snmp_Video.$ref ] );
            if ( defined ($NAME) ) {
                 $NAME=$NAME->{$snmp_Video.$ref };
                 $common->addVideo( { NAME => $NAME } );
            }
        } # End Video
        elsif  ( $result_table->{$result} =~ /3\.1\.14$/ ) {
            # We have a Modem
            my $NAME=$session->get_request(-varbindlist => [ $snmp_Modem.$ref ] );
            if ( defined ($NAME) ) {
                 $NAME=$NAME->{$snmp_Modem.$ref };
                 $common->addModem( { NAME => $NAME } );
            }
        } # End Modem
        elsif  ( $result_table->{$result} =~ /3\.1\.15$/ ) {
            # We have a ParallelPort
            my $NAME=$session->get_request(-varbindlist => [ $snmp_ParallelPort.$ref ] );
            if ( defined ($NAME) ) {
                 $NAME=$NAME->{$snmp_ParallelPort.$ref };
                 $common->addPorts( { NAME => $NAME , TYPE => "Parallel" } );
            }
        } # End ParallelPort
        elsif  ( $result_table->{$result} =~ /3\.1\.17$/ ) {
            # We have a SerialPort
            my $NAME=$session->get_request(-varbindlist => [ $snmp_SerialPort.$ref ] );
            if ( defined ($NAME) ) {
                 $NAME=$NAME->{$snmp_SerialPort.$ref };
                 $common->addPorts( { NAME => $NAME , TYPE => "Serial" } );
            }
        } # End SerialPort
    } # End scan of hrDeviceType
}

1;
