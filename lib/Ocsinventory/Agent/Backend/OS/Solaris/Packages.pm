package Ocsinventory::Agent::Backend::OS::Solaris::Packages;

use strict;
use warnings;

sub check {
      my $params = shift;
     my $common = $params->{common};
      # Do not run an package inventory if there is the --nosoftware parameter
      return if ($params->{config}->{nosoftware});
      $common->can_run("pkginfo") || $common->can_run("pkg");
}

sub run {
     my $params = shift;
     my $common = $params->{common};
     my $chaine ;
     my @tab;

     my $name;
     my $version;
     my $comments;
     my $publisher;

     if ( `uname -r` =~ /5.11/ ) {
     # Solaris 11

     foreach (`pkg info`) {
         if (/^\s*$/) {
             $common->addSoftware({
                 'NAME'          => $name,
                 'VERSION'       => $version,
                 'COMMENTS'      => $comments,
                 'PUBLISHER'     => $publisher,
             });
             $name = '';
             $version = '';
             $comments = '';
             $publisher = '';
         } elsif (/Name:\s+(.+)/) {
             $name = $1;
         } elsif (/Version:\s+(.+)/) {
             $version = $1;
         } elsif (/Publisher:\s+(.+)/) {
             $publisher = $1;
         } elsif (/Summary:\s+(.+)/) {
             $comments = $1;
         }
     }

     } else {
     # Solaris 10 and lower

     foreach (`pkginfo -l`) {
         if (/^\s*$/) {
             $common->addSoftware({
                 'NAME'          => $name,
                 'VERSION'       => $version,
                 'COMMENTS'      => $comments,
                 'PUBLISHER'     => $publisher,
             });
             $name = '';
             $version = '';
             $comments = '';
             $publisher = '';
         } elsif (/PKGINST:\s+(.+)/) {
             $name = $1;
         } elsif (/VERSION:\s+(.+)/) {
             $version = $1;
         } elsif (/VENDOR:\s+(.+)/) {
             $publisher = $1;
         } elsif (/DESC:\s+(.+)/) {
             $comments = $1;
         }
     }
     my $testrep;
     $testrep=0;
     #opendir(DIR,'/var/sis/') || exit ;
     opendir(DIR,'/var/sis/') || ($testrep=1) ;
     if ($testrep==0) {
         foreach (`ls /var/sis/*.SIS`) {
             $chaine= `cat $_` ;
             @tab = split(/;/, $chaine);
             if (/^\/var\/sis\/(\S+).SIS/){
                 $common->addSoftware({
                     'VERSION'       => $tab[2],
                     'NAME'          => $tab[0]." ($1)",
                     'PUBLISHER'     => $tab[1],
                     'COMMENTS'      => $1,
                 });
             }
         }
     }
     closedir(DIR);

     }
}

1;
