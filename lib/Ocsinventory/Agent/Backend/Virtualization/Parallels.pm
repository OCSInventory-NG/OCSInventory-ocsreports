package Ocsinventory::Agent::Backend::Virtualization::Parallels;

use strict;
use warnings;
use XML::Simple;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    return $common->can_run('prlctl') 
}

sub run {
    my $params = shift;
    my $common = $params->{common};
    my $config = $params->{config};

    my %status_list = (
        'running' => 'running',
        'blocked' => 'blocked',
        'paused' => 'paused',
        'suspended' => 'suspended',
        'crashed' => 'crashed',
        'dying' => 'dying',
    );

    my $xmlfile = undef;

    if (defined($config->{server}) && $config->{server}) {
        my $dir = $config->{server};
        $dir =~ s/\//_/g;
        $config->{vardir} = $config->{basevardir}."/".$dir;
    } elsif (defined($config->{local}) && $config->{local}) {
        $config->{vardir} = $config->{basevardir}."/__LOCAL__";
    }
    if (-d $config->{vardir}) {
        $xmlfile = $config->{vardir}."/uuid.xml";    
    }
    
    my ($uuid,$mem,$status,$name,$subsys)=undef;
    my $cpus = 1;
    my @users = ();

    # We don't want to scan user directories unless --scan-homedirs is used
    return unless $config->{scanhomedirs};

    foreach my $lsuser ( glob("/Users/*") ) {
        $lsuser =~ s/.*\///; # Just keep the login
        next if $lsuser =~ /Shared/i;
        next if $lsuser =~ /^\./i; # Ignore hidden directory
        next if $lsuser =~ /\ /; # Ignore directory with space in the name
        next if $lsuser =~ /'/; # Ignore directory with space in the name
        push(@users,$lsuser);
    }

    foreach my $user (@users) {
        my @command = `su $user -c "prlctl list -a"`;
        shift (@command);

        foreach my $line ( @command ) {
            chomp $line;
            my @params = split(/  /, $line);
            $uuid = $params[0];
            $status = $params[1];

            # Avoid security risk. Should never appends
            next if $uuid =~ /(;\||&)/;

            foreach my $infos ( `sudo -u $user prlctl list -i $uuid`) {
                if ($infos =~ m/^\s\smemory\s(.*)Mb/) {
                    $mem = $1;
                }
                elsif ($infos =~ m/^\s\scpu\s([0-9]{1,2})/) {
                    $cpus= $1;
                }
                elsif ($infos =~ m/^Name:\s(.*)/) {
                    $name = $1;
                }
                elsif ($infos =~ m/^OS:\s(.*)/) {
                    $subsys = $1;
                }
            }

            $uuid =~  s/{// if ($uuid =~ m/{/);
            $uuid =~  s/}// if ($uuid =~ m/}/);
            
            my ($hwUUID,$currentUUID)=undef;

            # if vm is running, fetch the real uuid
            if ($status eq 'running') {
                my $command = "sudo -u $user prlctl exec $uuid /usr/sbin/ioreg -rd1 -c IOPlatformExpertDevice";

                open(IOREG, "$command |") or die "Failed to run '$command': $!\n";
                while(<IOREG>) {
                    if (/^[\s|]*"(\w+)"\s*=\s*(.*)$/) {
                        next unless $1 eq "IOPlatformUUID";
                        $currentUUID = $2;
                        $currentUUID =~ s/\"//g;
                    } 
                }
            }
 
            if (! -e $xmlfile) {
                my $uuid_xml = {
                    'PARALLELS' => {
                        'VIRTUALMACHINES' => {
                            'NAME'          => [$name],
                            'PARALLELSUUID' => [$uuid], # Damned random uuid value
                            'GUESTUUID'     => [$currentUUID], # The true and great uuid value
                        },
                    },
                };

                my $xs = XML::Simple->new(
                    ForceArray => 1,
                    KeepRoot => 1,
                    XMLDecl  => '<?xml version="1.0" encoding="UTF-8"?>');
               
                my $xml = $xs->XMLout($uuid_xml);

                open (MYFILE, "> $xmlfile");
                print MYFILE "$xml"; # write xml output to the newly created file
                close (MYFILE);

                $hwUUID = $currentUUID; # fetch guest uuid value, even if it's empty (vm not currently running)

            } else {
               my $parallels = XML::Simple->new(
                   ForceArray => 1,
                   KeepRoot   => 1,
                   XMLDecl    => '<?xml version="1.0" encoding="UTF-8"?>',
               );

                my $vm = $parallels->XMLin($xmlfile);

                my $numberOfVMs = 0;
                my $boolean = 0;

                while (defined $vm->{PARALLELS}[0]{VIRTUALMACHINES}[$numberOfVMs]) {
                    $numberOfVMs+=1;
                }
                
                for (my $counter=0; $counter<$numberOfVMs; $counter++) {
                    if ( $vm->{PARALLELS}[0]{VIRTUALMACHINES}[$counter]{NAME}[0] eq $name ) {
                        # if the vm with this name exists in the file, set its GUESTUUID value, if the currentUUID isn't empty
                        if ( defined ($currentUUID)) {
                            $vm->{PARALLELS}[0]{VIRTUALMACHINES}[$counter]{GUESTUUID}[0] = $currentUUID;
                        }
                        $vm->{PARALLELS}[0]{VIRTUALMACHINES}[$counter]{PARALLELSUUID}[0] = $uuid; # if vm re-registered, parallels uuid changes
                        $boolean = 1;
                    
                        if ( defined ($vm->{PARALLELS}[0]{VIRTUALMACHINES}[$counter]{GUESTUUID}[0])) {    
                            $hwUUID = $vm->{PARALLELS}[0]{VIRTUALMACHINES}[$counter]{GUESTUUID}[0];
                        }
                    }
                }

                unless ($boolean) {
                    # if vm doesn't exist, push it to the file
                    push @{$vm->{PARALLELS}[0]{VIRTUALMACHINES}},
                    {
                        NAME          => [$name],
                        PARALLELSUUID => [$uuid],
                        GUESTUUID     => [$currentUUID],
                    };
                }

                open (MYFILE, "> $xmlfile");
                print MYFILE $parallels->XMLout($vm);
                close (MYFILE);
            }

            $common->addVirtualMachine ({
                NAME      => $name,
                VCPU      => $cpus,
                MEMORY    => $mem,
                STATUS    => $status,
                SUBSYSTEM => $subsys,
                VMTYPE    => "Parallels",
                UUID      => $hwUUID,
            });
        }
    }
}

1;

