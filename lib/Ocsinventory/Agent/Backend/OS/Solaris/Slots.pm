package Ocsinventory::Agent::Backend::OS::Solaris::Slots;

use strict;
sub check {
    my $params = shift;
    my $common = $params->{common};
    $common->can_run ("prtdiag") 
}

sub run {
    my $params = shift;
    my $common = $params->{common};

    my $description;
    my $designation;
    my $name;
    my $status;  
    my @pci;
    my $flag;
    my $flag_pci;
    my $model;
    my $sun_class;

    chomp($model = `uname -i`);
    # debug print model
    #print "Model: '$model'";
    $sun_class = 0;
    # we map (hopfully) our server model to a known class
    if ($model =~ /SUNW,SPARC-Enterprise/) { $sun_class = 1; }	# M9000
    if ($model =~ /SUNW,SPARC-Enterprise-T\d/) { $sun_class = 22; }	# T5220
    if ($model =~ /SUNW,Sun-Fire-\d/) { $sun_class = 3; }		# 280R, 480R
    if ($model =~ /SUNW,Sun-Fire-V\d/) { $sun_class = 3; }	# V490
    if ($model =~ /SUNW,Sun-Fire-T\d/) { $sun_class = 4; }	# T2000
    if ($model =~ /SUNW,Sun-Blade-1500/) { $sun_class = 8; }	# Blade 1500 workstation
    if ($model =~ /i86pc/)	{ $sun_class = 21; }		# x86 hardware
    if ($model =~ /SUNW,T5/)	{ $sun_class = 22; }		# T5240, T5440
    if ($model =~ /sun4v/)	{ $sun_class = 22; }		# T3-x, T4-x, T5-x
    #Debug
    #print "sun_class : $sun_class\n";

    if ( $sun_class == 0 ) {
       # Default class, probably doesn't work

      foreach (`prtdiag `) {
        last if(/^\=+/ && $flag_pci);
        next if(/^\s+/ && $flag_pci);

        if($flag && $flag_pci && /^(\S+)\s+/) {
          $name = $1;
        }
        if($flag && $flag_pci && /(\S+)\s*$/) {
	  $designation = $1;
	}
	if($flag && $flag_pci && /^\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+(\S+)/) {
	  $description = $1;
	}
	if($flag && $flag_pci && /^\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+(\S+)/) {
          $status = $1;
	}
	if($flag && $flag_pci) {
          $common->addSlot({
            DESCRIPTION =>  $description,
	    DESIGNATION =>  $designation,
	    NAME        =>  $name,
	    STATUS      =>  $status,
          });
	}
	if(/^=+\s+IO Cards/){$flag_pci = 1;}
	if($flag_pci && /^-+/){$flag = 1;}
      }
    }

   
    if ( $sun_class == 1 ) {
      # M9000
      #========================= IO Devices =========================
      #
      #    IO                                                Lane/Frq
      #LSB Type  LPID   RvID,DvID,VnID       BDF       State Act,  Max   Name                           Model
      #--- ----- ----   ------------------   --------- ----- ----------- ------------------------------ --------------------
      #    Logical Path
      #    ------------
      #00  PCIx  0       8,  125, 1033       2,  0,  0  okay   133,  133  pci-pciexclass,060400          N/A
      #    /pci@0,600000/pci@0
      #

      foreach (`prtdiag -v`) {
	last if(/^\=+/ && $flag_pci && $flag);

	if ($flag && $flag_pci && /^(\d+)\s+(\S+)\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+(\S+)\s+\S+\s+\S+\s+((\S+)-\S+)\s+(\S+).*/ ) {
	  $designation = "LSB " . $1;
	  $status = $3;
	  $name = $4;
	  $description = "[$2] $5";
	  $description .= " ($6)" unless ($6=~/N\/A/);

          $common->addSlot({
            DESCRIPTION =>  $description,
            DESIGNATION =>  $designation,
            NAME            =>  $name,
            STATUS          =>  $status,
            });
        }
        if(/^=+\S+\s+IO Devices/){$flag_pci = 1;  }
        if($flag_pci && /^-+/){$flag = 1;}
      }
    }


    if ( $sun_class == 2 ) {
      # to be checked
      foreach (`prtdiag `) {
        if (/pci/) {
	  @pci = split(/ +/);
	  $name=$pci[4]." ".$pci[5];
	  $description=$pci[0]." (".$pci[1].")";
	  $designation=$pci[3];
	  $status="";
	  $common->addSlot({
            DESCRIPTION =>  $description,
	    DESIGNATION =>  $designation,
	    NAME        =>  $name,
	    STATUS      =>  $status,
	   });
	}
      }	
    }


    if ( $sun_class == 3 ) {
      # SUNW,Sun-Fire-480R
      # ========================= IO Cards =========================
      # 
      #                     Bus  Max
      #  IO  Port Bus       Freq Bus  Dev,
      # Type  ID  Side Slot MHz  Freq Func State Name                              Model
      # ---- ---- ---- ---- ---- ---- ---- ----- --------------------------------  ----------------------
      # PCI   8    B    3    33   33  3,0  ok    SUNW,qlc-pci1077,2312.1077.101.2+                       
      # PCI   8    B    3    33   33  3,1  ok    SUNW,qlc-pci1077,2312.1077.101.2+                       
      # PCI   8    B    5    33   33  5,0  ok    pci-pci1011,25.4/pci108e,1000     PCI-BRIDGE            
      # PCI   8    B    5    33   33  0,0  ok    pci108e,1000-pci108e,1000.1       device on pci-bridge  
      # PCI   8    B    5    33   33  0,1  ok    SUNW,qfe-pci108e,1001             SUNW,pci-qfe/pci-bridg+

      foreach (`prtdiag `) {
	last if(/^\=+/ && $flag_pci && $flag);          # End of IO Devices, next section starts here

        if($flag && $flag_pci && /PCI/) {
	  @pci = split(/ +/);
	  $name=join(" ",@pci[9..$#pci]);
	  $description="[".$pci[0]."] ".$pci[8];
	  $designation=$pci[0]."/".$pci[1]."/".$pci[2]."/".$pci[3];
	  $status=$pci[7];

	  $common->addSlot({
            DESCRIPTION =>  $description,
            DESIGNATION =>  $designation,
            NAME        =>  $name,
            STATUS      =>  $status,
            });
        }

        if(/^=+\S+\s+IO Cards/){
	  $flag_pci = 1;                  # Start of IO Devices section, still header to skip
        }

        if($flag_pci && /^-+/){
	  $flag = 1;                      # End of IO Devices header, real info starts here
        }
      }
    }


    if ( $sun_class == 21 ) {
      # x86 hardware

      $flag = 0;
      foreach (`/usr/sbin/smbios -t SMB_TYPE_SLOT`) {
        if ($flag && /^ID.*/) {
          # write current slot
          $common->addSlot({
	    DESCRIPTION =>  "$description ($status)",
	    DESIGNATION =>  $designation,
	    NAME        =>  $name,
	    STATUS      =>  $status,
            });
	  $flag = 0;
	}
	elsif(/\s+Location Tag:\s+(.*)$/) {
	  $description = $1;
	}
	elsif(/\s+Slot ID:\s+(.*)$/) {
	  $designation= $1;
	}
	elsif(/\s+Type:\s+\S+\s+\((.*)\)$/) {
	  $name= $1;
	}
	elsif(/\s+Usage:\s+\S+\s+\((.*)\).*$/) {
	  $status = $1;
	}
	$flag = 1;
      }

      # Finally add the last card
      $common->addSlot({
        DESCRIPTION =>  "$description ($status)",
	DESIGNATION =>  $designation,
	NAME        =>  $name,
	STATUS      =>  $status,
        });
    }


    if ( $sun_class == 4 ) {
      # SUNW,Sun-Fire-T200
      #========================= IO Configuration =========================
      #
      #            IO
      #Location    Type  Slot Path                                          Name                      Model
      #----------- ----- ---- --------------------------------------------- ------------------------- ---------
      #IOBD/PCIE0   PCIE    0                /pci@780/pci@0/pci@8/network@0    network-pciex8086,105e SUNW,pcie+

      foreach(`prtdiag `) {
	last if(/^\=+/ && $flag_pci && $flag);          # End of IO Devices, next section starts here

	if($flag && $flag_pci && /PCI/) {
          @pci = split(/ +/);
	  $name=$pci[5];
	  $description="[".$pci[1]."] ".$pci[4];
	  $designation=$pci[0];
	  $status=" ";

	  if($flag && $flag_pci) {
	    $common->addSlot({
              DESCRIPTION =>  $description,
              DESIGNATION =>  $designation,
              NAME        =>  $name,
              STATUS      =>  $status,
              });
	  }
	}

	if(/^=+\S+\s+IO Configuration/){
	  $flag_pci = 1;                  # Start of IO Devices section, still header to skip
	}

	if($flag_pci && /^-+/){
	  $flag = 1;                      # End of IO Devices header, real info starts here
	}
      }
    }


    if ( $sun_class == 8 ) {
      # SUNW,Sun-Blade-1500
      #================================= IO Devices =================================
      #Bus     Freq  Slot +      Name +
      #Type    MHz   Status      Path                          Model
      #------  ----  ----------  ----------------------------  --------------------
      #pci     33    MB          isa/su (serial)                                  
      #              okay        /pci@1e,600000/isa@7/serial@0,3f8

      foreach (`prtdiag `) {
	last if(/^\=+/ && $flag_pci && $flag);		# End of IO Devices, next section starts here

	#                         pci     33      MB      isa/su(serial)   SUNW,xxx
	if($flag && $flag_pci && /(\S+)\s+(\S+)\s+(\S+)\s+(\S+\s+\(\S+\))\s*(.*)/) {
	  $name = $5;
	  $designation = $3;
	  $description = "[$1] $4";
	  $status = " ";
       
	  $common->addSlot({
            DESCRIPTION =>  $description,
            DESIGNATION =>  $designation,
            NAME            =>  $name,
            STATUS          =>  $status,
            });
	}

	if(/^=+\S+\s+IO Devices/){
	  $flag_pci = 1;			# Start of IO Devices section, still header to skip
	}

	if($flag_pci && /^-+/){
	  $flag = 1;			# End of IO Devices header, real info starts here
	}
      }
    }


    if ( $sun_class == 22 ) {
      # SUNW,T5440
      #======================================== IO Devices =======================================
      #Slot +            Bus   Name +                            Model      Max Speed  Cur Speed 
      #Status            Type  Path                                         /Width     /Width    
      #-------------------------------------------------------------------------------------------
      #MB/HBA            PCIE  scsi-pciex1000,58                 LSI,1068E  --         --	
      #                        /pci@400/pci@0/pci@1/scsi@0                 

      # SPARC T3-1
      #================================= IO Devices =================================
      #Slot +            Bus   Name +                            Model        Speed 
      #Status            Type  Path                                                 
      #------------------------------------------------------------------------------
      #/SYS/MB/SASHBA0   PCIE  scsi-pciex1000,72                 LSI,2008     --
      #                        /pci@400/pci@1/pci@0/pci@4/scsi@0           
      #/SYS/MB/RISER2/PCIE2PCIE  network-pciex108e,abcd            SUNW,pcie-qgc  --
      #                        /pci@400/pci@1/pci@0/pci@6/network@0        

      # SPARC T4-4
      #======================================== IO Devices =======================================
      #Slot +            Bus   Name +                            Model      Max Speed  Cur Speed 
      #Status            Type  Path                                         /Width     /Width    
      #-------------------------------------------------------------------------------------------
      #/SYS/MB/REM0/SASHBA0 PCIE  LSI,sas-pciex1000,72              LSI,2008   --         --
      #                        /pci@400/pci@1/pci@0/pci@0/LSI,sas@0        
      #/SYS/RIO/NET0     PCIE  network-pciex8086,10c9                       --         --
      #                        /pci@400/pci@1/pci@0/pci@2/network@0        
      #/SYS/RIO/NET1     PCIE  network-pciex8086,10c9                       --         --
      #                        /pci@400/pci@1/pci@0/pci@2/network@0,1      
      #/SYS/PCI-EM2      PCIE  SUNW,qlc-pciex1077,2532           QEM3572    --         --
      #                        /pci@400/pci@1/pci@0/pci@4/pci@0/pci@2/SUNW,qlc@0

      # SPARC T5-4
      #======================================== IO Devices =======================================
      #Slot +            Bus   Name +                            Model      Max Speed  Cur Speed 
      #Status            Type  Path                                         /Width     /Width    
      #-------------------------------------------------------------------------------------------
      #/SYS/MB/USB_CTLR  PCIE  usb-pciexclass,0c0330                        --         --
      #                        /pci@300/pci@1/pci@0/pci@4/pci@0/pci@6/usb@0
      #/SYS/RIO/XGBE0    PCIE  network-pciex8086,1528                       --         --
      #                        /pci@300/pci@1/pci@0/pci@4/pci@0/pci@8/network@0
      #/SYS/MB/SASHBA0   PCIE  scsi-pciex1000,87                 LSI,2308_2 --         --
      #                        /pci@300/pci@1/pci@0/pci@4/pci@0/pci@c/scsi@0

      foreach (`prtdiag `) {
	last if(/^\=+/ && $flag_pci && $flag);		# End of IO Devices, next section starts here

	# Lazy match for $name due to differences in prtdiag output:
	#    * the "Model" column does not always have a value	
	#    * 5-column output on Sol10, 6-column output on Sol11
	if($flag && $flag_pci && /(\S+)\s+(\S+)\s+((\S+)-\S+)\s+(\S+)\s+.*/) {
	  $designation = $1;
	  $status = " ";
	  $name = $3;
	  $description = "[$2] $4";
	  $description .= " ($5)" unless ($5=~/.*GT\/?x\d+|--/);

	  $common->addSlot({
            DESCRIPTION =>  $description,
            DESIGNATION =>  $designation,
            NAME            =>  $name,
            STATUS          =>  $status,
            });
	}

	if(/^=+\S+\s+IO Devices/){
	  $flag_pci = 1;			# Start of IO Devices section, still header to skip
	}

	if($flag_pci && /^-+/){
	  $flag = 1;			# End of IO Devices header, real info starts here
	}
      }
    }
 
}
1;
