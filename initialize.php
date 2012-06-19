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

// for extended error reporting set to true!
if (!defined('KIT_DEBUG')) define('KIT_DEBUG', true);

function kit_registry_error_handler($level, $message, $file, $line) {
	switch ($level):
		case 1:			$type = 'E_ERROR'; break;
		case 2:			$type = 'E_WARNING'; break;
		case 4:			$type = 'E_PARSE'; break;
		case 8:			$type = 'E_NOTICE'; break;
		case 16: 		$type = 'E_CORE_ERROR';	break;
		case 32: 		$type = 'E_CORE_WARNING';	break;
		case 64: 		$type = 'E_COMPILE_ERROR'; break;
		case 128:		$type = 'E_COMPILE_WARNING'; break;
		case 256:		$type = 'E_USER_ERROR';	break;
		case 512:		$type = 'E_USER_WARNING';	break;
		case 1024: 	$type = 'E_USER_NOTICE'; break;
		case 2047: 	$type = 'E_ALL'; break;
		case 2048: 	$type = 'E_STRICT';	break;
		default:		$type = $level;	break;
	endswitch;
	echo sprintf(	'<div style="margin:5px 15px;padding:10px;border:1px solid #000;color:#000;background-color:#ffd;">'.
								'<table width="99%%"><colgroup><col width="120" /><col width="*" /></colgroup>'.
								'<tr><td>Type</td><td style="font-weight:bold;color:red;">%s</td></tr><tr><td>Message</td><td style="color:red;">%s</td></tr>'.
								'<tr><td style="vertical-align:top;">Line:File</td><td><b>%s</b> : <i>%s</i></td></tr></table></div>',
								$type, $message, $line, $file);
}
if (KIT_DEBUG == true) {
	// Prompt all errors and use own error_handler
	ini_set('error_reporting', E_ALL);
	set_error_handler("kit_registry_error_handler");
}

// include language file
if(!file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.php'); // Vorgabe: DE verwenden
	if (!defined('KIT_REGISTRY_LANGUAGE')) define('KIT_REGISTRY_LANGUAGE', 'DE'); // die Konstante gibt an in welcher Sprache KIT Registry aktuell arbeitet
}
else {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php');
	if (!defined('KIT_REGISTRY_LANGUAGE')) define('KIT_REGISTRY_LANGUAGE', LANGUAGE); // die Konstante gibt an in welcher Sprache KIT Registry aktuell arbeitet
}

if (!class_exists('dbconnectle')) 				require_once(WB_PATH.'/modules/dbconnect_le/include.php');
if (!class_exists('Dwoo')) 								require_once(WB_PATH.'/modules/dwoo/include.php');
if (!class_exists('kitContactInterface')) require_once(WB_PATH.'/modules/kit/class.interface.php');

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tools.php');
require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.registry.php');

global $parser;
global $dbKITregistryFiles;
global $dbKITregistryCfg;
global $registryTools;
global $dbKITregistryGroups;

if (!is_object($parser)) $parser = new Dwoo();
if (!is_object($dbKITregistryFiles)) $dbKITregistryFiles = new dbKITregistryFiles();
if (!is_object($dbKITregistryCfg)) $dbKITregistryCfg = new dbKITregistryCfg();
if (!is_object($registryTools)) $registryTools = new kitRegistryTools();
if (!is_object($dbKITregistryGroups)) $dbKITregistryGroups = new dbKITregistryGroups();

?>