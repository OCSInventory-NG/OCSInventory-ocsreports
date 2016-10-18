package Ocsinventory::Agent::Backend::OS::AIX::Modems;
use strict;

sub check {
    my $params = shift;
    my $common = $params->{common};
    $common->can_run("lsdev")
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    for (`lsdev -Cc adapter -F 'name:type:description'`){
        if (/modem/i && /\d+\s(.+):(.+)$/){
            my $name = $1;
            my $description = $2;
            $common->addModems({
                'DESCRIPTION'  => $description,
                'NAME'          => $name,
            });
        }
    }
}

1
