###
# SNMP: OID: 43 SYSTEM: Printer_Mib 
###
package Ocsinventory::Agent::Modules::Snmp::Printer_Mib;

use strict;

sub snmp_info {
   #TODO: find a common OID from Printer_Mib (compatible with most printers) instead of using hrPrinterStatus from Host_Ressources_Mib
   return ( { oid_value => "1.3.6.1.2.1.25.3.5.1.1.1", oid_name => "Printer_Mib" } );
}

sub snmp_run {
    my ($session , $snmp )= @_;
    my $logger=$snmp->{logger};
    my $common=$snmp->{common};
  
    my ($result,$name,$serialnumber,$lifecount,$countunit,$printerstatus,$errorstate);
  
    $logger->debug("Running Printer MIB module");
  
    $common->setSnmpCommons( {TYPE => "Printer"} );
  
    # prtGeneralPrinterName
    my $snmp_name="1.3.6.1.2.1.43.5.1.1.16.1";
    # prtInputSerialNumber
    my $snmp_serialnumber="1.3.6.1.2.1.43.5.1.1.17.1";
    # prtMarkerLifeCount
    my $snmp_lifecount="1.3.6.1.2.1.43.10.2.1.4.1.1";
    # prtMarkerCounterUnit
    my $snmp_countunit="1.3.6.1.2.1.43.10.2.1.3.1.1";
    # prtInputTable      
    my $snmp_inputable="1.3.6.1.2.1.43.8.2";
    # prtMarkerSuppliesTable
    my $snmp_markersuppliestable="1.3.6.1.2.1.43.11.1";
    # prtMarkerColorantValue
    my $snmp_colorantvalue="1.3.6.1.2.1.43.12.1.1.4";
    # hrPrinterStatus
    my $snmp_printerstatus="1.3.6.1.2.1.25.3.5.1.1.1";
    # PrinterDetectedErrorState
    my $snmp_errorstate="1.3.6.1.2.1.25.3.5.1.2.1";
  
    #Trays informations we want to get 
    my $trayinfos = {
        name => "1.3.6.1.2.1.43.8.2.1.13",    #prtInputName
        description => "1.3.6.1.2.1.43.8.2.1.18",   #prtInputDescription
        level => "1.3.6.1.2.1.43.8.2.1.10",    #prtInputLevel
        maxcapacity => "1.3.6.1.2.1.43.8.2.1.9",   #prtInputMaxCapacity
    };
  
    # Cartridges informations we want to get 
    my $cartridgeinfos = {
        description => "1.3.6.1.2.1.43.11.1.1.6",   #prtMarkerSuppliesDescription
        type => "1.3.6.1.2.1.43.11.1.1.5",     #prtMarkerSuppliesType
        level => "1.3.6.1.2.1.43.11.1.1.9",    #prtMarkerSuppliesSupplyLevel
        maxcapacity => "1.3.6.1.2.1.43.11.1.1.8",   #prtMarkerSuppliesMaxCapacity
    };
  
    # Translation for the prtMarkerCounterUnit integer
    my $countunit_translation = { 
        3 => 'tenThousandthsOfInches',
        4 => 'micrometers',
        5 => 'characters',
        6 => 'lines',
        7 => 'impressions',
        8 => 'sheets',
        9 => 'dotRow',
        11 => 'hours',
        16 => 'feet',
        17 => 'meters',
    };
  
    # Translation for the prtMarkerSuppliesType integer
    my $suppliestype_translation = {
        1 => 'other',
        2 => 'unknown',
        3 => 'toner',
        4 => 'wasteToner',
        5 => 'ink',
        6 => 'inkCartridge',
        7 => 'inkRibbon',
        8 => 'wasteInk',
        9 => 'opc',
        10 => 'developer',
        11 => 'fuserOil',
        12 => 'solidWax',
        13 => 'ribbonWax',
        14 => 'wasteWax',
    };
  
    my $printerstatus_translation = {
        1 => 'other',
        2 => 'unknown',
        3 => 'idle',
        4 => 'printing',
        5 => 'warmup',
    };
  
    my $errorstate_translation = {
        '0x00' => 'lowPaper',
        '0x01' => 'noPaper',
        '0x02' => 'lowToner',
        '0x03' => 'noToner',
        '0x04' => 'doorOpen',
        '0x05' => 'jammed',
        '0x06' => 'offline',
        '0x07' => 'serviceRequested',
        '0x08' => 'inputTrayMissing',
        '0x09' => 'outputTrayMissing',
        '0x10' => 'markerSupplyMissing',
        '0x11' => 'outputNearFull',
        '0x12' => 'outputFull',
        '0x13' => 'inputTrayEmpty',
        '0x14' => 'overduePreventMaint',
    };
  
    #####
  
    #Getting printer informations
    $result=$session->get_request( -varbindlist => [$snmp_name]);
    $name=$result->{$snmp_name};
  
    $result=$session->get_request(-varbindlist => [$snmp_serialnumber]);
    $serialnumber=$result->{$snmp_serialnumber};
  
    $result=$session->get_request(-varbindlist => [$snmp_lifecount]);
    $lifecount=$result->{$snmp_lifecount};
  
    $result=$session->get_request(-varbindlist => [$snmp_countunit]);
    $countunit=$countunit_translation->{ $result->{$snmp_countunit} };
  
    $result=$session->get_request(-varbindlist => [$snmp_printerstatus]);
    $printerstatus=$result->{$snmp_printerstatus};
  
    $result=$session->get_request(-varbindlist => [$snmp_errorstate]);
    $errorstate=$result->{$snmp_errorstate};
  
    # Adding informations to XML
    $common->setSnmpPrinter({
        NAME => $name,
        SERIALNUMBER => $serialnumber,
        COUNTER => "$lifecount $countunit",
        STATUS => $printerstatus_translation->{$printerstatus},
        ERRORSTATE => $errorstate_translation->{$errorstate},
    }); 
  
    # Getting trays informations using the table
    my $inputable=$session->get_table(-baseoid => $snmp_inputable) ;
    my $trays = $common->getSnmpTable($inputable,$snmp_inputable,$trayinfos);
  
    for my $tray ( keys %$trays ) {
        #Adding informations about trays in XML
        $common->addSnmpPrinterTray({
            NAME => $trays->{$tray}->{name},
            DESCRIPTION => $trays->{$tray}->{description},
            LEVEL => $trays->{$tray}->{level},
            MAXCAPACITY => $trays->{$tray}->{maxcapacity},
        }); 
    }
  
    # Getting cartridges informations using the table
    my $markersuppliestable=$session->get_table(-baseoid => $snmp_markersuppliestable) ; 
    my $cartridges = $common->getSnmpTable($markersuppliestable,$snmp_markersuppliestable,$cartridgeinfos);
  
    for my $cartridge ( keys %$cartridges ) {
        # Getting colorant value 
        $result = $session->get_request(-varbindlist => [$snmp_colorantvalue.$cartridge]);
        my $colorantvalue = $result->{$snmp_colorantvalue.$cartridge};
  
        # Adding informations about cartridges in XML
        $common->addSnmpPrinterCartridge({
            DESCRIPTION => $cartridges->{$cartridge}->{description},
            TYPE => $suppliestype_translation->{ $cartridges->{$cartridge}->{type} },
            LEVEL => $cartridges->{$cartridge}->{level},
            MAXCAPACITY => $cartridges->{$cartridge}->{maxcapacity},
            COLOR => $colorantvalue,
        });
    }

}
1;
