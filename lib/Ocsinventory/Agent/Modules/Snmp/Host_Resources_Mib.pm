###
#
###
package Ocsinventory::Agent::Modules::Snmp::Host_Resources_Mib;

use strict;
use warnings;

use Data::Dumper;

sub snmp_run {
   my ($session , $snmp )= @_;
   my $logger=$snmp->{logger};
   my $common=$snmp->{common};
   
   $logger->debug("Running Host_Resource_Mib module");
   # OID 
   my $soft="1.3.6.1.2.1.25.6.3.1.";
   my $memory="1.3.6.1.2.1.25.2.2.0";
   my $snmp_storageType="1.3.6.1.2.1.25.2.3.1.2";
   my $snmp_storageDescr="1.3.6.1.2.1.25.2.3.1.3.";
   my $snmp_storageSize="1.3.6.1.2.1.25.2.3.1.5.";
   my $snmp_storageUsed="1.3.6.1.2.1.25.2.3.1.6.";

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
         $DATE=$session->get_request ( [ $soft."5.".$ref ]);
         $common->addSoftware( { 
			NAME=>$result_table->{ $result } ,
                        DATE=>$DATE
		        } );
      }
   }
   
   # Memory
   $result=$session->get_request(-varbindlist => [ $memory ]);
   if ( defined ($result) ) {
      $common->setHardware( {MEMORY => $result->{$memory} } );
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
            $TOTAL=$session->get_request(-varbindlist => [ $snmp_storageSize.$ref ]);
            if ( defined ( $TOTAL ) ) {
		$TOTAL=$TOTAL->{$snmp_storageSize.$ref};
            }
	    $FREE=$session->get_request(-varbindlist => [ $snmp_storageUsed.$ref ]);
             if ( defined ( $FREE ) ) {
		$FREE=$TOTAL - $FREE->{$snmp_storageUsed.$ref};
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
         }
      }
   }
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
               $common->addCPU( { 
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
             $common->addPrinter( { NAME => $NAME } );
         }
      } 
      elsif  ( $result_table->{$result} =~ /3\.1\.13$/ ) {
         # We have a Keyboard
         my $DESCRIPTION=$session->get_request(-varbindlist => [ $snmp_Keyboard.$ref ] );
         if ( defined ($DESCRIPTION) ) {
             $DESCRIPTION=$DESCRIPTION->{$snmp_Keyboard.$ref };
             $common->addInput( { DESCRIPTION => $DESCRIPTION , TYPE => "Keyboard" } );
         }
      }
      elsif  ( $result_table->{$result} =~ /3\.1\.16$/ ) {
         # We have a Pointing
         my $DESCRIPTION=$session->get_request(-varbindlist => [ $snmp_Pointing.$ref ] );
         if ( defined ($DESCRIPTION) ) {
             $DESCRIPTION=$DESCRIPTION->{$snmp_Pointing.$ref };
             $common->addInput( { DESCRIPTION => $DESCRIPTION , TYPE => "Mouse" } );
         }
      }
      elsif  ( $result_table->{$result} =~ /3\.1\.11$/ ) {
         # We have a Audio
         my $NAME=$session->get_request(-varbindlist => [ $snmp_Audio.$ref ] );
         if ( defined ($NAME) ) {
             $NAME=$NAME->{$snmp_Audio.$ref };
             $common->addSound( { NAME => $NAME } );
         }
      }
      elsif  ( $result_table->{$result} =~ /3\.1\.10$/ ) {
         # We have a Video
         my $NAME=$session->get_request(-varbindlist => [ $snmp_Video.$ref ] );
         if ( defined ($NAME) ) {
             $NAME=$NAME->{$snmp_Video.$ref };
             $common->addVideo( { NAME => $NAME } );
         }
      }
      elsif  ( $result_table->{$result} =~ /3\.1\.14$/ ) {
         # We have a Modem
         my $NAME=$session->get_request(-varbindlist => [ $snmp_Modem.$ref ] );
         if ( defined ($NAME) ) {
             $NAME=$NAME->{$snmp_Modem.$ref };
             $common->addModem( { NAME => $NAME } );
         }
      }
      elsif  ( $result_table->{$result} =~ /3\.1\.15$/ ) {
         # We have a ParallelPort
         my $NAME=$session->get_request(-varbindlist => [ $snmp_ParallelPort.$ref ] );
         if ( defined ($NAME) ) {
             $NAME=$NAME->{$snmp_ParallelPort.$ref };
             $common->addPort( { NAME => $NAME , TYPE => "Parallel" } );
         }
      }
      elsif  ( $result_table->{$result} =~ /3\.1\.17$/ ) {
         # We have a SerialPort
         my $NAME=$session->get_request(-varbindlist => [ $snmp_SerialPort.$ref ] );
         if ( defined ($NAME) ) {
             $NAME=$NAME->{$snmp_SerialPort.$ref };
             $common->addPort( { NAME => $NAME , TYPE => "Serial" } );
         }
      }
   }
}

1;
