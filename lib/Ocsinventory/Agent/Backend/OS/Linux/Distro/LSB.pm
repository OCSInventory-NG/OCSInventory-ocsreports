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
    chomp($release =`lsb_release -is`);

    my $OSversion;
    chomp($OSversion =`lsb_release -rs`);
 
    my $OSComment;
    chomp($OSComment =`uname -v`);

    $common->setHardware({ 
        OSNAME => $release,
        OSVERSION => $OSversion,
        OSCOMMENTS => "$OSComment"
    });
}

1;
