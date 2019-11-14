package Ocsinventory::Agent::Network;
# TODO:
#  - set the correct deviceID and olddeviceID
use strict;
use warnings;

use IO::Socket::SSL qw(SSL_VERIFY_NONE);
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
    } else {
        $self->{ua}->env_proxy;
    }
    my $version = 'OCS-NG_unified_unix_agent_v';
    $version .= exists ($self->{config}->{VERSION})?$self->{config}->{VERSION}:'';
    $self->{ua}->agent($version);
    $self->{ua}->credentials(
        $uaserver, # server:port, port is needed 
        $self->{config}->{realm},
        $self->{config}->{user},
        $self->{config}->{password}
    );

    #Setting SSL configuration depending on LWP version
    $self->{ua}->_agent =~ /^libwww-perl\/(.*)$/;
    my $lwp_version= $1;
    $lwp_version=$self->{common}->convertVersion($lwp_version,3);  

    if ($lwp_version > 583) {
        $self->{ua}->ssl_opts(
            verify_hostname => $self->{config}->{ssl},
            SSL_ca_file => $self->{config}->{ca}
        ); 

        if ($self->{config}->{ssl} == 0 ) {
            $self->{ua}->ssl_opts(
                SSL_verify_mode => SSL_VERIFY_NONE
            );
        }
    } elsif ($self->{config}->{ssl} eq 1) {
      # SSL verification is disabled by default in LWP prior to version 6
      # we activate it using Crypt::SSLeay environment variables
      $ENV{HTTPS_CA_FILE} = $self->{config}->{ca};
    }

    bless $self;
}

sub sendXML {
    my ($self, $args) = @_;

    my $logger = $self->{logger};
    my $compress = $self->{compress};
    my $message = $args->{message};

    my $common = $self->{common};

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
    if (!$res->is_success) {
        $logger->error ('Cannot establish communication : '.$res->status_line);
        return;
    }

    return $res ;

}

sub getXMLResp {

    my ($self, $res, $msgtype) = @_;
    my $logger = $self->{logger};
    my $compress = $self->{compress};

    # If no answer from OCS server
    return unless $res;

    # Reading the XML response from OCS server
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
        config => $self->{config},
        common => $self->{common},
    });

    return $response;
}

sub getFile {
    my ($self,$proto,$uri,$filetoget,$filepath) = @_;
    my $logger= $self->{logger};

    chomp($proto,$uri,$filetoget,$filepath);
 
    my $url = "$proto://$uri/$filetoget";
    my $response = $self->{ua}->mirror($url,$filepath);

    if ($response->is_success){
        $logger->debug("Success downloading $filetoget file...");
    } else {
        $logger->error("Failed downloading $filetoget: ".$response->status_line." !!!");
        return 1;
    }
}

1;
