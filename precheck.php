<?php

/**
 * kitRegistry
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2011 - 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
  if (defined('LEPTON_VERSION'))
    include(WB_PATH.'/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root.'/framework/class.secure.php')) {
    include($root.'/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

// Checking Requirements
global $database;

$checked = true;

// check PHP version
$PRECHECK['PHP_VERSION'] = array(
		'VERSION' => '5.2.0',
		'OPERATOR' => '>='
);

// modified precheck array
$check = array(
		'dbConnect_LE' => array(
		'directory' => 'dbconnect_le',
		'version' => '0.70',
		'problem' => 'dbConnect_LE => <b><a href="https://addons.phpmanufaktur.de/download.php?file=dbConnect_LE" target="_blank">Download actual version</a></b>'
		),
		'Dwoo' => array(
			'directory' => 'dwoo',
			'version' => '0.14',
			'problem' => 'Dwoo => <b><a href="https://addons.phpmanufaktur.de/download.php?file=Dwoo" target="_blank">Download actual version</a></b>'
		),
		'KeepInTouch' => array(
			'directory' => 'kit',
			'version' => '0.61',
			'problem' => 'KeepInTouch => <b><a href="https://addons.phpmanufaktur.de/download.php?file=KeepInTouch" target="_blank">Download actual version</a></b>'
			),
		'kitDirList' => array(
			'directory' => 'kit_dirlist',
			'version' => '0.29',
			'problem' => 'kitDirList => <b><a href="https://addons.phpmanufaktur.de/download.php?file=kitDirList" target="_blank">Download actual version</a></b>'
			)
);

$versionSQL = "SELECT `version` FROM `".TABLE_PREFIX."addons` WHERE `directory`='%s'";

foreach ($check as $name => $addon) {
	// loop throug the addons and check the versions
	$version = $database->get_one(sprintf($versionSQL, $addon['directory']), MYSQL_ASSOC);
	if (false === ($status = version_compare(!empty($version) ? $version : '0', $addon['version'], '>='))) {
		$checked = false;
		$key = $addon['problem'];
	}
	else
		$key = $name;
	$PRECHECK['CUSTOM_CHECKS'][$key] = array(
			'REQUIRED' => $addon['version'],
			'ACTUAL' => !empty($version) ? $version : '- not installed -',
			'STATUS' => $status
	);
}

// check default charset
$SQL = "SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='default_charset'";
$charset = $database->get_one($SQL, MYSQL_ASSOC);
if ($charset != 'utf-8') {
	$checked = false;
	$key = 'This addon needs UTF-8 as default charset!';
}
else
	$key = 'UTF-8';

$PRECHECK['CUSTOM_CHECKS'][$key] = array(
		'REQUIRED' => 'utf-8',
		'ACTUAL' => $charset,
		'STATUS' => ($charset == 'utf-8')
);

if (!$checked) {
	// if a problem occured prompt a hint and grant that the LEPTON/WB precheck fail
	$PRECHECK['CUSTOM_CHECKS']['Please install or update all required addons.<br />Need help? Please contact the <b><a href="https://phpmanufaktur.de/support" target="_blank">phpManufaktur Support Group</a></b>.'] = array(
			'REQUIRED' => 'OK',
			'ACTUAL' => 'PROBLEM',
			'STATUS' => false
	);
}



/*
// Checking Requirements

$PRECHECK['PHP_VERSION'] = array('VERSION' => '5.2.0', 'OPERATOR' => '>=');
$PRECHECK['WB_ADDONS'] = array(
	'dbconnect_le'	=> array('VERSION' => '0.70', 'OPERATOR' => '>='),
	'dwoo' => array('VERSION' => '0.14', 'OPERATOR' => '>='),
	'droplets' => array('VERSION' => '1.0', 'OPERATOR' => '>='),
	'kit' => array('VERSION' => '0.61', 'OPERATOR' => '>='),
	'kit_dirlist' => array('VERSION' => '0.29', 'OPERATOR' => '>=')
);

global $database;
$sql = "SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='default_charset'";
$result = $database->query($sql);
if ($result) {
	$data = $result->fetchRow(MYSQL_ASSOC);
	$PRECHECK['CUSTOM_CHECKS'] = array(
		'Default Charset' => array(
			'REQUIRED' => 'utf-8',
			'ACTUAL' => $data['value'],
			'STATUS' => ($data['value'] === 'utf-8')
		)
	);
}


?> */