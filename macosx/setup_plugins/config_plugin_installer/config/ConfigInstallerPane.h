//
//  MyInstallerPane.h
//  config
//
//  Created by Gilles Dubois on 02/02/2020.
//  Copyright © 2020 Gilles Dubois. All rights reserved.
//

#import <InstallerPlugins/InstallerPlugins.h>

@interface ConfigInstallerPane : InstallerPane {
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

@end
