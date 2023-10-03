$appname = "OCS Inventory NG Agent"
$path = "C:\OCSUpdateTemp"
$logFile = "$path\update.log"

$date = (Get-date).ToString("dd/MM/yyyy HH:mm")
Add-Content $logFile -value "$date - Execute removeupdateagent.ps1"
$date = (Get-date).ToString("dd/MM/yyyy HH:mm")
Add-Content $logFile -value "$date - Stop OCS Inventory Service before uninstall"

# Stop OCS Inventory Service before uninstall 
Stop-Service -Name "OCS Inventory Service"

$date = (Get-date).ToString("dd/MM/yyyy HH:mm")
Add-Content $logFile -value "$date - Stop OCS Inventory Service OK"

# Get registry uninstalling path
$32bit = get-itemproperty 'HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\*' | Select-Object DisplayName, DisplayVersion, UninstallString, PSChildName | Where-Object { $_.DisplayName -match "^*$appname*"}
$64bit = get-itemproperty 'HKLM:\Software\Wow6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*' | Select-Object DisplayName, DisplayVersion, UninstallString, PSChildName | Where-Object { $_.DisplayName -match "^*$appname*"}

# Test if 32 or 64bits installation
if ($64bit -eq "" -or $64bit.count -eq 0) {
	if ($32bit.UninstallString) {
		$uninst = $32bit.UninstallString
		$date = (Get-date).ToString("dd/MM/yyyy HH:mm")
		Add-Content $logFile -value  + "$date - Uninstall old agent is in progress..."
        cmd.exe /c $uninst /S
	}
}
else {
	if ($64bit.UninstallString) {
		$uninst = $64bit.UninstallString
		$date = (Get-date).ToString("dd/MM/yyyy HH:mm")
		Add-Content $logFile -value "$date - Uninstall old agent is in progress..."
		cmd.exe /c $uninst /S
	}
}

# Unregister old ocs inventory scheduled task if exists
schtasks /delete /tn "OCS Inventory Agent Update" /f

$date = (Get-date).ToString("dd/MM/yyyy HH:mm")
Add-Content $logFile -value "$date - Scheduled task OCS Inventory Agent Update to launch the packager file"

# Register new ocs inventory scheduled task
$hour = (Get-Date).AddMinutes(2).ToString("HH:mm")
$result = schtasks /create /tn "OCS Inventory Agent Update" /tr "cmd.exe /c $path\ocsupdate.exe" /sc once /st $hour /ru System

if(!$result) {
	$date = (Get-date).ToString("dd/MM/yyyy HH:mm")
	Add-Content $logFile -value "$date - Scheduled task NOT OK"
	Add-Content $logFile -value "END LOG"
	[Environment]::Exit(1)
}

$date = (Get-date).ToString("dd/MM/yyyy HH:mm")
Add-Content $logFile -value "$date - Scheduled task OK. The new agent will be installed in 2 minutes"
Add-Content $logFile -value "END LOG"
[Environment]::Exit(0)