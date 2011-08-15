###############################################################################
## OCSINVENTORY-NG
## Copyleft Guillaume PROTET 2010
## Web : http://www.ocsinventory-ng.org
##
## This code is open source and may be copied and modified as long as the source
## code is always made freely available.
## Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
################################################################################

package Ocsinventory::Agent::Modules::Snmp;

use strict;
no strict 'refs';
use warnings;

use XML::Simple;
use Digest::MD5;

sub new {
   my $name="snmp";   #Set the name of your module here

   my (undef,$context) = @_;
   my $self = {};


   #Create a special logger for the module
   $self->{logger} = new Ocsinventory::Logger ({
            config => $context->{config}
   });
   $self->{logger}->{header}="[$name]";

   $self->{common} = $context->{common};

   $self->{context}=$context;

   $self->{structure}= {
			name => $name,
			start_handler => $name."_start_handler", 
			prolog_writer => undef,      
			prolog_reader => $name."_prolog_reader", 
			inventory_handler => undef,
			end_handler => $name."_end_handler",
   };

   $self->{number_scan}=0;
   $self->{snmp_oid_run}=$name."_oid_run";
   $self->{func_oid}={};
   $self->{snmp_dir}=[];

   my $spec_dir_snmp="Ocsinventory/Agent/Modules/Snmp/";
   $self->{spec_dir_snmp}=$spec_dir_snmp;
   $self->{spec_module_snmp}="Ocsinventory::Agent::Modules::Snmp::";

   # We are going to search where is the directory Ocsinventory/Modules/snmp
   foreach my $dir ( @INC ) {
      my $res_dir=$dir."/".$spec_dir_snmp;
      if ( -d $res_dir ) {
        push(@{$self->{snmp_dir}},$res_dir);
      }
   }

   #We create a xml for the snmp inventory that we will be sent to server
   $self->{inventory}={};

   bless $self;
}


sub snmp_start_handler { 	
   my $self = shift;
   my $logger = $self->{logger};
   my $common = $self->{context}->{common};
   my $config = $self->{context}->{config};
   
   $logger->debug("Calling snmp_start_handler");

   #Disabling module if local mode
   if ($config->{stdout} || $config->{local}) {
     $self->{disabled} = 1;
     $logger->info("Agent is running in local mode...disabling module");
   }

   #If we cannot load prerequisite, we disable the module 
   unless ($common->can_load('Net::SNMP')) { 
     $self->{disabled} = 1;
     $logger->error("Net::SNMP perl module is missing !!");
     $logger->error("Humm my prerequisites are not OK...disabling module :( :(");
   }
}


sub snmp_prolog_reader {
   my ($self, $prolog) = @_;
   my $logger = $self->{logger};
   my $network = $self->{context}->{network};

   my $option;

   $logger->debug("Calling snmp_prolog_reader");
   
   $prolog	= XML::Simple::XMLin( $prolog, ForceArray => ['OPTION', 'PARAM']);

   for $option (@{$prolog->{OPTION}}){
      if( $option->{NAME} =~/snmp/i){
         $self->{doscans} = 1 ;
         for ( @{ $option->{PARAM} } ) {

            if($_->{'TYPE'} eq 'DEVICE'){
                #Adding the IP in the devices array
                push @{$self->{netdevices}},{
                IPADDR => $_->{IPADDR},
                MACADDR => $_->{MACADDR},
                };
            }

            if($_->{'TYPE'} eq 'COMMUNITY'){
                #Get the uri to download file for SNMP communities
                my $snmpcom_loc = $_->{SNMPCOM_LOC}; 
                my $snmp_dir = "$self->{context}->{installpath}/snmp";

                mkdir($snmp_dir) unless -d $snmp_dir;

                #Download snmp_com.txt file using https
                if ($network->getHttpsFile($snmpcom_loc,"snmp_com.txt","$snmp_dir/snmp_com.txt","cacert.pem",$self->{context}->{installpath})) {
                  if ( -f "$snmp_dir/snmp_com.txt") {
                    my $snmp_com = XML::Simple::XMLin("$snmp_dir/snmp_com.txt", ForceArray => ['COMMUNITY']);

                    for (@{$snmp_com->{COMMUNITY}}){
                      push @{$self->{communities}},{
                         VERSION=>$_->{VERSION},
                         NAME=>$_->{NAME}
                      };
                    }
                  }
                } else {
                  $logger->debug("Cannot download file for SNMP communities informations :( :(");
                }
            }
         }
      }
   }
}


