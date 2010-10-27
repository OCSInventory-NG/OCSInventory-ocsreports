package Ocsinventory::Agent::Network;
# TODO:
#  - set the correct deviceID and olddeviceID
use strict;
use warnings;

use LWP::UserAgent;
use Socket;

use Ocsinventory::Compress;


sub new {
  my (undef, $params) = @_;

  my $self = {};
  
  $self->{accountconfig} = $params->{accountconfig}; 
  $self->{accountinfo} = $params->{accountinfo}; 
  $self->{common} = $params->{common}; 

  my $logger = $self->{logger} = $params->{logger};

  $self->{config} = $params->{config};
  my $uaserver;

  if ($self->{config}->{server} =~ /^http(|s):\/\//) {
      $self->{URI} = $self->{config}->{server};
      $uaserver = $self->{config}->{server};
      $uaserver =~ s/^http(|s):\/\///;
      $uaserver =~ s/\/.*//;
      if ($uaserver !~ /:\d+$/) {
          $uaserver .= ':443' if $self->{config}->{server} =~ /^https:/;
          $uaserver .= ':80' if $self->{config}->{server} =~ /^http:/;
      }
  } else {
      $self->{URI} = "http://".$self->{config}->{server}.$self->{config}->{remotedir};
      $uaserver = $self->{config}->{server};
  }


  $self->{compress} = new Ocsinventory::Compress ({logger => $logger});
  # Connect to server
  $self->{ua} = LWP::UserAgent->new(keep_alive => 1);
  if ($self->{config}->{proxy}) {
    $self->{ua}->proxy(['http', 'https'], $self->{config}->{proxy});
  }  else {
    $self->{ua}->env_proxy;
  }
  my $version = 'OCS-NG_unified_unix_agent_v';
  $version .= exists ($self->{config}->{VERSION})?$self->{config}->{VERSION}:'';
  $self->{ua}->agent($version);
    $self->{config}->{user}.",".
    $self->{config}->{password}."";
  $self->{ua}->credentials(
    $uaserver, # server:port, port is needed 
    $self->{config}->{realm},
    $self->{config}->{user},
    $self->{config}->{password}
  );

  bless $self;
}


sub sendXML {
  my ($self, $args) = @_;

  my $logger = $self->{logger};
  my $compress = $self->{compress};
  my $message = $args->{message};

  my $req = HTTP::Request->new(POST => $self->{URI});

  $req->header('Pragma' => 'no-cache', 'Content-type',
    'application/x-compress');

  $logger->debug ("sending XML");


  $logger->debug ("sending: ".$message);

  my $compressed = $compress->compress($message);

  if (!$compressed) {
    $logger->error ('failed to compress data');
    return;
  }

  $req->content($compressed);

  my $res = $self->{ua}->request($req);

  # Checking if connected
  if(!$res->is_success) {
    $logger->error ('Cannot establish communication : '.$res->status_line);
    return;
  }

  return $res ;

}

sub getXMLResp {

  my ($self, $res, $msgtype) = @_;
  my $logger = $self->{logger};
  my $compress = $self->{compress};

  #Reading the XML response from OCS server
  my $content = $compress->uncompress($res->content);

  if (!$content) {
    $logger->error ("Deflating problem");
    return;
  }

  my $tmp = "Ocsinventory::Agent::XML::Response::".$msgtype;
  eval "require $tmp";
  if ($@) {
      $logger->error ("Can't load response module $tmp: $@");
  }
  $tmp->import();
  my $response = $tmp->new ({
     accountconfig => $self->{accountconfig},
     accountinfo => $self->{accountinfo},
     content => $content,
     logger => $logger,
     #origmsg => $message,
     config => $self->{config}

  });

  return $response;
}


sub getHttpFile {
  my ($self,$uri,$filetoget,$filepath) = @_;
  my $url = "http://$uri/$filetoget";

  $self->{ua}->mirror($url,$filepath);
}

sub getHttpsFile {
  my ($self, $uri, $filetoget, $filepath ,$certfile, $installpath) = @_ ;

  my $logger = $self->{logger};
  my ($ctx, $ssl, $got);

  if ($self->{common}->can_load('Net::SSLeay')) {

    eval {
      $| = 1;
      $logger->debug('Initialize ssl layer...');

      # Initialize openssl
      if ( -e '/dev/urandom') {
        $Net::SSLeay::random_device = '/dev/urandom';
        $Net::SSLeay::how_random = 512;
      } else {
        srand (time ^ $$ ^ unpack "%L*", `ps wwaxl | gzip`);
        $ENV{RND_SEED} = rand 4294967296;
      }

      Net::SSLeay::randomize();
      Net::SSLeay::load_error_strings();
      Net::SSLeay::ERR_load_crypto_strings();
      Net::SSLeay::SSLeay_add_ssl_algorithms();

      #Create ctx object
      $ctx = Net::SSLeay::CTX_new() or die_now("Failed to create SSL_CTX $!");
      Net::SSLeay::CTX_load_verify_locations( $ctx, "$installpath/$certfile", $installpath )
      or die_now("CTX load verify loc: $!");

      # Tell to SSLeay where to find AC file (or dir)
      Net::SSLeay::CTX_set_verify($ctx, &Net::SSLeay::VERIFY_PEER,0);
      Net::SSLeay::die_if_ssl_error('callback: ctx set verify');

      my($server_name,$server_port,$server_dir);

      if($uri =~ /^([^:]+):(\d{1,5})(.*)$/){
        $server_name = $1;
        $server_port = $2;
        $server_dir = $3;
      } elsif ($uri =~ /^([^\/]+)(.*)$/) {
        $server_name = $1;
        $server_dir = $2;
        $server_port = '443';
      }

      $server_dir .= '/' unless $server_dir=~/\/$/;

      $server_name = gethostbyname ($server_name) or die;
      my $dest_serv_params  = pack ('S n a4 x8', &AF_INET, $server_port, $server_name );

      # Connect to server
      $logger->debug("Connect to server: $uri...");
      socket  (SOCKET, &AF_INET, &SOCK_STREAM, 0) or die "socket: $!";
      connect (SOCKET, $dest_serv_params) or die "connect: $!";

      # Flush socket
      select  (SOCKET); $| = 1; select (STDOUT);
      $ssl = Net::SSLeay::new($ctx) or die_now("Failed to create SSL $!");
      Net::SSLeay::set_fd($ssl, fileno(SOCKET));

      # SSL handshake
      $logger->debug('Starting SSL connection...');
      Net::SSLeay::connect($ssl);
      Net::SSLeay::die_if_ssl_error('callback: ssl connect!');

      # Get file
      my $http_request = "GET /$server_dir/$filetoget HTTP/1.0\n\n";
      Net::SSLeay::ssl_write_all($ssl, $http_request);
      shutdown SOCKET, 1;

      $got = Net::SSLeay::ssl_read_all($ssl);
      $got = (split("\r\n\r\n", $got))[1] or die;

      #Create file on disk
      open FILE, ">$filepath" or die("Cannot open info file: $!");
      print FILE $got;
      close FILE;
    };

    if($@){
      $logger->error("Error: SSL hanshake has failed");
      Net::SSLeay::free ($ssl) if $ssl;
      Net::SSLeay::CTX_free ($ctx);
      close SOCKET;
      return 0; 
    }
    else {
      $logger->debug("Success. :-)");
      Net::SSLeay::free ($ssl);
      Net::SSLeay::CTX_free ($ctx);
      close SOCKET;
    }

  } else {  	#Exit if can't load Net::SSLeay 
    return 0;
  }

  1;
}


1;
