package Ocsinventory::Agent::Backend::OS::BSD::Drives;

use strict;

sub run {
    my $params = shift;
    my $common = $params->{common};
  
    my $free;
    my $filesystem;
    my $total;
    my $type;
    my $volumn;
  
  
    for my $t ("ffs","ufs","zfs") {
    # OpenBSD has no -m option so use -k to obtain results in kilobytes
        for (`df -P -t $t -k`){
            if (/^(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\n/){
                $volumn = $1;
                $filesystem = $t;
                $total = sprintf("%i",$2/1024);
                $free = sprintf("%i",$4/1024);
                $type = $6;
    
                $common->addDrive({
                    FREE => $free,
                    FILESYSTEM => $filesystem,
                    TOTAL => $total,
                    TYPE => $type,
                    VOLUMN => $volumn
                });
            }
        }
    }
}
1;
