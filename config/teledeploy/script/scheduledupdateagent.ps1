# Move ocsupdate.exe on Temp file
Move-Item .\*.exe -Destination C:\Temp\ocsupdate.exe

# Unregister old ocs inventory scheduled task if exists
Unregister-ScheduledTask -TaskName "OCS Inventory Agent Update" -Confirm:$false

# Register new ocs inventory scheduled task
$action = New-ScheduledTaskAction -Execute 'C:\Temp\ocsupdate.exe'
$trigger = New-ScheduledTaskTrigger -Once -At ((Get-Date).AddMinutes(30))
$result = Register-ScheduledTask -Action $action -Trigger $trigger -TaskName "OCS Inventory Agent Update" -Description "Scheduled task to update OCS Inventory Agent" -RunLevel Highest

if(!$result) {
	exit 1
}