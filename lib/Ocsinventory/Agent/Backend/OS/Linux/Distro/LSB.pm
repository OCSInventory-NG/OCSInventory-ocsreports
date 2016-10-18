package Ocsinventory::Agent::Backend::OS::Linux::Distro::LSB;

sub check {
    my $params = shift;
    my $common = $params->{common};
    $common->can_run("lsb_release")
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    my $release;
    foreach (`lsb_release -i`) {
        $release = $1 if /Distributor\sID:\s+(.+)/;
    }

    my $OSversion;
    foreach (`lsb_release -r`){
        $osversion = $1 if /Release:\s+(.+)/;
    }
 
    my $OSComment;
    chomp($OSComment =`uname -v`);

    $common->setHardware({ 
        OSNAME => $release,
        OSVERSION => $osversion,
        OSCOMMENTS => "$OSComment"
    });
}

1;
