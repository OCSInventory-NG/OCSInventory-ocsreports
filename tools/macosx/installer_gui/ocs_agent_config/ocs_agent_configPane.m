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

#import "ocs_agent_configPane.h"


@implementation ocs_agent_configPane
  

- (NSString *)title
{
	return [[NSBundle bundleForClass:[self class]] localizedStringForKey:@"PaneTitle" value:nil table:nil];
}


- (NSArray *) protocols {
	return [NSArray arrayWithObjects:@"http", @"https", nil];
}


- (void)didEnterPane:(InstallerSectionDirection)dir {
	NSAlert *cfgFileExistsWrn;
	NSString *tmpPath = @"/tmp/ocs_installer";
	
	filemgr = [ NSFileManager defaultManager];
	tmpCfgFilePath = @"/tmp/ocs_installer/ocsinventory-agent.cfg";
	tmpModulesFilePath = @"/tmp/ocs_installer/modules.conf";
	tmpServerdirFilePath = @"/tmp/ocs_installer/serverdir";
	tmpCacertFilePath = @"/tmp/ocs_installer/cacert.pem";
	
	//Checking if temp directory exists
	if ([filemgr fileExistsAtPath:tmpPath]) {
        [filemgr removeItemAtPath:tmpCfgFilePath error:nil];
        [filemgr removeItemAtPath:tmpModulesFilePath error:nil];
        [filemgr removeItemAtPath:tmpServerdirFilePath error:nil];
        [filemgr removeItemAtPath:tmpCacertFilePath error:nil];
	} else {
        [filemgr createDirectoryAtPath:tmpPath withIntermediateDirectories:true attributes:nil error:nil];
	}	
	
	
	if ([filemgr fileExistsAtPath:@"/etc/ocsinventory-agent/ocsinventory-agent.cfg"]) {
		//We display a warning dialog
		cfgFileExistsWrn = [[NSAlert alloc] init];
		
		[cfgFileExistsWrn setMessageText:NSLocalizedStringFromTableInBundle(@"Already_conf_warn",nil,[NSBundle bundleForClass:[self class]], @"Warning about already existing cofiguration file")];
		[cfgFileExistsWrn setInformativeText:NSLocalizedStringFromTableInBundle(@"Already_conf_warn_comment",nil,[NSBundle bundleForClass:[self class]], @"Warning about already existing cofiguration file comment")];
		[cfgFileExistsWrn addButtonWithTitle:NSLocalizedStringFromTableInBundle(@"Yes",nil,[NSBundle bundleForClass:[self class]], @"Yes button")];
		[cfgFileExistsWrn addButtonWithTitle:NSLocalizedStringFromTableInBundle(@"No",nil,[NSBundle bundleForClass:[self class]], @"No button")];
		[cfgFileExistsWrn setAlertStyle:NSInformationalAlertStyle];
		
		
		if ([cfgFileExistsWrn runModal] != NSAlertFirstButtonReturn) {
			// No button was clicked, we don't display config pane
			[self gotoNextPane];
		}

		[cfgFileExistsWrn release];
	}
	
	
	// fill defaults values
	[server setStringValue:@"ocsinventory-ng"];
	[logfile setStringValue:@"/var/log/ocsng.log"];
	[debugmode setState:1];
	[lazymode setState:0];
	[download setState:1];
	[ssl setState:1];
	
	//Defaults for protocol droping list
	[protocolist removeAllItems];
	[protocolist addItemWithTitle: @"http://"];
	[protocolist addItemWithTitle: @"https://"];
	[protocolist selectItemWithTitle: @"http://"];

}

- (IBAction) chooseCacertFile:(id)sender {
	NSOpenPanel *panel = [NSOpenPanel openPanel];
	NSArray* fileTypes = [[NSArray alloc] initWithObjects:@"pem",@"PEM",@"crt",@"CRT",nil];
	
	//Configuration for the browse panel
	[panel setCanChooseDirectories:NO];
	[panel setCanChooseFiles:YES];
	[panel setAllowsMultipleSelection:NO];
    [panel setAllowedFileTypes:fileTypes];
	
    // Get panel return value
    NSInteger clicked = [panel runModal];
    
    // If OK clicked only
    if (clicked == NSFileHandlingPanelOKButton) {
        for (NSURL *url in [panel URLs]) {
            // do something with the url here.
            NSString *path = url.path;
            [cacertfile setStringValue:path];
        }
    }
}

- (IBAction) chooseProtocol:(id)sender {
	NSString *protocol = [protocolist titleOfSelectedItem];
	
	//We show the selected protocol
	[protocolist setTitle:protocol];
}

- (IBAction) getConfig:(id)sender  {
	BOOL srvAddrChk;
	
	// enable the Continue button only if server address filled
	srvAddrChk = ([[server stringValue] length] > 0);
    [self setNextEnabled:srvAddrChk];
}

