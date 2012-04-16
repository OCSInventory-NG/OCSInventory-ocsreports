#!/bin/bash

#Use this script to build OCS Inventory NG MacOSX agent
#Run 'sh BUILDME.sh' to build official released agent

#TODO: remove darwin-perl-lib directory at the end of the script

OCSNG_PATH="OCSNG.app"
PATCHES_PATH="patches"
TOOLS_PATH="tools/macosx"

if [ ! -x ../../inc ]; then
	echo "You're probably building from BZR, you're missing the "inc" directory in ../../"
	exit 1;
fi

if [ ! -x ./darwin-perl-lib ]; then
	if [ ! -e ./scripts/macosx-perl-lib-dep-snapshot.tar.gz ]; then
		echo "You're missing the darwin-perl-lib directory, did you run the create-darwin-perl-lib_fromCPAN.pl script?"
		exit 1;
	else
		echo 'extracting from snapshot perl-lib deps to ./'
		tar -zxvf ./scripts/macosx-perl-lib-dep-snapshot.tar.gz
	fi
fi

if [ -x $OCSNG_PATH ]; then
	echo "removing old $OCSNG_PATH"
        sudo rm -R -f $OCSNG_PATH
fi

if [ -x package-root ]; then
	echo 'removing old package-root'
	sudo rm -R -f package-root
fi

echo "Building OS X App"
cd ocsng_app-xcode/
xcodebuild -alltargets
cp ./build/UninstalledProducts/ocscontact ./build/UninstalledProducts/OCSNG.app/Contents/Resources 
cp -R ./build/UninstalledProducts/OCSNG.app ../
xcodebuild clean
cd ../
mkdir $OCSNG_PATH/Contents/Resources/lib

echo "Buidling unified source"
cd ../../

echo 'removing non-MacOS/Generic backend modules'
cd ./lib/Ocsinventory/Agent/Backend/OS/
rm -R -f `ls -l | grep -v MacOS | grep -v CVS | grep -v Generic`
cd ../../../../../

echo "Building Makefile.pl...."
perl Makefile.PL
make
cp -R blib/lib ./$TOOLS_PATH/$OCSNG_PATH/Contents/Resources
cp ocsinventory-agent ./$TOOLS_PATH/
make clean

echo 'patching main perl script for OSX'
cd ./$TOOLS_PATH/
patch -N ./ocsinventory-agent ./$PATCHES_PATH/ocsinventory-agent-darwin.patch
cp ocsinventory-agent $OCSNG_PATH/Contents/Resources/
rm ocsinventory-agent

echo 'copying down darwin-dep libs'
cp -R darwin-perl-lib/ $OCSNG_PATH/Contents/Resources/lib/

echo 'copying uninstall script'
cp scripts/uninstaller.sh $OCSNG_PATH/Contents/Resources/

if [ ! -d ./installer_gui/iceberg/plugins ]; then
	mkdir ./installer_gui/iceberg/plugins/
fi

cd ./installer_gui/ocs_agent_config/
xcodebuild -alltargets
cp -R ./build/Release/ocs_agent_config.bundle ../iceberg/plugins/
xcodebuild clean
cd ../ocs_agent_daemon_options/
xcodebuild -alltargets
cp -R ./build/Release/ocs_agent_daemon_options.bundle ../iceberg/plugins/
xcodebuild clean
cd ../../
cp ./scripts/installer.sh installer_gui/iceberg/scripts/

echo "moving $OCSNG_PATH...enter your password if needed"	
sudo mv $OCSNG_PATH ./installer_gui/iceberg/

echo 'Now you can build final gui installer using iceberg'

echo "done"
