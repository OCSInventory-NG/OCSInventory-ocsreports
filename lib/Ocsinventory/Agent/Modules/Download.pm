###############################################################################
## OCSINVENTORY-NG 
## Copyleft Pascal DANEK 2005
## Web : http://ocsinventory.sourceforge.net
##
## This code is open source and may be copied and modified as long as the source
## code is always made freely available.
## Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
################################################################################
# Function by hook:
# -download_prolog_reader, download_message, download
# -download_inventory_handler
# -download_end_handler, begin, done, clean, finish, period, download, execute,
#   check_signature and build_package
package Ocsinventory::Agent::Modules::Download;

use strict;

use Fcntl qw/:flock/;
use XML::Simple;
use LWP::UserAgent;
use Compress::Zlib;
use Digest::MD5;
use File::Path;
use Socket;

# Can be missing. By default, we use MD5
# You have to install it if you want to use SHA1 digest
eval{ require Digest::SHA1 };

#Global vars
my $ua;
my $config;
my @prior_pkgs;


sub new {

   my $name="download";   #Set the name of your module here

   my (undef,$context) = @_;
   my $self = {};  
   
   #Create a special logger for the module
   $self->{logger} = new Ocsinventory::Logger ({
            config => $context->{config},
   });

   #We use the common object for the module
   $self->{common} = $context->{common};

   $self->{context} = $context;
   $self->{logger}->{header}="[$name]";
   $self->{structure} = {
			name => $name,
			start_handler => $name."_start_handler", 
			prolog_writer => undef, 
			prolog_reader => $name."_prolog_reader", 
			inventory_handler => $name."_inventory_handler", 
			end_handler => $name."_end_handler" 
   };
 
   $self->{settings} = {
		https_port => '443',
		# Time to wait between scheduler periods, scheduling cycles and fragments downloads
		frag_latency_default 	=> 10,
		period_latency_default 	=> 0,
		cycle_latency_default 	=> 10,
		max_error_count			=> 30,
		# Number of loops for one period
		period_lenght_default 	=> 10,
   };

   $self->{messages} = {
		# Errors
		code_succes				 	=> 'SUCCESS',
		err_bad_id				 	=> 'ERR_BAD_ID',
		err_download_info		 	=> 'ERR_DOWNLOAD_INFO',
		err_bad_digest			 	=> 'ERR_BAD_DIGEST',
		err_download_pack		 	=> 'ERR_DOWNLOAD_PACK',
		err_build			 		=> 'ERR_BUILD',
		err_execute				 	=> 'ERR_EXECUTE',
		err_clean			 		=> 'ERR_CLEAN',
		err_timeout					=> 'ERR_TIMEOUT',
		err_already_setup			=> 'ERR_ALREADY_SETUP',
   };
   
   #Special hash for packages
   $self->{packages}= {};

   bless $self;
}


sub download_start_handler {
   my $self = shift;
   my $logger = $self->{logger};
   my $common = $self->{context}->{common};
   my $config = $self->{context}->{config};

   $logger->debug("Calling download_start_handler");

   #If we cannot load prerequisite, we disable the module 
   unless ($common->can_load('Net::SSLeay qw(die_now die_if_ssl_error)')) {
     $self->{disabled} = 1;
     $logger->debug("Humm my prerequisites are not OK...disabling module :( :( ");
   }
}


