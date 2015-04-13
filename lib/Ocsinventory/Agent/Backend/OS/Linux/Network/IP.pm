package Ocsinventory::Agent::Backend::OS::Linux::Network::IP;

sub check {
  return unless can_run ("ifconfig");
  return unless can_run ("ip");
  1;
}

# Initialise the distro entry
sub run {
  	my $params = shift;
  	my $common = $params->{common};
  	my @ip;
	my @ip6;

	if (can_run("ip")){
		foreach (`ip addr show`){
			if (/^\s*inet\s+(\S+)\/(\d+)/){
				($1=~/127.+/)?next:push @ip, $1;
			} elsif (/^\s*inet\s+(\S+)\/(\d{1,3})\s+.*(\S+)/){
				($1=~/::1.+/)?next:push @ip6, $1;
			}
		}
	} elsif (can_run("ifconfig")){
  		foreach (`ifconfig`){
    		#if(/^\s*inet\s*(\S+)\s*netmask/){
    		if(/^\s*inet add?r\s*:\s*(\S+)/ || /^\s*inet\s+(\S+)/){
      			($1=~/127.+/)?next:push @ip, $1;
    		} elsif (/^\s*inet6\s+(\S+)/){
				($1=~/::1.+/)?next:push @ip6, $1;
			} 
  		}
	}

  	my $ip=join "/", @ip;
	my $ip6=join "/", @ip6;

	if (defined $ip) {
  		$common->setHardware({IPADDR => $ip});
	} elsif (defined $ip6) {
		$common->setHardware({IPADDR => $ip6});
	}

}

1;
