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

$module_directory     = 'kit_registry';
$module_name          = 'kitRegistry';
$module_function      = 'tool';
$module_version       = '0.12';
$module_status        = 'Beta';
$module_platform      = '2.8';
$module_author        = 'Ralf Hertsch, Berlin (Germany)';
$module_license       = 'MIT License (MIT)';
$module_description   = 'PDF/DOC Registry for usage with KeepInTouch (KIT)';
$module_home          = 'http://phpmanufaktur.de';
$module_guid          = '9899D0DA-283E-4C00-B475-4950F8320B3D';

?>