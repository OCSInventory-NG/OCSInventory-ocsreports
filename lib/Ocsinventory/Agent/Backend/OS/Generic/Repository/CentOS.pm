package Ocsinventory::Agent::Backend::OS::Generic::Repository::CentOS;

use strict;
use warnings;

sub check {
    my $params = shift;
    my $common = $params->{common};
    return unless $common->can_run("yum");
}

my $repo_name;
my $repo_baseurl;
my $repo_filename;
my $repo_pkgs;
my $repo_rev;
my $repo_size;
my $repo_tag;
my $repo_updated;
my $repo_mirrors;
my $repo_expire;
my $repo_exclude;
my $repo_excluded;
my $repo_metalink;

sub run {
    my $params = shift;
    my $common = $params->{common};
    my @repository=`LANG=C yum -v repolist 2>/dev/null`;
    push @repository, "\n";

    for (my $i=0;$i<$#repository;$i++){
        my $line=$repository[$i];
        if ($line =~ /^$/ && $repo_name && $repo_filename) {
            $common->addRepo({
                BASEURL => $repo_baseurl,
                EXCLUDE => $repo_exclude,
                EXCLUDED => $repo_excluded,
                EXPIRE => $repo_expire,
                FILENAME => $repo_filename,
                MIRRORS => $repo_mirrors,
                NAME => $repo_name,
                PKGS => $repo_pkgs,
                REVISION => $repo_rev,
                SIZE => $repo_size,
                TAG => $repo_tag,
                UPDATED => $repo_updated,
            });
            $repo_name = $repo_baseurl = $repo_filename = $repo_pkgs = $repo_size = $repo_tag = $repo_updated = $repo_mirrors = $repo_expire = $repo_exclude = $repo_excluded = $repo_rev = $repo_metalink = undef;
        }
        $repo_name = $1 if ($line =~ /^Repo-name\s+:\s(.*)/i);
        $repo_baseurl = $1 if ($line =~ /^Repo-baseurl\s+:\s(.*)/i);
        $repo_mirrors = $1 if ($line =~ /^Repo-mirrors\s+:\s(.*)/i);
        $repo_filename = $1 if ($line =~ /^Repo-filename:\s(.*)/i);
        $repo_pkgs = $1 if ($line =~ /^Repo-pkgs\s+:\s(.*)/i);
        $repo_rev = $1 if ($line =~ /^Repo-revision:\s(.*)/i);
        $repo_size = $1 if ($line =~ /^Repo-size\s+:\s(.*)/i);
        $repo_tag = $1 if ($line =~ /^Repo-tags\s+:\s(.*)/i);
        $repo_updated = $1 if ($line =~ /^Repo-updated\s+:\s(.*)/i);
        $repo_exclude = $1 if ($line =~ /^Repo-exclude\s+:\s(.*)/i);
        $repo_excluded = $1 if ($line =~ /^Repo-excluded\s+:\s(.*)/i);
        $repo_expire = $1 if ($line =~ /^Repo-expire\s+:\s(.*)/i);
        $repo_metalink =$1 if ($line =~ /^Repo-metalink\s+:\s(.*)/i);
    }
}

1;
