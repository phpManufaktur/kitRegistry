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

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die('invalid call of '.$_SERVER['SCRIPT_NAME']);

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php');
require_once(WB_PATH.'/framework/functions.php');

class registryBackend {
	
	const request_action						= 'act';
	const request_items							= 'its';
	const request_tab								= 'tab';
	const request_file							= 'fupl';
	
	const action_about							= 'abt';
	const action_config							= 'cfg';
	const action_config_check				= 'cfgc';
	const action_default						= 'def';
	const action_group							= 'grp';
	const action_group_check				= 'grpc';
	const action_edit								= 'edt';
	const action_edit_check					= 'edtc';
	const action_list								= 'lst';
	const action_replicate					= 'rpl';
	
	private $tab_navigation_array = array(
		self::action_list								=> reg_tab_list,
		self::action_edit								=> reg_tab_edit,
		self::action_group							=> reg_tab_group,
		self::action_config							=> reg_tab_config,
		self::action_about							=> reg_tab_about
	);
	
	private $page_link 					= '';
	private $img_url						= '';
	private $template_path			= '';
	private $error							= '';
	private $message						= '';
	private $registry_path			= '';
	
	public function __construct() {
		$this->page_link = ADMIN_URL.'/admintools/tool.php?tool=kit_registry';
		$this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/htt/' ;
		$this->img_url = WB_URL. '/modules/'.basename(dirname(__FILE__)).'/images/';
		date_default_timezone_set(reg_cfg_time_zone);
		$this->registry_path = WB_PATH.MEDIA_DIRECTORY.'/kit_protected/registry/';
	} // __construct()
	
	/**
    * Set $this->error to $error
    * 
    * @param STR $error
    */
  public function setError($error) {
  	$dbg = debug_backtrace();
    $caller = next($dbg);
  	$this->error = sprintf('[%s::%s - %s] %s', basename($caller['file']), $caller['function'], $caller['line'], $error);
  } // setError()

  /**
    * Get Error from $this->error;
    * 
    * @return STR $this->error
    */
  public function getError() {
    return $this->error;
  } // getError()

  /**
    * Check if $this->error is empty
    * 
    * @return BOOL
    */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError

  /**
   * Reset Error to empty String
   */
  public function clearError() {
  	$this->error = '';
  }

  /** Set $this->message to $message
    * 
    * @param STR $message
    */
  public function setMessage($message) {
    $this->message = $message;
  } // setMessage()

  /**
    * Get Message from $this->message;
    * 
    * @return STR $this->message
    */
  public function getMessage() {
    return $this->message;
  } // getMessage()

  /**
    * Check if $this->message is empty
    * 
    * @return BOOL
    */
  public function isMessage() {
    return (bool) !empty($this->message);
  } // isMessage
  
