package Ocsinventory::Agent::Backend::OS::Generic::Packaging::Deb;

use strict;
use warnings;
use File::Basename;
use File::stat;


sub check { 
    my $params = shift;
    my $common = $params->{common};
    $common->can_run("dpkg") }

sub run {
    my $params = shift;
    my $common = $params->{common};
    my $size;
    my $key;
    my $value;
    my %statinfo;

    # List of files from which installation date will be extracted
    my @listfile=glob('"/var/lib/dpkg/info/*.list"');

    foreach my $file_list (@listfile){
        my $stat=stat($file_list);
        my ($year,$month,$day,$hour,$min,$sec)=(localtime($stat->mtime))[5,4,3,2,1,0];
        $value=sprintf "%02d/%02d/%02d %02d:%02d:%02d",($year+1900),$month,$day,$hour,$min,$sec;
        $key=fileparse($file_list, ".list");
        $key =~ s/(\s+):.+/$1/;
        $statinfo{$key}=$value;
    }
  
    # use dpkg-query --show --showformat='${Package}|||${Version}\n'
    foreach(`dpkg-query --show --showformat='\${Package}---\${Version}---\${Installed-Size}---\${Homepage}---\${Description}\n'`) {
        if (/^(\S+)---(\S+)---(\S*)---(\S*)---(.*)/) {
            if ($3) { 
                $size=$3;
            } else {
                $size='Unknown size';
            }
            $key=$1;
            if (exists $statinfo{$key}) {
                $common->addSoftware ({
                    'NAME'          => $1,
                    'VERSION'       => $2,
                    'FILESIZE'      => $size,
                    'PUBLISHER'     => $4,
                    'COMMENTS'      => $5,
                    'INSTALLDATE'   => $statinfo{$key},
                    'FROM'          => 'deb'
                });
            }
        }
    }
}

1;