sub download_prolog_reader{      #Read prolog response

	my ($self,$prolog) = @_;
	
	my $context = $self->{context};
	my $logger = $self->{logger};
	my $common = $self->{common};
	my $settings = $self->{settings};
	my $messages = $self->{messages};
	my $packages = $self->{packages};

	$logger->debug("Calling download_prolog_reader");
	
	$logger->debug($prolog);
	
	$prolog = XML::Simple::XMLin( $prolog, ForceArray => ['OPTION', 'PARAM']);
	my $option;
	# Create working directory
	my $opt_dir = $context->{installpath}.'/download';
	mkdir($opt_dir) unless -d $opt_dir;
	
	# We create a file to tell to download process that we are running
	open SUSPEND, ">$opt_dir/suspend";
	close(SUSPEND);
	
	# Create history file if needed
	unless(-e "$opt_dir/history"){
		open HISTORY, ">$opt_dir/history" or die("Cannot create history file: $!");
		close(HISTORY);
	}
	
	# Create lock file if needed
	unless(-e "$opt_dir/lock"){
		open LOCK, ">$opt_dir/lock" or die("Cannot create lock file: $!");
		close(LOCK);
	}
	
	# Retrieve our options
	for $option (@{$prolog->{OPTION}}){
		if( $option->{NAME} =~/download/i){
			for ( @{ $option->{PARAM} } ) {
				# Type of param
				if($_->{'TYPE'} eq 'CONF'){
					# Writing configuration
					open CONFIG, ">$opt_dir/config" or die("Cannot open/create config file ($opt_dir/config)");
					if(flock(CONFIG, LOCK_EX)){
						$logger->debug("Writing config file.");
						print CONFIG XMLout($_, RootName => 'CONF');
						close(CONFIG);
						$config = $_;
					}else{
						$logger->error("Cannot lock config file !!");
						close(CONFIG);
						return 0;
					}
					
					# Apply config
					# ON ?
					if($_->{'ON'} == '0'){
						$logger->info("Download is off.");
						open LOCK, "$opt_dir/lock" or die("Cannot open lock file: $!");
						if(flock(LOCK, LOCK_EX|LOCK_NB)){
							close(LOCK);
							unlink("$opt_dir/suspend");
							return 0;
						}else{
							$logger->debug("Try to kill current download process...");
							my $pid = <LOCK>;
							close(LOCK);
							$logger->debug("Sending USR1 to $pid...");
							if(kill("USR1", $pid)){
								$logger->debug("Success.");
							}else{
								$logger->debug("Failed.");
							}
							return 0;
						}
					}
				# Maybe a new package to download
				}elsif($_->{'TYPE'} eq 'PACK'){
					$packages->{$_->{'ID'}} = {
						'PACK_LOC' => $_->{'PACK_LOC'},
						'INFO_LOC' => $_->{'INFO_LOC'},
						#'ID' => $_->{'ID'},
						'CERT_PATH' => $_->{'CERT_PATH'},
						'CERT_FILE' => $_->{'CERT_FILE'}
					};
				}
			}
		}
	}
		
	# We are now in download child
	# Connect to server
	$ua = LWP::UserAgent->new();
	$ua->agent('OCS_NG_Unified_Unix_Agent_v'.$context->{version});
	$ua->credentials( $context->{'OCS_AGENT_SERVER_NAME'}, 
		$context->{authrealm}, 
		$context->{authuser} => $context->{authpwd} 
	);
	
	# Check history file
	unless(open HISTORY, "$opt_dir/history") {
		flock(HISTORY, LOCK_EX);
		unlink("$opt_dir/suspend");
		$logger->error("Cannot read history file: $!");
		return 1;
	}
	
	chomp(my @done = <HISTORY>);
	close(HISTORY);
		
	# Package is maybe already handled
	for (keys %$packages){
		my $dir = $opt_dir."/".$_;
		my $fileid = $_;
		my $infofile = 'info';
		my $location = $packages->{$_}->{'INFO_LOC'};

		if($common->already_in_array($fileid, @done)){
			$logger->info("Will not download $fileid. (already in history file)");
			&download_message($fileid, $messages->{err_already_setup},$logger,$context);
			next;
		}
		
		# Looking for packages status
		unless(-d $dir){
			$logger->debug("Making working directory for $fileid.");
			mkdir($dir) or die("Cannot create $fileid directory: $!");
			open FH, ">$dir/since" or die("Cannot create $fileid since file: $!");;
			print FH time();
			close(FH);
		}
		
		# Retrieve and writing info file if needed
		unless(-f "$dir/$infofile"){
			# Special value INSTALL_PATH
			$packages->{$_}->{CERT_PATH} =~ s/INSTALL_PATH/$context->{installpath}/;
			$packages->{$_}->{CERT_FILE} =~ s/INSTALL_PATH/$context->{installpath}/;
		
            if (!-f $packages->{$_}->{CERT_FILE}) {
		$logger->debug("installpath=$context->{installpath}");
                $logger->error("No certificat found in ".$packages->{$_}->{CERT_FILE});
            }

			# Getting info file
			$logger->debug("Retrieving info file for $fileid");
			
			my ($ctx, $ssl, $ra);
			eval {
				$| = 1;
				$logger->debug('Initialize ssl layer...');
				
				# Initialize openssl
				if ( -e '/dev/urandom') {
					$Net::SSLeay::random_device = '/dev/urandom';
					$Net::SSLeay::how_random = 512;
				}
				else {
					srand (time ^ $$ ^ unpack "%L*", `ps wwaxl | gzip`);
					$ENV{RND_SEED} = rand 4294967296;
				}
				
				Net::SSLeay::randomize();
				Net::SSLeay::load_error_strings();
				Net::SSLeay::ERR_load_crypto_strings();
				Net::SSLeay::SSLeay_add_ssl_algorithms();
				
				#Create ctx object
				$ctx = Net::SSLeay::CTX_new() or die_now("Failed to create SSL_CTX $!");
				Net::SSLeay::CTX_load_verify_locations( $ctx, $packages->{$_}->{CERT_FILE},  $packages->{$_}->{CERT_PATH} )
				  or die_now("CTX load verify loc: $!");
				# Tell to SSLeay where to find AC file (or dir)
				Net::SSLeay::CTX_set_verify($ctx, &Net::SSLeay::VERIFY_PEER, \&ssl_verify_callback);
				die_if_ssl_error('callback: ctx set verify');
				
				my($server_name,$server_port,$server_dir);
				
				if($packages->{$_}->{INFO_LOC}=~ /^([^:]+):(\d{1,5})(.*)$/){
					$server_name = $1;
					$server_port = $2;
					$server_dir = $3;
				}elsif($packages->{$_}->{INFO_LOC}=~ /^([^\/]+)(.*)$/){
					$server_name = $1;
					$server_dir = $2;	
					$server_port = $settings->{https_port};
				}
				$server_dir .= '/' unless $server_dir=~/\/$/;
				
				$server_name = gethostbyname ($server_name) or die;
				my $dest_serv_params  = pack ('S n a4 x8', &AF_INET, $server_port, $server_name );
				
				# Connect to server
				$logger->debug("Connect to server: $packages->{$_}->{INFO_LOC}...");
				socket  (S, &AF_INET, &SOCK_STREAM, 0) or die "socket: $!";
				connect (S, $dest_serv_params) or die "connect: $!";
				
				# Flush socket
				select  (S); $| = 1; select (STDOUT);
				$ssl = Net::SSLeay::new($ctx) or die_now("Failed to create SSL $!");
				Net::SSLeay::set_fd($ssl, fileno(S));
				
				# SSL handshake
				$logger->debug('Starting SSL connection...');
				Net::SSLeay::connect($ssl);
				die_if_ssl_error('callback: ssl connect!');
				
				# Get info file
				my $http_request = "GET /$server_dir".$fileid."/info HTTP/1.0\n\n";
				Net::SSLeay::ssl_write_all($ssl, $http_request);
				shutdown S, 1;
				
				$ra = Net::SSLeay::ssl_read_all($ssl);
				$ra = (split("\r\n\r\n", $ra))[1] or die;
				$logger->debug("Info file: $ra");
				
				my $xml = XML::Simple::XMLin( $ra ) or die;
				
				$xml->{PACK_LOC} = $packages->{$_}->{PACK_LOC};
				
				$ra = XML::Simple::XMLout( $xml ) or die;
				
				open FH, ">$dir/info" or die("Cannot open info file: $!");
				print FH $ra;
				close FH;
			};
			if($@){
				download_message({ $fileid }, $self->{messages}->{err_download_info},$logger,$context);
				$logger->error("Error: SSL hanshake has failed");
				next;	
			}
			else {
				$logger->debug("Success. :-)");
			}
			Net::SSLeay::free ($ssl);
			Net::SSLeay::CTX_free ($ctx);
			close S;
			sleep(1);
		}
	}
	unless(unlink("$opt_dir/suspend")){
		$logger->error("Cannot delete suspend file: $!");
		return 1;
	}
	return 0;
}