  /**
   * Return Version of Module
   *
   * @return FLOAT
   */
  public function getVersion() {
    // read info.php into array
    $info_text = file(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.php');
    if ($info_text == false) {
      return -1; 
    }
    // walk through array
    foreach ($info_text as $item) {
      if (strpos($item, '$module_version') !== false) {
        // split string $module_version
        $value = explode('=', $item);
        // return floatval
        return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
      } 
    }
    return -1;
  } // getVersion()
  
  public function getTemplate($template, $template_data) {
  	global $parser;
  	try {
  		$result = $parser->get($this->template_path.$template, $template_data); 
  	} catch (Exception $e) {
  		$this->setError(sprintf(reg_error_template_error, $template, $e->getMessage()));
  		return false;
  	}
  	return $result;
  } // getTemplate()
  
  
  /**
   * Verhindert XSS Cross Site Scripting
   * 
   * @param REFERENCE $_REQUEST Array
   * @return $request
   */
	public function xssPrevent(&$request) { 
  	if (is_string($request)) {
	    $request = html_entity_decode($request);
	    $request = strip_tags($request);
	    $request = trim($request);
	    $request = stripslashes($request);
  	}
	  return $request;
  } // xssPrevent()
	
  public function action() {
  	$html_allowed = array();
  	foreach ($_REQUEST as $key => $value) {
  		if (!in_array($key, $html_allowed)) {
  			$_REQUEST[$key] = $this->xssPrevent($value);	  			
  		} 
  	}
    isset($_REQUEST[self::request_action]) ? $action = $_REQUEST[self::request_action] : $action = self::action_default;
  	switch ($action):
  	case self::action_about:
  		$this->show(self::action_about, $this->dlgAbout());
  		break;
  	case self::action_replicate:
  		$this->show(self::action_list, $this->actionReplicate());
  		break;
  	case self::action_edit:
  		$this->show(self::action_edit, $this->dlgEdit());
  		break;
  	case self::action_edit_check:
  		$this->show(self::action_edit, $this->checkEdit());
  		break;
  	case self::action_config:
  		$this->show(self::action_config, $this->dlgConfig());
  		break;
  	case self::action_group:
  		$this->show(self::action_group, $this->dlgGroups());
  		break;
  	case self::action_group_check:
  		$this->show(self::action_group, $this->checkGroupEdit());
  		break;
  	case self::action_config_check:
  		$this->show(self::action_config, $this->checkConfig());
  		break;
  	case self::action_list:
  	default:
  		$this->show(self::action_list, $this->dlgRegistryList());
  		break;
  	endswitch;
  } // action
	
  	
  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste
   * 
   * @param $action - aktives Navigationselement
   * @param $content - Inhalt
   * 
   * @return ECHO RESULT
   */
  public function show($action, $content) {
  	$navigation = array();
  	foreach ($this->tab_navigation_array as $key => $value) {
  		$navigation[] = array(
  			'active' 	=> ($key == $action) ? 1 : 0,
  			'url'			=> sprintf('%s&%s=%s', $this->page_link, self::request_action, $key),
  			'text'		=> $value
  		);
  	}
  	$data = array(
  		'WB_URL'			=> WB_URL,
  		'navigation'	=> $navigation,
  		'error'				=> ($this->isError()) ? 1 : 0,
  		'content'			=> ($this->isError()) ? $this->getError() : $content
  	);
  	echo $this->getTemplate('backend.body.htt', $data);
  } // show()

  public function dlgAbout() {
  	$data = array(
  		'version'					=> sprintf('%01.2f', $this->getVersion()),
  		'img_url'					=> $this->img_url.'/kit_registry_logo_450_294.jpg',
  		'release_notes'		=> file_get_contents(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.txt'),
  	);
  	return $this->getTemplate('backend.about.htt', $data);
  } // dlgAbout()
  
  public function dlgRegistryList() {
  	global $dbKITregistryFiles;
  	global $dbKITregistryCfg;
  	global $registryTools;
  	
  	$tab_list = $dbKITregistryCfg->getValue(dbKITregistryCfg::cfgRegistryListTabs);
  	
  	$tab = (isset($_REQUEST[self::request_tab])) ? $_REQUEST[self::request_tab] : $tab_list[0];
  	$SQL = sprintf(	"SELECT * FROM %s WHERE %s='%s' AND %s!='%s' ORDER BY %s",
  									$dbKITregistryFiles->getTableName(),
  									dbKITregistryFiles::field_sub_dir,
  									($tab == 'special') ? '#' : $tab,
  									dbKITregistryFiles::field_status,
  									dbKITregistryFiles::status_deleted,
  									dbKITregistryFiles::field_filename_registry);
  	if (!$dbKITregistryFiles->sqlExec($SQL, $files)) {
  		$this->setError($dbKITregistryFiles->getError());
  		return false;
  	}
  	$items = array();
  	foreach ($files as $file) {
  		$items[] = array(
  			'id'						=> $file[dbKITregistryFiles::field_id],
  			'link'					=> sprintf('%s&amp;%s=%s&amp;%s=%s', $this->page_link, self::request_action, self::action_edit, dbKITregistryFiles::field_id, $file[dbKITregistryFiles::field_id]),
  			'name_registry'	=> $file[dbKITregistryFiles::field_filename_registry],
  			'name_original'	=> $file[dbKITregistryFiles::field_filename_original],
  			'group'					=> '',
  			'status'				=> $dbKITregistryFiles->status_array[$file[dbKITregistryFiles::field_status]],
  			'filemtime'			=> $file[dbKITregistryFiles::field_filemtime],
  			'file_datetime'	=> date(reg_cfg_datetime_str, $file[dbKITregistryFiles::field_filemtime]),
  			'filesize'			=> $registryTools->bytes2Str($file[dbKITregistryFiles::field_filesize])		
  		);
  	}
  	
  	$header = array(
  		'id'							=> reg_th_id,
  		'filename'				=> reg_th_filename,
  		'group'						=> reg_th_group,
  		'status'					=> reg_th_status,
  		'filemtime'				=> reg_th_filemtime,
  		'filesize'				=> reg_th_filesize
  	);
  	
  	$data = array(
  		'head'							=> reg_header_registry_list,
  		'intro'							=> ($this->isMessage()) ? $this->getMessage() : reg_intro_registry_list,
  		'is_message'				=> ($this->isMessage()) ? 1 : 0,
  		'header'						=> $header,
  		'form_action'				=> $this->page_link,
  		'page_link'					=> $this->page_link,
  		'edit_name'					=> self::action_edit,
  		'action_name'				=> self::request_action,
  		'action_replicate'	=> self::action_replicate,
  		'text_replicate'		=> reg_text_replicate,
  		'btn_replicate'			=> reg_btn_replicate,
  		'tab_list'					=> $tab_list,
  		'tab_active'				=> $tab,
  		'tab_name'					=> self::request_tab,
  		'files'							=> $items,
  		'msg_tab_empty'			=> reg_msg_tab_empty
  	);
  	return $this->getTemplate('backend.registry.list.htt', $data);
  } // dlgRegistryList()
  
  /**
   * Das Registry Verzeichni scannen und mit der Datenbank abgleichen
   * 
   * @param STR $directory
   */
  private function scan_registry_dir($directory, &$message = '') {
  	global $dbKITregistryFiles;
  	global $dbKITregistryCfg;
  	
  	$sub_dirs = $dbKITregistryCfg->getValue(dbKITregistryCfg::cfgRegistryListTabs);
  	
    $handle =  opendir($directory); 
    while ($file = readdir($handle)) { 
      if ($file != "." && $file != "..") { 
        if (is_dir($directory.$file)) {  
          // Erneuter Funktionsaufruf, um das aktuelle Verzeichnis auszulesen
          $this->scan_registry_dir($directory.$file.'/'); 
        }
        else { 
          // Wenn Verzeichnis-Eintrag eine Datei ist, diese ausgeben
          $actual_file = page_filename(utf8_encode($file));
          $actual_file = $directory.$actual_file;
          $where = array(dbKITregistryFiles::field_filepath_registry => $actual_file);
          $data = array();
          if (!$dbKITregistryFiles->sqlSelectRecord($where, $data)) {
          	$this->setError($dbKITregistryFiles->getError());
          	return false;
          }
          if (count($data) > 0) {
          	// Datensatz existiert
          	$data = $data[0];
          	$update = array();
          	// Vergleichen, ob sich etwas veraendert hat
          	if (filemtime($actual_file) != $data[dbKITregistryFiles::field_filemtime]) {
          		$update[dbKITregistryFiles::field_filemtime] = filemtime($actual_file);
          	}
          	if (filesize($actual_file) != $data[dbKITregistryFiles::field_filesize]) {
          		$update[dbKITregistryFiles::field_filesize] = filesize($actual_file);
          	}
          	if ($data[dbKITregistryFiles::field_status] == dbKITregistryFiles::status_deleted) {
          		$update[dbKITregistryFiles::field_status] = dbKITregistryFiles::status_active;
          		$message .= sprintf(reg_msg_registry_file_undeleted, $data[dbKITregistryFiles::field_filename_registry]);
          	}
          	if (count($update) > 0) { 
          		$where = array(dbKITregistryFiles::field_id => $data[dbKITregistryFiles::field_id]);
          		if (!$dbKITregistryFiles->sqlUpdateRecord($update, $where)) {
          			$this->setError($dbKITregistryFiles->getError());
          			return false;
          		}
          		$message .= sprintf(reg_msg_registry_file_updated, $data[dbKITregistryFiles::field_filename_registry]);
          	}
          }
          else {
          	// es existiert noch kein Eintrag
          	if (!file_exists($actual_file)) {
          		// Datei muss noch umbenannt werden
          		if (!rename($directory.$file, $actual_file)) {
          			$this->setError(sprintf(reg_error_rename_file, basename($directory.$file), basename($actual_file)));
          			return false;
          		}
          		$message .= sprintf(reg_msg_registry_file_renamed, basename($directory.$file), basename($actual_file));
          	}
          	$sub_dir = substr(basename($actual_file), 0, 1);
          	if (!in_array($sub_dir, $sub_dirs)) $sub_dir = '#';
          	if (!file_exists($this->registry_path.$sub_dir)) {
          		if (!mkdir($this->registry_path.$sub_dir)) {
          			$this->setError(sprintf(reg_error_mkdir, $this->registry_path.$sub_dir));
          			return false;
          		}
          		$message .= sprintf(reg_msg_mkdir, '/'.$sub_dir);
          	}
          	$check_file = page_filename(utf8_encode($file));
          	$check_file = $this->registry_path.$sub_dir.'/'.$check_file;
          	// pruefen, ob sich die Datei im richtigen Verzeichnis befindet
          	if ($actual_file != $check_file) {
          		if (!rename($actual_file, $check_file)) {
          			$this->setError(sprintf(reg_error_rename_file, basename($actual_file), '/'.$sub_dir.'/'.basename($check_file)));
          			return false;
          		} 
          		$message .= sprintf(reg_msg_registry_file_moved, basename($actual_file), $sub_dir);
          		$actual_file = $check_file; 
          	}
          	$data = array(
          		dbKITregistryFiles::field_filename_original			=> utf8_encode(basename($directory.$file)),
          		dbKITregistryFiles::field_filename_registry			=> basename($actual_file),
          		dbKITregistryFiles::field_filepath_registry			=> $actual_file,
          		dbKITregistryFiles::field_filemtime							=> filemtime($actual_file),
          		dbKITregistryFiles::field_filesize							=> filesize($actual_file),
          		dbKITregistryFiles::field_filetype							=> pathinfo($actual_file, PATHINFO_EXTENSION),
          		dbKITregistryFiles::field_status								=> dbKITregistryFiles::status_active,
          		dbKITregistryFiles::field_sub_dir								=> $sub_dir
          	);
          	$id = -1;
          	if (!$dbKITregistryFiles->sqlInsertRecord($data, $id)) {
          		$this->setError($dbKITregistryFiles->getError());
          		return false;
          	}
          	$message .= sprintf(reg_msg_registry_file_added, $data[dbKITregistryFiles::field_filename_registry]);
          }
        } 
      }
    } 
    closedir($handle); 
  } // dir_rekursiv() 
  
  public function actionReplicate() {
  	$message = '';
  	if (!file_exists($this->registry_path)) {
  		if (mkdir($this->registry_path, 0755, true) == true) {
  			$message .= reg_msg_registry_mkdir; 
  		}
  		else {
  			$this->setError(reg_error_registry_mkdir);
  			return false;
  		}
  	}
  	
  	$this->scan_registry_dir($this->registry_path, $message);
  	
  	$this->setMessage($message);
  	return $this->dlgRegistryList();
  } // actionReplicate()
  
  public function dlgConfig() {
		global $dbKITregistryCfg;
		$SQL = sprintf(	"SELECT * FROM %s WHERE NOT %s='%s' ORDER BY %s",
										$dbKITregistryCfg->getTableName(),
										dbKITregistryCfg::field_status,
										dbKITregistryCfg::status_deleted,
										dbKITregistryCfg::field_name);
		$config = array();
		if (!$dbKITregistryCfg->sqlExec($SQL, $config)) {
			$this->setError($dbKITregistryCfg->getError());
			return false;
		}
		$count = array();
		$header = array(
			'identifier'	=> reg_header_cfg_identifier,
			'value'				=> reg_header_cfg_value,
			'description'	=> reg_header_cfg_description
		);
		
		$items = array();
		// bestehende Eintraege auflisten
		foreach ($config as $entry) {
			$id = $entry[dbKITregistryCfg::field_id];
			$count[] = $id;
			$value = (isset($_REQUEST[dbKITregistryCfg::field_value.'_'.$id])) ? $_REQUEST[dbKITregistryCfg::field_value.'_'.$id] : $entry[dbKITregistryCfg::field_value];
			$value = str_replace('"', '&quot;', stripslashes($value));
			$items[] = array(
				'id'					=> $id,
				'identifier'	=> constant($entry[dbKITregistryCfg::field_label]),
				'value'				=> $value,
				'name'				=> sprintf('%s_%s', dbKITregistryCfg::field_value, $id),
				'description'	=> constant($entry[dbKITregistryCfg::field_description])  
			);
		}
		$data = array(
			'form_name'						=> 'reg_cfg',
			'form_action'					=> $this->page_link,
			'action_name'					=> self::request_action,
			'action_value'				=> self::action_config_check,
			'items_name'					=> self::request_items,
			'items_value'					=> implode(",", $count), 
			'head'								=> reg_header_cfg,
			'intro'								=> $this->isMessage() ? $this->getMessage() : reg_intro_cfg,
			'is_message'					=> $this->isMessage() ? 1 : 0,
			'items'								=> $items,
			'btn_ok'							=> reg_btn_ok,
			'btn_abort'						=> reg_btn_abort,
			'abort_location'			=> $this->page_link,
			'header'							=> $header
		);
		return $this->getTemplate('backend.config.htt', $data);
	} // dlgConfig()
	
	/**
	 * Ueberprueft Aenderungen die im Dialog dlgConfig() vorgenommen wurden
	 * und aktualisiert die entsprechenden Datensaetze.
	 * 
	 * @return STR DIALOG dlgConfig()
	 */
	public function checkConfig() {
		global $dbKITregistryCfg;
		$message = '';
		// ueberpruefen, ob ein Eintrag geaendert wurde
		if ((isset($_REQUEST[self::request_items])) && (!empty($_REQUEST[self::request_items]))) {
			$ids = explode(",", $_REQUEST[self::request_items]);
			foreach ($ids as $id) {
				if (isset($_REQUEST[dbKITregistryCfg::field_value.'_'.$id])) {
					$value = $_REQUEST[dbKITregistryCfg::field_value.'_'.$id];
					$where = array();
					$where[dbKITregistryCfg::field_id] = $id; 
					$config = array();
					if (!$dbKITregistryCfg->sqlSelectRecord($where, $config)) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbKITregistryCfg->getError()));
						return false;
					}
					if (sizeof($config) < 1) {
						$this->setError(sprintf(reg_error_cfg_id, $id));
						return false;
					}
					$config = $config[0];
					if ($config[dbKITregistryCfg::field_value] != $value) {
						// Wert wurde geaendert
							if (!$dbKITregistryCfg->setValue($value, $id) && $dbKITregistryCfg->isError()) {
								$this->setError($dbKITregistryCfg->getError());
								return false;
							}
							elseif ($dbKITregistryCfg->isMessage()) {
								$message .= $dbKITregistryCfg->getMessage();
							}
							else {
								// Datensatz wurde aktualisiert
								$message .= sprintf(reg_msg_cfg_id_updated, $config[dbKITregistryCfg::field_name]);
							}
					}
				}
			}		
		}		
		$this->setMessage($message);
		return $this->dlgConfig();
	} // checkConfig()
  
	public function dlgEdit() {
		global $dbKITregistryFiles;
		global $registryTools;
		global $dbKITregistryGroups; 
		
		$id = isset($_REQUEST[dbKITregistryFiles::field_id]) ? $_REQUEST[dbKITregistryFiles::field_id] : -1;
		
		if ($id > 0) {
			// existierender Datensatz
			$where = array(dbKITregistryFiles::field_id => $id);
			$file = array();
			if (!$dbKITregistryFiles->sqlSelectRecord($where, $file)) {
				$this->setError($dbKITregistryFiles->getEngine()); return false;
			}
			if (count($file) < 1) {
				$this->setError(sprintf(reg_error_invalid_id, $id)); return false;
			}
			$file = $file[0];
		}
		else {
			// neuer Datensatz
			$file = $dbKITregistryFiles->getFields();
			$file[dbKITregistryFiles::field_status] = dbKITregistryFiles::status_active;
			$file[dbKITregistryFiles::field_filemtime] = -1;
			$file[dbKITregistryFiles::field_protect] = dbKITregistryFiles::protect_undefined;
		}
		foreach ($dbKITregistryFiles->getFields() as $key => $value) {
			switch ($key):
			case dbKITregistryFiles::field_content_groups:
				if (isset($_REQUEST[$key])) $file[$key] = implode(',', $_REQUEST[$key]);
				break;
			default:
				if (isset($_REQUEST[$key])) $file[$key] = $_REQUEST[$key];
			endswitch;
		}
		
		// maximale Uploadgroesse
		$post_max_size = $registryTools->convertBytes(ini_get('post_max_size'));
		$upload_max_filesize = $registryTools->convertBytes(ini_get('upload_max_filesize'));
		$max_size = ($post_max_size >= $upload_max_filesize) ? $upload_max_filesize : $post_max_size;
		$max_size = $registryTools->bytes2Str($max_size);
			
		// Status
		$status_array = array();
		foreach ($dbKITregistryFiles->status_array as $value => $text) {
			$status_array[] = array(
				'value'			=> $value,
				'text'			=> $text,
				'selected'	=> ($file[dbKITregistryFiles::field_status] == $value) ? 1 : 0
			);
		}
		
		// Protection
		$protect_array = array();
		foreach ($dbKITregistryFiles->protect_array as $value => $text) {
			$protect_array[] = array(
				'value'			=> $value,
				'text'			=> $text,
				'selected'	=> ($file[dbKITregistryFiles::field_protect] == $value) ? 1 : 0
			); 
		}
		
		// Content Groups
		$content_groups = explode(',', $file[dbKITregistryFiles::field_content_groups]);
		$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s'",
										$dbKITregistryGroups->getTableName(),
										dbKITregistryGroups::field_status,
										dbKITregistryGroups::status_active);
		$groups = array();
		if (!$dbKITregistryGroups->sqlExec($SQL, $groups)) {
			$this->setError($dbKITregistryGroups->getError()); return false;
		}
		$groups_array = array();
		$groups_array[] = array(
			'value'			=> '-1',
			'text'			=> reg_text_undetermined,
			'selected'	=> 0
		);
		foreach ($groups as $group) {
			$groups_array[] = array(
				'value'			=> $group[dbKITregistryGroups::field_group_id],
				'text'			=> $group[dbKITregistryGroups::field_group_name],
				'selected'	=> (in_array($group[dbKITregistryGroups::field_group_id], $content_groups)) ? 1 : 0
			);
		}
		
		
		$items = array(
			array(
				'label'		=> reg_label_id,
				'name'		=> dbKITregistryFiles::field_id,
				'value'		=> $id,
				'hint'		=> ''
			),
			array(
				'label'		=> reg_label_file_upload,
				'name'		=> self::request_file,
				'value'		=> '',
				'hint'		=> sprintf(reg_hint_file_upload, $max_size)
			),
			array(
				'label'		=> reg_label_filename_orginal,
				'name'		=> dbKITregistryFiles::field_filename_original,
				'value'		=> $file[dbKITregistryFiles::field_filename_original],
				'hint'		=> reg_hint_filename_original
			),
			array(
				'label'		=> reg_label_filename_registry,
				'name'		=> dbKITregistryFiles::field_filename_registry,
				'value'		=> $file[dbKITregistryFiles::field_filename_registry],
				'hint'		=> reg_hint_filename_registry
			),
			array(
				'label'		=> reg_label_filepath,
				'name'		=> dbKITregistryFiles::field_filepath_registry,
				'value'		=> str_replace(WB_PATH, '', $file[dbKITregistryFiles::field_filepath_registry]),
				'hint'		=> ''
			),
			array(
				'label'		=> reg_label_filemtime,
				'name'		=> dbKITregistryFiles::field_filemtime,
				'value'		=> ($file[dbKITregistryFiles::field_filemtime] > 0) ? date(reg_cfg_datetime_str, $file[dbKITregistryFiles::field_filemtime]) : '',
				'hint'		=> ''
			),
			array(
				'label'		=> reg_label_filesize,
				'name'		=> dbKITregistryFiles::field_filesize,
				'value'		=> ($file[dbKITregistryFiles::field_filesize] > 0) ? $registryTools->bytes2Str($file[dbKITregistryFiles::field_filesize]) : '',
				'hint'		=> ''
			),
			array(
				'label'		=> reg_label_downloads,
				'name'		=> dbKITregistryFiles::field_download_count,
				'value'		=> $file[dbKITregistryFiles::field_download_count],
				'hint'		=> ''
			),
			array(
				'label'		=> reg_label_download_last,
				'name'		=> dbKITregistryFiles::field_download_last,
				'value'		=> (strtotime($file[dbKITregistryFiles::field_download_last]) > 0) ? date(reg_cfg_datetime_str, strtotime($file[dbKITregistryFiles::field_download_last])) : '',
				'hint'		=> ''
			),
			array(
				'label'		=> reg_label_description,
				'name'		=> dbKITregistryFiles::field_description,
				'value'		=> $file[dbKITregistryFiles::field_description],
				'hint'		=> reg_hint_registry_file_description
			),
			array(
				'label'		=> reg_label_keywords,
				'name'		=> dbKITregistryFiles::field_keywords,
				'value'		=> $file[dbKITregistryFiles::field_keywords],
				'hint'		=> reg_hint_registry_file_keywords
			),
			array(
				'label'		=> reg_label_content,
				'name'		=> dbKITregistryFiles::field_content,
				'value'		=> $file[dbKITregistryFiles::field_content],
				'hint'		=> reg_hint_registry_file_content
			),
			array(
				'label'		=> reg_label_status,
				'name'		=> dbKITregistryFiles::field_status,
				'value'		=> $status_array,
				'hint'		=> ''
			),
			array(
				'label'		=> reg_label_protect_type,
				'name'		=> dbKITregistryFiles::field_protect,
				'value'		=> $protect_array,
				'hint'		=> reg_hint_registry_protect_type
			),
			array(
				'label'		=> reg_label_protect_groups,
				'name'		=> dbKITregistryFiles::field_protect_groups,
				'value'		=> $file[dbKITregistryFiles::field_protect_groups],
				'hint'		=> reg_hint_registry_protect_groups
			),
			array(
				'label'		=> reg_label_content_groups,
				'name'		=> dbKITregistryFiles::field_content_groups,
				'value'		=> $groups_array,
				'hint'		=> reg_hint_content_groups
			)
		);
		$data = array(
			'form_action'				=> $this->page_link,
			'action_name'				=> self::request_action,
			'action_value'			=> self::action_edit_check,
			'id_name'						=> dbKITregistryFiles::field_id,
			'id_value'					=> $id,
			'head'							=> reg_header_registry_edit,
			'intro'							=> $this->isMessage() ? $this->getMessage() : reg_intro_registry_edit,
			'is_message'				=> $this->isMessage() ? 1 : 0,
			'items'							=> $items,
			'btn_ok'						=> reg_btn_ok,
			'btn_abort'					=> reg_btn_abort,
			'abort_location'		=> $this->page_link				
		);
		return $this->getTemplate('backend.registry.edit.htt', $data);
	} // dlgEdit()
	
	public function checkEdit() {
		global $dbKITregistryFiles;
		global $dbKITregistryCfg;
		
		$message = '';
		$id = isset($_REQUEST[dbKITregistryFiles::field_id]) ? $_REQUEST[dbKITregistryFiles::field_id] : -1;
		
		if ($id > 0) {
			// existierender Datensatz
			$where = array(dbKITregistryFiles::field_id => $id);
			$file = array();
			if (!$dbKITregistryFiles->sqlSelectRecord($where, $file)) {
				$this->setError($dbKITregistryFiles->getError()); return false;
			}
			if (count($file) < 1) {
				$this->setError(sprintf(reg_error_invalid_id, $id)); return false;
			}
			$file = $file[0];
		}
		else {
			// neuer Datensatz
			$file = $dbKITregistryFiles->getFields();
			$file[dbKITregistryFiles::field_status] = dbKITregistryFiles::status_active;
			$file[dbKITregistryFiles::field_filemtime] = -1;
			$file[dbKITregistryFiles::field_protect] = dbKITregistryFiles::protect_undefined;
		}
		
		
		// Pruefen, ob eine Datei uebertragen wurde...
		if (isset($_FILES[self::request_file]) && (is_uploaded_file($_FILES[self::request_file]['tmp_name']))) {
			if ($_FILES[self::request_file]['error'] == UPLOAD_ERR_OK) {
				$file_original = $_FILES[self::request_file]['name'];
				$tmp = explode('.', $file_original);
				$ext = end($tmp);
				$allowed_filetypes = $dbKITregistryCfg->getValue(dbKITregistryCfg::cfgAllowedFileTypes);
				if (!in_array($ext, $allowed_filetypes)) {
					$exts = implode(', ', $allowed_filetypes);
					$this->setMessage(sprintf(reg_msg_file_ext_not_allowed, $_FILES[self::request_file]['name'], $exts));
					@unlink($_FILES[self::request_file]['tmp_name']);
					return $this->dlgEdit();
				}
				$tmp_file = $_FILES[self::request_file]['tmp_name'];
				$sub_dir = $_FILES[self::request_file]['name'][0];
				$list_tabs = $dbKITregistryCfg->getValue(dbKITregistryCfg::cfgRegistryListTabs);
				if (!in_array($sub_dir, $list_tabs)) $sub_dir = '#';
				if (!file_exists($this->registry_path.$sub_dir)) {
          if (!mkdir($this->registry_path.$sub_dir)) {
          	$this->setError(sprintf(reg_error_mkdir, $this->registry_path.$sub_dir));
          	return false;
          }
          $message .= sprintf(reg_msg_mkdir, '/'.$sub_dir);
        }  	
				$file_registry = page_filename($_FILES[self::request_file]['name']);
				$upl_file = $this->registry_path.$sub_dir.'/'.$file_registry;
				if (!move_uploaded_file($tmp_file, $upl_file)) {
					// error moving file
					$this->setError(sprintf(reg_error_upload_move_file, $upl_file)); 
					return false;
				}
				// Upload erfolgreich
				if (!empty($file[dbKITregistryFiles::field_filepath_registry]) && file_exists($file[dbKITregistryFiles::field_filepath_registry])) {
					if (!unlink($file[dbKITregistryFiles::field_filepath_registry])) {
						// vorherige Datei konnte nicht geloescht werden
						$message .= sprintf(reg_msg_problem_unlink_registry_file, str_replace(WB_PATH, '', $file[dbKITregistryFiles::field_filepath_registry]));
					}
				}
				$file[dbKITregistryFiles::field_filename_original] 	= $file_original;
        $file[dbKITregistryFiles::field_filename_registry] 	= $file_registry;
        $file[dbKITregistryFiles::field_filepath_registry] 	= $upl_file;
        $file[dbKITregistryFiles::field_filemtime]					= filemtime($upl_file);
        $file[dbKITregistryFiles::field_filesize]						= filesize($upl_file);
        $file[dbKITregistryFiles::field_filetype]						= $ext;
        $file[dbKITregistryFiles::field_status]							= dbKITregistryFiles::status_active;
        $file[dbKITregistryFiles::field_sub_dir]						= $sub_dir;          	
			}
			else {
				switch ($_FILES[self::request_file]['error']):
				case UPLOAD_ERR_INI_SIZE:
					$error = sprintf(reg_error_upload_ini_size, ini_get('upload_max_filesize'));
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$error = reg_error_upload_form_size;
					break;
				case UPLOAD_ERR_PARTIAL:
					$error = sprintf(reg_error_upload_partial, $_FILES[self::request_file]['name']);
					break;
				default:
					$error = reg_error_upload_undefined_error;
				endswitch;
				$this->setError($error);
				return false;
			}	
		}
		
		foreach ($file as $key => $value) {
			switch ($key):
			case dbKITregistryFiles::field_description:
			case dbKITregistryFiles::field_keywords:
			case dbKITregistryFiles::field_content:
			case dbKITregistryFiles::field_protect:
			case dbKITregistryFiles::field_status:
				if (isset($_REQUEST[$key])) $file[$key] = $_REQUEST[$key];
				break;
			case dbKITregistryFiles::field_protect_groups:
				if (isset($_REQUEST[$key])) {
					$arr = explode(',', $_REQUEST[$key]);
					$new = array();
					foreach ($arr as $item) {
						$new[] = trim($item);
					}
					$file[$key] = implode(',', $new);
				}
				break;
			case dbKITregistryFiles::field_content_groups:
				if (isset($_REQUEST[$key])) {
					$grp_array = $_REQUEST[$key];
					if (($i =array_search('-1', $grp_array)) !== false) unset($grp_array[$i]);
					$file[$key] = implode(',', $grp_array);
				}
				break;
			default:
				continue;
			endswitch;
		}
		
		// Mindestanforderungen pruefen
		if (empty($file[dbKITregistryFiles::field_filepath_registry])) {
			$message .= reg_msg_registry_incomplete;
			$this->setMessage($message);
			return $this->dlgEdit();
		}
		
		if ($id > 0) {
			// Datensatz aktualisieren
			$where = array(dbKITregistryFiles::field_id => $id);
			if (!$dbKITregistryFiles->sqlUpdateRecord($file, $where)) {
				$this->setError($dbKITregistryFiles->getError());
				return false;
			}
			$message .= sprintf(reg_msg_registry_updated, $file[dbKITregistryFiles::field_filename_registry]);
		}
		else {
			// Datensatz einfuegen
			if (!$dbKITregistryFiles->sqlInsertRecord($file, $id)) {
				$this->setError($dbKITregistryFiles->getError());
				return false;
			}
			$message .= sprintf(reg_msg_registry_inserted, $file[dbKITregistryFiles::field_filename_registry]);
		}
		
		foreach ($dbKITregistryFiles->getFields() as $key => $value) {
			unset($_REQUEST[$key]);
		}
		unset($_FILES[self::request_file]);
		$_REQUEST[dbKITregistryFiles::field_id] = $id;
		$this->setMessage($message);
		return $this->dlgEdit();
	} // checkEdit()
  
	public function dlgGroups() {
		global $dbKITregistryGroups;
		
		$id = isset($_REQUEST[dbKITregistryGroups::field_id]) ? $_REQUEST[dbKITregistryGroups::field_id] : -1;
		if ($id > 0) {
			// existierender Datensatz
			$where = array(dbKITregistryGroups::field_id => $id);
			$group = array();
			if (!$dbKITregistryGroups->sqlSelectRecord($where, $group)) {
				$this->setError($dbKITregistryGroups->getError()); return false;
			}
			if (count($group) < 1) {
				$this->setError(sprintf(reg_error_invalid_id, $id)); return false;
			}
			$group = $group[0];
		}
		else {
			$group = $dbKITregistryGroups->getFields();
			$group[dbKITregistryGroups::field_status] = dbKITregistryGroups::status_active;
			$group[dbKITregistryGroups::field_id] = -1;
		}
		foreach ($dbKITregistryGroups->getFields() as $key => $value) {
			if (isset($_REQUEST[$key])) $group[$key] = $_REQUEST[$key];
		}
		$status = array();
		foreach ($dbKITregistryGroups->status_array as $value => $text) {
			$status[] = array(
				'value'		=> $value,
				'text'		=> $text,
				'selected'=> ($group[dbKITregistryGroups::field_status] == $value) ? 1 : 0
			);
		}
		$items = array(
			array(
				'label'		=> reg_label_id,
				'name'		=> dbKITregistryGroups::field_id,
				'value'		=> $group[dbKITregistryGroups::field_id],
				'hint'		=> ''),
			array(
				'label'		=> reg_label_group_id,
				'name'		=> dbKITregistryGroups::field_group_id,
				'value'		=> $group[dbKITregistryGroups::field_group_id],
				'hint'		=> reg_hint_group_id),
			array(
				'label'		=> reg_label_group_name,
				'name'		=> dbKITregistryGroups::field_group_name,
				'value'		=> $group[dbKITregistryGroups::field_group_name],
				'hint'		=> reg_hint_group_name ),
			array(
				'label'		=> reg_label_group_desc,
				'name'		=> dbKITregistryGroups::field_group_desc,
				'value'		=> $group[dbKITregistryGroups::field_group_desc],
				'hint'		=> reg_hint_group_desc ),
			array(
				'label'		=> reg_label_status,
				'name'		=> dbKITregistryGroups::field_status,
				'value'		=> $status,
				'hint'		=> reg_hint_status )			
		);
		
		$header = array(
			'id'					=> reg_th_id,
			'group_id'		=> reg_th_group_id,
			'group_name'	=> reg_th_group_name,
			'status'			=> reg_th_status
		);
		
		$SQL = sprintf( "SELECT * FROM %s WHERE %s!='%s' ORDER BY %s",
										$dbKITregistryGroups->getTableName(),
										dbKITregistryGroups::field_status,
										dbKITregistryGroups::status_deleted,
										dbKITregistryGroups::field_group_id);
		$grps = array();
		if (!$dbKITregistryGroups->sqlExec($SQL, $grps)) {
			$this->setError($dbKITregistryGroups->getError()); return false;
		}
		$groups = array();
		foreach ($grps as $grp) {
			$groups[] = array(
				'id'					=> $grp[dbKITregistryGroups::field_id],
				'group_id'		=> $grp[dbKITregistryGroups::field_group_id],
				'link'				=> sprintf('%s&amp;%s=%s&amp;%s=%s', $this->page_link, self::request_action, self::action_group, dbKITregistryGroups::field_id, $grp[dbKITregistryGroups::field_id]),
				'group_name'	=> $grp[dbKITregistryGroups::field_group_name],
				'status'			=> $dbKITregistryGroups->status_array[$grp[dbKITregistryGroups::field_status]]
			);
		}
		$data = array(
			'head'						=> reg_header_groups,
			'header'					=> $header,
			'intro'						=> ($this->isMessage()) ? $this->getMessage() : reg_intro_groups,
			'is_message'			=> ($this->isMessage()) ? 1 : 0, 
			'form_action'			=> $this->page_link,
			'action_name'			=> self::request_action,
			'action_value'		=> self::action_group_check,
			'group_name'			=> dbKITregistryGroups::field_id,
			'group_value'			=> $id,
			'group'						=> $items,
			'btn_ok'					=> reg_btn_ok,
			'btn_abort'				=> reg_btn_abort,
			'abort_location'	=> $this->page_link,
			'groups'					=> $groups
		);
		return $this->getTemplate('backend.group.list.htt', $data);
	} // dlgGroups()
	
	public function checkGroupEdit() {
		global $dbKITregistryGroups;
		
		$id = (isset($_REQUEST[dbKITregistryGroups::field_id])) ? $_REQUEST[dbKITregistryGroups::field_id] : -1;
		$group_id = (isset($_REQUEST[dbKITregistryGroups::field_group_id])) ? $_REQUEST[dbKITregistryGroups::field_group_id] : '';
		$group_id = page_filename($group_id);
		if (empty($group_id) || strlen($group_id) < 5) {
			$this->setMessage(reg_msg_group_id_invalid);
			return $this->dlgGroups();
		}
		
		if ($id > 0) {
			$where = array(dbKITregistryGroups::field_id => $id);
			$group = array();
			if (!$dbKITregistryGroups->sqlSelectRecord($where, $group)) {
				$this->setError($dbKITregistryGroups->getError()); return false;
			}
			if (count($group) < 1) {
				$this->setError(sprintf(reg_error_invalid_id, $id)); return false;
			}
			$group = $group[0];
			// wurde die Group ID geaendert?
			if (($group[dbKITregistryGroups::field_group_id] !== $group_id) ||
					(($group[dbKITregistryGroups::field_status] == dbKITregistryGroups::status_deleted) &&
					 ($_REQUEST[dbKITregistryGroups::field_status] !== dbKITregistryGroups::status_deleted))) {
				// pruefen, ob die group_id verwendet werden kann
				$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s!='%s' AND %s!='%s'",
												$dbKITregistryGroups->getTableName(),
												dbKITregistryGroups::field_group_id,
												$group_id,
												dbKITregistryGroups::field_id,
												$id,
												dbKITregistryGroups::field_status,
												dbKITregistryGroups::status_deleted);
				$check = array();
				if (!$dbKITregistryGroups->sqlExec($SQL, $check)) {
					$this->setError($dbKITregistryGroups->getError()); return false;
				}
				if (count($check) > 1) {
					if (($group[dbKITregistryGroups::field_status] == dbKITregistryGroups::status_deleted) &&
					 		($_REQUEST[dbKITregistryGroups::field_status] !== dbKITregistryGroups::status_deleted)) {
					 	// Datensatz kann nicht entsperrt werden!
					 	$this->setMessage(sprintf(reg_msg_group_status_invalid, $dbKITregistryGroups->status_array[dbKITregistryGroups::status_deleted], $group[dbKITregistryGroups::field_group_id], $group[dbKITregistryGroups::field_id]));
					 	return $this->dlgGroups();
					}
					else {
						// group_id wird bereits verwendet!
						$this->setMessage(sprintf(reg_msg_group_id_in_usage, $group_id, $check[0][dbKITregistryGroups::field_id]));
						return $this->dlgGroups();
					}
				}
			}
			// ok - Datensatz sichern
			$data = array(
				dbKITregistryGroups::field_group_id => $group_id,
				dbKITregistryGroups::field_group_name => $_REQUEST[dbKITregistryGroups::field_group_name],
				dbKITregistryGroups::field_group_desc => $_REQUEST[dbKITregistryGroups::field_group_desc],
				dbKITregistryGroups::field_status => $_REQUEST[dbKITregistryGroups::field_status]
			);
			$where = array(dbKITregistryGroups::field_id => $id);
			if (!$dbKITregistryGroups->sqlUpdateRecord($data, $where)) {
				$this->setError($dbKITregistryGroups->getError()); return false;
			}
			unset($_REQUEST[dbKITregistryGroups::field_id]);
			unset($_REQUEST[dbKITregistryGroups::field_group_id]);
			unset($_REQUEST[dbKITregistryGroups::field_group_name]);
			unset($_REQUEST[dbKITregistryGroups::field_group_desc]);
			unset($_REQUEST[dbKITregistryGroups::field_status]);
			$this->setMessage(sprintf(reg_msg_group_update_successfull, $id));
			return $this->dlgGroups();
		}
		else {
			// neuer Datensatz
			// pruefen, ob die group_id verwendet werden kann
			$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s!='%s'",
											$dbKITregistryGroups->getTableName(),
											dbKITregistryGroups::field_group_id,
											$group_id,
											dbKITregistryGroups::field_status,
											dbKITregistryGroups::status_deleted);
			$check = array();
			if (!$dbKITregistryGroups->sqlExec($SQL, $check)) {
				$this->setError($dbKITregistryGroups->getError()); return false;
			}
			if (count($check) > 1) {
				$this->setMessage(reg_msg_group_id_in_usage, $group_id, $check[0][dbKITregistryGroups::field_id]);
				return $this->dlgGroups();
			}
			if (empty($_REQUEST[dbKITregistryGroups::field_group_name])) $_REQUEST[dbKITregistryGroups::field_group_name] = $_REQUEST[dbKITregistryGroups::field_group_id];
			$data = array(
				dbKITregistryGroups::field_group_id => $group_id,
				dbKITregistryGroups::field_group_name => $_REQUEST[dbKITregistryGroups::field_group_name],
				dbKITregistryGroups::field_group_desc => $_REQUEST[dbKITregistryGroups::field_group_desc],
				dbKITregistryGroups::field_status => $_REQUEST[dbKITregistryGroups::field_status]
			);
			if (!$dbKITregistryGroups->sqlInsertRecord($data, $id)) {
				$this->setError($dbKITregistryGroups->getError()); return false;
			}
			unset($_REQUEST[dbKITregistryGroups::field_id]);
			unset($_REQUEST[dbKITregistryGroups::field_group_id]);
			unset($_REQUEST[dbKITregistryGroups::field_group_name]);
			unset($_REQUEST[dbKITregistryGroups::field_group_desc]);
			unset($_REQUEST[dbKITregistryGroups::field_status]);
			$this->setMessage(reg_msg_group_inserted, $id);
			return $this->dlgGroups();
		}
	} // checkGroupEdit();
	
} // class registryBackend

?>