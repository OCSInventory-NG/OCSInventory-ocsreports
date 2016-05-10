package Ocsinventory::Agent::Backend::OS::Linux::Network::IP;

sub check {
  return unless can_run ("ifconfig") || can_run("ip");
  1;
}

# Initialise the distro entry
sub run {
    my $params = shift;
    my $common = $params->{common};
    my @ip;

    if (can_run("ip")){
        foreach (`ip a`){
            if (/inet (\S+)\/\d{1,2}/){
                ($1=~/127.+/)?next:push @ip,$1;
            } elsif (/inet6 (\S+)\d{2}/){
                ($1=~/::1\/128/)?next:push @ip, $1;
            }
        }
    } elsif (can_run("ifconfig")){
        foreach (`ifconfig`){
            #if(/^\s*inet\s*(\S+)\s*netmask/){
            if (/^\s*inet add?r\s*:\s*(\S+)/ || /^\s*inet\s+(\S+)/){
                ($1=~/127.+/)?next:push @ip, $1;
            } elsif (/^\s*inet6\s+(\S+)/){
                ($1=~/::1/)?next:push @ip, $1;
            }
        }

    my $ip=join "/", @ip;
    if (defined $ip) {
          $common->setHardware({IPADDR => $ip});
    }
}

1;
