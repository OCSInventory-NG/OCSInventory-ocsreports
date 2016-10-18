package Ocsinventory::Agent::Backend::OS::Linux::Sys;

#$LunchAfter = "Ocsinventory::Agent::Backend::OS::Linux::VirtualFs::Sys";

sub check {
    my $params = shift;
    my $common = $params->{common};

    return unless $common->can_run ("mount");
    foreach (`mount`) {
        return 1 if (/type\ sysfs/);
    }
    0;
}

sub run {}

1;
