package Ocsinventory::Agent::Backend::OS::Generic::Repository::Rhel;

use strict;
use warnings;

sub check {
    my $params = shift;
    my $common = $params->{common};
    return unless $common->can_run("dnf");
}

my $repo_name;
my $repo_baseurl;
my $repo_filename;
my $repo_pkgs;
my $repo_rev;
my $repo_size;
my $repo_tag;
my $repo_updated;

sub run {
    my $params = shift;
    my $common = $params->{common};
    my @repository=`LANG=C dnf -v repolist 2>/dev/null`;
    push @repository, "\n";

    for (my $i=0;$i<$#repository;$i++){
         my $line=$repository[$i];
         if ($line =~ /^$/ && $repo_name && $repo_filename) {
            $common->addRepo({
                BASEURL => $repo_baseurl,
                FILENAME => $repo_filename,
                NAME => $repo_name,
                PACKAGES => $repo_pkgs,
                REVISION => $repo_rev,
                SIZE => $repo_size,
                TAG => $repo_tag,
                UPDATED => $repo_updated,
            });
            $repo_name = $repo_baseurl = $repo_filename = $repo_pkgs = $repo_rev = $repo_size = $repo_tag = $repo_updated = undef;
        }

        $repo_name=$1 if ($line =~ /^Repo-name\s+:\s(.*)/i);
        $repo_baseurl=$1 if ($line =~ /^Repo-baseurl\s+:\s(.*)/i);
        $repo_filename=$1 if ($line =~ /^Repo-filename:\s(.*)/i);
        $repo_pkgs=$1 if ($line =~ /^Repo-pkgs\s+:\s(.*)/i);
        $repo_rev=$1 if ($line =~ /^Repo-revision:\s(.*)/i);
        $repo_size=$1 if ($line =~ /^Repo-size\s+:\s(.*)/i);
        $repo_tag=$1 if ($line =~ /^Repo-tags\s+:\s(.*)/i);
        $repo_updated=$1 if ($line =~ /^Repo-updated\s+:\s(.*)/i);
    }
}

1;
