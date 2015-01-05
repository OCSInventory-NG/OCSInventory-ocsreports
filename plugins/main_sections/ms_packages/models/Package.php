<?php

/**
 * Holds a package information
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class Package {
	const ACTION_STORE = 'STORE';
	const ACTION_EXECUTE = 'EXECUTE';
	const ACTION_LAUNCH = 'LAUNCH';
	
	const OS_WINDOWS = 'WINDOWS';
	const OS_LINUX = 'LINUX';
	const OS_MAC = 'MAC';
	const OS_ANDROID = 'ANDROID';
	
	// General properties
	private $timestamp;
	private $name;
	private $description;
	private $os;
	private $action;
	private $actionParam;
	private $file;
	private $fileSize;
	
	// Fragments
	private $fragSize;
	
	// Redistribution
	private $useRedistrib;
	private $redistribDocRoot;
	private $redistribPriority;
	private $redistribFragments;
	
	// Notification
	private $useNotif;
	private $notifText;
	private $notifCountdown;
	private $canAbort;
	private $canDelay;
	
	// Post-execution text
	private $usePostExec;
	private $postExecText;
	
	public static function buildFromRequest($data) {
		$package = new Package(intval(trim($data['timestamp'])));
		
		if (isset($data['name']))
			$package->setName(trim($data['name']));
		
		if (isset($data['description']))
			$package->setDescription(trim($data['description']));
		
		if (isset($data['os']))
			$package->setOs(trim($data['os']));
		
		if (isset($data['action']))
			$package->setAction(trim($data['action']));
		
		if (isset($data['actionParam']))
			$package->setActionParam(trim($data['actionParam']));
		
		if (isset($data['fragSize']))
			$package->setFragSize(intval(trim($data['fragSize'])));
		
		if (isset($data['useRedistrib']) and $data['useRedistrib'] == 'on') {
			$package->setUseRedistrib(true);
			
			if (isset($data['redistribDocRoot']))
				$package->setRedistribDocRoot(trim($data['redistribDocRoot']));
			
			if (isset($data['redistribPriority']))
				$package->setRedistribPriority(intval(trim($data['redistribPriority'])));
			
			if (isset($data['redistribFragments']))
				$package->setRedistribFragments(intval(trim($data['redistribFragments'])));
		} else {
			$package->setUseRedistrib(false);
		}

		if (isset($data['useNotif']) and $data['useNotif'] == 'on') {
			$package->setUseNotif(true);

			if (isset($data['notifText']))
				$package->setNotifText(trim($data['notifText']));
			
			if (isset($data['notifCountdown']))
				$package->setNotifCountdown(intval(trim($data['notifCountdown'])));
			
			$package->setCanAbort(isset($data['canAbort']) and $data['canAbort'] == 'on');
			$package->setCanDelay(isset($data['canDelay']) and $data['canDelay'] == 'on');
		} else {
			$package->setUseNotif(false);
		}
		
		if (isset($data['usePostExec']) and $data['usePostExec'] == 'on') {
			$package->setUsePostExec(true);
		
			if (isset($data['postExecText']))
				$package->setPostExecText(trim($data['postExecText']));
		} else {
			$package->setUsePostExec(false);
		}
		
		return $package;
	}
	
	/**
	 * @param string $timestamp
	 * @param string $name
	 * @param string $description
	 */
	public function __construct($timestamp) {
		$this->timestamp = $timestamp;
		
		if ($this->isTemp()) {
			$this->file = $this->getRoot().'/tmp/package';
		}
	}
	
	public function validate() {
		$errors = array();
		
		// TODO error translations
		// TODO check for field sizes
		
		// Special case for android apk launch
		if ($this->os == Package::OS_ANDROID && $this->action == Package::ACTION_LAUNCH) {
			$this->actionParam = $this->timestamp.'.apk';
		}
		
		// Check mandatory data
		$mandatory_fields = array('name', 'description', 'os', 'action', 'fragSize');
		foreach ($mandatory_fields as $field) {
			if (!$this->$field) {
				$errors[$field] []= 'This field is mandatory';
			}
		}
		
		// Check dropdown lists
		if ($this->os and !in_array($this->os, array(Package::OS_WINDOWS, Package::OS_LINUX, Package::OS_MAC, Package::OS_ANDROID))) {
			$errors['os'] []= 'Invalid value';
		}
		
		if ($this->action and !in_array($this->action, array(Package::ACTION_STORE, Package::ACTION_EXECUTE, Package::ACTION_LAUNCH))) {
			$errors['action'] []= 'Invalid value';
		}
		
		// Check dependencies
		if ($this->os == Package::OS_WINDOWS) {
			if ($this->useNotif) {
				$mandatory_fields = array('notifText', 'notifCountdown');
				foreach ($mandatory_fields as $field) {
					if (!$this->$field) {
						$errors[$field] []= 'This field is mandatory';
					}
				}
			}
			if ($this->usePostExec) {
				if (!$this->postExecText) {
					$errors['postExecText'] []= 'This field is mandatory';
				}
			}
		}
		
		if ($this->useRedistrib) {
			if (!$this->redistribDocRoot) {
				$errors['redistribDocRoot'] []= 'This field is mandatory';
			}
		}
		
		// Check unique fields
		if ($this->name and package_name_exists($this->name)) {
			$errors['name'] []= 'This package name already exists';
		}
		
		return $errors;
	}
	
	public function create() {
		if (!$this->isTemp()) {
			throw new Exception('The temporary package file could not be found');
		}
		
		$xml = $this->toXML();
		
		// Cut the package into fragments
		$fragSize = $this->getFragSize();
		
		$file = fopen($this->file, 'rb');
		for ($i = 0; $i < $this->getFragsNumber(); $i++) {
			$fragContent = fread($file, $fragSize);
			
			// Write fragment
			$fragFile = fopen($this->getRoot().'/'.$this->timestamp.'-'.($i+1), 'w+b');
			fwrite($fragFile, $fragContent);
			fclose($fragFile);
		}
		
		fclose($file);
		unlink($this->file);
		rmdir($this->getRoot().'/tmp');
		
		$infoFile = fopen($this->getRoot().'/info', 'w+');
		fwrite($infoFile, $xml);
		fclose($infoFile);
		
		$stmt = mysqli_prepare($_SESSION['OCS']['writeServer'], 'DELETE FROM download_available WHERE FILEID = ?');
		mysqli_stmt_bind_param($stmt, 's', $this->timestamp);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);

		$insertQuery =
			'INSERT INTO download_available (FILEID, NAME, PRIORITY, FRAGMENTS, SIZE, OSNAME, COMMENT, ID_WK)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
		
		$stmt = mysqli_prepare($_SESSION['OCS']['writeServer'], $insertQuery);
		
		$priority = 5;
		$idWk = 0;
		
		$fragsNumber = $this->getFragsNumber();
		$fileSize = $this->getFileSize();
		
		mysqli_stmt_bind_param($stmt, 'ssssssss',
			$this->timestamp, $this->name, $priority /* TODO */, $fragsNumber,
			$fileSize, $this->os, $this->description, $idWk
		);
		$success = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		
		return $success;
	}
	
	public function toXML() {
		$doc_xml = new DOMDocument('1.0', 'UTF-8');
		$root_xml = $doc_xml->createElement('DOWNLOAD');
		$root_xml->setAttribute('ID', $this->timestamp);
		$root_xml->setAttribute('PRI', 5); // TODO
		$root_xml->setAttribute('ACT', $this->action);
		$root_xml->setAttribute('DIGEST', md5_file($this->file));
		$root_xml->setAttribute('PROTO', 'HTTP');
		$root_xml->setAttribute('FRAGS', $this->getFragsNumber());
		$root_xml->setAttribute('DIGEST_ALGO', 'MD5');
		$root_xml->setAttribute('DIGEST_ENCODE', 'HEXA');
		
		switch ($this->action) {
			case Package::ACTION_STORE:
				$root_xml->setAttribute('PATH', $this->actionParam);
				break;
			case Package::ACTION_LAUNCH:
				$root_xml->setAttribute('NAME', $this->actionParam);
				break;
			case Package::ACTION_EXECUTE:
				$root_xml->setAttribute('COMMAND', $this->actionParam);
				break;
		}

		$root_xml->setAttribute('NOTIFY_USER', intval($this->useNotif));
		$root_xml->setAttribute('NOTIFY_TEXT', $this->notifText);
		$root_xml->setAttribute('NOTIFY_COUNTDOWN', $this->notifCountdown ?: '');
		$root_xml->setAttribute('NOTIFY_CAN_ABORT', intval($this->canAbort));
		$root_xml->setAttribute('NOTIFY_CAN_DELAY', intval($this->canDelay));

		$root_xml->setAttribute('NEED_DONE_ACTION', intval($this->usePostExec));
		$root_xml->setAttribute('NEED_DONE_ACTION_TEXT', intval($this->postExecText));

		$root_xml->setAttribute('GARDEFOU', 'rien');

		$doc_xml->appendChild($root_xml);
		return $doc_xml->saveXML();
	}
	
	public function getRoot() {
		return get_download_root().$this->timestamp;
	}
	
	public function exists() {
		return file_exists($this->getRoot().'/info');
	}
	
	public function isTemp() {
		return file_exists($this->getRoot().'/tmp/package');
	}
	
	public function getTimestamp() {
		return $this->timestamp;
	}
	
	public function setTimestamp($timestamp) {
		$this->timestamp = $timestamp;
		return $this;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}
	
	public function getOs() {
		return $this->os;
	}
	
	public function setOs($os) {
		$this->os = $os;
		return $this;
	}
	
	public function getAction() {
		return $this->action;
	}
	
	public function setAction($action) {
		$this->action = $action;
		return $this;
	}
	
	public function getActionParam() {
		return $this->actionParam;
	}
	
	public function setActionParam($actionParam) {
		$this->actionParam = $actionParam;
		return $this;
	}
	
	public function getFile() {
		return $this->file;
	}
	
	public function setFile($file) {
		$this->file = $file;
		return $this;
	}
	
	public function getFileSize() {
		if (is_null($this->fileSize)) {
			$this->fileSize = filesize($this->file);
		}
		
		return $this->fileSize;
	}
	
	public function getFragSize() {
		return $this->fragSize;
	}
	
	public function setFragSize($fragSize) {
		if ($fragSize < 1 or $fragSize > $this->getFileSize()) {
			$this->fragSize = $this->getFileSize();
		} else {
			$this->fragSize = $fragSize;
		}
		
		return $this;
	}
	
	public function getFragsNumber() {
		return ceil($this->getFileSize() / $this->getFragSize());
	}
	
	public function useRedistrib() {
		return $this->useRedistrib;
	}
	
	public function setUseRedistrib($useRedistrib) {
		$this->useRedistrib = $useRedistrib;
		return $this;
	}
	
	public function getRedistribDocRoot() {
		return $this->redistribDocRoot;
	}
	
	public function setRedistribDocRoot($redistribDocRoot) {
		$this->redistribDocRoot = $redistribDocRoot;
		return $this;
	}
	
	public function getRedistribPriority() {
		return $this->redistribPriority;
	}
	
	public function setRedistribPriority($redistribPriority) {
		$this->redistribPriority = $redistribPriority;
		return $this;
	}
	
	public function getRedistribFragments() {
		return $this->redistribFragments;
	}
	
	public function setRedistribFragments($redistribFragments) {
		$this->redistribFragments = $redistribFragments;
		return $this;
	}
	
	public function useNotif() {
		return $this->useNotif;
	}
	
	public function setUseNotif($useNotif) {
		$this->useNotif = $useNotif;
		return $this;
	}
	
	public function getNotifText() {
		return $this->notifText;
	}
	
	public function setNotifText($notifText) {
		$this->notifText = $notifText;
		return $this;
	}
	
	public function getNotifCountdown() {
		return $this->notifCountdown;
	}
	
	public function setNotifCountdown($notifCountdown) {
		$this->notifCountdown = $notifCountdown;
		return $this;
	}
	
	public function getCanAbort() {
		return $this->canAbort;
	}
	
	public function setCanAbort($canAbort) {
		$this->canAbort = $canAbort;
		return $this;
	}
	
	public function getCanDelay() {
		return $this->canDelay;
	}
	
	public function setCanDelay($canDelay) {
		$this->canDelay = $canDelay;
		return $this;
	}
	
	public function usePostExec() {
		return $this->usePostExec;
	}
	
	public function setUsePostExec($usePostExec) {
		$this->usePostExec = $usePostExec;
		return $this;
	}
	
	public function getPostExecText() {
		return $this->postExecText;
	}
	
	public function setPostExecText($postExecText) {
		$this->postExecText = $postExecText;
		return $this;
	}
}

?>