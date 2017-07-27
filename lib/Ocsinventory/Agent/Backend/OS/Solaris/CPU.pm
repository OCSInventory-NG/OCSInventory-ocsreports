package Ocsinventory::Agent::Backend::OS::Solaris::CPU;

use strict;

sub check {
  my $params = shift;
  my $common = $params->{common};
  my $logger = $params->{logger};

  if (!$common->can_run ("memconf")) {
    $logger->debug('memconf not found in $PATH');
    return;
  }

  1;
}

sub run {
  my $params = shift;
  my $common = $params->{common};

  #modif 20100329 
  my @cpu;
  my $current;
  my $cpu_core;
  my $cpu_thread;
  my $cpu_slot;
  my $cpu_speed;
  my $cpu_type;
  my $model;
  my $zone;
  my $sun_class_cpu=0;
  my $cpusocket=1;
  my $cpu_arch;
  my $cpu_manufacturer;
  my $data_width;
  my %coretable = ( 'dual', 2, 'quad', 4, 'six', 6, 'eight', 8, 'ten', 10, 'twelve', 12 );
  my $aarch;

  
  chomp($aarch = `uname -p`);
  chomp($cpu_arch = `uname -m`);
  chomp($data_width = `isainfo -b`);

  if ( !$common->can_run("zonename") || `zonename` =~ /global/ ) {
    # Either pre Sol10 or in Sol10/Sol11 global zone
    $zone = "global";
  } else {
    # Sol10/Sol11 local zone
    $zone = "";
  }
 
  if ($zone) {
    if ( $aarch =~ /sparc/ && $common->can_run("virtinfo") && `virtinfo -t` =~ /.*LDoms guest.*/ ) {
      $model = "Solaris Ldom";
    } else {
      chomp($model = `uname -i`);
    }
  } else {
    $model = "Solaris Containers";
  }
  
  #print "CPU Model: $model\n";
  # we map (hopfully) our server model to a known class
  #
  #    #sun_class_cpu    sample out from memconf
  #     0               (default)        generic detection with prsinfo
  #    1               Sun Microsystems, Inc. Sun Fire 880 (4 X UltraSPARC-III 750MHz)
  #    2               Sun Microsystems, Inc. Sun Fire V490 (2 X dual-thread UltraSPARC-IV 1350MHz)
  #    3               Sun Microsystems, Inc. Sun-Fire-T200 (Sun Fire T2000) (8-core quad-thread UltraSPARC-T1 1000MHz)
  #    4        Sun Microsystems, Inc. SPARC Enterprise T5220 (4-core 8-thread UltraSPARC-T2 1165MHz)
  #
  #if ($model eq "SUNW,Sun-Fire-280R") { $sun_class_cpu = 1; }
  #if ($model eq "SUNW,Sun-Fire-480R") { $sun_class_cpu = 1; }
  #if ($model eq "SUNW,Sun-Fire-V240") { $sun_class_cpu = 1; }
  #if ($model eq "SUNW,Sun-Fire-V245") { $sun_class_cpu = 1; }  
  #if ($model eq "SUNW,Sun-Fire-V250") { $sun_class_cpu = 1; }
  #if ($model eq "SUNW,Sun-Fire-V440") { $sun_class_cpu = 1; }
  #if ($model eq "SUNW,Sun-Fire-V445") { $sun_class_cpu = 1; }
  #if ($model eq "SUNW,Sun-Fire-880") { $sun_class_cpu = 1; }
  #if ($model eq "SUNW,Sun-Fire-V490") { $sun_class_cpu = 2; }
  #if ($model eq "SUNW,Netra-T12") { $sun_class_cpu = 2; }    
  #if ($model eq "SUNW,Sun-Fire-T200") { $sun_class_cpu = 3; } 
  #if ($model eq "SUNW,SPARC-Enterprise-T1000") { $sun_class_cpu = 4; }
  #if ($model eq "SUNW,SPARC-Enterprise-T5220") { $sun_class_cpu = 4; }
  #if ($model eq "SUNW,SPARC-Enterprise-T5240") { $sun_class_cpu = 4; }
  #if ($model eq "SUNW,SPARC-Enterprise-T5120") { $sun_class_cpu = 4; }
  #if ($model eq "SUNW,SPARC-Enterprise") { $sun_class_cpu = 4; } 
  if ($model  =~ /SUNW,SPARC-Enterprise/) { $sun_class_cpu = 5; } # M5000
  if ($model  =~ /SUNW,SPARC-Enterprise-T\d/){ $sun_class_cpu = 4; } #T5220 - T5210  
  if ($model  =~ /SUNW,Netra-T/){ $sun_class_cpu = 2; }
  if ($model  =~ /SUNW,Sun-Fire-\d/){ $sun_class_cpu = 1; }
  if ($model  =~ /SUNW,Sun-Fire-V/){ $sun_class_cpu = 2; }  
  if ($model  =~ /SUNW,Sun-Fire-T\d/) { $sun_class_cpu = 3; }  
  if ($model  =~ /Solaris Containers/){ $sun_class_cpu = 6; } 
  if ($model  =~ /SUNW,SPARCstation/) { $sun_class_cpu = 7; }
  if ($model  =~ /SUNW,Sun-Blade-100/) { $sun_class_cpu = 7; }
  if ($model  =~ /SUNW,Sun-Blade-1500/) { $sun_class_cpu = 7; }
  if ($model  =~ /SUNW,Ultra/) { $sun_class_cpu = 7; }
  if ($model =~ /FJSV,GPUZC-M/) { $sun_class_cpu = 8; }
  # Recent and current hardware
  if ($model =~ /SUNW,T5/) { $sun_class_cpu = 20; }     # T5240, T5440
  if ($model =~ /i86pc/)   { $sun_class_cpu = 21; }     # Solaris Intel
  if ($model =~ /sun4v/)   { $sun_class_cpu = 22; }     # T3-x, T4-x, T5-x
  if ($model =~ /Solaris Ldom/){ $sun_class_cpu = 23; }



  if($sun_class_cpu == 0)
  {
  # if our maschine is not in one of the sun classes from upside, we use psrinfo
    # a generic methode
    foreach (`psrinfo -v`)
    {
      if (/^\s+The\s(\w+)\sprocessor\soperates\sat\s(\d+)\sMHz,/)
      {
        $cpu_type = $1;
        $cpu_speed = $2;
        $cpu_slot++;
      }
    }
  }


  if($sun_class_cpu == 1)
  {

  # Sun Microsystems, Inc. Sun Fire 880 (4 X UltraSPARC-III 750MHz)
    foreach (`memconf 2>&1`)
    {
      if(/^Sun Microsystems, Inc. Sun Fire\s+\S+\s+\((\d+)\s+X\s+(\S+)\s+(\d+)/)
      {
        $cpu_manufacturer = "Sun Microsystems, Inc.";
        $cpu_slot = $1;
        $cpu_type = $2;
        $cpu_speed = $3;
    $cpu_core=$1;
    $cpu_thread="0";
      }
        
      elsif (/^Sun Microsystems, Inc. Sun Fire\s+\S+\s+\((\S+)\s+(\d+)/)
      {
        $cpu_manufacturer = "Sun Microsystems, Inc.";
          $cpu_slot="1";
          $cpu_type=$1;
          $cpu_speed=$2;
      $cpu_core="1";
      $cpu_thread="0";
      }
      
    }
  }

  if($sun_class_cpu == 2)
  { 
  
  #Sun Microsystems, Inc. Sun Fire V490 (2 X dual-thread UltraSPARC-IV 1350MHz)
    foreach (`memconf 2>&1`)
    {
      if(/^Sun Microsystems, Inc. Sun Fire\s+\S+\s+\((\d+)\s+X\s+(\S+)\s+(\S+)\s+(\d+)/)     
      {
        $cpu_manufacturer = "Sun Microsystems, Inc.";
        $cpu_slot = $1;
        $cpu_type = $3 . " (" . $2 . ")";
        $cpu_speed = $4;
    $cpu_core=$1;
    $cpu_thread=$2;
      }
      elsif (/^Sun Microsystems, Inc. Sun Fire\s+V\S+\s+\((\d+)\s+X\s+(\S+)\s+(\d+)(\S+)/)
    {
        $cpu_manufacturer = "Sun Microsystems, Inc.";
        $cpu_slot = $1;
        $cpu_type = $2 . " (" . $1 . ")";
        $cpu_speed = $3;
        $cpu_core=$1;
        $cpu_thread=$2;
      }
      #    Sun Microsystems, Inc. Sun Fire V240 (UltraSPARC-IIIi 1002MHz)
      elsif (/^Sun Microsystems, Inc. Sun Fire\s+\S+\s+\((\S+)\s+(\d+)/)
      {
        $cpu_manufacturer = "Sun Microsystems, Inc.";
          $cpu_slot="1";
          $cpu_type=$1;
          $cpu_speed=$2;
          $cpu_core="1";
        $cpu_thread="0";
      }
      
    }
  }
  
  if($sun_class_cpu == 3)
  {
    foreach (`memconf 2>&1`)
    {
    #Sun Microsystems, Inc. Sun-Fire-T200 (Sun Fire T2000) (8-core quad-thread UltraSPARC-T1 1000MHz)
    #Sun Microsystems, Inc. Sun-Fire-T200 (Sun Fire T2000) (4-core quad-thread UltraSPARC-T1 1000MHz)
      if(/^Sun Microsystems, Inc.\s+\S+\s+\(\S+\s+\S+\s+\S+\)\s+\((\d+).*\s+(\S+)-\S+\s+(\S+)\s+(\d+)/)
      {
        # T2000 has only one CPU
        $cpu_manufacturer = "Sun Microsystems, Inc.";
        $cpu_slot = 1;
        $cpu_type = "$3 ($1-Core $2-Thread)";
        $cpu_speed = $4;
        $cpu_core=$1;
	$cpu_thread = $coretable{lc($2)};
      }
    }
  }
  
  if($sun_class_cpu == 4)
  {
    
    foreach (`memconf 2>&1`)
    {
    
      #Sun Microsystems, Inc. SPARC Enterprise T5120 (8-core 8-thread UltraSPARC-T2 1165MHz)
      #Sun Microsystems, Inc. SPARC Enterprise T5120 (4-core 8-thread UltraSPARC-T2 1165MHz)
      #Oracle Corporation SPARC Enterprise T5220 (8-Core 8-Thread UltraSPARC-T2 1415MHz)
      if(/^(.*)\s+SPARC.+\((\d+)*(\S+)\s+(\d+)*(\S+)\s+(\S+)\s+(\d+)MHz\)/)
      {
        $cpu_manufacturer = $1;
        $cpu_slot = 1;
        $cpu_type = "$6 ($2-Core $4-Thread)";
        $cpu_speed = $7;
    $cpu_core=$2;
    $cpu_thread=$4;
        
      }
    }
  }
  
  if($sun_class_cpu == 5)
  {
    foreach (`memconf 2>&1`)
    {
      #Sun Microsystems, Inc. Sun SPARC Enterprise M5000 Server (6 X dual-core dual-thread SPARC64-VI 2150MHz)
      #Sun Microsystems, Inc. SPARC Enterprise M8000 Server (2 X Quad-Core Dual-Thread SPARC64-VII 2520MHz)

      #Fujitsu SPARC Enterprise M4000 Server (4 X dual-core dual-thread SPARC64-VI 2150MHz)
      if(/^Sun Microsystems, Inc\..+\((\d+)\s+X\s+(\S+)-\S+\s+(\S+)-\S+\s+(\S+)\s+(\d+)/)
      {
        $cpu_manufacturer = "Sun Microsystems, Inc.";
        $cpu_slot = $1;
        $cpu_type = "$4 ($2-Core $3-Thread)";
        $cpu_speed = $5;
        $cpu_core = $coretable{lc($2)};
	$cpu_thread = $coretable{lc($3)};
      }
      #Fujitsu SPARC Enterprise M4000 Server (4 X dual-core dual-thread SPARC64-VI 2150MHz)
      if(/^Fujitsu SPARC Enterprise.*\((\d+)\s+X\s+(\S+)-\S+\s+(\S+)-\S+\s+(\S+)\s+(\d+)/) 
      {
        $cpu_manufacturer = "Fujitsu";
        $cpu_slot = $1;
        $cpu_type = "$4 ($2-Core $3-Thread)";
        $cpu_speed = $5;
        $cpu_core = $coretable{lc($2)};
	$cpu_thread = $coretable{lc($3)};
      }
      
    }
  }
  
  
  if($sun_class_cpu == 6)
  {
    $cpu_manufacturer = "Solaris Container";
    foreach (`prctl -n zone.cpu-shares $$`)
    {
        $cpu_type = $1 if /^zone.(\S+)$/;        
        $cpu_type = $cpu_type." ".$1 if /^\s*privileged+\s*(\d+).*$/;
        $cpu_slot = 1 if /^\s*privileged+\s*(\d+).*$/;
    }    
  }

  if($sun_class_cpu == 7) {
    foreach(`memconf 2>&1`) {
      #Sun Microsystems, Inc. Sun Blade 1500 (UltraSPARC-IIIi 1062MHz)
      #Sun Microsystems, Inc. Sun Blade 100 (UltraSPARC-IIe 502MHz)
      #Sun Microsystems, Inc. Sun Ultra 5/10 UPA/PCI (UltraSPARC-IIi 333MHz)
      #Sun Microsystems, Inc. Sun Ultra 1 SBus (UltraSPARC 143MHz)
      #Sun Microsystems, Inc. SPARCstation 20 (1 X 390Z50) (SuperSPARC 50MHz)
      #Sun Microsystems, Inc. SPARCstation 5 (TurboSPARC-II 170MHz)
      if (/^Sun Microsystems, Inc\..+\((\S+)\s+(\d+)MHz\)/) {
        $cpu_manufacturer = "Sun Microsystems, Inc.";
        $cpu_slot = 1;
        $cpu_type = $1;
        $cpu_speed = $2;
        $cpu_core = 1;
        $cpu_thread = 0;
      }
    }
  }

  if ($sun_class_cpu == 8) {
    foreach (`memconf 2>&1`){
    #Fujitsu PRIMEPOWER450 4x SPARC64 V clone (4 X SPARC64-V 1978MHz)
    #Fujitsu PRIMEPOWER250 2x SPARC64 V clone (2 X SPARC64-V 1649MHz)
    if (/^FJSV,GPUZC-M.+\((\d+)\s+X\s+(\S+)\s+(\d+)MHz\)/) {
           $cpu_manufacturer = "Fujitsu";
           $cpu_slot = $1;
           $cpu_type = $2;
           $cpu_speed = $3;
           $cpu_core = $1;
           $cpu_thread = "0";
      }
    }
  }
   
  if ($sun_class_cpu == 20) {
    foreach (`memconf 2>&1`) {
      #Oracle Corporation T5240 (2 X 8-Core 8-Thread UltraSPARC-T2+ 1582MHz)
      #Oracle Corporation T5440 (4 X 8-Core 8-Thread UltraSPARC-T2+ 1596MHz)
      #Sun Microsystems, Inc. T5240 (2 X 8-Core 8-Thread UltraSPARC-T2+ 1582MHz)
      if(/^(.*)\s+T\d+\s+\((\d+)\s+X\s+(\d+)-\S+\s+(\d)+-\S+\s+(\S+)\s+(\d+)MHz\)/) {
        $cpu_manufacturer = $1;
        $cpu_slot = $2;
        $cpu_type = "$5 ($3-Core $4-Thread)";
        $cpu_speed = $6;
        $cpu_core = $3;
        $cpu_thread = $4;
      }
    }
  }

  if ($sun_class_cpu == 21) {
    foreach (`memconf 2>&1`) {
      # Solaris on x86
      #Oracle Corporation Sun Fire X4270 Server (2 X Quad-Core Hyper-Threaded Intel(R) Xeon(R) X5570 @ 2.93GHz)
      #HP ProLiant DL380 G5 (2 X Quad-Core Intel(R) Xeon(R) E5430 @ 2.66GHz)
      #HP ProLiant DL380 G7 (2 X Six-Core Hyper-Threaded Intel(R) Xeon(R) X5670 @ 2.93GHz)
      #HP ProLiant DL580 G7 (4 X Ten-Core Hyper-Threaded Intel(R) Xeon(R) E7- 4860 @ 2.27GHz)
      #HP ProLiant DL380p Gen8 (2 X Eight-Core Hyper-Threaded Intel(R) Xeon(R) E5-2660 0 @ 2.20GHz Proc 2)
      #HP ProLiant DL380 Gen9 (2 X Twelve-Core Hyper-Threaded Intel(R) Xeon(R) E5-2680 v3 @ 2.50GHz Proc 2 2497MHz)
      if(/^.*\((\d+) X (\S+)-Core Hyper-Threaded (\S+)(.*) @ (\S+)GHz.*\)/) {
        # Hyper-Threaded CPU
        $cpu_manufacturer = $3;
        $cpu_slot = $1;
        $cpu_type = $3 . $4;
        $cpu_speed = $5 * 1000;
        $cpu_core = $coretable{lc($2)};
        $cpu_thread = "on";
      }
      elsif(/^.*\((\d+) X (\S+)-Core (\S+)(.*) @ (\S+)GHz.*\)/) {
        # Non Hyper-Threaded CPU
        $cpu_manufacturer = $3;
        $cpu_slot = $1;
        $cpu_type = $3 . $4;
        $cpu_speed = $5 * 1000;
        $cpu_core = $coretable{lc($2)};
        $cpu_thread = "off";
      }
      elsif(/^.*\((\S+)-Core (\S+)(.*) @ (\S+)GHz.*\)/) {
       # single core CPU
        $cpu_manufacturer = $2;
        $cpu_slot = 1;
        $cpu_type = $2 . $3;
        $cpu_speed = $4 * 1000;
        $cpu_core = $coretable{lc($1)};
        $cpu_thread = "off";
      }
    }
  }

  if ($sun_class_cpu == 22) {
    foreach (`memconf 2>&1`) {
      #Oracle Corporation SPARC T3-1 (16-Core 8-Thread SPARC-T3 1649MHz)
      #Oracle Corporation SPARC T3-2 (2 X 16-Core 8-Thread SPARC-T3 1649MHz)
      #Oracle Corporation SPARC T4-1 (8-Core 8-Thread SPARC-T4 2848MHz)
      #Oracle Corporation SPARC T4-2 (2 X 8-Core 8-Thread SPARC-T4 2848MHz)
      #Oracle Corporation SPARC T4-4 (4 X 8-Core 8-Thread SPARC-T4 2998MHz)
      #Oracle Corporation SPARC T5-2 (16-Core 8-Thread SPARC-T5 3600MHz)
      #Oracle Corporation SPARC T5-4 (4 X 16-Core 8-Thread SPARC-T5 3600MHz)
      if(/^Oracle Corporation.+\((\d+)-Core (\d+)-Thread (\S+) (\d+)MHz\)/) {
        # Single CPU string
        $cpu_manufacturer = "Oracle Corporation";
        $cpu_slot = 1;
        $cpu_type = "$3 ($1-Core $2-Thread)";
        $cpu_speed = $4;
        $cpu_core = $1;
        $cpu_thread = $2;
      }
      elsif(/^Oracle Corporation.+\((\d+) X (\d+)-Core (\d+)-Thread (\S+) (\d+)MHz\)/) {
        # Multiple CPU string
        $cpu_manufacturer = "Oracle Corporation";
        $cpu_slot = $1;
        $cpu_type = "$4 ($2-Core $3-Thread)";
        $cpu_speed = $5;
        $cpu_core = $2;
        $cpu_thread = $3;
      }
    }
  }

  if($sun_class_cpu == 23)
  {
  # Solaris LDom support
    $cpu_slot=1;
    $cpu_core = 0;
    $cpu_thread = 0;
    $cpu_manufacturer = "Solaris LDom";
    foreach (`psrinfo -pv`) {
      if (/^The physical processor has.* (\d+) virtual processors.*/) {
        $cpu_thread = $cpu_thread + $1;
      }
      elsif (/^\s+(\S+)\s+\(.*clock\s+(\d+)\sMHz\)/) {
        $cpu_type = $1;
        $cpu_speed = $2;
      }
    }
  }
 
  # for debug only
  #print "cpu_slot: " . $cpu_slot . "\n";
  #print "cpu_type: " . $cpu_type . "\n";
  #print "cpu_speed: " . $cpu_speed . "\n";
  #print "cpu_core: " . $cpu_core . "\n";
  #print "cpu_thread: " . $cpu_thread . "\n";
 
  # Finally, add the found CPUs
  for $cpusocket ( 1 .. $cpu_slot ) {
    $current->{MANUFACTURER} = $cpu_manufacturer if $cpu_manufacturer;
    $current->{SPEED} = $cpu_speed if $cpu_speed;
    $current->{CURRENT_SPEED} = $cpu_speed if $cpu_speed;
    $current->{TYPE} = $cpu_type if $cpu_type;
    $current->{CORES} = $cpu_core if $cpu_core;
    $current->{CPUARCH} = $cpu_arch if $cpu_arch;
    if ( $cpu_core == 0 ) {
      $current->{LOGICAL_CPUS} = $cpu_thread if ( $cpu_thread );
    } else {
      $current->{LOGICAL_CPUS} = $cpu_core * $cpu_thread if ( $cpu_core && $cpu_thread );
    }
    $current->{DATA_WIDTH} = $data_width if $data_width;
    $common->addCPU($current);
  }
 
  # insert to values we have found
  # $common->setHardware({
  #   PROCESSORT => $cpu_type,
  #   PROCESSORN => $cpu_slot,
  #   PROCESSORS => $cpu_speed
  # });

}
#run();
1;