sub ssl_verify_callback {
	my ($ok, $x509_store_ctx) = @_;
	return $ok; 
}


sub download_inventory_handler{      	# Adding the ocs package ids to softwares
   
   my ($self,$inventory) = @_;
	
   my $context = $self->{context};
   my $logger = $self->{logger};


	$logger->debug("Calling download_inventory_handler");

	my @history;


	# Read download history file
	if( open PACKAGES, "$context->{installpath}/download/history" ){
		flock(PACKAGES, LOCK_SH);
		while(<PACKAGES>){
			chomp( $_ );
			push @history, { ID => $_ };
		}
	}
	close(PACKAGES);

	# Add it to inventory (will be handled by Download.pm server module
	push @{ $inventory->{xmlroot}->{'CONTENT'}->{'DOWNLOAD'}->{'HISTORY'} },{
		'PACKAGE'=> \@history
	};
}



sub download_end_handler{    	# Get global structure
   
   my $self = shift;
	
   my $context = $self->{context};
   my $logger = $self->{logger};
   my $common = $self->{common};
   my $settings = $self->{settings};
   my $messages = $self->{messages};
   my $packages = $self->{packages};

   $logger->debug("Calling download_end_handler");

	my $dir = $context->{installpath}."/download";
	my $pidfile = $dir."/lock";
	
	return 0 unless -d $dir;
	
	# We have jobs, we do it alone
	my $fork = fork();
	if($fork>0){
		return 0;
	}elsif($fork<0){
		return 1;
	}else{
		$SIG{'USR1'} = sub { 
			print "Exiting on signal...\n";
			&finish($logger, $context);
		};
		# Go into working directory
		chdir($dir) or die("Cannot chdir to working directory...Abort\n");
	}
	
	# Maybe an other process is running
	exit(0) if begin($pidfile,$logger);
	# Retrieve the packages to download
	opendir DIR, $dir or die("Cannot read working directory: $!");
	
	my $end;
	
	while(1){
		# If agent is running, we wait 
		if (-e "suspend") {
			$logger->debug('Found a suspend file... Will wait 10 seconds before retry');
			sleep(10);
			next;
		}
		
		$end = 1;
		
		#TODO Uncomment this line #undef $packages;

		# Reading configuration
		open FH, "$dir/config" or die("Cannot read config file: $!");
		if(flock(FH, LOCK_SH)){
			$config = XMLin("$dir/config");
			close(FH);
			# If Frag latency is null, download is off
			if($config->{'ON'} eq '0'){
				$logger->info("Option turned off. Exiting.");
				finish($logger, $context);
			}
		}else{
			$logger->error("Cannot read config file :-( . Exiting.");
			close(FH);
			finish($logger, $context);
		}
		
		# Retrieving packages to download and their priority
		while(my $entry = readdir(DIR)){
			next if $entry !~ /^\d+$/;
						
			next unless(-d $entry);
			
			# Clean package if info file does not still exist
			unless(-e "$entry/info"){
				$logger->debug("No info file found for $entry!!");
				clean( $entry, $logger, $context, $messages, $packages );
				next;
			}
			my $info = XML::Simple::XMLin( "$entry/info" ) or next;
			
			# Check that fileid == directory name
			if($info->{'ID'} ne $entry){	
				$logger->debug("ID in info file does not correspond!!");
				clean( $entry, $logger, $context, $messages, $packages );
				download_message($entry, $messages->{err_bad_id},$logger,$context);
				next;
			}
			
			# Manage package timeout
			# Clean package if since timestamp is not present
			unless(-e "$entry/since"){
				$logger->debug("No since file found!!");
				clean($entry, $logger, $context,$messages,$packages );
				next;
			}else{
				my $time = time();
				if(open SINCE, "$entry/since"){
					my $since = <SINCE>;
					if($since=~/\d+/){
						if( (($time-$since)/86400) > $config->{TIMEOUT}){
							$logger->error("Timeout Reached for $entry.");
							clean($entry, $logger, $context,$messages,$packages );
							&download_message($entry, $messages->{err_timeout},$logger,$context);
							close(SINCE);
							next;
						}else{
							$logger->debug("Checking timeout for $entry... OK");
						}
					}else{
						$logger->error("Since data for $entry is incorrect.");
						clean($entry, $logger, $context, $messages, $packages );
						&download_message($entry, $messages->{err_timeout},$logger,$context);
						close(SINCE);
						next;
					}
					close(SINCE);
				}else{
					$logger->error("Cannot find since data for $entry.");
					clean($entry, $logger, $context, $messages, $packages );
					&download_message($entry, $messages->{err_timeout},$logger,$context);
					next;
				}
			}
			
			# Building task file if needed
			unless( -f "$entry/task" and -f "$entry/task_done" ){
				open FH, ">$entry/task" or die("Cannot create task file for $entry: $!");
				
				my $i;
				my $frags = $info->{'FRAGS'};
				# There are no frags if there is only a command
				if($frags){
					for($i=1;$i<=$frags;$i++){
						print FH "$entry-$i\n";
					}
				};
				close FH;
				# To be sure that task file is fully created
				open FLAG, ">$entry/task_done" or die ("Cannot create task flag file for $entry: $!");
				close(FLAG);
			}
			# Store info XML descriptions in package attributes
			for (keys %$info){
				$packages->{$entry}->{$_} = $info->{$_}
			}
	
			$end = 0;
		}
		# Rewind directory
		rewinddir(DIR);
		# Call packages scheduler
		if($end){
			last;
		}else{
			period($packages,$logger,$context,$self->{messages},$settings);	
		}
	}
	$logger->info("No more package to download.");
	finish($logger, $context);
}

