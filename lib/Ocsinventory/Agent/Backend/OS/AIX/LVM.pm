package Ocsinventory::Agent::Backend::OS::AIX::LVM;

use strict;
use vars qw($runAfter);
$runAfter = ["Ocsinventory::Agent::Backend::OS::AIX::Drives"];

sub check {
    return unless can_run ("lspv") || can_run('lsvg') || can_run('lslv');
    1
}

my $line;

sub run {
    my $params = shift;
    my $common = $params->{common};

    my @physvol;
    my @volgrp;
    my $format;
    my $total;
    my $total_pps;
    my $free;
    my $free_pps;
    my $lps;
    my $volume_name;
    my $volume_size;
    my $volume_uuid;
    my $status;
    my $vg_name;
    my $type;

    use constant MB => (1024*1024);

    # We retrieve the disk list
    foreach my $line (`lspv`) {
        chomp;
        my $name = split(/\s+/, $line);
        push @physvol, $name;
    }

    foreach my $nom (@physvol) {
        foreach my $line (`lspv $nom`){
            if ($line =~ /PHYSICAL VOLUME:\s+(\S+)/) {
                $format = "AIX PV";
            }
            if ($line =~ /PV STATE:\ss+(\S+)/) {
                $status = $1;
            }
            if ($line =~ /FREE PPs:\s+(\d+)/) {
                $free_pps = $1;
            }
            if ($line =~ /TOTAL PPs:\s+(\d+)/) {
                $total_pps = $1;
            }
            if ($line =~ /VOLUME GROUP:\s+(\S+)/) {
                $vg_name = "VG $1";
            }
            if ($line =~ /PP SIZE:\s+(\d+)/) {
                $volume_size = $1;
            }
            if ($line =~ /PV IDENTIFIER:\s+(\S+)/) {
                $volume_uuid = $1;
            }
            if ($volume_size){
                $total = $total_pps * $volume_size;
                $free = $free_pps * $volume_size;
            }
        
            $common->addDrive({
                FREE => $free,
                FILESYSTEM => $format,
                TOTAL => $total,
                TYPE => "DISK",
                VOLUMN => $status,
                SERIAL => $volume_uuid
            });
        }
    }

    foreach my $nom (@physvol) {
        foreach my $line (`lslv $nom`){
            if ($line =~ /LV IDENTIFIER:\s+(\S+)/) {
                $volume_uuid = $1;
            }
            if ($line =~ /LV STATE:\s+(\S+)/) {
                $status = $1;
            }
            if ($line =~ /PP SIZE:\s+(\d+)/) {
                $volume_size = $1;
            }
            if ($line =~ /LPs:\s+(\d+)/) {
                $lps = $1;
            }
            if ($line =~ /TYPE:\s+(\S+)/) {
                $type = $1;
            }

            $total = $lps * $volume_size;

            $common->addDrive({
                FREE => "",
                FILESYSTEM => "",
                TOTAL => $total,
                TYPE => $type,
                VOLUMN => $status,
                SERIAL => $volume_uuid
            });
        }
    }
     
    # We retrieve the disk list
    foreach my $line (`lsvg`) {
        chomp;
        my $name = split(/\s+/, $line);
        push @volgrp, $name;
    }

    foreach my $nom (@volgrp) {
        foreach my $line (`lsvg $nom`){
            if ($line =~ /VG IDENTIFIER:\s+(\S+)/) {
                $volume_uuid = $1;
            }
            if ($line =~ /VG STATE:\s+(\S+)/) {
                $status = $1;
            }
            if ($line =~ /TOTAL PPs:\s+(\d+)/) {
                $volume_size = $1;
            }
            if ($line =~ /FREE PPs:\s+(\d+)/) {
                $free = $1;
            }
            $common->addDrive({
                FREE => $free,
                FILESYSTEM => "",
                TOTAL => $volume_size,
                TYPE => "AIX VG",
                VOLUMN => $status,
                SERIAL => $volume_uuid
            });
        }
    }
}

1;
