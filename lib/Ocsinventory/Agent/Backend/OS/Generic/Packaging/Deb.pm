package Ocsinventory::Agent::Backend::OS::Generic::Packaging::Deb;

use strict;
use warnings;

sub check { can_run("dpkg") }

sub run {
  my $params = shift;
  my $common = $params->{common};
  my $size;
  
# use dpkg-query --show --showformat='${Package}|||${Version}\n'
  foreach(`dpkg-query --show --showformat='\${Package}---\${Version}---\${Installed-Size}---\${Description}\n'`) {
     if (/^(\S+)---(\S+)---(\S+)---(.*)/) { 
       if ($3) { 
	$size=$3;
       } else {
        $size='Unknown size';
       }	
       $common->addSoftware ({
         'NAME'          => $1,
         'VERSION'       => $2,
         'FILESIZE'      => $size,
         'COMMENTS'      => $4,
         'FROM'          => 'deb'
       });
    }
  }
}

1;
