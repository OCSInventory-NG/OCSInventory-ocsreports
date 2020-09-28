<?php
/*
 * Copyright 2005-2020 OCSInventory-NG/OCSInventory-ocsreports contributors.
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
  * Class for PackageBuilderFormOptions
  */
class PackageBuilderFormOptions
{
    private $packageBuilderParseXml;

    function __construct($packageBuilderParseXml) {
        $this->packageBuilderParseXml = $packageBuilderParseXml;
    }

    /**
     *  Generate Options
     */
    public function generateOptions($optionInfos, $l) {
        $html = "";
        
        foreach($optionInfos->formoption as $formblock) {
            foreach($formblock as $formblockDetails) {
                $html .= '  <div class="form-group">
                                <label class="control-label col-sm-2" for="'.$formblockDetails->id.'">'.$l->g(intval($formblockDetails->label)).'</label>
                                <div class="col-sm-9">';
                $html .= $this->generateField($formblockDetails, $l);
                $html .= '</div></div>';
            }
        }

        return $html;
    }

    /**
     *  Generate Option's field
     */
    private function generateField($formblockDetails, $l) {
        switch($formblockDetails->type) {
            case 'select':
                $select = '<select name="'.$formblockDetails->id.'" id="'.$formblockDetails->id.'" class="form-control">';
                foreach($formblockDetails->options as $options) {
                    foreach($options as $option) {
                        $select .= '<option value="'.$option->id.'">'.$l->g(intval($option->name)).'</option>';
                    } 
                }
                $select .= '</select>';
                return $select;
            break;

            case 'code':
                return '<div class="editor__body">
                            <div id="editorCode" class="editor__code"></div>
                        </div>
                        <script>
                            let codeEditor = ace.edit("editorCode", {
                                mode: "ace/mode/'.$formblockDetails->language.'",
                                selectionStyle: "text"
                            });
                            
                            // use setOptions method to set several options at once
                            codeEditor.setOptions({
                                autoScrollEditorIntoView: true,
                                copyWithEmptySelection: true,
                            });
                            
                            codeEditor.setTheme("ace/theme/solarized_dark");
                        </script>';
            break;

            default:
                return '<input type="'.$formblockDetails->type.'" name="'.$formblockDetails->id.'" id="'.$formblockDetails->id.'" value="'.$formblockDetails->defaultvalue.'" class="form-control">';
        }
    }

}