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

# New lib for scanning

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
   $self->{snmp_oid_xml}=$name."_oid_xml";
   $self->{func_oid}={};
   $self->{snmp_dir}=[];
$self->{snmp_vardir} = ["$self->{context}->{installpath}/snmp/mibs/local/","$self->{context}->{installpath}/snmp/mibs/remote/"];

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
   my $snmp_vardir = $self->{snmp_vardir};

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
            #Adding the community in the communities array
            push @{$self->{communities}},{
              VERSION=>$_->{VERSION},
              NAME=>$_->{NAME},
              USERNAME=>$_->{USERNAME},
              AUTHKEY=>$_->{AUTHKEY},
              AUTHPASSWD=>$_->{AUTHPASSWD},
            };
          }
          if($_->{'TYPE'} eq 'NETWORK'){
            push @{$self->{nets_to_scan}},$_->{SUBNET};
          }
          
          # Creating the directory for xml if they don't yet exist
          mkdir($self->{context}->{installpath}."/snmp") unless -d $self->{context}->{installpath}."/snmp";
          mkdir($self->{context}->{installpath}."/snmp/mibs") unless -d $self->{context}->{installpath}."/snmp/mibs";
          foreach my $dir ( @{$snmp_vardir}) {
             mkdir($dir) unless -d $dir;
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
      $communities=[{VERSION=>"2c",NAME=>"public"}];
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
   # ifPhysAddress.1
   my $snmp_macaddr="1.3.6.1.2.1.2.2.1.6.";
   my $snmp_ifdescr="1.3.6.1.2.1.2.2.1.2.";
   my $snmp_iftype="1.3.6.1.2.1.2.2.1.3.";

   my $full_oid=undef;

   # Initalising the XML properties 
   my $snmp_inventory = $self->{inventory};
   $snmp_inventory->{xmlroot}->{QUERY} = ['SNMP'];
   $snmp_inventory->{xmlroot}->{DEVICEID} = [$self->{context}->{config}->{deviceid}];

   # Scanning network
   $logger->debug("Snmp: Scanning network");

   my $nets_to_scan=$self->{nets_to_scan};
   foreach my $net_to_scan ( @$nets_to_scan ){
      $self->snmp_ip_scan($net_to_scan);
   }
   $logger->debug("Snmp: Ending Scanning network");

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
                -retries     => 2 ,
                -timeout     => 3,
                -version     => 'snmpv'.$comm->{VERSION},
                -hostname    => $device->{IPADDR},
                -community   => $comm->{NAME},
                -translate   => [-nosuchinstance => 0, -nosuchobject => 0],
                -username      => $comm->{USER},
                -authpassword  => $comm->{AUTHPASSWD},
                -authprotocol  => $comm->{AUTHPROTO},
                -privpassword  => $comm->{PRIVPASSWD},
                -privprotocol  => $comm->{PRIVPROTO},
             );

	     #For a use in constructor module (Cisco)
	     $self->{username}=$comm->{USER};
	     $self->{authpassword}=$comm->{AUTHPASSWD};
	     $self->{authprotocol}=$comm->{AUTHPROTO};
	     $self->{privpassword}=$comm->{PRIVPASSWD};
	     $self->{privprotocol}= $comm->{PRIVPROTO};

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

	     #For a use in constructor module (Cisco)
             $self->{snmp_community}=$comm->{NAME}; 
             $self->{snmp_version}=$comm->{VERSION};

             $full_oid=$session->get_request( -varbindlist => [$snmp_sysobjectid] );
             last LIST_SNMP if ( defined $full_oid);
             $session->close;
             $self->{snmp_session}=undef;
          }
      }
		
      if ( defined $full_oid ) { 
        $full_oid=$full_oid->{$snmp_sysobjectid};

		$session->max_msg_size(8192);
        # We have found the good Community, we can scan this equipment
        my ($constr_oid,$device_name,$description,$location,$contact,$uptime,$domain,$macaddr);

        # We indicate that we scan a new equipment
        $self->{number_scan}++;

        my $result;

        $result=$session->get_request( -varbindlist => [$snmp_sysname]);
        if ( defined ( $result->{$snmp_sysname} ) && length($result->{$snmp_sysname}) > 0 ) {
           $device_name=$result->{$snmp_sysname}; 
        } else {
	   $device_name="Not Defined";
        }

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

        ####################
        # finding MACADDRESS for checksum
        ####################
        my $index=$snmp_iftype;
        $result=$session->get_next_request(-varbindlist => [$index]);
           
        $macaddr=undef;
        my $ref_iftype=3;
        # We scan the kinod of connexion, if it is a ethernet, we can use this mac address
        # If we have a ref_iftype > 3 then we have no eth (type 6)
        while ( ! defined($macaddr) && defined( $result) && $ref_iftype == 3  ) {
	   foreach $index ( keys %{$result} ) {
              if ( $index =~ /1\.3\.6\.1\.2\.1\.2\.2\.1\.(\S+)\.(\S+)/ ) {
	         $ref_iftype=$1;
	         my $ref_mac=$2;
	         if ( $result->{$index} == 6   ) {
	            my $res_mac=$session->get_request(-varbindlist => [$snmp_macaddr.$ref_mac]);
		    if ( defined ($res_mac ) && defined ($res_mac->{$snmp_macaddr.$ref_mac}) && $res_mac->{$snmp_macaddr.$ref_mac} ne '' ) {
		        $macaddr=" ".$res_mac->{$snmp_macaddr.$ref_mac};
		    } else {
		       $result=$session->get_next_request(-varbindlist => [$index]);
		    }
                 } else {
	            $result=$session->get_next_request(-varbindlist => [$index]);
	         }
	      } else {
		 $result=undef;
	      }
	   }
	}

        if ( defined($macaddr) && $macaddr =~ /^ 0x(\w{2})(\w{2})(\w{2})(\w{2})(\w{2})(\w{2})$/ ) {
           $macaddr="$1:$2:$3:$4:$5:$6";
        } elsif ( ! defined($macaddr) || $macaddr eq "endOfMibView" ) {
	   if (defined ( $device->{MACADDR} ) ) {
	      $macaddr = $device->{MACADDR};
           } else {
              $macaddr=$device_name;
              #$macaddr=$device->{IPADDR}."_".$device_name;
           }
        }

	if ( defined ($macaddr) ) {
           if ( $full_oid  =~ /1\.3\.6\.1\.4\.1\.(\d+)/ ) {
               $system_oid=$1;
           }

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

           # We run the special treatments for the OID vendor 
           if ( $self->{snmp_oid_run}($self,$system_oid) == 1 ) {
              # We have no vendor oid for this equipment (run or xml)
              # we use default.pm
                 $self->{snmp_oid_run}($self,"Default");
           }


           #Add all the informations in the xml for this device
           push @{$snmp_inventory->{xmlroot}->{CONTENT}->{DEVICE}},$devicedata;
         }
      }
      # We have finished with this equipment
      $session->close;
      $self->{snmp_session}=undef;

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


