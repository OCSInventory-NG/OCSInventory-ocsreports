package Ocsinventory::Agent::Backend::OS::Linux::LVM;

use strict;
use vars qw($runAfter);
$runAfter = ["Ocsinventory::Agent::Backend::OS::Linux::Drives"];

sub check {
    my $params = shift;
    my $common = $params->{common};
    return unless $common->can_run ("pvs");
    1
}

sub run {
    my $params = shift;
    my $common = $params->{common};
    
    use constant MB => (1024*1024);
    
    if ($common->can_run('pvs')) {
        foreach (`pvs --noheading --nosuffix --units b -o +pv_uuid`) {
            chomp;
            $_ =~s/^\s+//;
            my @vs_elem=split('\s+');
            my $status='VG: '.$vs_elem[1].', Fmt: '.$vs_elem[2].', Attr: '.$vs_elem[3];
            $common->addDrive({
                FREE => $vs_elem[5]/MB,
                FILESYSTEM => 'LVM PV',
                TOTAL => $vs_elem[4]/MB,
                TYPE => $vs_elem[0],
                VOLUMN => $status,
                SERIAL => $vs_elem[6]
            });
        }
    }
    
    if ($common->can_run('vgs')) {
        foreach (`vgs --noheading --nosuffix --units b -o +vg_uuid,vg_extent_size`) {
            chomp;
            $_ =~s/^\s+//;
            my @vs_elem=split('\s+');
            my $status = 'PV/LV: '.$vs_elem[1].'/'.$vs_elem[2]
                .', Attr: '.$vs_elem[4].', PE: '.($vs_elem[8]/MB).' MB';
            $common->addDrive({
                FREE => $vs_elem[6]/MB,
                FILESYSTEM => 'LVM VG',
                TOTAL => $vs_elem[5]/MB,
                TYPE => $vs_elem[0],
                VOLUMN => $status,
                SERIAL => $vs_elem[7]
            });
        }
    }
    
    if ($common->can_run('lvs')) {
        foreach (`lvs -a --noheading --nosuffix --units b -o lv_name,vg_name,lv_attr,lv_size,lv_uuid,seg_count`) {
            chomp;
            $_ =~s/^\s+//;
            my @vs_elem=split('\s+');
            my $status='Attr: '.$vs_elem[2].', Seg: '.$vs_elem[5];
            $common->addDrive({
                FREE => 0,
                FILESYSTEM => 'LVM LV',
                TOTAL => $vs_elem[3]/MB,
                TYPE => $vs_elem[1].'/'.$vs_elem[0],
                VOLUMN => $status,
                SERIAL => $vs_elem[4]
            });
        }
    }

}    
1;