# Schedule the packages
sub period{
	my ($packages,$logger,$context,$messages,$settings) = @_ ;

	my $period_lenght_default = $settings->{period_lenght_default} ;
	my $frag_latency_default= $settings->{frag_latency_default} ;
	my $cycle_latency_default= $settings->{cycle_latency_default} ;
	my $period_latency_default= $settings->{period_latency_default} ;

	my $i;

	#Serching packages with the priority 0
   for (keys %$packages) {
		if ($packages->{$_}->{'PRI'} eq "0") {
			 push (@prior_pkgs,$_);
		 }
	}


	$logger->debug("New period. Nb of cycles: ".
	(defined($config->{'PERIOD_LENGTH'})?$config->{'PERIOD_LENGTH'}:$period_lenght_default));

	for($i=1;$i<=( defined($config->{'PERIOD_LENGTH'})?$config->{'PERIOD_LENGTH'}:$period_lenght_default);$i++){
		# Highest priority
		if(@prior_pkgs){
			$logger->debug("Managing ".scalar(@prior_pkgs)." package(s) with absolute priority.");
			for(@prior_pkgs){
				# If done file found, clean package
				if(-e "$_/done"){
					$logger->debug("done file found!!");
					done($_,$logger,$context,$messages,$settings);
					next;
					}
				download($_,$logger,$context,$messages,$settings,$packages);
					$logger->debug("Now pausing for a fragment latency => ".(
					defined($config->{'FRAG_LATENCY'})?$config->{'FRAG_LATENCY'}:$frag_latency_default)
					." seconds");
				sleep( defined($config->{'FRAG_LATENCY'})?$config->{'FRAG_LATENCY'}:$frag_latency_default );
			}
			next;
		}

		# Normal priority
      
		for (keys %$packages){
			# If done file found, clean package
			if(-e "$_/done"){
				$logger->debug("done file found!!");
				done($_,$logger,$context,$messages,$settings);
				next;
			}
			next if $i % $packages->{$_}->{'PRI'} != 0;
			download($_,$logger,$context,$messages,$settings,$packages);
			
			$logger->debug("Now pausing for a fragment latency => ".
			(defined( $config->{'FRAG_LATENCY'} )?$config->{'FRAG_LATENCY'}:$frag_latency_default)
			." seconds");
			
			sleep(defined($config->{'FRAG_LATENCY'})?$config->{'FRAG_LATENCY'}:$frag_latency_default);
		}
		
		$logger->debug("Now pausing for a cycle latency => ".(
		defined($config->{'CYCLE_LATENCY'})?$config->{'CYCLE_LATENCY'}:$cycle_latency_default)
		." seconds");
		
		sleep(defined($config->{'CYCLE_LATENCY'})?$config->{'CYCLE_LATENCY'}:$cycle_latency_default);
	}
	sleep($config->{'PERIOD_LATENCY'}?$config->{'PERIOD_LATENCY'}:$period_latency_default);
}

