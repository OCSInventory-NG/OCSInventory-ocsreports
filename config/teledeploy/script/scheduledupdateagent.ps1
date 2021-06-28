# Move ocsupdate.exe on Temp file
$location = Get-Location
$tempFolder = $env:temp
Move-Item "$location\*.exe" -Destination "$tempFolder\ocsupdate.exe"
Move-Item "$location\removeupdateagent.ps1" -Destination "$tempFolder\removeupdateagent.ps1"

# Unregister old ocs inventory scheduled task if exists
Unregister-ScheduledTask -TaskName "OCS Inventory Agent Uninstall" -Confirm:$false

# Register new ocs inventory scheduled task
$action = New-ScheduledTaskAction -Execute "PowerShell.exe" -Argument "-ExecutionPolicy ByPass -File $tempFolder\removeupdateagent.ps1"
$trigger = New-ScheduledTaskTrigger -Once -At ((Get-Date).AddMinutes(5))
$result = Register-ScheduledTask -Action $action -Trigger $trigger -TaskName "OCS Inventory Agent Uninstall" -Description "Scheduled task to uninstall old version of OCS Inventory Agent" -User "System" -RunLevel Highest

if(!$result) {
	exit 1
}
