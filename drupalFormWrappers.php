<?
require_once realpath(dirname(__FILE__) . "/cloudService.php");

function attachDrupalTextField(&$form, $cloudService, $key, $title, $defaultValue = NULL, $maxLength = 255) {
	$newFormValue = &$cloudService->get($form, $key);
	$newFormValue = getDrupalTextField($cloudService, $key, $title, $defaultValue, $maxLength);
}

function getDrupalTextField($cloudService, $key, $title, $defaultValue = NULL, $maxLength = 255) {
	return array (
			'#type' => 'textfield',
			'#maxlength' => $maxLength,
			'#title' => $title,
			'#default_value' => $cloudService->getValue($key, $defaultValue)
		);
}

function attachDrupalCheckBox(&$form, $cloudService, $key, $title, $defaultValue = false) {
	$newFormValue = &$cloudService->get($form, $key);
	$newFormValue = getDrupalCheckBox($cloudService, $key, $title, $defaultValue, $maxLength);
}

function getDrupalCheckBox($cloudService, $key, $title, $defaultValue = false) {
	return array (
		'#type' => 'checkbox',
		'#title' => $title,
		'#default_value' => $cloudService->getValue($key, $defaultValue)
	);
}

function attachDrupalFieldSet(&$form, $cloudService, $key, $title, $collapsed = true) {
	$newFormValue = &$cloudService->get($form, $key);
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

function attachDrupalSelect(&$form, $cloudService, $key, $title, $options = array(), $defaultValue = NULL) {
	$newFormValue = &$cloudService->get($form, $key);
	$newFormValue = getDrupalSelect($cloudService, $key, $title, $options, $defaultValue);
}

function getDrupalSelect($cloudService, $key, $title, $options = array(), $defaultValue = NULL) {
	return array(
		'#type' => 'select',
		'#title' => $title,
		'#default_value' => $cloudService->getValue($key, $defaultValue),
		'#options' => $options,
	);
}
?>