# Download a fragment of the specified package
sub download {
	my ($id,$logger,$context,$messages,$settings,$packages) = @_;

	my $error;
	my $proto = $packages->{$id}->{'PROTO'};
	my $location = $packages->{$id}->{'PACK_LOC'};
	my $URI = "$proto://$location/$id/";
 
	# If we find a temp file, we know that the update of the task file has failed for any reason. So we retrieve it from this file
	if(-e "$id/task.temp") {
		unlink("$id/task.temp");
		rename("$id/task.temp","$id/task") or return 1;
	}
	
	# Retrieve fragments already downloaded
	unless(open TASK, "$id/task"){
		$logger->error("Cannot open $id/task.");
		return 1;
	}
	my @task = <TASK>;
	
	# Done
	if(!@task){
		$logger->debug("Download of $id... Finished.");
		close(TASK);
		execute($id,$logger,$context,$messages,$settings,$packages);
		return 0;
	}
	
	my $fragment = shift(@task);
	my $request = HTTP::Request->new(GET => $URI.$fragment);
	
	$logger->debug("Downloading $fragment...");
	
	# Using proxy if possible
	$ua->env_proxy;
	my $res = $ua->request($request);
	
	# Checking if connected
	if($res->is_success) {
		$logger->debug("Success :-)");
		$error = 0;
		open FRAGMENT, ">$id/$fragment" or return 1;
		print FRAGMENT $res->content;
		close(FRAGMENT);
		
		# Updating task file
		rename(">$id/task", ">$id/task.temp");
		open TASK, ">$id/task" or return 1;
		print TASK @task;
		close(TASK);
		unlink(">$id/task.temp");
		
	}
	else {
		#download_message($id, ERR_DOWNLOAD_PACK);
		close(TASK);
		$logger->error("Error :-( ".$res->code);
		$error++;
		if($error > $settings->{maw_error_count}){
			$logger->error("Error : Max errors count reached");
			finish($logger,$context);
		}
		return 1;
	}
	return 0;
}

