//
// OCSINVENTORY-NG
//
// Copyleft Wes Young (claimid.com/saxjazman9 - Barely3am.com) 2008
// 
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/
//
//


#import <Cocoa/Cocoa.h>
#include <Security/Authorization.h>
#include <Security/AuthorizationTags.h>

int main(int argc, char *argv[]) {
	NSAutoreleasePool *autoreleasepool = [[NSAutoreleasePool alloc] init];
	
	[NSApplication sharedApplication];
	
	int launchOcsAgent = 1;
	
	//Getting current user
	NSString *user = NSUserName() ;


	if (![user isEqualToString:@"root"]) {     //If not launched by Launchd
		//show icon on Dock
		if (![[NSUserDefaults  standardUserDefaults] boolForKey:@"hideDockIcon"]) {
			ProcessSerialNumber psn = { 0, kCurrentProcess };
			TransformProcessType(&psn, kProcessTransformToForegroundApplication);
		} 
	    		
		NSAlert *askOcsAgentLaunch = [[NSAlert alloc] init];
	
		[askOcsAgentLaunch setMessageText:@"Do you want to launch OCS Inventory NG agent ?"];
		[askOcsAgentLaunch setInformativeText:@"This will take contact with OCS Inventory NG server"];
		[askOcsAgentLaunch addButtonWithTitle:@"Yes"];
		[askOcsAgentLaunch addButtonWithTitle:@"No"];
		[askOcsAgentLaunch setAlertStyle:NSInformationalAlertStyle];

		//Our application become the topmost window
		[NSApp activateIgnoringOtherApps:YES];
		
		if ([askOcsAgentLaunch runModal] != NSAlertFirstButtonReturn) {
			// Button 'No' was clicked, we don't launch OCS agent
			launchOcsAgent = 0;
		}
		
		[askOcsAgentLaunch release];
	}	
								 
	if (launchOcsAgent == 1 ) {						    
	
		// Too be on the safe side, I chose the array length to be 10.
		const int kPIDArrayLength = 10;
		pid_t myArray[kPIDArrayLength];
		unsigned int numberMatches;

		// simple way of geting our PID, see if we're already running....
		int error = GetAllPIDsForProcessName("OCSNG",myArray,kPIDArrayLength,&numberMatches,NULL);
		if (error == 0) { // Success
			if (numberMatches > 1) {
				// There's already a copy of this app running
				return -1;
			}
		}

		//We launch contact to server using Authorization Services (with asking password)
		OSStatus myStatus;
		AuthorizationFlags myFlags = kAuthorizationFlagDefaults;
		AuthorizationRef myAuthorizationRef;
 
 
		myStatus = AuthorizationCreate(NULL, kAuthorizationEmptyEnvironment,myFlags, &myAuthorizationRef);
	
		if (myStatus != errAuthorizationSuccess)
			return myStatus;
 
		do
		{
			{
				AuthorizationItem myItems = {kAuthorizationRightExecute, 0, NULL, 0};
				AuthorizationRights myRights = {1, &myItems};
 
				myFlags = kAuthorizationFlagDefaults |
						kAuthorizationFlagInteractionAllowed |
						kAuthorizationFlagPreAuthorize |
						kAuthorizationFlagExtendRights;
				myStatus = AuthorizationCopyRights (myAuthorizationRef,&myRights, NULL, myFlags, NULL );
			}
 
			if (myStatus != errAuthorizationSuccess) break;
			{
				//We use an helper tool instead of running OCS agent directly
				NSString *ocscontactPath = [[NSBundle mainBundle] pathForResource:@"ocscontact"ofType:nil];
			
				char *myArguments[] = { "", NULL };
				FILE *myCommunicationsPipe = NULL;
				char myReadBuffer[128];
			
				myFlags = kAuthorizationFlagDefaults;
				myStatus = AuthorizationExecuteWithPrivileges
						(myAuthorizationRef, [ocscontactPath UTF8String], kAuthorizationFlagDefaults, myArguments,
						 &myCommunicationsPipe);
		
			
				if (myStatus == errAuthorizationSuccess)
					for(;;)
					{
						int bytesRead = read (fileno (myCommunicationsPipe),myReadBuffer, sizeof (myReadBuffer));
					
						if (bytesRead < 1) break;
						write (fileno (stdout), myReadBuffer, bytesRead);
					}
			}
		} while (0);
 
		AuthorizationFree (myAuthorizationRef, kAuthorizationFlagDefaults);
		
	
		return myStatus;		
	}
	
	[autoreleasepool release];
    return NSApplicationMain(argc,  (const char **) argv);
	
}
