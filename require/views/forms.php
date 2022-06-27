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

function show_form_field($data, $errors, $type, $name, $label, $options = array()) {
    $id = str_replace(array('[', ']'), '_', $name);

    if (isset($errors[$id]) && $errors[$id]) {
        echo '<div class="field field-has-errors field-' . htmlspecialchars($id .($options['field_class'] ? ' ' . $options['field_class'] : '')) . '">';
        echo '<ul class="field-error-list">';

        foreach ($errors[$id] as $err) {
            echo '<li>' . htmlspecialchars($err) . '</li>';
        }

        echo '</ul>';
    } else {
        $field_class = $options['field_class'] ?? null;
        echo '<div class="field field-' . htmlspecialchars($id .($field_class ? ' ' . $field_class : '')) . '">';
    }

    if (is_defined($data[$name])) {
        $options['value'] = $data[$name];
    }

    if ($label) {
        show_form_label($name, $label . ' :');
    }

    switch ($type) {
        case 'input':
            show_form_input($name, $options);
            break;
        case 'textarea':
            show_form_textarea($name, $options);
            break;
        case 'select':
            show_form_select($name, $options);
            break;
    }

    echo '</div>';
}

function show_form_label($name, $label, $options = array()) {
    $options = array_merge(array(
        'attrs' => array()
            ), $options);

    $attrs = array_merge(array(
        'for' => str_replace(array('[', ']'), '_', $name)
            ), $options['attrs']);

    echo '<label ' . attrs_to_html($attrs) . '>' . htmlspecialchars($label) . '</label>';
}

function show_form_input($name, $options = array()) {
    $options = array_merge(array(
        'value' => null,
        'type' => 'text',
        'attrs' => array()
            ), $options);

    $attrs = array_merge(array(
        'type' => $options['type'],
        'name' => $name,
        'id' => str_replace(array('[', ']'), '_', $name)
            ), $options['attrs']);

    if ($options['type'] == 'checkbox' && $options['value'] == 'on') {
        $attrs['checked'] = 'checked';
    } else if ($options['value']) {
        $attrs['value'] = $options['value'];
    }

    echo '<input ' . attrs_to_html($attrs) . '>';
}

function show_form_textarea($name, $options = array()) {
    $options = array_merge(array(
        'value' => null,
        'attrs' => array()
            ), $options);

    $attrs = array_merge(array(
        'name' => $name,
        'id' => str_replace(array('[', ']'), '_', $name)
            ), $options['attrs']);

    echo '<textarea ' . attrs_to_html($attrs) . '>';
    if ($options['value']) {
        echo htmlspecialchars($options['value']);
    }
    echo '</textarea>';
}

function show_form_select($name, $options = array()) {
    $options = array_merge(array(
        'type' => 'select',
        'value' => null,
        'options' => array(),
        'attrs' => array(),
        'newline' => false
            ), $options);

    $attrs = array_merge(array(
        'name' => $name,
        'id' => str_replace(array('[', ']'), '_', $name)
            ), $options['attrs']);

    if ($options['type'] == 'select') {
        echo '<select ' . attrs_to_html($attrs) . '>';
    } else if ($options['type'] == 'radio') {
        echo '<div class="radio-container">';
    }

    foreach ($options['options'] as $key => $opt) {
        if ($options['type'] == 'select') {
            if ($options['value'] && $options['value'] == $key) {
                echo '<option value="' . htmlspecialchars($key) . '" selected="selected">' . htmlspecialchars($opt) . '</option>';
            } else {
                echo '<option value="' . htmlspecialchars($key) . '">' . htmlspecialchars($opt) . '</option>';
            }
        } else if ($options['type'] == 'radio') {
            $id = $name . '_' . $key;
            $input_attrs = array_merge($attrs, array(
                'id' => $id,
            ));
            if ($options['value'] && $options['value'] == $key) {
                $input_attrs['checked'] = 'checked';
            }

            show_form_input($name, array(
                'type' => 'radio',
                'value' => $key,
                'attrs' => $input_attrs
            ));
            show_form_label($id, $opt);

            if ($options['newline']) {
                echo '<br/>';
            }
        }
    }

    if ($options['type'] == 'select') {
        echo '</select>';
    } else if ($options['type'] == 'radio') {
        echo '</div>';
    }
}

function show_form_submit($name, $label) {
    echo '<input type="submit" name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars(str_replace(array('[', ']'), '_', $name)) . '" value="' . htmlspecialchars($label) . '"/>';
}

function attrs_to_html($attrs) {
    $html_attrs = array();

    foreach ($attrs as $key => $val) {
        if (is_array($val)) {
            $html_attrs [] = htmlspecialchars($key) . '="' . htmlspecialchars(implode(' ', $val)) . '"';
        } else {
            $html_attrs [] = htmlspecialchars($key) . '="' . htmlspecialchars($val) . '"';
        }
    }

    return implode(' ', $html_attrs);
}

?>