#########################################################
#							#
# function for scanning range of ip and determining if	#
# there is an equipment for snmp scan			#
# Parameters:						#
#	(self)						#
#	net_to_scan indicate the subnet to scan         #
#							#
#########################################################
###
sub snmp_ip_scan {
   my ($self,$net_to_scan) = @_;
   my $logger=$self->{logger};
   my $common=$self->{common};

   if ($common->can_load('Net::Netmask') ) {
      my $block=Net::Netmask->new($net_to_scan);
      my $size=$block->size()-2;
      my $index=1;

      if ( $common->can_run('nmap') && $common->can_load('Nmap::Parser')  ) {
         $logger->debug("Scannig $net_to_scan with nmap");
         my $nmaparser = Nmap::Parser->new;

         $nmaparser->parsescan("nmap","-sP","-PR",$net_to_scan);
         for my $host ($nmaparser->all_hosts("up")) {
            my $res=$host->addr;
	    $logger->debug("Found $res");
	    push( @{$self->{netdevices}},{ IPADDR=>$res }) unless $self->search_netdevice($res);
         }
      } elsif ($common->can_load('Net::Ping'))  {
         $logger->debug("Scanning $net_to_scan with ping");
         my $ping=Net::Ping->new("icmp",1);

         while ($index <= $size) {
            my $res=$block->nth($index);
            if ($ping->ping($res)) {
	      $logger->debug("Found $res");
	      push( @{$self->{netdevices}},{ IPADDR=>$res }) unless $self->search_netdevice($res);
            }
            $index++;
         }
         $ping->close();
      } else {
	$logger->debug("No scan possible");
      }
   } else {
      $logger->debug("Net::Netmask not present: no scan possible");
   }
}