# Assemble and handle downloaded package
sub execute{
	my ($id,$logger,$context,$messages,$settings,$packages) = @_;

   my $common = $context->{common};

	my $tmp = $id."/tmp";
	my $exit_code;
	
	$logger->debug("Execute orders for package $id.");
	
	if(build_package($id,$logger,$context,$messages,$packages)){
		clean($id,$logger, $context,$messages,$packages);
		return 1;
	}else{
		# First, we get in temp directory
		unless( chdir($tmp) ){
		 	$logger->error("Cannot chdir to working directory: $!");
			download_message($id, $messages->{err_execute}, $logger,$context);
			clean($id,$logger, $context,$messages,$packages);
			return 1;
		}
		
		# Executing preorders (notify user, auto launch, etc....
	# 		$id->{NOTIFY_USER}
	# 		$id->{NOTIFY_TEXT}
	# 		$id->{NOTIFY_COUNTDOWN}
	# 		$id->{NOTIFY_CAN_ABORT}
        # TODO: notification to send through DBUS to the user
		
		
		eval{
			# Execute instructions
			if($packages->{$id}->{'ACT'} eq 'LAUNCH'){
				my $exe_line = $packages->{$id}->{'NAME'};
				$packages->{$id}->{'NAME'} =~ s/^([^ -]+).*/$1/;
				# Exec specified file (LAUNCH => NAME)
				if(-e $packages->{$id}->{'NAME'}){
					$logger->debug("Launching $packages->{$id}->{'NAME'}...");
					chmod(0755, $packages->{$id}->{'NAME'}) or die("Cannot chmod: $!");
					$exit_code = system( "./".$exe_line );
				}else{
					die();
				}
				
			}elsif($packages->{$id}->{'ACT'} eq 'EXECUTE'){
				# Exec specified command EXECUTE => COMMAND
				$logger->debug("Execute $packages->{$id}->{'COMMAND'}...");
				system( $packages->{$id}->{'COMMAND'} ) and die();
				
			}elsif($packages->{$id}->{'ACT'} eq 'STORE'){
				# Store files in specified path STORE => PATH
				$packages->{$id}->{'PATH'} =~ s/INSTALL_PATH/$context->{installpath}/;
				
				# Build it if needed
				my @dir = split('/', $packages->{$id}->{'PATH'});
				my $dir;
				
				for(@dir){
					$dir .= "$_/";
					unless(-e $dir){
						mkdir($dir);
						$logger->debug("Create $dir...");
					}	
				}
				
				$logger->debug("Storing package to $packages->{$id}->{'PATH'}...");
				# Stefano Brandimarte => Stevenson! <stevens@stevens.it>
				system($common->get_path('cp')." -pr * ".$packages->{$id}->{'PATH'}) and die();
			}
		};
		if($@){
			# Notify success to ocs server
			download_message($id, $messages->{err_execute},$logger,$context);
			chdir("../..") or die("Cannot go back to download directory: $!");
			clean($id,$logger,$context,$messages,$packages);
			return 1;
		}else{
			chdir("../..") or die("Cannot go back to download directory: $!");
			done($id, (defined($exit_code)?$exit_code:'_NONE_'),$logger,$context,$messages,$settings,$packages);
			return 0;
		}
	}	
}

