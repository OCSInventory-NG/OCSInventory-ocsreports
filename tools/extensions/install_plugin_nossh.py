import os
import shutil
import getpass

#Variable
location = "/etc/ocsinventory-server"
directory = "/usr/share/ocsinventory-reports/ocsreports/extensions/"
plugins = []
selection = -1

#Get directory where are the plugins
print("Where are installed the extensions ? [" + directory + "]")
res = input()
if res != '':
	directory = res

#Get all plugins in extension directory
for x in os.listdir(directory):
	if os.path.isdir(directory + x):
		plugins.append(x)

#Select plugin for installation
valSelection = True
while valSelection:
	i = 0
	for file in plugins:
		if file != 0:
			print("[" + str(i) + "] => " + file)
			i += 1
	selection = input()
	#Check input
	try:
		selection = int(selection)
		if selection >= 0 and selection < len(plugins):
			valSelection = False
	except:
		print("Enter a valid number")

#Installation
print("Is the communication server installed on the same machine ? [y]/n")

#Installation in same machine
print("Where is the communication plugin directory located [" + location + "]")
res = input()
if res != '':
    location = res

#check if location exist
if not os.path.exists(location):
    print("ERROR: enter a valid path")
    exit()

#create directory if not exist
if not os.path.exists(location + "/perl/Apache/Ocsinventory/Plugins/" + plugins[selection].title()):
    os.makedirs(location + "/perl/Apache/Ocsinventory/Plugins/" + plugins[selection].title())

#Check if plugin files exists
if not os.path.exists(directory + plugins[selection] + "/APACHE/Map.pm") or not os.path.exists(directory + plugins[selection] + "/APACHE/" + plugins[selection] + ".conf"):
    print("ERROR: check if Map.pm and " + plugins[selection] + ".conf exist")
    exit()

#Copy Files
shutil.copyfile(directory + plugins[selection] + "/APACHE/Map.pm", location + "/perl/Apache/Ocsinventory/Plugins/" + plugins[selection].title() + "/Map.pm")
shutil.copyfile(directory + plugins[selection] + "/APACHE/" + plugins[selection] + ".conf", location + "/plugins/" + plugins[selection] + ".conf")

print(plugins[selection] + "has been successfully installed ! Don't forget to restart your Apache server")

exit()