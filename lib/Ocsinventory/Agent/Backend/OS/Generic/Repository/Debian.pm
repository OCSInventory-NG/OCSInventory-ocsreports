package Ocsinventory::Agent::Backend::OS::Generic::Repository::Debian;

use strict;
use warnings;

sub check{
    my $params = shift;
    my $common = $params->{common};
    return unless $common->can_run("apt");
}

sub run{
  my $params = shift;
  my $common = $params->{common};

  my ($repo_name, $repo_baseurl);

  my @repository=`apt-cache policy | grep -i http | awk {'print $2 $3'} | sort -u`;
  push @repository, "\n";

  for (my $i;$i<$#repository;$i++){
      my $line=$repository[$i];
      if ($line =~ /^$/ && $repo_name && $repo_baseurl){
          $common->addRepo({
              NAME => $repo_name,
              BASEURL => $repo_baseurl,
          });
          $repo_name = $repo_baseurl = undef;
      }
      $repo_name=$1 if ($line =~ /.*\s(\w-?\/\w)/);
      $repo_baseurl=$1 if ($line =~ /^https?:\/\//);
  }
}

1;
