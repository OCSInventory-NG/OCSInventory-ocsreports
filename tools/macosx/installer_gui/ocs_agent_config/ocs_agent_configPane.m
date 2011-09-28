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
		[filemgr removeFileAtPath:tmpCfgFilePath handler:nil];
		[filemgr removeFileAtPath:tmpModulesFilePath handler:nil];
		[filemgr removeFileAtPath:tmpServerdirFilePath handler:nil];
		[filemgr removeFileAtPath:tmpCacertFilePath handler:nil];
		
	} else {
		[filemgr createDirectoryAtPath:tmpPath attributes:nil];
		
	}	
	
	
	if ([filemgr fileExistsAtPath:@"/etc/ocsinventory-agent/ocsinventory-agent.cfg"]) {
		//We display a warning dialog
		cfgFileExistsWrn = [[NSAlert alloc] init];
		
		[cfgFileExistsWrn setMessageText:@"OCS agent configuration file seems to already exists."
										@"Do you want to launch OCS Inventory NG agent configuration?"];
		
		[cfgFileExistsWrn setInformativeText:@"The previous /etc/ocsinventory-agent/ocsinventory-agent.cfg file will be erased"];
		[cfgFileExistsWrn addButtonWithTitle:@"Yes"];
		[cfgFileExistsWrn addButtonWithTitle:@"No"];
		[cfgFileExistsWrn setAlertStyle:NSInformationalAlertStyle];
		
		
		if ([cfgFileExistsWrn runModal] != NSAlertFirstButtonReturn) {
			// No button was clicked, we don't display config pane
			[self gotoNextPane];
		}
	}
	
	[cfgFileExistsWrn release];
	
	// fill defaults values
	[server setStringValue:@"ocsinventory-ng"];
	[logfile setStringValue:@"/var/log/ocsng.log"];
	[debugmode setState:1];
	[download setState:1];
	
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
	
	//Running brozse panel
	int result = [panel runModalForTypes:fileTypes];

	//Getting cacert file path
	if (result == NSOKButton) {
		[cacertfile setStringValue:[panel filename]];
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
			if ( [debugmode state] == 1) {
			    [ocsAgentCfgContent appendString:@"debug=1\n"];
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
								 
				[filemgr copyPath:[cacertfile objectValue] toPath:tmpCacertFilePath handler:nil];
			}
			
			if ( [download state] == 1 && [[cacertfile stringValue] length] == 0 ) {
				//We display a warning dialog
				caCertWrn = [[NSAlert alloc] init];
				
				[caCertWrn addButtonWithTitle:@"OK"];
				[caCertWrn setMessageText:@"You have enabled Download feature but you don't specify a certificate file"];
				[caCertWrn setInformativeText:@"If you don't specifiy a certificate file, Download feature won't work"];
				[caCertWrn setAlertStyle:NSInformationalAlertStyle]; 
				[caCertWrn runModal];  // display the warning dialog
				[caCertWrn release];	  // dispose the warning dialog	
				
			}
			
	
		} else {
			//We display a warning dialog
			srvConfigWrn = [[NSAlert alloc] init];
			
			[srvConfigWrn addButtonWithTitle:@"OK"];
			[srvConfigWrn setMessageText:@"Invalid OCS server address"];
			[srvConfigWrn setInformativeText:@"Please check and re-enter your OCS server address"];
			[srvConfigWrn setAlertStyle:NSInformationalAlertStyle]; 
			[srvConfigWrn runModal];
			[srvConfigWrn release];		

		}
	}
	
return (YES);
}
	

@end
