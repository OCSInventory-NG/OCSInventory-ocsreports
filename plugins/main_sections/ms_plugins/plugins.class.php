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
 * This class give basic functions for plugins developpers. (WIP)
 */
class plugins {
    // Unused ATM !
    protected $menus;
    protected $rights;

    private function getMenus() {
        return $this->menus;
    }

    private function getRights() {
        return $this->rights;
    }

    private function setMenus($table) {
        $this->menus = $this->menu + $table;
    }

    private function setRights($table) {
        $this->rights = $this->rights + $table;
    }

    /**
     * This function adds a computer detail entry into the plugins.xml.
     *
     * @param string $name : Name of the plugin
     * @param string $category : Category in cd details
     * @param string $available (optional) : NULL per defaut (Don't use it if u don't know what this is supposed to do.)
     */
    public function add_cd_entry($name, $category, $available = NULL) {

        $xmlfile = CD_CONFIG_DIR . "plugins.xml";

        if (file_exists($xmlfile)) {
            $xml = simplexml_load_file($xmlfile);

            $label = rand(50000, 60000);

            $menu = $xml->addChild("plugin");
            $menu->addAttribute("id", "cd_" . $name . "");
            $menu->addChild("label", "g(" . $label . ")");
            $menu->addChild("system", "1");
            $menu->addChild("category", $category);
            if ($available != null) {
                $menu->addChild("available", $available);
            }

            $xml->asXML($xmlfile);
        }
    }

    /**
     * This function will remove the computer detail node with the id => cd_$name
     *
     * @param string $name : Name of the plugin
     */
    public function del_cd_entry($name) {
        $xmlfile = CD_CONFIG_DIR . "plugins.xml";

        if (file_exists($xmlfile)) {
            $xml = simplexml_load_file($xmlfile);

            foreach ($xml as $value) {
                if ($value['id'] == "cd_" . $name) {
                    $dom = dom_import_simplexml($value);
                    $dom->parentNode->removeChild($dom);
                }
            }

            $xml->asXML($xmlfile);
        }
    }

    /**
     * This function creates a menu or a submenu in OCS inventory.
     * By default, only super administrator profile can see the created menu.
     *
     * @param string $name : The name of the menu you want to crate
     * @param integer $label : You need to give a label to your menu, it's like a reference for OCS.
     * @param String $plugindirectory : Your plugin directory
     * @param string $menu (Optional) : If you want to create a submenu not a menu
     */
    public function add_menu($name, $label, $plugindirectory, $displayname, $menu = "") {

        // add menu entry
        if ($menu == "") {
            $xmlfile = CONFIG_DIR . "main_menu.xml";

            if (file_exists($xmlfile)) {
                $xml = simplexml_load_file($xmlfile);

                $menu = $xml->addChild("menu-elem");
                $menu->addAttribute("id", "ms_" . $name . "");
                $menu->addChild("label", "g(" . $label . ")");
                $menu->addChild("url", "ms_" . $name . "");
                $menu->addChild("submenu", " ");

                $xml->asXML($xmlfile);
            }
        } else {
            $xmlfile = CONFIG_DIR . "main_menu.xml";

            if (file_exists($xmlfile)) {
                $xml = simplexml_load_file($xmlfile);

                $mainmenu = $xml->xpath("/menu/menu-elem[attribute::id='" . $menu . "']/submenu");
                $submenu = $mainmenu['0']->addChild("menu-elem");
                $submenu->addAttribute("id", "ms_" . $name . "");
                $submenu->addChild("label", "g(" . $label . ")");
                $submenu->addChild("url", "ms_" . $name . "");

                $xml->asXML($xmlfile);
            }
        }

        // Add url entry for menu
        $xmlfile = CONFIG_DIR . "urls.xml";

        if (file_exists($xmlfile)) {
            $xml = simplexml_load_file($xmlfile);

            $urls = $xml->addChild("url");
            $urls->addAttribute("key", "ms_" . $name . "");
            $urls->addChild("value", $name);
            $urls->addChild("directory", "ms_" . $plugindirectory . "");

            $xml->asXML($xmlfile);
        }

        // add permissions for menu
        $xmlfile = PROFILES_DIR . "sadmin.xml";

        if (file_exists($xmlfile)) {
            $xml = simplexml_load_file($xmlfile);

            $xml->pages->addChild("page", "ms_" . $name . "");

            $xml->asXML($xmlfile);
        }

        // Add label entry
        $file = fopen(PLUGINS_DIR . "language/english/english.txt", "a+");
        fwrite($file, $label . " " . $displayname . "\n");
        fclose($file);
    }