# Check package integrity
sub build_package{
	my ($id,$logger,$context,$messages,$packages) = @_;

   my $common = $context->{common};

	my $count = $packages->{$id}->{'FRAGS'};
	my $i;
	my $tmp = "./$id/tmp";
	
	unless(-d $tmp){
		mkdir("$tmp");
	}
	# No job if no files
	return 0 unless $count;
	
	# Assemble package
	$logger->info("Building package for $id.");
	
	for($i=1;$i<=$count;$i++){
		if(-f "./$id/$id-$i"){
			# We make a tmp working directory
			if($i==1){
				open PACKAGE, ">$tmp/build.tar.gz" or return 1;
			}
			# We write each fragment in the final package
			open FRAGMENT, "./$id/$id-$i" or return 1;
			my $row;
			while($row = <FRAGMENT>){
				print PACKAGE $row;
			}
			close(FRAGMENT);
		}else{
			return 1;
		}
	}
	close(PACKAGE);
	# 
	if(check_signature($packages->{$id}->{'DIGEST'}, "$tmp/build.tar.gz", $packages->{$id}->{'DIGEST_ALGO'}, $packages->{$id}->{'DIGEST_ENCODE'},$logger)){
		download_message($id, $messages->{err_bad_digest},$logger,$context);
		return 1;
	}
	
	if( system( $common->get_path("tar")." -xvzf $tmp/build.tar.gz -C $tmp") ){
		$logger->error("Cannot extract $id.");
		download_message($id,$messages->{err_build},$logger,$context);
		return 1;
	}
	$logger->debug("Building of $id... Success.");
	unlink("$tmp/build.tar.gz") or die ("Cannot remove build file: $!\n");
	return 0;
}

sub check_signature{
	my ($checksum, $file, $digest, $encode,$logger) = @_;
		
	$logger->info("Checking signature for $file.");
	
	my $base64;
		
	# Open file
	unless(open FILE, $file){
		$logger->error("cannot open $file: $!");
		return 1;
	}
	
	binmode(FILE);
	# Retrieving encoding form
	if($encode =~ /base64/i){
		$base64 = 1;
		$logger->debug('Digest format: Base 64');
	}elsif($encode =~ /hexa/i){
		$logger->debug('Digest format: Hexadecimal');
	}else{
		$logger->debug('Digest format: Not supported');
		return 1;
	}
	
	eval{
		# Check it
		if($digest eq 'MD5'){
			$logger->debug('Digest algo: MD5');
			if($base64){
				die unless Digest::MD5->new->addfile(*FILE)->b64digest eq $checksum; 
			}
			else{
				die unless Digest::MD5->new->addfile(*FILE)->hexdigest eq $checksum;
			}
		}elsif($digest eq 'SHA1'){
			$logger->debug('Digest algo: SHA1');
			if($base64){
				die unless Digest::SHA1->new->addfile(*FILE)->b64digest eq $checksum;
			}
			else{
				die unless Digest::SHA1->new->addfile(*FILE)->hexdigest eq $checksum;
			}
		}else{
			$logger->debug('Digest algo unknown: '.$digest);
			die;
		}
	};
	if($@){
		$logger->debug("Digest checking error !!");
		close(FILE);
		return 1;
	}else{
		close(FILE);
		$logger->debug("Digest OK...");
		return 0;
	}
}

