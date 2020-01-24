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

@interface ocs_agent_configPane : InstallerPane {
	IBOutlet NSTextField *server;
	IBOutlet NSTextField *tag;
	IBOutlet NSTextField *logfile;
	IBOutlet NSTextField *cacertfile;
	IBOutlet NSButton *debugmode;
	IBOutlet NSButton *lazymode;
	IBOutlet NSButton *download;
	IBOutlet NSButton *ssl;
	IBOutlet NSPopUpButton *protocolist;

	NSFileManager *filemgr;
	NSString *tmpCfgFilePath;
	NSString *tmpModulesFilePath;
	NSString *tmpServerdirFilePath;
	NSString *tmpCacertFilePath;	
	
	
}

- (IBAction) getConfig:(id)sender ;
- (IBAction) chooseCacertFile:(id)sender;
- (IBAction) chooseProtocol:(id)sender;



@end