#########################################################
#							#
# function for executing sub perl with the oid		#
# Parameters: 						#
#         (self)					#
#         system oid for execute the .pm associated	#
#							#
#########################################################
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
      $self->{func_oid}{$system_oid}{oid_value}="1.3.6.1.2.1.1.2.0";
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

      # We indicate that this equipment is the last scanned
      $self->{func_oid}{$system_oid}{last_exec}=$self->{number_scan};

      if (defined ($result) && length ($result->{$oid_scan}) != 0 ){
         	# This OID exist, we can execute it
         	$logger->debug("Launching $system_oid\n");
         	&{$self->{func_oid}{$system_oid}{snmp_run}}($session,$self);
      } else {
		   return 1;
      }
   }
   snmp_oid_xml($self,$system_oid);
   return 0;

}

#########################################################
#                                                       #
# function for executing sub xml with the oid           #
# Parameters:                                           #
#         (self)                                        #
#         system oid for execute the .xml associated    #
#                                                       #
# $xml_oid->{system_oid} Ref the oid in the xml         #
#                       {active} 0 Ko 1 OK              #
#			{xml_data} info get in xml file #
#                       {last_exec} num for verify that #
# this equipment is already scaned with this xml or no  #
#########################################################
sub snmp_oid_xml {
    my ($self,$system_oid)=@_;

    my $logger=$self->{logger};
    my $spec_module_snmp=$self->{spec_module_snmp};
    my $snmp_vardir = $self->{snmp_vardir};

   if ( ! defined ( $self->{xml_oid}{$system_oid} )) {
      # We init the default value
      $self->{xml_oid}{$system_oid}={};
      $self->{xml_oid}{$system_oid}{active}=0;

      # Can we find it in the snmp directory
      foreach my $dir ( @{$self->{snmp_vardir}} ) {

         # Can we find it in the snmp var directory
         if (  $self->{xml_oid}{$system_oid}{active} == 0  ) {
          if ( -r $dir.$system_oid.".xml" ) {
             # We find the xml
             #my $module_found=$spec_module_snmp.$system_oid;
             #eval "use $module_found";
             if ( $self->{xml_oid}{$system_oid}{xml_data}=XML::Simple::XMLin($dir.$system_oid.".xml",ForceArray => 1 ) ) {
                # We have load the xml 
		$logger->debug("Load module xml $system_oid");
                $self->{xml_oid}{$system_oid}{active}=1;
                $self->{xml_oid}{$system_oid}{last_exec}=0;
		if ( defined( $self->{xml_oid}{$system_oid}{PARAMETERS}[0]{NAME}[0] ) ) {
		   $self->{xml_oid}{$system_oid}{oid_name}=$self->{xml_oid}{$system_oid}{PARAMETERS}[0]{NAME}[0];
                }
             } else {
                $logger->debug ("Failed to load xml $system_oid: $@");
                $self->{xml_oid}{$system_oid}{active} = 0;
             }
          } else {
	        $logger->debug("No xml found for $dir$system_oid.xml ");
          }
        }
      }
   }

   # now we have an information for the xml associated with this oid
   # It can be active or no 
   
   if ( $self->{xml_oid}{$system_oid}{active} == 1 && $self->{xml_oid}{$system_oid}{last_exec} < $self->{number_scan} ) {
      $logger->debug ("Begin xml on $system_oid");
      $self->{xml_oid}{$system_oid}{last_exec}=$self->{number_scan};
      
      # We have done other scan so we can now execute own scan
      # We have actualy only v1, we can have after other parallel version 
      if ( xml_scan_v1($self,$system_oid,$self->{xml_oid}{$system_oid}{xml_data}) == 1 ) {
	    $self->{xml_oid}{$system_oid}{active}=0;
	    return 1;
      }
      return 0;
   } 
   return 1;
}

#########################################################
#                                                       #
# function for executing xml scan v1                    #
# Parameters:                                           #
#         (self)                                        #
#         system oid                                    #
#         xml in input                                  #
#                                                       #
#########################################################
sub xml_scan_v1 {
   my ($self,$system_oid,$xml)=@_;
   my $xmltags=$self->{context}->{common}->{xmltags};
   my $logger=$self->{logger};
   
   return 1 if ( xml_parameters_v1($self,$system_oid,$xml) == 1 ) ;

   if ( ! defined ($xmltags) ) {
      $xmltags={};
   }
   $logger->debug("Begin scanning v1 on $system_oid");

   my $filters=[];
   if ( defined ($xml->{LOOPS}) ) {
      return 1 if ( xml_loops_v1($self,$xml->{LOOPS},$xmltags,$filters) == 1 );
   }
   if ( defined ($xml->{DATA}) ) {
      return 1 if ( xml_data_v1($self,$xml->{DATA}[0],$xmltags,$filters,0) == 1 );
   }
   return 0;
}

