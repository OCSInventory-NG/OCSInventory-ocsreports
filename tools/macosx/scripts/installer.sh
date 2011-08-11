#!/bin/bash


PREFIX=/tmp/ocs_installer

if [ "$1" == "-custom" ]; then
	CUSTOM=1
	PREFIX=./
fi

INSTALL_PATH="/Applications/OCSNG.app"

echo "Setting rights to $INSTALL_PATH"
sudo chown -R root:wheel $INSTALL_PATH
sudo chmod -R 755 $INSTALL_PATH

echo "Setting rights to $INSTALL_PATH/Contents/Resources/ocscontact"
sudo chown root:wheel $INSTALL_PATH/Contents/Resources/ocscontact
sudo chmod 700 $INSTALL_PATH/Contents/Resources/ocscontact

echo "Copying uninstall script to $INSTALL_PATH"
sudo cp $PREFIX/scripts/uninstaller.sh $INSTALL_PATH/Contents/Resources/
sudo chmod 700 $INSTALL_PATH/Contents/Resources/uninstaller.sh

ETCPATH="/etc/ocsinventory-agent"
sudo mkdir $ETCPATH/
sudo cp $PREFIX/ocsinventory-agent.cfg $ETCPATH/
sudo cp $PREFIX/modules.conf $ETCPATH/

VARPATH="/var/lib/ocsinventory-agent"
sudo mkdir -p $VARPATH
sudo chown -R root:wheel $VARPATH

if [ "$CUSTOM" == 1 ]; then
	if [ -e $PREFIX/cacert.pem ]; then
		echo "copying cacert.pem to $VARPATH/"
		sudo cp $PREFIX/cacert.pem $VARPATH/
	fi
else
	if [ -e $PREFIX/serverdir ] && [ -e $PREFIX/cacert.pem ]; then
		SERVERDIR=`cat $PREFIX/serverdir` 
		mkdir $SERVERDIR
		cp $PREFIX/cacert.pem $SERVERDIR/
	fi
fi

echo "Setting LaunchDaemons plists"
LAUNCHDPATH="/Library/LaunchDaemons/"

if [ "$CUSTOM" == 1 ]; then
	sudo cp $PREFIX/launchfiles/org.ocsng.agent.plist $LAUNCHDPATH
else
	sudo cp $PREFIX/org.ocsng.agent.plist $LAUNCHDPATH
fi

sudo chown root:wheel $LAUNCHDPATH/org.ocsng.agent.plist
sudo chmod 644 $LAUNCHDPATH/org.ocsng.agent.plist

if [[ -f $PREFIX/now || $CUSTOM -eq 1 ]]; then
	echo 'Loading Service'
	sudo launchctl load $LAUNCHDPATH/org.ocsng.agent.plist

	echo 'Starting Service'
	sudo launchctl start org.ocsng.agent
fi

if [ ! "$CUSTOM" == 1 ]; then
	echo 'Removing temporary directory'
	rm -Rf $PREFIX
fi

echo 'done'
exit 0 
