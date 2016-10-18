package Ocsinventory::Agent::Backend::OS::Generic::Packaging::Gentoo;

use strict;
use warnings;

sub check {
    my $params = shift;
    my $common = $params->{common};
    $common->can_run("equery")
}
sub run {
    my $params = shift;
    my $common = $params->{common};

# TODO: This had been rewrite from the Linux agent _WITHOUT_ being checked!
# New format for listing softwares with equery command (equery 0.3.0.5)
# Older version don't run with these options

    my $equery_vers = `equery --version ` =~ /.*\((.*)\).*/;
    $equery_vers = $1;

    if ($equery_vers =~ /^0.3/) {
        foreach (`equery list --format='\$cp \$fullversion' '*'`){
            if (/^(.*) (.*)/) {
                $common->addSoftware({
                    'NAME'          => $1,
                    'VERSION'       => $2,
                });
            }
        }
    } else {
    # Old version of Gentoo
        foreach (`equery list -i`){
            if (/^([a-z]\w+-\w+\/\.*)-([0-9]+.*)/) {
                $common->addSoftware({
                    'NAME'          => $1,
                    'VERSION'       => $2,
                });
            }
        }
    } 
}

1;
