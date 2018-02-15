package Ocsinventory::Agent::Backend::Virtualization::Qemu;
# With Qemu 0.10.X, some option will be added to get more and easly information (UUID, memory, ...)

use strict;

sub check { 
    my $params = shift;
    my $common = $params->{common};
    return ($common->can_run('qemu') || $common->can_run('kvm') || $common->can_run('qemu-kvm'))
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    foreach ( `ps -ef` ) {
        if (m/^\S+\s+(\d+).*((qemu|kvm|(qemu-kvm)).*\-([fh]d[a-d]|drive|cdrom).*)$/) {
            # match only if an qemu instance
            
            my $name = "N/A";
            my $mem = 0;
            my $uuid;
            my $vmtype = $3;
            my $vcpu = 1;
            my $pid = $1;
                        
            if  (open F, "/proc/$pid/cmdline") {
                my @a=split "\000-", <F>;
                close F;
                foreach my $option ( @a ) {
                    if ($option =~ m/^name\000(\S+)/) {
                        $name = $1;
                    } elsif ($option =~ m/^m\000(\S+)/) {
                        $mem = $1;
                    } elsif ($option =~ m/^uuid\000(\S+)/) {
                        $uuid = $1;
                    } elsif ($option =~ m/.*uuid=(\S+)/) {
                        $uuid = $1;
                    } elsif ($option =~ m/^smp\000(\d+)/) {
                        $vcpu = $1;
                    }
                }
            }

            
            if ($mem == 0 ) {
                # Default value
                $mem = 128;
            }
            
            $common->addVirtualMachine ({
                NAME      => $name,
                UUID      => $uuid,
                VCPU      => $vcpu,
                MEMORY    => $mem,
                STATUS    => "running",
                SUBSYSTEM => $vmtype,
                VMTYPE    => $vmtype,
            });
        }
    }
}

1;
