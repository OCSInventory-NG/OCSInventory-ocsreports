package Ocsinventory::Agent::Backend::OS::Generic::Screen;
use strict;
use utf8;

use Parse::EDID;

sub haveExternalUtils {
    my $common = shift;

    return $common->can_run("monitor-get-edid-using-vbe") || $common->can_run("monitor-get-edid") || $common->can_run("get-edid");
}

sub check {
    my $params = shift;
    my $common = $params->{common};

    return unless -d "/sys/devices" || haveExternalUtils($common);
    1;
}

sub _getManufacturerFromCode {
    my $code = shift;
    my $h = {
    "ACR" => "Acer America Corp.",
    "ACT" => "Targa",
    "ADI" => "ADI Corporation http://www.adi.com.tw",
    "AOC" => "AOC International (USA) Ltd.",
    "API" => "Acer America Corp.",
    "APP" => "Apple Computer, Inc.",
    "ART" => "ArtMedia",
    "AST" => "AST Research",
    "AMW" => "AMW",
    "AUO" => "AU Optronics Corporation",
    "BMM" => "BMM",
    "BNQ" => "BenQ Corporation",
    "BOE" => "BOE Display Technology",
    "CPL" => "Compal Electronics, Inc. / ALFA",
    "CPQ" => "COMPAQ Computer Corp.",
    "CPT" => "Chunghwa Picture Tubes, Ltd.",
    "CTX" => "CTX - Chuntex Electronic Co.",
    "DEC" => "Digital Equipment Corporation",
    "DEL" => "Dell Computer Corp.",
    "DPC" => "Delta Electronics, Inc.",
    "DWE" => "Daewoo Telecom Ltd",
    "ECS" => "ELITEGROUP Computer Systems",
    "ENC" => "EIZO",
    "EIZ" => "EIZO",
    "EPI" => "Envision Peripherals, Inc.",
    "FCM" => "Funai Electric Company of Taiwan",
    "FUJ" => "Fujitsu",
    "FUS" => "Fujitsu Siemens",
    "GSM" => "LG Electronics Inc. (GoldStar Technology, Inc.)",
    "GWY" => "Gateway 2000",
    "HEI" => "Hyundai Electronics Industries Co., Ltd.",
    "HIQ" => "Hyundai ImageQuest",
    "HIT" => "Hitachi",
    "HSD" => "Hannspree Inc",
    "HSL" => "Hansol Electronics",
    "HTC" => "Hitachi Ltd. / Nissei Sangyo America Ltd.",
    "HWP" => "Hewlett Packard",
    "IBM" => "IBM PC Company",
    "ICL" => "Fujitsu ICL",
    "IFS" => "InFocus",
    "IQT" => "Hyundai",
    "IVM" => "Idek Iiyama North America, Inc.",
    "KFC" => "KFC Computek",
    "LEN" => "Lenovo",
    "LGD" => "LG Display",
    "LKM" => "ADLAS / AZALEA",
    "LNK" => "LINK Technologies, Inc.",
    "LPL" => "LG Philips",
    "LTN" => "Lite-On",
    "MAG" => "MAG InnoVision",
    "MAX" => "Maxdata Computer GmbH",
    "MEI" => "Panasonic Comm. & Systems Co.",
    "MEL" => "Mitsubishi Electronics",
    "MIR" => "Miro Computer Products AG",
    "MTC" => "MITAC",
    "MS_" => "Panasonic",
    "NAN" => "NANAO",
    "NEC" => "NEC Technologies, Inc.",
    "NVD" => "Fujitsu",
    "NOK" => "Nokia",
    "OQI" => "OPTIQUEST",
    "PBN" => "Packard Bell",
    "PCK" => "Daewoo",
    "PDC" => "Polaroid",
    "PGS" => "Princeton Graphic Systems",
    "PHL" => "Philips Consumer Electronics Co.",
    "PRT" => "Princeton",
    "PTS" => "ProView/EMC/PTS YakumoTFT17SL",
    "REL" => "Relisys",
    "SAM" => "Samsung",
    "SMI" => "Smile",
    "SMC" => "Samtron",
    "SNI" => "Siemens Nixdorf",
    "SNY" => "Sony Corporation",
    "SPT" => "Sceptre",
    "SRC" => "Shamrock Technology",
    "STN" => "Samtron",
    "STP" => "Sceptre",
    "TAT" => "Tatung Co. of America, Inc.",
    "TOS" => "Toshiba",
    "TRL" => "Royal Information Company",
    "TSB" => "Toshiba, Inc.",
    "UNM" => "Unisys Corporation",
    "VSC" => "ViewSonic Corporation",
    "WTC" => "Wen Technology",
    "ZCM" => "Zenith Data Systems",
    "___" => "Targa" };
  
    return $h->{$code} if (exists ($h->{$code}) && $h->{$code});
    return "Unknown manufacturer code ".$code;
}