    /**
     * This function deletes a menu or a submenu in OCS inventory.
     * By default, only super administrator profile can see the created menu.
     *
     * @param string $name : The name of the menu you want to delete
     * @param integer $label : You need to give the label of the deleted menu.
     * @param string $menu (Optional) : If you want to delete a submenu not a menu
     */
    public function del_menu($name, $label, $displayname, $menu = "") {

        // Delete menu and all his sub menu
        if ($menu == "") {
            $xmlfile = CONFIG_DIR . "main_menu.xml";

            if (file_exists($xmlfile)) {
                $xml = simplexml_load_file($xmlfile);

                $mainmenu = $xml->xpath("/menu");

                foreach ($mainmenu as $listmenu) {
                    foreach ($listmenu as $info) {
                        if ($info['id'] == "ms_" . $name) {
                            $dom = dom_import_simplexml($info);
                            $dom->parentNode->removeChild($dom);
                        }
                    }
                }
                $xml->asXML($xmlfile);
            }
        } else {
            $xmlfile = CONFIG_DIR . "main_menu.xml";

            if (file_exists($xmlfile)) {
                $xml = simplexml_load_file($xmlfile);

                $mainmenu = $xml->xpath("/menu/menu-elem[attribute::id='" . $menu . "']/submenu");

                foreach ($mainmenu as $submenu) {
                    foreach ($submenu as $info) {
                        if ($info['id'] == "ms_" . $name) {
                            $dom = dom_import_simplexml($info);
                            $dom->parentNode->removeChild($dom);
                        }
                    }
                }
                $xml->asXML($xmlfile);
            }
        }


        // Remove Url node
        $xmlfile = CONFIG_DIR . "urls.xml";

        if (file_exists($xmlfile)) {
            $xml = simplexml_load_file($xmlfile);

            foreach ($xml as $value) {
                if ($value['key'] == "ms_" . $name) {
                    $dom = dom_import_simplexml($value);
                    $dom->parentNode->removeChild($dom);
                }
            }
            $xml->asXML($xmlfile);
        }

        // Remove permissions
        $xmlfile = PROFILES_DIR . "sadmin.xml";

        if (file_exists($xmlfile)) {
            $xml = simplexml_load_file($xmlfile);

            $mypage = $xml->pages->page;

            foreach ($mypage as $pages) {
                if ($pages == "ms_" . $name) {
                    $dom = dom_import_simplexml($pages);
                    $dom->parentNode->removeChild($dom);
                }
            }
            $xml->asXML($xmlfile);
        }

        // Remove Label entry
        $reading = fopen(PLUGINS_DIR . 'language/english/english.txt', 'a+');
        $writing = fopen(PLUGINS_DIR . 'language/english/english.tmp', 'w');

        $replaced = false;

        while (!feof($reading)) {
            $line = fgets($reading);
            if (stristr($line, $label . " " . $displayname)) {
                $line = "";
                $replaced = true;
            }
            fputs($writing, $line);
        }
        fclose($reading);
        fclose($writing);
        // might as well not overwrite the file if we didn't replace anything
        if ($replaced) {
            rename(PLUGINS_DIR . 'language/english/english.tmp', PLUGINS_DIR . 'language/english/english.txt');
        } else {
            unlink(PLUGINS_DIR . 'language/english/english.tmp');
        }
    }

    /**
     * This function is used to add permission to see a page for a fixed profile
     * (admin / ladmin / etc etc...)
     * @param string $profilename : The name of the profile
     * @param string $page : Name of the page u want to be seed by the profile
     */
    public function add_rights($profilename, $page) {
        if ($profilename == "sadmin") {
            exit;
        }

        $xmlfile = PROFILES_DIR . $profilename . ".xml";

        if (file_exists($xmlfile)) {
            $xml = simplexml_load_file($xmlfile);

            $xml->pages->addChild("page", "ms_" . $page . "");

            $xml->asXML($xmlfile);
        }
    }

    /**
     * This function is used for remove permission on one plugin's page for a fixed profile
     * (admin / ladmin / etc etc...)
     * @param string $profilename : The name of the profile
     * @param string $page : Name of the page u want to be seed by the profile
     */
    public function del_rights($profilename, $page) {
        if ($profilename == "sadmin") {
            exit;
        }

        $xmlfile = PROFILES_DIR . $profilename . ".xml";

        $xml = simplexml_load_file($xmlfile);

        $mypage = $xml->pages->page;

        foreach ($mypage as $pages) {
            if ($pages == $page) {
                $dom = dom_import_simplexml($pages);
                $dom->parentNode->removeChild($dom);
            }
        }
        $xml->asXML($xmlfile);
    }

    /**
     * This function tries to execute your query and throws an error message if there is a problem in the query.
     * Die if sql error happened
     *
     * @param string $query : Your database query here !
     * @param bool $cancel_on_error : if on true
     * @return mixed : Result returned by the query
     */
    public function sql_query($query) {
        global $l;
        global $protectedPost;

        try {
            $dbh = new PDO('mysql:host=' . SERVER_WRITE . ';dbname=' . DB_NAME . '', COMPTE_BASE, PSWD_BASE);
            $req = $dbh->query($query);
            if (!$req) {
                msg_error($l->g(2003) . " " . $l->g(7012));
                // Check if a plugin install request has been submited
                if (isset($protectedPost['plugin'])) {
                    $array = explode(".", $protectedPost['plugin']);
                    unset($protectedPost['plugin']);
                    delete_plugin($array[0], true);
                    die;
                }
                return false;
            } else {
                $anwser = $req->fetch();
                $dbh = null;

                return $anwser;
            }
        } catch (PDOException $e) {
            print "Error !: " . $e->getMessage() . "<br/>";
            return false;
        }
    }

}
?>
