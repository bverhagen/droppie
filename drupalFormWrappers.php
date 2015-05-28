<?
require_once realpath(dirname(__FILE__) . "/cloudService.php");

function attachDrupalTextField(&$form, $key, $title, $defaultValue = NULL, $maxLength = 255) {
	$newFormValue = &DroppieDefines::get($form, $key);
	$newFormValue = getDrupalTextField($key, $title, $defaultValue, $maxLength);
}

function getDrupalTextField($key, $title, $defaultValue = NULL, $maxLength = 255) {
	return array (
			'#type' => 'textfield',
			'#maxlength' => $maxLength,
			'#title' => $title,
			'#default_value' => DroppieDefines::getValue($key, $defaultValue)
		);
}

function attachDrupalCheckBox(&$form, $key, $title, $defaultValue = false) {
	$newFormValue = &DroppieDefines::get($form, $key);
	$newFormValue = getDrupalCheckBox($key, $title, $defaultValue, $maxLength);
}

function getDrupalCheckBox($key, $title, $defaultValue = false) {
	return array (
		'#type' => 'checkbox',
		'#title' => $title,
		'#default_value' => DroppieDefines::getValue($key, $defaultValue)
	);
}

function attachDrupalFieldSet(&$form, $key, $title, $collapsed = true) {
	$newFormValue = &DroppieDefines::get($form, $key);
	$newFormValue = getDrupalFieldSet($title, $collapsed);
}

function getDrupalFieldSet($title, $collapsed = true) {
	return array (
		'#type' => 'fieldset',
		'#collapsible' => TRUE,
		'#collapsed' => $collapsed,
		'#title' => $title,
		'#tree' => TRUE
	);
}

function attachDrupalSelect(&$form, $key, $title, $options = array(), $defaultValue = NULL) {
	$newFormValue = &DroppieDefines::get($form, $key);
	$newFormValue = getDrupalSelect($key, $title, $options, $defaultValue);
}

function getDrupalSelect($key, $title, $options = array(), $defaultValue = NULL) {
	return array(
		'#type' => 'select',
		'#title' => $title,
		'#default_value' => DroppieDefines::getValue($key, $defaultValue),
		'#options' => $options,
	);
}
?>
