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

class ExtensionHook{

    const XML_HOOKS_FILE = "/hook.xml";

    const LANG_HOOK = "lang";
    const MENU_HOOK = "menu";
    const SUB_MENU_HOOK = "submenu";
    const CD_DETAIL_HOOK = "cdentry";

    const IDENTIFIER = "identifier";
    const MAIN_MENU_IDENTIFIER = "mainmenuidentifier";
    const TRANSLATION = "translation";
    const AVAILABLE = "available";
    const CATEGORY = "category";
    const EXTENSION = "extension";

    public $menuExtensionsHooks = array();
    public $subMenuExtensionsHooks = array();
    public $languageExtensionsHooks = array();
    public $computerDetailExtensionsHooks = array();

    // Simple array of menu available in all loaded extension
    public $extDeclaredMenu = array();
    public $computerDeclaredMenu = array();

    private $xmlElement;

    private $currentScannedExt = "";

    public $activatedExt = array();

    function __construct($activatedExt) {
        $this->activatedExt = $activatedExt;

        foreach ($this->activatedExt as $extLabel) {
            if($this->haveHook($extLabel)){
                $this->readHookXml($extLabel);
            }
            $this->addTranslation($extLabel);
        }

    }

    /**
     *
     * @param String $hookType Constant hook type
     */
    public function needHookTrigger($hookType){
        switch ($hookType) {
            case self::LANG_HOOK:
                if(empty($this->languageExtensionsHooks)){
                    return false;
                }else{
                    return true;
                }

            case self::MENU_HOOK:
                if(empty($this->menuExtensionsHooks)){
                    return false;
                }else{
                    return true;
                }

            case self::SUB_MENU_HOOK:
                if(empty($this->subMenuExtensionsHooks)){
                    return false;
                }else{
                    return true;
                }

            case self::CD_DETAIL_HOOK:
                if(empty($this->computerDetailExtensionsHooks)){
                    return false;
                }else{
                    return true;
                }
                break;

            default:
                return false;
        }
    }