- (BOOL)shouldExitPane:(InstallerSectionDirection)Direction {
    NSMutableString *ocsAgentCfgContent;
	NSMutableString *modulesCfgContent;
	NSString *serverDir;
	NSMutableString *protocolName;
	NSAlert *srvConfigWrn;
	NSAlert *caCertWrn;
	NSString *protocol = [protocolist titleOfSelectedItem];

    // check the direction of movement
    if (Direction == InstallerDirectionForward) {
									
		if ( [[server stringValue] length] > 0) {
		
			ocsAgentCfgContent = [@"server=" mutableCopy];
	
			//Adding server value to the mutable string
			[ocsAgentCfgContent appendString:protocol];
			[ocsAgentCfgContent appendString:[server objectValue]];
			[ocsAgentCfgContent appendString:@"/ocsinventory"];
			[ocsAgentCfgContent appendString:@"\n"];
			
			//if tag filled
			if ( [[tag stringValue] length] > 0) {
			    [ocsAgentCfgContent appendString:@"tag="];
				[ocsAgentCfgContent appendString:[tag objectValue]];
				[ocsAgentCfgContent appendString:@"\n"];	
			}
			
			//if logfile filled
			if ( [[logfile stringValue] length] > 0) {
			    [ocsAgentCfgContent appendString:@"logfile="];
				[ocsAgentCfgContent appendString:[logfile objectValue]];
				[ocsAgentCfgContent appendString:@"\n"];	
			}

			//if debugmode checked
			if ([debugmode state] == 1) {
				[ocsAgentCfgContent appendString:@"debug=1\n"];
			} else {
				[ocsAgentCfgContent appendString:@"debug=0\n"];
			}
			
			//if lazymode checked
			if ([lazymode state] == 1) {
				[ocsAgentCfgContent appendString:@"lazy=1\n"];
			} else {
				[ocsAgentCfgContent appendString:@"lazy=0\n"];
			}
			
			//if ssl checked
			if ([ssl state] == 1) {
				[ocsAgentCfgContent appendString:@"ssl=1\n"];
			} else {
				[ocsAgentCfgContent appendString:@"ssl=0\n"];
			}
			
			//Writing to ocsinventory agent configuration file
			[ocsAgentCfgContent writeToFile:tmpCfgFilePath atomically: YES encoding:NSUTF8StringEncoding error:NULL];
	
			
			
			modulesCfgContent = [@"# this list of module will be load by the at run time\n"
								@"# to check its syntax do:\n"
								@"# #perl modules.conf\n"
								@"# You must have NO error. Else the content will be ignored\n"
								@"# This mechanism goal it to keep compatibility with 'plugin'\n"
								@"# created for the previous linux_agent.\n"
								@"# The new unified_agent have its own extension system that allow\n"
								@"# user to add new information easily.\n"
								@"\n" 
								@"#use Ocsinventory::Agent::Modules::Example;\n"
								mutableCopy];
			
			//if download checked
			if ( [download state] == 1) {
				[modulesCfgContent appendString:@"use Ocsinventory::Agent::Modules::Download;\n"
												@"\n"
												@"# DO NOT REMOVE THE 1;\n"
												@"1;"
												];
			} else {
				[modulesCfgContent appendString:@"#use Ocsinventory::Agent::Modules::Download;\n"
												@"\n"
												@"# DO NOT REMOVE THE 1;\n"
												@"1;"
												];
			}
				
				
			//Writing to modules configuration file
			[modulesCfgContent writeToFile:tmpModulesFilePath atomically: YES encoding:NSUTF8StringEncoding error:NULL];

			
			//We have to copy cacert.pem if is asked by user
			if ( [[cacertfile stringValue] length] > 0) {
				
				protocolName = [protocol mutableCopy];
				[protocolName replaceOccurrencesOfString:@"/" withString:@""
							  options:NSCaseInsensitiveSearch 
							  range:NSMakeRange(0, [protocolName length])];

				
				serverDir = [NSString stringWithFormat:@"/var/lib/ocsinventory-agent/%@__%@_ocsinventory", protocolName, [server objectValue]];
				[serverDir writeToFile:tmpServerdirFilePath atomically: YES encoding:NSUTF8StringEncoding error:NULL];
								 
                [filemgr copyItemAtPath:[cacertfile objectValue] toPath:tmpCacertFilePath error:nil];
			}
			
			if ( [download state] == 1 && [ssl state] == 1 && [[cacertfile stringValue] length] == 0 ) {
				//We display a warning dialog
				caCertWrn = [[NSAlert alloc] init];
				
				[caCertWrn addButtonWithTitle:@"OK"];
				[caCertWrn setMessageText:NSLocalizedStringFromTableInBundle(@"Missing_cert_warn",nil,[NSBundle bundleForClass:[self class]], @"Warning about missing certificate file")];
				[caCertWrn setInformativeText:NSLocalizedStringFromTableInBundle(@"Missing_cert_warn_comment",nil,[NSBundle bundleForClass:[self class]], @"Warning about missing certificate file comment")];
				[caCertWrn setAlertStyle:NSInformationalAlertStyle]; 
				[caCertWrn runModal];  // display the warning dialog
				[caCertWrn release];	  // dispose the warning dialog	
				
			}
			
	
		} else {
			//We display a warning dialog
			srvConfigWrn = [[NSAlert alloc] init];
			
			[srvConfigWrn addButtonWithTitle:@"OK"];
			[srvConfigWrn setMessageText:NSLocalizedStringFromTableInBundle(@"Invalid_srv_addr",nil,[NSBundle bundleForClass:[self class]], @"Warning about invalid server address")];
			[srvConfigWrn setInformativeText:NSLocalizedStringFromTableInBundle(@"Invalid_srv_addr_comment",nil,[NSBundle bundleForClass:[self class]], @"Warning about invalid server address comment")];
			[srvConfigWrn setAlertStyle:NSInformationalAlertStyle]; 
			[srvConfigWrn runModal];
			[srvConfigWrn release];
			
			[self gotoPreviousPane];

		}
	}
	
return (YES);
}
	

@end