sub snmp_end_handler {
   my $self = shift;
   my $logger = $self->{logger};
   my $common = $self->{context}->{common};
   my $network = $self->{context}->{network};

   $logger->debug("Calling snmp_end_handler");

   #If no order form server
   return unless $self->{doscans};

   #Flushing xmltags if it has not been done
   $common->flushXMLTags();

   #We get the config
   my $config = $self->{context}->{config};
   
   
   my $ip=$self->{netdevices};
   my $communities=$self->{communities};
   if ( ! defined ($communities ) ) {
      $logger->debug("We have no Community from server, we use default public community");
      $communities=[{VERSION=>"2",NAME=>"public"}];
   }

   my ($name,$comm,$error,$system_oid);

   # sysName.0
   my $snmp_sysname="1.3.6.1.2.1.1.5.0";
   # sysDescr.0
   my $snmp_sysdescr="1.3.6.1.2.1.1.1.0";
   # sysLocation.0
   my $snmp_syslocation="1.3.6.1.2.1.1.6.0";
   # sysUpTime.0
   my $snmp_sysuptime="1.3.6.1.2.1.1.3.0"; 
   # sysObjectId.0
   my $snmp_sysobjectid="1.3.6.1.2.1.1.2.0";
   # syscontact.0
   my $snmp_syscontact="1.3.6.1.2.1.1.4.0";



   # Initalising the XML properties 
   my $snmp_inventory = $self->{inventory};
   $snmp_inventory->{xmlroot}->{QUERY} = ['SNMP'];
   $snmp_inventory->{xmlroot}->{DEVICEID} = [$self->{context}->{config}->{deviceid}];

   # Begin scanning ip tables 
   foreach my $device ( @$ip ) {
      my $session;
      my $devicedata = $common->{xmltags};     #To fill the xml informations for this device

      $logger->debug("Scanning $device->{IPADDR} device");	
      # Search for the good snmp community in the table community
      LIST_SNMP: foreach $comm ( @$communities ) {

          # Test if we use SNMP v3
          if ( $comm->{VERSION} eq "3"  ) {
	    ($session, $error) = Net::SNMP->session(
                -retries     => 1 ,
                -timeout     => 3,
                -version     => 'snmpv'.$comm->{VERSION},
                -hostname    => $device->{IPADDR},
		# -community   => $comm->{NAME},
                -translate   => [-nosuchinstance => 0, -nosuchobject => 0],
	        -username      => $comm->{USER},
                -authpassword  => $comm->{AUTHPASSWD},
                -authprotocol  => $comm->{AUTHPROTO},
                -privpassword  => $comm->{PRIVPASSWD},
                -privprotocol  => $comm->{PRIVPROTO},
             );
          } else {
            # We have an older version v2c ou v1
	    ($session, $error) = Net::SNMP->session(
                -retries     => 1 ,
                -timeout     => 3,
                -version     => 'snmpv'.$comm->{VERSION},
                -hostname    => $device->{IPADDR},
		          -community   => $comm->{NAME},
                -translate   => [-nosuchinstance => 0, -nosuchobject => 0],
             );
          };
          unless (defined($session)) {
             $logger->error("Snmp ERROR: $error");
          } else {
	          $self->{snmp_session}=$session;

   	     $self->{snmp_community}=$comm->{NAME}; #For a use in constructor module (Cisco)
             $self->{snmp_version}=$comm->{VERSION};

             $name=$session->get_request( -varbindlist => [$snmp_sysname] );
             last LIST_SNMP if ( defined $name);
             $session->close;
	          $self->{snmp_session}=undef;
          }
      }
		
      if ( defined $self->{snmp_session} ) { 
        # We have found the good Community, we can scan this equipment
        my ($constr_oid,$full_oid,$device_name,$description,$location,$contact,$uptime,$domain,$macaddr);

        # We indicate that we scan a new equipment
        $self->{number_scan}++;

        my $result;


        $result=$session->get_request( -varbindlist => [$snmp_sysname]);
        $device_name=$result->{$snmp_sysname}; 

        $result=$session->get_request(-varbindlist => [$snmp_sysobjectid]);
        $full_oid=$result->{$snmp_sysobjectid}; 

        $result=$session->get_request(-varbindlist => [$snmp_sysdescr]);
        $description=$result->{$snmp_sysdescr};

        $result=$session->get_request(-varbindlist => [$snmp_syslocation]);
        $location=$result->{$snmp_syslocation};
        
        $result=$session->get_request(-varbindlist => [$snmp_sysuptime]);
        $uptime=$result->{$snmp_sysuptime};

        $result=$session->get_request(-varbindlist => [$snmp_syscontact]);
        $contact=$result->{$snmp_syscontact};

        if ( $full_oid  =~ /1\.3\.6\.1\.4\.1\.(\d+)/ ) {
            $system_oid=$1;
        }

        # We run the special treatments for the OID vendor 
        if ( $self->{snmp_oid_run}($self,$system_oid) == 1 ) {
           # We have no vendor oid for this equipment
           # we use default.pm
           $self->{snmp_oid_run}($self,"Default");
        }

        $session->close;
	$self->{snmp_session}=undef;

        $macaddr = $device->{MACADDR};

        #Create SnmpDeviceID
        my $md5 = Digest::MD5->new;
        $md5->add($macaddr, $system_oid);
        my $snmpdeviceid = $md5->hexdigest;

        #Adding standard informations
        $common->setSnmpCommons({ 
          IPADDR => $device->{IPADDR},
          MACADDR => $macaddr,
          SNMPDEVICEID => $snmpdeviceid,
          NAME => $device_name,
          DESCRIPTION => $description,
          CONTACT => $contact,
          LOCATION => $location,
          UPTIME => $uptime,
          WORKGROUP => $domain,
        });

        #Add all the informations in the xml for this device
        push @{$snmp_inventory->{xmlroot}->{CONTENT}->{DEVICE}},$devicedata;
      }

      #We clear the xml data for this device 
      $common->flushXMLTags(); 
   }
  
  $logger->info("No more SNMP device to scan"); 

  #Formatting the XML and sendig it to the server
  my $content = XMLout( $snmp_inventory->{xmlroot},  RootName => 'REQUEST' , XMLDecl => '<?xml version="1.0" encoding="UTF-8"?>', SuppressEmpty => undef );

  #Cleaning XML to delete unprintable characters
  my $clean_content = $common->cleanXml($content);

  $network->sendXML({message => $clean_content});

  $logger->debug("End snmp_end_handler :)");
}


