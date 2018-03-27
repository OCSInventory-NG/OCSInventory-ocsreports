package Ocsinventory::Agent::Backend::OS::Generic::Repository::BSD;

use strict;
use warnings;

sub check {
    my $params = shift;
    my $common = $params->{common};
    return unless $common->can_run("pkg");
}

my $repo_name;
my $repo_baseurl;
my $repo_priority;
my $repo_mirror_type;
my $repo_signature_type;
my $repo_fingerprints;
my $repo_enable;

sub run {
    my $params = shift;
    my $common = $params->{common};

    if ($^O eq 'freebsd') {
        foreach my $line (`LANG=C pkg -vv 2>/dev/null`){
            next if ($line =~ /^Repositories:/i);
            if ($line =~ /^(\w):\s\{/i){
                $repo_name = $1;
            } elsif ($line =~ /^url\s+:\s\"(.*)\"/i) {
                $repo_baseurl = $1;
            } elsif ($line =~ /^enabled\s+:\s(.*)\,/i){
                $repo_enable = $1;
            } elsif ($line =~ /^priority\s+:\s(.*)\,/i) {
                $repo_priority = $1;
            } elsif ($line =~ /^Mirror_type\s+:\s\"(.*)\"\,/i){
                $repo_mirror_type = $1;
            } elsif ($line =~ /^signature_type\s+:\s\"(.*)\"\,/i){
                $repo_signature_type = $1;
            } elsif ($line =~ /^fingerprints\s+:\s\"(.*)\"/i){
                $repo_fingerprints = $1;
            }
            if ($line =~ /^  \}$/) {
                $common->addRepo({
                    BASEURL => $repo_baseurl,
                    NAME => $repo_name,
                    ENABLED => $repo_enable,
                    PRIORITY => $repo_priority,
                    MIRROR => $repo_mirror_type,
                    SIGNATURE => $repo_signature_type,
                    FINGERPRINTS => $repo_fingerprints,
                });
                $repo_name = $repo_baseurl = $repo_enable = $repo_priority = $repo_mirror_type = $repo_signature_type = $repo_fingerprints = undef;
            }
        }
    }
}

1;
