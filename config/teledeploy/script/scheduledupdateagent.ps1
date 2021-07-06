# Move ocsupdate.exe on Temp file
$location = Get-Location
$tempFolder = "OCSUpdateTemp"
$path = "C:\$tempFolder"

# Create new folder to move OCS script and OCS exe
New-Item -Path "C:\" -Name "$tempFolder" -ItemType "directory"
Move-Item "$location\*.exe" -Destination "$path\ocsupdate.exe"
Move-Item "$location\removeupdateagent.ps1" -Destination "$path\removeupdateagent.ps1"

# Unregister old ocs inventory scheduled task if exists
schtasks /delete /tn "OCS Inventory Agent Uninstall" /f
#Unregister-ScheduledTask -TaskName "OCS Inventory Agent Uninstall" -Confirm:$false

# Register new ocs inventory scheduled task
$hour = (Get-Date).AddMinutes(5).ToString("HH:mm")
$result = schtasks /create /tn "OCS Inventory Agent Uninstall" /tr "PowerShell.exe -ExecutionPolicy ByPass -File $path\removeupdateagent.ps1" /sc once /st $hour /ru System
#$action = New-ScheduledTaskAction -Execute "PowerShell.exe" -Argument "-ExecutionPolicy ByPass -File $tempFolder\removeupdateagent.ps1"
#$trigger = New-ScheduledTaskTrigger -Once -At ((Get-Date).AddMinutes(5))
#$result = Register-ScheduledTask -Action $action -Trigger $trigger -TaskName "OCS Inventory Agent Uninstall" -Description "Scheduled task to uninstall old version of OCS Inventory Agent" -User "System" -RunLevel Highest

if(!$result) {
	exit 1
}