sub getEdid {
    my $raw_edid;
    my $port = $_[0];
  
  # Mandriva
    $raw_edid = `monitor-get-edid-using-vbe --port $port 2>/dev/null`;
  
    # Since monitor-edid 1.15, it's possible to retrieve EDID information
    # through DVI link but we need to use monitor-get-edid
    if (!$raw_edid) {
        $raw_edid = `monitor-get-edid --vbe-port $port 2>/dev/null`;
    }   
  
    if (!$raw_edid) {
        foreach (1..5) { # Sometime get-edid return an empty string...
            $raw_edid = `get-edid 2>/dev/null`;
            last if (length($raw_edid) == 128 || length($raw_edid) == 256);
        }
    }
    return unless (length($raw_edid) == 128 || length($raw_edid) == 256);
  
    return $raw_edid;
}

sub run {
    my $params = shift;
    my $common = $params->{common};
    my $logger = $params->{logger};

    my $raw_perl = 1;
    my $verbose;
    my $MonitorsDB;
    my $base64;
    my $uuencode;
  
    my %found;

    my @edid_list;
    # first check sysfs if there are edid entries
    for my $file(split(/\0/,`find /sys/devices -wholename '*/card*/edid' -print0`)) {
        open(my $sys_edid_fd,'<',$file);
        my $raw_edid = do { local $/; <$sys_edid_fd> };
        if (length($raw_edid) == 128 || length($raw_edid) == 256 ) {
            push @edid_list, $raw_edid;
        }
    }

    # if not fall back to the old method
    if (!@edid_list && haveExternalUtils($common)) {
        for my $port(0..20){
            my $raw_edid = getEdid($port);
            if ($raw_edid){
                if (length($raw_edid) == 128 || length($raw_edid) == 256) {
                    push @edid_list, $raw_edid;
                }
            }
        }
    }

    for my $raw_edid(@edid_list) {
        my $edid = parse_edid($raw_edid);
        if (my $err = check_parsed_edid($edid)) {
            $logger->debug("check failed: bad edid: $err");
        }
        my $caption = $edid->{monitor_name};
        my $description = $edid->{week}."/".$edid->{year};
        my $manufacturer = _getManufacturerFromCode($edid->{manufacturer_name});
        my $serial = $edid->{serial_number};
        if (!exists $found{$serial}) {
            $found{$serial} = 1;
 
            eval "use MIME::Base64;";
            $base64 = encode_base64($raw_edid) if !$@;
            if ($common->can_run("uuencode")) {
                chomp($uuencode = `echo $raw_edid|uuencode -`);
                if (!$base64) {
                    chomp($base64 = `echo $raw_edid|uuencode -m -`);
                }
            }
            $common->addMonitor ({
                BASE64 => $base64,
                CAPTION => $caption,
                DESCRIPTION => $description,
                MANUFACTURER => $manufacturer,
                SERIAL => $serial,
                UUENCODE => $uuencode,
            });
        }
    }
}
1;

