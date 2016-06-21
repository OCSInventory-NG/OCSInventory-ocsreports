###############################################################################
## OCSINVENTORY-NG
## Copyleft Guillaume PROTET 2010
## Web : http://www.ocsinventory-ng.org
##
## This code is open source and may be copied and modified as long as the source
## code is always made freely available.
## Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
################################################################################

package Ocsinventory::Agent::Modules::Processes;

sub new {

    my $name="processes"; # Name of the module

    my (undef,$context) = @_;
    my $self = {};

    #Create a special logger for the module
    $self->{logger} = new Ocsinventory::Logger ({
        config => $context->{config}
    });
    $self->{logger}->{header}="[$name]";
    $self->{context}=$context;
    $self->{structure}= {
        name => $name,
        start_handler => undef,    #or undef if don't use this hook
        prolog_writer => undef,    #or undef if don't use this hook
        prolog_reader => undef,    #or undef if don't use this hook
        inventory_handler => $name."_inventory_handler",    #or undef if don't use this hook
        end_handler => undef    #or undef if don't use this hook
    };
    bless $self;
}

######### Hook methods ############

sub processes_inventory_handler {


     my $self = shift;
     my $logger = $self->{logger};
     my $common = $self->{context}->{common};

     $logger->debug("Yeah you are in Processes_inventory_handler:)");

     # test if ps command is available :)
     sub check {can_run("ps")}

     my $line;
     my $begin;
     my %month = (
         'Jan' => '01',
         'Feb' => '02',
         'Mar' => '03',
         'Apr' => '04',
         'May' => '05',
         'Jun' => '06',
         'Jul' => '07',
         'Aug' => '08',
         'Sep' => '09',
         'Oct' => '10',
         'Nov' => '11',
         'Dec' => '12',
     );
     my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
     my $the_year=$year+1900;

     my $os;
     chomp($os=`uname -s`);

     if ($os eq "SunOS") {
         open(PS, "ps -A -o user,pid,pcpu,pmem,vsz,rss,tty,s,stime,time,comm|");
     } else {
         open(PS, "ps aux|");
     }

     while ($line = <PS>) {
         next if ($. ==1);
         if ($line =~
             /^(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.*?)\s*$/){
             my $user = $1;
             my $pid= $2;
             my $cpu= $3;
             my $mem= $4;
             my $vsz= $5;
             my $tty= $7;
             my $started= $9;
             my $time= $10;
             my $cmd= $11;

             if ($started =~ /^(\w{3})/)  {
                 my $d=substr($started, 3);
                 my $m=substr($started, 0,3);
                 $begin=$the_year."-".$month{$m}."-".$d." ".$time;
             } else {
                 $begin=$the_year."-".$mon."-".$mday." ".$started;
             }

             push @{$common->{xmltags}->{PROCESSES}},
             {
                 USER => $user,
                 PID => $pid,
                 CPUUSAGE => $cpu,
                 MEM => $mem,
                 VIRTUALMEMORY => $vsz,
                 TTY => $tty,
                 STARTED => $begin,
                 CMD => $cmd
             };
         }
     }
     close(PS);
}

1;
