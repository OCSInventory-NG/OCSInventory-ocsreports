<?php

/**
 * Unserialize the profile config from the old txt config files
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class TxtProfileSerializer {
	public function serialize($profile) {
		throw new Exception('Cannot serialize OCS 2.2 profile config to old (pre 2.2) txt files');
	}

	public function unserialize($name, $profile_data) {
		if (!is_array($profile_data)) {
			return false;
		}
		
		$label = $profile_data['INFO']['NAME'];
		$profile = new Profile($name, $label);
		
		foreach ($profile_data['RESTRICTION'] as $key => $restriction) {
			$profile->setRestriction($key, $restriction);
		}
		
		foreach ($profile_data['CONFIGURATION'] as $key => $val) {
			$profile->setConfig($key, $val);
		}
		
		foreach ($profile_data['ADMIN_BLACKLIST'] as $key => $val) {
			if ($val == 'YES') {
				$profile->addToBlacklist($key);
			}
		}
		
		foreach ($profile_data['PAGE_PROFIL'] as $page) {
			$profile->addPage($page);
		}
		
		return $profile;
	}
}

?>