<?php
/**
 * kitRegistry
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 */

// Include config file
$config_path = '../../config.php';
if (!file_exists($config_path)) {
	$config_path = 'config.php';
	if (!file_exists($config_path)) {
		die('Missing Configuration File...');
	} 
}
require_once($config_path);
require_once(WB_PATH.'/modules/kit_registry/initialize.php');
require_once(WB_PATH.'/modules/kit_registry/class.frontend.php');

if (!isset($_GET['file']) || !is_numeric($_GET['file'])) {
	// access not allowed
	header($_SERVER['SERVER_PROTOCOL']." 403 Forbidden");
	exit('<p><i>kitRegistry:</i> <b>ACCESS DENIED!</b></p>');
}
$id = (int) $_GET['file'];

$where = array(dbKITregistryFiles::field_id => $id);
$file = array();
if (!$dbKITregistryFiles->sqlSelectRecord($where, $file)) {
	die($dbKITregistryFiles->getError());
}
if (count($file) < 1) {
	die(sprintf(reg_error_invalid_id, $id));
}
$file = $file[0];

if ($file[dbKITregistryFiles::field_status] != dbKITregistryFiles::status_active) {
	// Download nur fuer aktive Dateien!
	die(sprintf(reg_error_file_not_available, $dbKITregistryFiles->status_array[$file[dbKITregistryFiles::field_status]]));
}

// Dokumentenschutz pruefen
if ($file[dbKITregistryFiles::field_protect] == dbKITregistryFiles::protect_undefined) {
	// fuer dieses Dokument ist noch kein Schutz definiert, Abbruch...
	die(reg_error_file_no_protection_defined);
}
elseif ($file[dbKITregistryFiles::field_protect] == dbKITregistryFiles::protect_none) {
	// Datei ist fuer den oeffentlichen Download freigegeben
	$data = array(
		dbKITregistryFiles::field_download_count => $file[dbKITregistryFiles::field_download_count]+1,
		dbKITregistryFiles::field_download_last => date('Y-m-d H:i:s')
	);
	// Datensatz aktualisieren
	if (!$dbKITregistryFiles->sqlUpdateRecord($data, $where)) {
		die($dbKITregistryFiles->getError());
	}
	// start download
	header('Content-type: application/force-download');
	header('Content-Transfer-Encoding: Binary');
	header('Content-length: '.$file[dbKITregistryFiles::field_filesize]); 
	header('Content-disposition: attachment;filename="'.$file[dbKITregistryFiles::field_filename_registry].'"');
	readfile($file[dbKITregistryFiles::field_filepath_registry]);
	exit();						
}
else {
	// Berechtigung pruefen
	//if (!isset($_SESSION['kdl_aut'])) {
	if (false == (isset($_SESSION['kdl_pct']) && isset($_SESSION['kdl_aut']) && isset($_SESSION['kdl_usr']) && isset($_GET['file']))) {
		// Login Dialog aufrufen
		$login_id = $dbKITregistryCfg->getValue(dbKITregistryCfg::cfgRegistryDroplet);
		if ($login_id > 0) {
			$link = array(
				kitRegistry::request_action => kitRegistry::action_login,
				kitRegistry::request_file => $id
			);
			$registryTools->getPageLinkByPageID($login_id, $url, $link); 
			header('Location: '.$url);
			exit();
		}		
	} 
	//print_r($_SESSION);
	//exit('kkl');	
	// ... und berechtigt
	header('Content-type: application/force-download');
	header('Content-Transfer-Encoding: Binary');
	header('Content-length: '.$file[dbKITregistryFiles::field_filesize]); 
	header('Content-disposition: attachment;filename="'.$file[dbKITregistryFiles::field_filename_registry].'"');
	readfile($file[dbKITregistryFiles::field_filepath_registry]);
	exit();
}

?>