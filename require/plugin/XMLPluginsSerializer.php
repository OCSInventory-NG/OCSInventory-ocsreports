<?php

/**
 * Serialize / unserialize the plugins from an XML file
 *
 * @author   Arthur Jaouen <arthur@factorfx.com>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License, version 2
 * @link     http://www.ocsinventory-ng.org/
 *
 */
class XMLPluginsSerializer {
	public function unserialize($xml) {
		$plugins_xml = simplexml_load_string($xml);
		
		$plugins = array();
		foreach ($plugins_xml->plugin as $plugin_xml) {
			$id = (string) $plugin_xml['id'];
			$label = (string) $plugin_xml->label;
			$system = (bool) $plugin_xml->system;
			$cat = (string) $plugin_xml->category;
			$available = (string) $plugin_xml->available;
			$hide_frame = (string) $plugin_xml->hide_frame;
			
			$plugin = new ComputerPlugin($id, $system, $label);
			if ($cat) $plugin->setCategory($cat);
			if ($available) $plugin->setAvailable($available);
			if ($hide_frame) $plugin->setHideFrame($hide_frame);
			
			$plugins[$id] = $plugin;
		}
		
		return $plugins;
	}
}

?>