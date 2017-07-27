package Ocsinventory::Agent::Backend::Virtualization::SolarisZones;

use strict;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    return unless $common->can_run('zoneadm'); 
    return unless $common->can_run('zonecfg'); 
    return unless check_solaris_valid_release();
}
sub check_solaris_valid_release{
    #check if Solaris 10 release is higher than 08/07
    #no problem if Solaris 11
    my $OSlevel;
    my @rlines;
    my $release_file;
    my $release;
    my $year;
    my $month;
    
    $OSlevel=`uname -r`;

    if ( $OSlevel =~ /5.10/ ) {
        $release_file = "/etc/release";
        if (!open(SOLVERSION, $release_file)) {
            return;
        }
        @rlines = <SOLVERSION>;
        @rlines = grep(/Solaris/,@rlines);
        $release = @rlines[0];
        $release =~ m/(\d)\/(\d+)/;
        $month = $1;
        $year = $2;
        $month =~ s/^0*//g;
        $year =~ s/^0*//g;
        if ($year <= 7 and $month < 8 ){
            return 0;
        }
    }
    1 
}

sub run {
    my @zones;
    my @lines;
    my $zone;
    my $zoneid;
    my $zonename;
    my $zonestatus;
    my $zonefile;
    my $pathroot;
    my $uuid;
    my $zonetype;
    my $memory;
    my $memcap;
    my $vcpu;
    my $params = shift;
    my $common = $params->{common};
    my $logger = $params->{logger};

    @zones = `/usr/sbin/zoneadm list -p`;
    @zones = grep (!/global/,@zones);

    foreach $zone (@zones) {    
        ($zoneid,$zonename,$zonestatus,$pathroot,$uuid,$zonetype)=split(/:/,$zone);

        $memory="";
        foreach (`/usr/sbin/zonecfg -z $zonename info capped-memory`) {
          if (/\s+physical:\s+(\S+)(\S)/) {
            # recalculate to GB
            $memory = $1        if ( $2 eq "G" ) ;
            $memory = $1 / 1024 if ( $2 eq "M" ) ;
          }
        }

        $vcpu="";
        foreach (`/usr/sbin/zonecfg -z $zonename info dedicated-cpu`) {
          if (/\s+ncpus:\s+\S*(\d+)/) {
            $vcpu = $1;
          }
        }

        my $machine = {
            MEMORY => $memory,
            NAME => $zonename,
            UUID => $uuid,
            STATUS => $zonestatus,
            SUBSYSTEM => $zonetype,
            VMTYPE => "Solaris Zones",
            VMID => $zoneid,
            VCPU => $vcpu,
        };
        $common->addVirtualMachine($machine);
    }
}

1;
