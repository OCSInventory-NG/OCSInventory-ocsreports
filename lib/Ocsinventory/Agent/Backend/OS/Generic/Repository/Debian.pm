package Ocsinventory::Agent::Backend::OS::Generic::Repository::Debian;

use strict;
use warnings;

sub check{
    my my $params = shift;
    my $common = $params->{common};
    return unless $common->can_run("apt");
}

sub run{
  my my $params = shift;
  my $common = $params->{common};

  my @repository=`apt-cache policy | grep -i http | awk {'print $2 $3'} | sort -u`;
  push @repository, "\n";

  for (my $i;$i<$#repository;i++){
      my $line=$repository[$i];
      if ($line =~ /^$/ && $repo_name && $repo_baseurl){
          $common->addRepo({
              NAME => $repo_name,
              BASEURL => $repo_baseurl,
          });
          $repo_name = $repo_basurl = undef;
      }
      $repo_name=$1 if ($line =~ /.*\s(\w-?\/\w)/);
      $repo_baseurl=$1 if ($line =~ /^https?:\/\/)
  }
}
