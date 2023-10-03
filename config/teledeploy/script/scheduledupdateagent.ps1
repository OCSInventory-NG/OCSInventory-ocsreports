# Move ocsupdate.exe on Temp file
$location = Get-Location
$tempFolder = "OCSUpdateTemp"
$path = "C:\$tempFolder"
$logFile = "$path\update.log"

#First remove folder if exists
Remove-Item "$path" -Force -Recurse

# Create new folder to move OCS script and OCS exe
New-Item -Path "C:\" -Name "$tempFolder" -ItemType "directory"
#Create log file
New-Item -Path "$path" -Name "update.log" -ItemType "file"
Move-Item "$location\ocsupdate.exe" -Destination "$path\ocsupdate.exe"
Move-Item "$location\removeupdateagent.ps1" -Destination "$path\removeupdateagent.ps1"

# Remove hidden attributes
Get-ChildItem "$path\ocsupdate.exe" -Force | ? {$_.mode -match "h"} |  foreach {$_.Attributes = [System.IO.FileAttributes]::Normal}
Get-ChildItem "$path\removeupdateagent.ps1" -Force | ? {$_.mode -match "h"} |  foreach {$_.Attributes = [System.IO.FileAttributes]::Normal}

Add-Content $logFile -value "UPDATE AGENT LOG FILE"
$date = (Get-date).ToString("dd/MM/yyyy HH:mm")
Add-Content $logFile -value "$date - Starting..."

# Unregister old ocs inventory scheduled task if exists
schtasks /delete /tn "OCS Inventory Agent Uninstall" /f

$date = (Get-date).ToString("dd/MM/yyyy HH:mm")
Add-Content $logFile -value "$date - Scheduled task OCS InventoryAgent Uninstall to launch $path\removeupdateagent.ps1"

# Register new ocs inventory scheduled task
$hour = (Get-Date).AddMinutes(5).ToString("HH:mm")
$result = schtasks /create /tn "OCS Inventory Agent Uninstall" /tr "PowerShell.exe -ExecutionPolicy ByPass -File $path\removeupdateagent.ps1" /sc once /st $hour /ru System

if(!$result) {
	$date = (Get-date).ToString("dd/MM/yyyy HH:mm")
	Add-Content $logFile -value "$date - Scheduled task NOT OK"
	[Environment]::Exit(1)
}

$date = (Get-date).ToString("dd/MM/yyyy HH:mm")
Add-Content $logFile -value "$date - Scheduled task OK. The old Agent will be uninstalled in 5 minutes"
[Environment]::Exit(0)
