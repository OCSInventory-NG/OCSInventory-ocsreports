
//
// OCSINVENTORY-NG 
// Copyleft Guillaume PROTET 2010
// Web : http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//
//


//ocscontact is the helper tool to run OCS agent using Authorization Services.
//ocscontact executable MUST be owned by root anbd have 700 permissions

#import "ocscontact.h"

@implementation ocscontact

int main( int argc, char *argv[], char *envp[]) {

	NSAutoreleasePool *autoreleasepool = [[NSAutoreleasePool alloc] init];
	NSLog(@"Running ocscontact");
	setuid(0); //To be able to run OCS agent as root
		
	NSTask *Task = [[NSTask alloc] init];

	//We get the path of ocsinventory-agent executable
	NSString *ocsinventoryAgentPath = [[NSBundle mainBundle] pathForResource:@"ocsinventory-agent"ofType:nil];

	[Task setLaunchPath:ocsinventoryAgentPath];
	[Task launch];
		
	[autoreleasepool release];
	return 0;
}



@end
