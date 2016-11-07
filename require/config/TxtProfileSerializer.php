<?php
/*
 * Copyright 2005-2016 OCSInventory-NG/OCSInventory-ocsreports contributors.
 * See the Contributors file for more details about them.
 *
 * This file is part of OCSInventory-NG/OCSInventory-ocsreports.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * OCSInventory-NG/OCSInventory-ocsreports is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OCSInventory-NG/OCSInventory-ocsreports. if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

/**
 * Unserialize the profile config from the old txt config files
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