#########################################################
#                                                       #
# function for parameters of xml v1                     #
# Parameters:                                           #
#         (self)                                        #
#         system oid                                    #
#         xml in input                                  #
#                                                       #
#########################################################
sub xml_parameters_v1 {
   my ($self,$system_oid,$xml)=@_;
   my $logger=$self->{logger};

   $logger->debug("Validating xml parameters");
   return 1 if ( ! defined($xml->{PARAMETERS}) ) ;
   return 0 if ( ! defined($xml->{PARAMETERS}[0]{EXECUTE}) ) ;

   # We first look if there is other scan to do
   $logger->debug("Begin looking other scan");
   foreach my $type_exec ( "RUN","XML" ) {
      if ( defined( $xml->{PARAMETERS}[0]{EXECUTE}[0]{$type_exec}) ) {
         my $liste_exec=$xml->{PARAMETERS}[0]{EXECUTE}[0]{$type_exec};
         my $lower_type_exec=lc($type_exec);
         foreach my $exec_perl ( @{$liste_exec} ) {
            my $a_exec="snmp_oid_".$lower_type_exec;
            $self->{$a_exec}($self,$exec_perl);
         }
      }
   }
   return 0;
}
#########################################################
#                                                       #
# function for reading for a loops                      #
# Parameters:                                           #
#         (self)                                        #
#         xml_loops (contain VALUE/INDEX/FILTER         #
#                  and a sub DATA                       #
#         result_table: pointer on where we             #
#                       put result information          #
#                                                       #
#  Return: 1 Pb                                         #
#          0 OK                                         #
#########################################################
sub xml_loops_v1 {
   my ($self,$xml_loops,$result_table,$filters)=@_;
   my $session=$self->{snmp_session};
   my $logger=$self->{logger};

   $logger->debug ("Begin xml loops");

ALL_LOOPS:   foreach my $uniq_loops ( @{$xml_loops} ) {
      if ( ! defined ($uniq_loops->{VALUE}) || ! defined($uniq_loops->{INDEX})|| ! defined($uniq_loops->{NAME_INDEX}) ) {
         $logger->debug("Error in xml File: VALUE, INDEX or NAME_INDEX not defined");
         return 1 ;
      }
      # We now replace already existant filters
      my $data_value=$uniq_loops->{VALUE}[0];
      my $index_value=$uniq_loops->{INDEX}[0];
      foreach my $filter ( @{$filters} ) {
         $data_value=~s/$filter->{NAME}/$filter->{VALUE}/g;
         $index_value=~s/$filter->{NAME}/$filter->{VALUE}/g;
      }
      my $index_equipement=$session->get_entries(-columns => [ $data_value ] );

      # If we have no data for this index it s not a problem in the xml
      next ALL_LOOPS if ( ! defined ( $index_equipement ) ) ;

      # We first verify if there is a filter on this value
      # in this case, we must filter the topic if this filter is not OK
      my $nbr_data=0;
      foreach my $uniq_index_equipement ( keys %{$index_equipement} ) {
         if ( defined ($uniq_loops->{FILTER}) && ! $index_equipement->{$uniq_index_equipement} =~ /$uniq_loops->{FILTER}[0]/  ) {
            delete $index_equipement->{$uniq_index_equipement};

         # The Filter is OK, we can looks sub tables for this INDEX
         } elsif ( $uniq_index_equipement =~ /$index_value/ ) {
            # we use the index on the value for finding what are the INDEX values
            push(@{$filters},{VALUE=>$1,NAME=>$uniq_loops->{NAME_INDEX}[0]});
            return 1 if ( xml_data_v1($self,$uniq_loops->{DATA}[0],$result_table,$filters,$nbr_data++) == 1);
            # After use this filter, we must take it out
            # so it can be used for other loops
            pop(@{$filters});
         }

      }
   }
   return 0;
}
#########################################################
#                                                       #
# function for reading for a table                      #
# Parameters:                                           #
#         (self)                                        #
#         xml_line (containt SET/VALUE/FILTER          #
#                  or a sub DATA                        #
#         value_IDX for subtitution in the information  #
#         result_table: pointer on the table where we   #
#                       put result information          #
#                                                       #
#  Return: 1 Pb                                         #
#          0 OK                                         #
#########################################################
sub xml_data_v1 {
   my ($self,$xml_table,$result_table,$filters,$pos_table)=@_;
   my $logger=$self->{logger};

   $logger->debug("Begin xml data");
   foreach my $table ( keys %{$xml_table} )  {
      if ( $table eq "DATA" ) {
         foreach my $subtable ( @{$xml_table->{DATA}} ) {
	    if ( !defined $result_table->{$subtable}[0] ) {
	       $result_table->{$subtable}[0]={};
            }
            return 1 if ( xml_data_v1($self,$xml_table->{$subtable},$filters,$result_table->[0]{$subtable}[0],0) == 1);
         }
      } elsif ( $table eq "LOOPS" ) {
         return 1 if ( xml_loops_v1($self,$xml_table->{LOOPS},$xml_table,$filters) == 1 );
      } else {
         # we look for all lines in this table
         foreach my $line ( keys %{$xml_table->{$table}[0]} ) {
            if ( $line eq "LOOPS" ) {
	       if ( ! defined ($result_table->{$table}[$pos_table]) ) {
	          $result_table->{$table}[$pos_table]={};
               }
	       return 1 if ( xml_loops_v1($self,$xml_table->{$table}[0]{LOOPS},$result_table->{$table}[$pos_table],$filters) == 1 );
            } else {
               my $result=xml_line_v1($self,$xml_table->{$table}[0]{$line}[0],$filters);
               if ( defined ($result) ) {
                  $result_table->{$table}[$pos_table]{$line}[0]=$result;
               }
            }
         }
      }
   }
   return 0;
}

