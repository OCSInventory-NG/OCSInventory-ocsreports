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

use Data::Dumper;
use XML::Simple;

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
   
   $logger->debug("Calling snmp_start_handler");

  #If we cannot load prerequisite, we disable the module 
  unless ($common->can_load('Net::SNMP')) { 
    $self->{disabled} = 1;
    $logger->debug("Humm my prerequisites are not OK...disabling module :( :( ");
  }
}


sub snmp_prolog_reader {
   my ($self, $prolog) = @_;
   my $logger = $self->{logger};

   my $option;

   $logger->debug("Calling snmp_prolog_reader");
   
   $prolog	= XML::Simple::XMLin( $prolog, ForceArray => ['OPTION', 'PARAM']);

   for $option (@{$prolog->{OPTION}}){
      if( $option->{NAME} =~/snmp/i){
         for ( @{ $option->{PARAM} } ) {

            if($_->{'TYPE'} eq 'DEVICE'){
                #Adding the IP in the devices array
                push @{$self->{netdevices}},{
                IP => $_->{IP}
                };
            }

            if($_->{'TYPE'} eq 'COMMUNITY'){
                #Adding the comminity in the communties array
                push @{$self->{communities}},{
                  VERSION=>$_->{VERSION},
                  NAME=>$_->{NAME}
                };
            }
         }
      }
   }
}


sub snmp_end_handler {
   my $self = shift;
   my $logger = $self->{logger};
   my $common = $self->{common};
   my $network = $self->{context}->{network};

   #We get the config
   my $config = $self->{context}->{config};
   
   
   my $ip=$self->{netdevices};
   my $communities=$self->{communities};

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


   $logger->debug("Calling snmp_end_handler");

   # Initalising the XML properties 
   my $snmp_inventory = $self->{inventory};
   $snmp_inventory->{xmlroot}->{QUERY} = ['SNMP'];
   $snmp_inventory->{xmlroot}->{DEVICEID} = [$self->{context}->{config}->{deviceid}];

   # Begin scanning ip tables 
   foreach my $device ( @$ip ) {
      my $session;
      my $devicedata = $common->{xmltags};     #To fill the xml informations for this device

      $logger->debug("Scanning $device->{IP} device");	
      # Search for the good snmp community in the table community
      LIST_SNMP: foreach $comm ( @$communities ) {
         # The snmp v3 will be implemented after
	 ($session, $error) = Net::SNMP->session(
                -retries     => 1 ,
                -timeout     => 3,
                -version     => $comm->{VERSION} ,
                -hostname    => $device->{IP}   ,
		          -community   => $comm->{NAME} ,
		#-username      => $comm->{username}, # V3 test after
		#-authkey       => $comm->{authkey},
                #-authpassword  => $comm->{authpasswd},
                #-authprotocol  => $comm->{authproto},
                #-privkey       => $comm->{privkey},
                #-privpassword  => $comm->{privpasswd},
                #-privprotocol  => $comm->{privproto},
          );
           unless (defined($session)) {
             $logger->error("Snmp ERROR: $error");
          } else {
	          $self->{snmp_session}=$session;
             $name=$session->get_request( -varbindlist => [$snmp_sysname] );
             last LIST_SNMP if ( defined $name);
             $session->close;
	          $self->{snmp_session}=undef;
          }
      }
		
      if ( defined $name ) { 
        # We have found the good Community, we can scan this equipment
        my ($constr_oid,$full_oid,$device_name,$description,$location,$contact,$uptime,$domain);

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
        $self->{snmp_oid_run}($self,$system_oid);

        $session->close;
	     $self->{snmp_session}=undef;

        #Adding standard informations
        $common->setSnmpCommons({ 
          IP => $device->{IP},
          NAME => $device_name,
          DESCRIPTION => $description,
          CONTACT => $contact,
          LOCATION => $location,
          UPTIME => $uptime,
          DOMAIN => $domain,
        });

        #Add all the informations in the xml for this device
        push @{$snmp_inventory->{xmlroot}->{CONTENT}->{DEVICE}},$devicedata;

        #We clear the xml data for this device 
        $common->{xmltags} = {};

      }
   }
  
  $logger->info("No more SNMP device to scan"); 

  #Formatting the XML and sendig it to the server
  my $content = XMLout( $snmp_inventory->{xmlroot},  RootName => 'REQUEST' , XMLDecl => '<?xml version="1.0" encoding="UTF-8"?>', SuppressEmpty => undef );

  $network->sendXML({message => $content});

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
                $self->{func_oid}{$system_oid}{active}=1;
            	 $self->{func_oid}{$system_oid}{last_exec}=0;
             }
          }
      }
   }

   if ( $self->{func_oid}{$system_oid}{active} == 1 && $self->{func_oid}{$system_oid}{last_exec} < $self->{number_scan} )
   { # we test that this function as never been executed for this equipment
      $logger->debug("Launching $system_oid\n" );
      &{$self->{func_oid}{$system_oid}{snmp_run}}($session,$self);
      # We indicate that this equipment is the last scanned
      $self->{func_oid}{$system_oid}{last_exec}=$self->{number_scan};
   }

}


1;
