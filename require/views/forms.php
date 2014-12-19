<?php
//====================================================================================
// OCS INVENTORY REPORTS
// Copyleft Arthur Jaouen 2014 (arthur(at)factorfx(dot)com)
// Web: http://www.ocsinventory-ng.org
//
// This code is open source and may be copied and modified as long as the source
// code is always made freely available.
// Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
//====================================================================================

function show_form_field($data, $errors, $type, $name, $label, $options = array()) {
	if (isset($errors[$name]) and $errors[$name]) {
		echo '<div class="field field-has-errors field-'.$name.($options['field_class'] ? ' '.$options['field_class'] : '').'">';
		echo '<ul class="field-error-list">';
		
		foreach ($errors[$name] as $err) {
			echo '<li>'.$err.'</li>';
		}
		
		echo '</ul>';
	} else {
		echo '<div class="field field-'.$name.($options['field_class'] ? ' '.$options['field_class'] : '').'">';
	}

	if (isset($data[$name]) and $data[$name]) {
		$options['value'] = $data[$name];
	}
	
	if ($label) {
		show_form_label($name, $label.' :');
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
	
	echo '<label '.attrs_to_html($attrs).'>'.$label.'</label>';
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
	
	if ($options['type'] == 'checkbox' and $options['value'] == 'on') {
		$attrs['checked'] = 'checked';
	} else if ($options['value']) {
		$attrs['value'] = $options['value'];
	}
	
	echo '<input '.attrs_to_html($attrs).'>';
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
	
	echo '<textarea '.attrs_to_html($attrs).'>';
	if ($options['value']) echo $options['value'];
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
		echo '<select '.attrs_to_html($attrs).'>';
	} else if ($options['type'] == 'radio') {
		echo '<div class="radio-container">';
	}
	
	foreach ($options['options'] as $key => $opt) {
		if ($options['type'] == 'select') {
			if ($options['value'] and $options['value'] == $key) {
				echo '<option value="'.$key.'" selected="selected">'.$opt.'</option>';
			} else {
				echo '<option value="'.$key.'">'.$opt.'</option>';
			}
		} else if ($options['type'] == 'radio') {
			$id = $name.'_'.$key;
			$input_attrs = array_merge($attrs, array(
				'id' => $id,
			));
			if ($options['value'] and $options['value'] == $key) {
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
	echo '<input type="submit" name="'.$name.'" id="'.$name.'" value="'.$label.'"/>';
}

function attrs_to_html($attrs) {
	$html_attrs = array();
	
	foreach ($attrs as $key => $val) {
		if (is_array($val)) {
			$html_attrs []= $key.'="'.implode(' ', $val).'"';
		} else {
			$html_attrs []= $key.'="'.$val.'"';
		}
	}
	
	return implode(' ', $html_attrs);
}

?>