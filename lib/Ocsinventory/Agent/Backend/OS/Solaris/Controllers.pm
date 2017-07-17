package Ocsinventory::Agent::Backend::OS::Solaris::Controllers;
use strict;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    $common->can_run ("cfgadm") 
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    my $name;
    my $type;
    my $description;

    foreach(`cfgadm -s cols=ap_id:type:info`){
        $name = $type = $description = "";
	next if (/^Ap_Id/);     
        if (/^(\S+)\s+/){
            $name = $1;
        }
        if(/^\S+\s+(\S+)/){
            $type = $1;
        }
        #No manufacturer, but informations about controller
        if(/^\S+\s+\S+\s+(.*)/){
            $description = $1;
        }
        $common->addController({
            'NAME'          => $name,
            'TYPE'          => $type,
            'DESCRIPTION'   => $description,
        });
    }
}
1;
