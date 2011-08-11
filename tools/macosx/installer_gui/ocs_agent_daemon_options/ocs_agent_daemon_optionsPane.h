//
// OCSINVENTORY-NG
//
// Copyleft Guillaume PROTET 2011
// 
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/
//
//

#import <Cocoa/Cocoa.h>
#import <InstallerPlugins/InstallerPlugins.h>

@interface ocs_agent_daemon_optionsPane : InstallerPane {
	
	IBOutlet NSTextField *periodicity;
	IBOutlet NSButton *now;
	IBOutlet NSButton *startup;
	
	NSFileManager *filemgr;
	NSString *tmpLaunchdFilePath;
	NSString *tmpNowFilePath;

}

@end
