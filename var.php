<?php
@session_start();
define("MAX_CACHED_SOFTS", 200 );		// Max number of softs that may be returned by optimizations queries
define("MAX_CACHED_REGISTRY", 200 );	// Max number of registry that may be returned by optimizations queries
define("USE_CACHE", 1 );				//Do we use cache tables ?
define("UPDATE_CHECKSUM", 1 );			// do we need to update software checksum when using dictionnary ?
define("UTF8_DEGREE", 1 );				// 0 For non utf8 database, 1 for utf8
define("GUI_VER", "5010");				// Version of the GUI
define("MAC_FILE", "files/oui.txt");	// File containing MAC database
define("SADMIN", 1);					// do NOT change
define("LADMIN", 2);   					// do NOT change
define("ADMIN", 3);						// do NOT change
define("TAG_NAME", "TAG"); 				// do NOT change
define("DEFAULT_LANGUAGE","french");
define("TAG_LBL", "Tag");				// Name of the tag information
define("PAG_INDEX","biere");            // define name in url (like multi=32)
//Creating array for page references (this is not really a function, juste for code reading)
$pages_refs['ms_all_computers']='hoegaarden';
$pages_refs['ms_config']='1664';
$pages_refs['ms_repart_tag']='chti';
$pages_refs['ms_groups']='gueuze';
$pages_refs['ms_all_soft']='delirium';
$pages_refs['ms_multi_search']='gauloise';
$pages_refs['ms_dict']='livinus';
$pages_refs['ms_upload_file']='cuvee_troll';
$pages_refs['ms_regconfig']='kwak';
$pages_refs['ms_logs']='duchesse_ane';
$pages_refs['ms_admininfo']='calsberg';
$pages_refs['ms_ipdiscover']='kro';
$pages_refs['ms_doubles']='tripel';
$pages_refs['ms_label']='guinness';
$pages_refs['ms_users']='corsendonk';
$pages_refs['ms_local']='gouden';
$pages_refs['ms_help']='duvel';
$pages_refs['ms_stats']='julius';
$pages_refs['ms_codes']='ciney';
$pages_refs['ms_blacklist']='westmalle';
$pages_refs['ms_console']='malheur';
$pages_refs['ms_components']='stella';
$pages_refs['ms_tele_package']='mere_noel';
$pages_refs['ms_tele_activate']='grimbergen';
$pages_refs['ms_tele_stats']='foster';
$pages_refs['ms_tele_actives']='petasse';
$pages_refs['ms_tele_massaffect']='bourgogne_des_flandres';
$pages_refs['ms_rules_redistrib']='leffe';
$pages_refs['ms_opt_param']='brigand';
$pages_refs['ms_opt_ipdiscover']='becasse';
$pages_refs['ms_admin_attrib']='moinette';
$pages_refs['ms_group_show']='chapeau_faro';
$pages_refs['ms_show_detail']='vondel';
?>
