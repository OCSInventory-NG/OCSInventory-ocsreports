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
    public function addTranslation($lang){
        
    }
    
    /**
     * @param String $identifier identifier of the menu 
     * @param Integer $translationNumber name of the menu in the interface
     * 
     * Note : The addTranslationHook will be applied before 
     * so you can use translation added by the plugin itself
     */
    public function addMenu($identifier, $translationNumber){
        
    }
    
    /**
     * @param String $mainMenuIdentifier identifier of the menu 
     * @param Integer $translationNumber name of the menu in the interface
     * 
     * Note : The addTranslationHook will be applied before 
     * so you can use translation added by the plugin itself
     */
    public function addSubMenu($mainMenuIdentifier, $translationNumber){
        
    }
    

    
}