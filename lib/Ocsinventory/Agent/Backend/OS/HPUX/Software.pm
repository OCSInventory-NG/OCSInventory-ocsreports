package Ocsinventory::Agent::Backend::OS::HPUX::Software;

sub check  { 
   my $params = shift;

   # Do not run an package inventory if there is the --nosoft parameter
   return if ($params->{params}->{nosoftware});

   $^O =~ /hpux/ 
}

sub run {
   my $params = shift;
   my $common = $params->{common};

   my @softList;
   my $software;

   

   @softList = `swlist | grep -v '^  PH' | grep -v '^#' |tr -s "\t" " "|tr -s " "` ;
   foreach $software (@softList) {
      chomp( $software );
      if ( $software =~ /^ (\S+)\s(\S+)\s(.+)/ ) {
         $common->addSoftwares({
                        'NAME'          => $1  ,
                        'VERSION'       => $2 ,
                        'COMMENTS'      => $3 ,
                        'PUBLISHER'     => "HP" ,
				  });
       }
    }

 }

1;
