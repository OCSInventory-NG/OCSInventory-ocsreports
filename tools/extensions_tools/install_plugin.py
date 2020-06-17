import os
import shutil
import getpass
from scp import SCPClient
import paramiko

#Variable
location = "/etc/ocsinventory-server"
directory = "/usr/share/ocsinventory-reports/ocsreports/extensions/"
plugins = []
selection = -1

#Get directory where are the plugins
print("Where is the plugins location [" + directory + "]")
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
print("The server is installed on the same server ? [y]/n")
sameMachine = input()
if sameMachine == 'n' or sameMachine == 'N':
	#Installation in other machine
	print("What is the host :")
	host = input()
	print("What is the username :")
	username = input()
	print("What is the password :")
	password = getpass.getpass()

	#Set location plugins server
	print("Where is the server location [" + location + "]")
	res = input()
	if res != '':
		location = res

	#Check if plugin files exists
	if not os.path.exists(directory + plugins[selection] + "/APACHE/Map.pm") or not os.path.exists(directory + plugins[selection] + "/APACHE/" + plugins[selection] + ".conf"):
		print("ERROR: check if Map.pm and " + plugins[selection] + ".conf exist")
		exit()

	#Connexion SSH + mkdir directory
	try:
		clientssh = paramiko.SSHClient()
		clientssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
		clientssh.connect(host, username=username, password=password)
		clientssh.exec_command("mkdir -p " + location + "/perl/Apache/Ocsinventory/Plugins/" + plugins[selection].title())
	except Exception as e:
		print(e)
		exit()

	#Connexion SCP + transfer
	try:
		with SCPClient(clientssh.get_transport()) as scp:
			scp.put(directory + plugins[selection] + "/APACHE/Map.pm", location + "/perl/Apache/Ocsinventory/Plugins/" + plugins[selection].title() + "/Map.pm")
			scp.put(directory + plugins[selection] + "/APACHE/" + plugins[selection] + ".conf", location + "/plugins/" + plugins[selection] + ".conf")
	except Exception as e:
		print(e)


else:
	#Installation in same machine
	print("Where is the server location [" + location + "]")
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
