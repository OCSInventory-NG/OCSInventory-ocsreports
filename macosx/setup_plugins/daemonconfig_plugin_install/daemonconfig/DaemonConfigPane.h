//
//  MyInstallerPane.h
//  daemonconfig
//
//  Created by OCSInventory on 02/02/2020.
//  Copyright © 2020 OCSInventory. All rights reserved.
//

#import <InstallerPlugins/InstallerPlugins.h>

@interface DaemonConfigPane : InstallerPane {
    
    IBOutlet NSTextField *periodicity;
    IBOutlet NSButton *now;
    IBOutlet NSButton *startup;
    
    NSFileManager *filemgr;
    NSString *tmpLaunchdFilePath;
    NSString *tmpNowFilePath;
    
}

@end