# Launch a download error to ocs server
sub download_message{
	my ($id, $code,$logger,$context) = @_;
	
	$logger->debug("Sending message for $id, code=$code.");
	
	my $xml = {
		'DEVICEID' => $context->{deviceid},
		'QUERY' => 'DOWNLOAD',
		'ID' => $id,
		'ERR' => $code
	};
	
	# Generate xml
	$xml = XMLout($xml, RootName => 'REQUEST');
	
	# Compress data
	$xml = Compress::Zlib::compress( $xml );
	
	my $URI = $context->{servername};
	
	# Send request
	my $request = HTTP::Request->new(POST => $URI);
	$request->header('Pragma' => 'no-cache', 'Content-type', 'application/x-compress');
	$request->content($xml);
	my $res = $ua->request($request);
	
	# Checking result
	if($res->is_success) {
		return 0;	
	}else{
		return 1;
	}
}

# At the beginning of end handler
sub begin{
	my ($pidfile,$logger) = @_;

	open LOCK_R, "$pidfile" or die("Cannot open pid file: $!"); 
	if(flock(LOCK_R,LOCK_EX|LOCK_NB)){
		open LOCK_W, ">$pidfile" or die("Cannot open pid file: $!");
		select(LOCK_W) and $|=1;
		select(STDOUT) and $|=1;
		print LOCK_W $$;
		$logger->info("Beginning work. I am $$.");
		return 0;
	}else{
		close(LOCK_R);
		$logger->error("$pidfile locked. Cannot begin work... :-(");
		return 1;
	}
}

sub done{	
	my ($id,$suffix,$logger,$context,$messages,$settings,$packages) = @_;

   my $common = $context->{common};

	my $frag_latency_default = $settings->{frag_latency_default};

	$logger->debug("Package $id... Done. Sending message...");
	# Trace installed package
	open DONE, ">$id/done";
	close(DONE);
	# Put it in history file
	open HISTORY, ">>history" or warn("Cannot open history file: $!");
	flock(HISTORY, LOCK_EX);
	my @historyIds = <HISTORY>;
	if( $common->already_in_array($id, @historyIds) ){
		$logger->debug("Warning: id $id has been found in the history file!!");
	}
	else {
		$logger->debug("Writing $id reference in history file");
		print HISTORY $id,"\n";
	}
	close(HISTORY);
	
	# Notify success to ocs server
	my $code;
	if($suffix ne '_NONE_'){
		$code = $messages->{code_succes}."_$suffix";
	}
	else{
		$code = $messages->{code_succes};
	}
	unless(download_message($id, $code,$logger,$context)){
		clean($id,$logger,$context,$messages,$packages);

	}else{
		sleep( defined($config->{'FRAG_LATENCY'})?$config->{'FRAG_LATENCY'}:$frag_latency_default );
	}
	return 0;
}

sub clean{
	my ($id,$logger,$context,$messages,$packages) = @_;

	$logger->info("Cleaning $id package.");

	delete $packages->{$id};
	
	#If the package is priority 0
	if ((my $index) = grep { $prior_pkgs[$_] eq $id } 0..$#prior_pkgs){
        	delete $prior_pkgs[$index];
	}



	unless(File::Path::rmtree($id, 0)){
		$logger->error("Cannot clean $id!! Abort...");
		download_message($id, $messages->{err_clean},$logger,$context);
		die();
	}
	return 0;
}

# At the end
sub finish{
	my ($logger,$context) = @_;
 
	open LOCK, '>'.$context->{installpath}.'/download/lock';
	$logger->debug("End of work...\n");
	exit(0);
}

1;