    /**
     * This method read the hook.xml in extension to create menu / lang / submenu and more to come.
     */
    private function readHookXml($extLabel){
        $this->currentScannedExt = $extLabel;
        $xmlStr = file_get_contents(EXT_DL_DIR.$extLabel.self::XML_HOOKS_FILE);
        $this->xmlElement = new SimpleXMLElement($xmlStr);
        foreach ($this->xmlElement->hook as $hooks) {
            switch ($hooks->attributes()->type) {
                case self::LANG_HOOK:
                    $this->addLangEntries($hooks->value);
                    break;

                case self::MENU_HOOK:
                    $this->extDeclaredMenu[strval($hooks->identifier)] = $this->currentScannedExt;
                    $menuHookArray = array(
                        self::IDENTIFIER => $hooks->identifier,
                        self::TRANSLATION => $hooks->translation
                    );
                    $this->addMenuEntry($menuHookArray);
                    break;

                case self::SUB_MENU_HOOK:
                    $this->extDeclaredMenu[strval($hooks->identifier)] = $this->currentScannedExt;
                    $subMenuHookArray = array(
                        self::MAIN_MENU_IDENTIFIER => $hooks->mainmenuidentifier,
                        self::IDENTIFIER => $hooks->identifier,
                        self::TRANSLATION => $hooks->translation
                    );
                    $this->addSubMenuEntry($subMenuHookArray);
                    break;

                case self::CD_DETAIL_HOOK:
                    $this->computerDeclaredMenu[strval($hooks->identifier)] = $this->currentScannedExt;
                    $computerDetailHookArray = array(
                        self::IDENTIFIER => $hooks->identifier,
                        self::TRANSLATION => $hooks->translation,
                        self::CATEGORY => $hooks->category,
                        self::AVAILABLE => $hooks->available
                    );
                    $this->addCdEntry($computerDetailHookArray);
                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Return the cd details in specified category
     *
     * @param $catName : Category name
     * @return array : Values
     */
    public function getCdEntryByCategory($catName){
        return $this->computerDetailExtensionsHooks[$catName] ?? '';
    }

    /**
     * Add cd computers entries in class attributes
     *
     * @param array $xmlHookRender Array for xml hooks that contains cd entries to add
     */
    private function addCdEntry(array $xmlHookRender){
        $this->computerDetailExtensionsHooks[(string)$xmlHookRender[self::CATEGORY]][(string)$xmlHookRender[self::IDENTIFIER]] = array(
            self::IDENTIFIER => (string)$xmlHookRender[self::IDENTIFIER],
            self::TRANSLATION => (string)$xmlHookRender[self::TRANSLATION],
            self::CATEGORY => (string)$xmlHookRender[self::CATEGORY],
            self::AVAILABLE => (string)$xmlHookRender[self::AVAILABLE],
            self::EXTENSION => $this->currentScannedExt
        );
    }

    /**
     * Add lang entries in the class attributes for later use
     *
     * @param array $xmlHookRender Array for xml hooks that contains all lang to add
     */
    private function addSubMenuEntry(array $xmlHookRender){
        $this->subMenuExtensionsHooks[(string)$xmlHookRender[self::MAIN_MENU_IDENTIFIER]][$this->currentScannedExt][] = array(
            self::IDENTIFIER => (string)$xmlHookRender[self::IDENTIFIER],
            self::TRANSLATION => (string)$xmlHookRender[self::TRANSLATION]
        );
    }

    /**
     * Add lang entries in the class attributes for later use
     *
     * @param array $xmlHookRender Array for xml hooks that contains all lang to add
     */
    private function addMenuEntry(array $xmlHookRender){
        $this->menuExtensionsHooks[$this->currentScannedExt][] = array(
            self::IDENTIFIER => (string)$xmlHookRender[self::IDENTIFIER],
            self::TRANSLATION => (string)$xmlHookRender[self::TRANSLATION]
        );
    }

    /**
     * Add lang entries in the class attributes for later use
     *
     * @param SimpleXMLElement $xmlElementHookRender Array for xml hooks that contains all lang to add
     */
    private function addLangEntries(SimpleXMLElement $xmlElementHookRender){
        foreach ($xmlElementHookRender as $value) {
            $this->languageExtensionsHooks[$this->currentScannedExt][] = (string)$value[0];
        }
    }

    /**
     * This method check if the extension have a hook xml file
     */
    private function haveHook($extLabel){
        return file_exists(EXT_DL_DIR.$extLabel.self::XML_HOOKS_FILE);
    }

    /**
     * @param type $lang identifier of the lang you want to extend.
     *
     * Possible values :
     * br_BR
     * cs_CZ
     * de_DE
     * en_GB
     * es_ES
     * fr_FR
     * it_IT
     * ja_JP
     * pl_PL
     * pt_PT
     * ru_RU
     * si_SI
     * tr_TR
     * ug_UY
     * uk_UA
     */
   public function addTranslation($extName){

        global $l;

        $currentLang = $_SESSION['OCS']['LANGUAGE'];
        $langFile = EXT_DL_DIR.$extName."/language/".$currentLang."/".$currentLang.".txt";

        if(file_exists($langFile)){
            $l->addExternalLangFile($langFile);
        }else{
            $langFile = EXT_DL_DIR.$extName."/language/en_GB/en_GB.txt";
            $l->addExternalLangFile($langFile);
        }
    }

    /**
     * @param String $mainMenuIdentifier identifier of the menu
     *
     * Get sub menu list for a menu
     */
    private function getSubMenu($mainMenuIdentifier){
        return $this->subMenuExtensionsHooks[$mainMenuIdentifier];
    }

    /**
     * Will generate MenuElement for each array entries.
     *
     * @param Array $menuDatas Array of values
     */
    public function generateMenuRenderer($menuDatas, $isSubMenu = false){

        global $l;

        $childrenArray = array();
        if(!$isSubMenu){
            $subMenusInfos = $this->generateMenuChildrensRenderer($menuDatas[self::IDENTIFIER]);
            if($subMenusInfos != false){
                $childrenArray = $subMenusInfos;
            }
        }

        if(!empty($childrenArray)){
            $menuElem = new MenuElem("g(".$menuDatas[self::TRANSLATION].")",$menuDatas[self::IDENTIFIER], $childrenArray);
        }else{
            $menuElem = new MenuElem("g(".$menuDatas[self::TRANSLATION].")",$menuDatas[self::IDENTIFIER]);
        }

        return $menuElem;
    }

    /**
     * Will generate MenuElement for each sub menus
     *
     * @param Array $menusArray Array of values
     */
    public function generateMenuChildrensRenderer($mainMenuIdentifier){
        $subMenus = $this->getSubMenu($mainMenuIdentifier);
        if(empty($subMenus)){
            return false;
        }else{
            $menusElemArray = array();
            foreach ($subMenus as $subMenusInfos) {
                for ($index = 0; $index < count($subMenusInfos); $index++) {
                    $menusElemArray[$subMenusInfos[$index][self::IDENTIFIER]] = $this->generateMenuRenderer($subMenusInfos[$index], true);
                }
            }
            return $menusElemArray;
        }
    }

    /**
     * This method will check if the menu is from an extension.
     *
     * @param $menuIdentifier
     *
     * @return boolean : if is in extDeclaredMenu array
     */
    public function isMenuFromExt($menuIdentifier){
        return array_key_exists($menuIdentifier, $this->extDeclaredMenu);
    }

    /**
     * This method will check if an extension add a menu to an existing menu
     *
     * @param $mainMenuUrl : Main menu url
     * @return boolean : true if ext have sub menu
     */
    public function haveExtSubMenu($mainMenuUrl){
        return array_key_exists($mainMenuUrl, $this->subMenuExtensionsHooks);
    }



}
