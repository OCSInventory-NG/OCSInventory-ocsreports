$appname = "OCS Inventory NG Agent"

# Stop OCS Inventory Service before uninstall 
Stop-Service -Name "OCS Inventory Service"

# Get registry uninstalling path
$32bit = get-itemproperty 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\*' | Select-Object DisplayName, DisplayVersion, UninstallString, PSChildName | Where-Object { $_.DisplayName -match "^*$appname*"}
$64bit = get-itemproperty 'HKLM:\Software\Wow6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*' | Select-Object DisplayName, DisplayVersion, UninstallString, PSChildName | Where-Object { $_.DisplayName -match "^*$appname*"}

# Test if 32 or 64bits installation
if ($64bit -eq "" -or $64bit.count -eq 0) {
	if ($32bit.UninstallString) {
		$uninst = $32bit.UninstallString
        cmd.exe /c $uninst /S
	}
}
else {
	if ($64bit.UninstallString) {
		$uninst = $64bit.UninstallString
		cmd.exe /c $uninst /S
	}
}

$path = Split-Path -Path $uninst
Remove-Item $path

$tempFolder = $env:temp

# Unregister old ocs inventory scheduled task if exists
Unregister-ScheduledTask -TaskName "OCS Inventory Agent Update" -Confirm:$false

# Register new ocs inventory scheduled task
$action = New-ScheduledTaskAction -Execute "cmd.exe" -Argument "/c $tempFolder\ocsupdate.exe"
$trigger = New-ScheduledTaskTrigger -Once -At ((Get-Date).AddMinutes(2))
$result = Register-ScheduledTask -Action $action -Trigger $trigger -TaskName "OCS Inventory Agent Update" -Description "Scheduled task to update OCS Inventory Agent" -User "System" -RunLevel Highest

if(!$result) {
	exit 1
}