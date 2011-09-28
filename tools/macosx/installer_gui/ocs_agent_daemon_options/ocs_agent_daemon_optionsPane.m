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

#import "ocs_agent_daemon_optionsPane.h"


@implementation ocs_agent_daemon_optionsPane

- (NSString *)title
{
	return [[NSBundle bundleForClass:[self class]] localizedStringForKey:@"PaneTitle" value:nil table:nil];
}


- (void)didEnterPane:(InstallerSectionDirection)dir {
	NSString *tmpPath = @"/tmp/ocs_installer";
	
	filemgr = [ NSFileManager defaultManager];
	tmpLaunchdFilePath =@"/tmp/ocs_installer/org.ocsng.agent.plist";
	tmpNowFilePath = @"/tmp/ocs_installer/now";
	
	//Checking if temp directory exists
	if ([filemgr fileExistsAtPath:tmpPath]) {
		[filemgr removeFileAtPath:tmpLaunchdFilePath handler:nil];
		[filemgr removeFileAtPath:tmpNowFilePath handler:nil];
		
	} else {
		[filemgr createDirectoryAtPath:tmpPath attributes:nil];

	}
	
	
	// fill defaults values
	[periodicity setStringValue:@"5"];
	[startup setState:1];
	[now setState:0];
	
	
}


- (BOOL)shouldExitPane:(InstallerSectionDirection)Direction {
	
	
	NSMutableString *launchdCfgFile;
	NSAlert *periodicityValueWrn;
	
	//Creating org.ocsng.agent.plist file for launchd
	//TODO: use XML parser instead of writing the XML as a simple text file ?
	launchdCfgFile = [@"<?xml version='1.0' encoding='UTF-8'?>\n"
					  @"<!DOCTYPE plist PUBLIC '-//Apple//DTD PLIST 1.0//EN' 'http://www.apple.com/DTDs/PropertyList-1.0.dtd'>\n"
					  @"<plist version='1.0'>\n"
					  @"<dict>\n"
					  @"\t<key>Label</key>\n"
					  @"\t<string>org.ocsng.agent</string>\n"
					  @"\t<key>ProgramArguments</key>\n"
					  @"\t\t<array>\n"
					  @"\t\t\t<string>/Applications/OCSNG.app/Contents/MacOS/OCSNG</string>\n"
					  @"\t\t</array>\n"
					  mutableCopy];	
	
	
	if ([startup state] == 1) {
		[launchdCfgFile  appendString:@"\t<key>RunAtLoad</key>\n"
									  @"\t<true/>\n"
									  ];
	}
	
	
	if ( [[periodicity stringValue] length] > 0) {
	
		//We convert string to numeric value and check if it is integer
		NSNumberFormatter *formatter = [[NSNumberFormatter alloc] init];
		NSNumber *convert = [formatter numberFromString:[periodicity stringValue]];
		[formatter release];
		
		if (convert) {
		
			int hours = [convert intValue];
			int seconds =  hours * 3600;
			NSLog(@"Valeur de periocity:%i",seconds);
		
			[launchdCfgFile  appendString:@"\t<key>StartInterval</key>\n"
										  @"\t<integer>"
										  ];
		 
			[launchdCfgFile  appendString:[NSString stringWithFormat:@"%d", seconds]];
			[launchdCfgFile  appendString:@"</integer>\n"];

		} else {
			//We display a warn message and we go back to pane
			periodicityValueWrn = [[NSAlert alloc] init];
		
			[periodicityValueWrn addButtonWithTitle:@"OK"];
			[periodicityValueWrn setMessageText:@"Invalid periodicity value"];
			[periodicityValueWrn setInformativeText:@"Please enter a valid number value"];
			[periodicityValueWrn setAlertStyle:NSInformationalAlertStyle]; 
			[periodicityValueWrn runModal];
			[periodicityValueWrn release];
			
			[self gotoPreviousPane];
		}
		
	}
	
	[launchdCfgFile  appendString:@"</dict>\n"
								  @"</plist>"
								  ];
	
	//Writing org.ocsng.agent.plist file
	[launchdCfgFile writeToFile:tmpLaunchdFilePath atomically: YES encoding:NSUTF8StringEncoding error:NULL];

	//Check if we launch agent after install
	if ([now state] == 1) {
		[filemgr createFileAtPath:tmpNowFilePath contents:nil attributes:nil];
	}
	
	
	return (YES);	
}


@end
