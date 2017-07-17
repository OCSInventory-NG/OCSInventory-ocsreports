package Ocsinventory::Agent::Backend::OS::Solaris::Drives;

#Filesystem            kbytes    used   avail capacity  Mounted on
#/dev/vx/dsk/bootdg/rootvol 16525754 5423501 10936996    34%    /
#/devices                   0       0       0     0%    /devices
#ctfs                       0       0       0     0%    /system/contract
#proc                       0       0       0     0%    /proc
#mnttab                     0       0       0     0%    /etc/mnttab


use strict;
sub check {
  my $params = shift;
  my $common = $params->{common};
  $common->can_run ("df")
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    my $free;
    my $filesystem;
    my $total;
    my $type;
    my $volumn;  

#Looking for mount points and disk space 
    for (`df -k`){
        if (/^Filesystem\s*/){next};
        # on Solaris 10 and up, /devices is an extra mount which we like to exclude
        if (/^\/devices/){next};
        # on Solaris 10 and up, /platform/.../libc_psr_hwcap1.so.1 is an extra mount which we like to exclude
        if (/^\/platform/){next};
        # exclude cdrom mount point
        if (/^\/.*\/cdrom/){next};
        if (/^swap.*/){next};
        # exclude special entries such as ctfs, proc, mnttab, etc...
        if (/^.*\s+0\s+0\s+0.*/){next};
        # skip nfs (dirty hack)
        if (/^\S+:\/.*/){next};
        # everything else is a local filesystem
        if (/^(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\n/){
            $filesystem = $6;
            $total = sprintf("%i",($2/1024));
            $free = sprintf("%i",($4/1024));
            $volumn = $1;
            chomp($type = `mount -v | grep  " $filesystem "`);
            $type =~ s/\S+\s+on\s+\S+\s+type\s+(\S+)\s+.*/$1/;
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

1;