#########################################################
#                                                       #
# function for reading for a uniq line in a table       #
# Parameters:                                           #
#         (self)                                        #
#         xml_line (containt SET/VALUE/FILTER          #
#         value_IDX for subtitution in the information  #
#                                                       #
#  Return: undef if no line correct                     #
#          the string for this line in others cases     #
#########################################################

sub xml_line_v1 {
   my ($self,$xml_line,$filters)=@_;
   my $session=$self->{snmp_session};

   return undef if ( ! defined ($xml_line) ) ;
   # We have a SET or a VALUE and an optional FILTER
   if ( defined ( $xml_line->{SET} ) ) {
      return($xml_line->{SET}[0]);
   }
   return undef if ( ! defined ( $xml_line->{VALUE} ) ) ;

   my $data_value=$xml_line->{VALUE}[0];

   # If we have a loop, we must substitute the VLAUE
   foreach my $filter ( @{$filters} ) {
      $data_value=~s/$filter->{NAME}/$filter->{VALUE}/g;
   }
   # We look for information in the equipment with the snmp interogation
   my $info_equipment=$session->get_request(-varbindlist => [$data_value]);
   # We verify that information has been returned for this value
   return undef if ( ! defined ($info_equipment) ) ;

   $info_equipment=$info_equipment->{$data_value};

   # Verify that we have data
   return undef if ( ! defined ($info_equipment) || $info_equipment eq "" );

   # If there is a fliter, we verifiy that this line pass the filter
   return undef if ( defined ($xml_line->{FILTER}) && ! ($info_equipment =~ /$xml_line->{FILTER}[0]/ )) ;

   # Remplacement
   return  $info_equipment if ( ! defined ($xml_line->{REPLACE}) );

   foreach my $replace ( @{$xml_line->{REPLACE}} ) {
      return undef if ( ! defined( $replace->{STRING}) || ! defined ($replace->{BY}) );
      $info_equipment=~s/$replace->{STRING}[0]/$replace->{BY}[0]/g;
   }
   return $info_equipment;
}

#########################################################
#							#
# function to search an IP adress in netdevices array	#
# Parameters: 						#
#         (self)					#
#         IP address to search				#
#							#
# Return: 1 if IP address was found			#
#          nothing in other case			#
#########################################################

sub search_netdevice {
   my ($self,$ip)= @_ ;

   for (@{$self->{netdevices}}) {
      if ($ip =~ /$_->{IPADDR}/) {
        return 1;
      }
   }
}

1;
