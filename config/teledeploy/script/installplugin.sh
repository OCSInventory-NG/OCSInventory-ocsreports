#!/bin/bash

# get plugin name
PLUGIN_NAME=$(ls | grep -oP '.*(?=\.pm)')
echo "Installing $PLUGIN_NAME plugin ..."
FILE_NAME=$(ls | grep -oP '.*\.pm')
if [ -z "$FILE_NAME" ]
    then
        echo "No plugin file provided, exiting\r"
    else
        # get Modules directory path for agent file
        TARGET_PATH=$(perl -MOcsinventory::Agent -e'print $_ . " => " . $INC{$_} . "\n" for keys %INC' | grep Ocsinventory/Agent.pm)
        TARGET_PATH=$(echo $TARGET_PATH | grep -oP '(?<==>).*(?=Agent\.pm)')
        MODULES_PATH="Agent/Modules/"


        # cp file to modules dir retrieved above
        cp $FILE_NAME $TARGET_PATH$MODULES_PATH

        # get string to add to modules.conf
        CONF_LINE="use Ocsinventory::Agent::Modules::$PLUGIN_NAME;\n"

        MODULES_CONF="/etc/ocsinventory*/modules.conf"
        # modify modules.conf
        if $(sed -i "/^# DO NOT REMOVE THE 1;.*/i $CONF_LINE" $MODULES_CONF)
            then
                echo 'Added configuration line to modules.conf \r'
            else
                echo 'Could not add configuration line to modules.conf \r'
        fi
        # test modules.conf
        if $(perl $MODULES_CONF)
            then
                echo 'modules.conf is ok, plugin was successfully installed\r'
            else
                echo 'something when wrong, please check modules.conf and plugin installation \r'
        fi
fi