sub snmp_oid_run {
    my ($self,$system_oid)=@_;

    my $logger=$self->{logger};
    my $session=$self->{snmp_session};
    my $spec_module_snmp=$self->{spec_module_snmp};

   unless ( defined ( $self->{func_oid}{$system_oid} )) {
      my $spec_dir_snmp=$self->{spec_dir_snmp};

      # We init the default value
      $self->{func_oid}{$system_oid}={};
      $self->{func_oid}{$system_oid}{active}=0;
      $self->{func_oid}{$system_oid}{oid_value}="1.3.6.1.2.1.1.5.0";
      $self->{func_oid}{$system_oid}{oid_name}="Undefined";


      # Can we find it in the snmp directory
      foreach my $dir ( @{$self->{snmp_dir}} ) {
          if ( -r $dir.$system_oid.".pm" ) {
             # We find the module
             my $module_found=$spec_module_snmp.$system_oid;
             eval "use $module_found";
             if ($@) {
                $logger->debug ("Failed to load $module_found: $@");
             } else {
                # We have execute it. We can get the function pointer on snmp_run
                my $package=$module_found."::";
                $self->{func_oid}{$system_oid}{snmp_run}=$package->{'snmp_run'};
                if ( defined ( $package->{'snmp_info'} ) ) {
                   my $return_info=&{$package->{'snmp_info'}};
                   if ( defined $return_info->{oid_value} ) {
                      $self->{func_oid}{$system_oid}{oid_value}=$return_info->{oid_value};
                   }
                   if ( defined $return_info->{oid_name} ) {
                      $self->{func_oid}{$system_oid}{oid_name}=$return_info->{oid_name};
                   }
                }

                $self->{func_oid}{$system_oid}{active}=1;
            	 $self->{func_oid}{$system_oid}{last_exec}=0;
             }
          }
      }
   }

   if ( $self->{func_oid}{$system_oid}{active} == 1 && $self->{func_oid}{$system_oid}{last_exec} < $self->{number_scan} )
   { # we test that this function as never been executed for this equipment
      # We test first that this OID exist for this equipment
      my $oid_scan=$self->{func_oid}{$system_oid}{oid_value};
      my $result=$session->get_request(-varbindlist => [ $oid_scan ] );

      $self->{func_oid}{$system_oid}{last_exec}=$self->{number_scan};
      if ( length ($result->{$oid_scan}) != 0 ) {
         # This OID exist, we can execute it
         #$logger->debug("Launching $system_oid\n");
         &{$self->{func_oid}{$system_oid}{snmp_run}}($session,$self);
      }
   # We indicate that this equipment is the last scanned
   } else {
      return 1;
   }
   return 0;

}


1;
