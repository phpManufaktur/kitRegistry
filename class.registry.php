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

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php');

class dbKITregistryFiles extends dbConnectLE {

	const field_id									= 'reg_id';
	const field_filename_original		= 'reg_file_original';
	const field_filename_registry		= 'reg_file_registry';
	const field_filepath_registry		= 'reg_file_path';
	const field_filetype						= 'reg_filetype';
	const field_filemtime						= 'reg_filemtime';
	const field_filesize						= 'reg_filesize';
	const field_download_count			= 'reg_download_count';
	const field_download_last				= 'reg_download_last';
	const field_sub_dir							= 'reg_sub_dir';
	const field_description					= 'reg_description';
	const field_keywords						= 'reg_keywords';
	const field_content							= 'reg_content';
	const field_content_groups			= 'reg_content_groups';
	const field_protect							= 'reg_protect';
	const field_protect_groups			= 'reg_protect_groups';
	const field_status							= 'reg_status';
	const field_timestamp						= 'reg_timestamp';

	const status_active							= 1;
	const status_locked							= 0;
	const status_removed						= 3;
	const status_outdated						= 4;
	const status_deleted						= -1;

	public $status_array = array(
		self::status_active			=> reg_status_active,
		self::status_locked			=> reg_status_locked,
		self::status_removed		=> reg_status_removed,
		self::status_outdated		=> reg_status_outdated,
		self::status_deleted		=> reg_status_deleted
	);

	// PROTECT - muessen mit Definitionen in class.dirlist.php identisch sein!
	const protect_none				= 'nn';
	const protect_undefined		= 'udf';
	//const protect_kit_auto		= 'kaut';
	const protect_kit_dist		= 'kdis';
	const protect_kit_news		= 'knew';
	const protect_kit_intern	= 'kint';
	//const protect_wb_auto			= 'waut';
	const protect_wb_group		= 'wgrp';

	public $protect_array = array(
		self::protect_none				=> reg_protect_none,
		self::protect_kit_dist		=> reg_protect_kit_dist,
		self::protect_kit_intern	=> reg_protect_kit_intern,
		self::protect_kit_news		=> reg_protect_kit_news,
		self::protect_wb_group		=> reg_protect_wb_group,
		self::protect_undefined		=> reg_protect_undefined
	);

	private $createTables 		= false;

  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_registry_files');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_filename_original, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_filename_registry, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_filepath_registry, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_filetype, "VARCHAR(5) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_filemtime, "INT(11) NOT NULL DEFAULT '-1'");
  	$this->addFieldDefinition(self::field_filesize, "INT(11) NOT NULL DEFAULT '-1'");
  	$this->addFieldDefinition(self::field_sub_dir, "VARCHAR(5) NOT NULL DEFAULT '#'");
  	$this->addFieldDefinition(self::field_download_count, "INT(11) NOT NULL DEFAULT '0'");
  	$this->addFieldDefinition(self::field_download_last, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
  	$this->addFieldDefinition(self::field_description, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_keywords, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_content, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_content_groups, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_protect, "VARCHAR(10) NOT NULL DEFAULT '".self::protect_undefined."'");
  	$this->addFieldDefinition(self::field_protect_groups, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
  	$this->setIndexFields(array(self::field_filename_registry));
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  } // __construct()

} // class dbKITregistryFiles

class dbKITregistryGroups extends dbConnectLE {

	const field_id						= 'grp_id';
	const field_group_id			= 'grp_group_id';
	const field_group_name		= 'grp_group_name';
	const field_group_desc		= 'grp_group_desc';
	const field_status				= 'grp_status';
	const field_timestamp			= 'grp_timestamp';

	const status_active							= 1;
	const status_locked							= 0;
	const status_deleted						= -1;

	public $status_array = array(
		self::status_active			=> reg_status_active,
		self::status_locked			=> reg_status_locked,
		self::status_deleted		=> reg_status_deleted
	);

	private $createTables 		= false;

  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_registry_groups');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_group_id, "VARCHAR(25) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_group_name, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_group_desc, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  } // __construct()

} // class dbKITregistryGroups

class dbKITregistryCfg extends dbConnectLE {

	const field_id						= 'cfg_id';
	const field_name					= 'cfg_name';
	const field_type					= 'cfg_type';
	const field_value					= 'cfg_value';
	const field_label					= 'cfg_label';
	const field_description		= 'cfg_desc';
	const field_status				= 'cfg_status';
	const field_timestamp			= 'cfg_timestamp';

	const status_active				= 1;
	const status_deleted			= 0;

	const type_undefined			= 0;
	const type_array					= 7;
  const type_boolean				= 1;
  const type_email					= 2;
  const type_float					= 3;
  const type_integer				= 4;
  const type_path						= 5;
  const type_string					= 6;
  const type_url						= 8;

  public $type_array = array(
  	self::type_undefined		=> '-UNDEFINED-',
  	self::type_array				=> 'ARRAY',
  	self::type_boolean			=> 'BOOLEAN',
  	self::type_email				=> 'E-MAIL',
  	self::type_float				=> 'FLOAT',
  	self::type_integer			=> 'INTEGER',
  	self::type_path					=> 'PATH',
  	self::type_string				=> 'STRING',
  	self::type_url					=> 'URL'
  );

  private $createTables 		= false;
  private $message					= '';

  const cfgRegistryExec				= 'cfgRegistryExec';
  const cfgRegistryListTabs		= 'cfgRegistryListTabs';
  const cfgAllowedFileTypes		= 'cfgAllowedFileTypes';
  const cfgRegistryDroplet		= 'cfgRegistryDroplet';
  const cfgCronjobDir         = 'cfgCronjobDir';
  const cfgFTPregistryGroup   = 'cfgFTPregistryGroup';
  const cfgMinSearchLength    = 'cfgMinSearchLength';

  public $config_array = array(
  	array('reg_label_cfg_exec', self::cfgRegistryExec, self::type_boolean, '1', 'reg_desc_cfg_exec'),
  	array('reg_label_cfg_list_tabs', self::cfgRegistryListTabs, self::type_array, 'a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,0,1,2,3,4,5,6,7,8,9,#', 'reg_desc_cfg_list_tabs'),
  	array('reg_label_cfg_allowed_files', self::cfgAllowedFileTypes, self::type_array, 'pdf,doc,txt', 'reg_desc_cfg_allowed_files'),
  	array('reg_label_cfg_registry_droplet', self::cfgRegistryDroplet, self::type_integer, '-1', 'reg_desc_cfg_registry_droplet'),
    array('reg_label_cfg_cronjob_dir', self::cfgCronjobDir, self::type_string, '/media/upload', 'reg_desc_cfg_cronjob_dir'),
  	array('reg_label_cfg_ftp_registry_group', self::cfgFTPregistryGroup, self::type_string, '', 'reg_desc_cfg_ftp_registry_group'),
    array('reg_label_cfg_min_search_length', self::cfgMinSearchLength, self::type_integer, '4', 'reg_desc_cfg_min_search_length')	
  );

  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_registry_config');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_name, "VARCHAR(32) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_type, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::type_undefined."'");
  	$this->addFieldDefinition(self::field_value, "VARCHAR(255) NOT NULL DEFAULT ''", false, false, true);
  	$this->addFieldDefinition(self::field_label, "VARCHAR(64) NOT NULL DEFAULT 'ed_str_undefined'");
  	$this->addFieldDefinition(self::field_description, "VARCHAR(255) NOT NULL DEFAULT 'ed_str_undefined'");
  	$this->addFieldDefinition(self::field_status, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
  	$this->setIndexFields(array(self::field_name));
  	$this->setAllowedHTMLtags('<a><abbr><acronym><span>');
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  	// Default Werte garantieren
  	if ($this->sqlTableExists()) {
  		$this->checkConfig();
  	}
  	date_default_timezone_set(reg_cfg_time_zone);
  } // __construct()

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
   * Aktualisiert den Wert $new_value des Datensatz $name
   *
   * @param $new_value STR - Wert, der uebernommen werden soll
   * @param $id INT - ID des Datensatz, dessen Wert aktualisiert werden soll
   *
   * @return BOOL Ergebnis
   *
   */
  public function setValueByName($new_value, $name) {
  	$where = array();
  	$where[self::field_name] = $name;
  	$config = array();
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(reg_error_cfg_name, $name)));
  		return false;
  	}
  	return $this->setValue($new_value, $config[0][self::field_id]);
  } // setValueByName()

  /**
   * Haengt einen Slash an das Ende des uebergebenen Strings
   * wenn das letzte Zeichen noch kein Slash ist
   *
   * @param STR $path
   * @return STR
   */
  public function addSlash($path) {
  	$path = substr($path, strlen($path)-1, 1) == "/" ? $path : $path."/";
  	return $path;
  }

  /**
   * Wandelt einen String in einen Float Wert um.
   * Geht davon aus, dass Dezimalzahlen mit ',' und nicht mit '.'
   * eingegeben wurden.
   *
   * @param STR $string
   * @return FLOAT
   */
  public function str2float($string) {
  	$string = str_replace('.', '', $string);
		$string = str_replace(',', '.', $string);
		$float = floatval($string);
		return $float;
  }

  public function str2int($string) {
  	$string = str_replace('.', '', $string);
		$string = str_replace(',', '.', $string);
		$int = intval($string);
		return $int;
  }

	/**
	 * Ueberprueft die uebergebene E-Mail Adresse auf logische Gueltigkeit
	 *
	 * @param STR $email
	 * @return BOOL
	 */
	public function validateEMail($email) {
		if(preg_match("/^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$/i", $email)) {
			return true; }
		else {
			return false; }
	}

  /**
   * Aktualisiert den Wert $new_value des Datensatz $id
   *
   * @param $new_value STR - Wert, der uebernommen werden soll
   * @param $id INT - ID des Datensatz, dessen Wert aktualisiert werden soll
   *
   * @return BOOL Ergebnis
   */
  public function setValue($new_value, $id) {
  	$value = '';
  	$where = array();
  	$where[self::field_id] = $id;
  	$config = array();
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(reg_error_cfg_id, $id)));
  		return false;
  	}
  	$config = $config[0];
  	switch ($config[self::field_type]):
  	case self::type_array:
  		// Funktion geht davon aus, dass $value als STR uebergeben wird!!!
  		$worker = explode(",", $new_value);
  		$data = array();
  		foreach ($worker as $item) {
  			$data[] = trim($item);
  		};
  		$value = implode(",", $data);
  		break;
  	case self::type_boolean:
  		$value = (bool) $new_value;
  		$value = (int) $value;
  		break;
  	case self::type_email:
  		if ($this->validateEMail($new_value)) {
  			$value = trim($new_value);
  		}
  		else {
  			$this->setMessage(sprintf(reg_msg_invalid_email, $new_value));
  			return false;
  		}
  		break;
  	case self::type_float:
  		$value = $this->str2float($new_value);
  		break;
  	case self::type_integer:
  		$value = $this->str2int($new_value);
  		break;
  	case self::type_url:
  	case self::type_path:
  		$value = $this->addSlash(trim($new_value));
  		break;
  	case self::type_string:
  		$value = (string) trim($new_value);
  		// Hochkommas demaskieren
  		$value = str_replace('&quot;', '"', $value);
  		break;
  	endswitch;
  	unset($config[self::field_id]);
  	$config[self::field_value] = (string) $value;
  	if (!$this->sqlUpdateRecord($config, $where)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	return true;
  } // setValue()

  /**
   * Gibt den angeforderten Wert zurueck
   *
   * @param $name - Bezeichner
   *
   * @return WERT entsprechend des TYP
   */
  public function getValue($name) {
  	$result = '';
  	$where = array();
  	$where[self::field_name] = $name;
  	$config = array();
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(reg_error_cfg_name, $name)));
  		return false;
  	}
  	$config = $config[0];
  	switch ($config[self::field_type]):
  	case self::type_array:
  		$result = explode(",", $config[self::field_value]);
  		break;
  	case self::type_boolean:
  		$result = (bool) $config[self::field_value];
  		break;
  	case self::type_email:
  	case self::type_path:
  	case self::type_string:
  	case self::type_url:
  		$result = (string) utf8_decode($config[self::field_value]);
  		break;
  	case self::type_float:
  		$result = (float) $config[self::field_value];
  		break;
  	case self::type_integer:
  		$result = (integer) $config[self::field_value];
  		break;
  	default:
  		$result = utf8_decode($config[self::field_value]);
  		break;
  	endswitch;
  	return $result;
  } // getValue()

  public function checkConfig() {
  	foreach ($this->config_array as $item) {
  		$where = array();
  		$where[self::field_name] = $item[1];
  		$check = array();
  		if (!$this->sqlSelectRecord($where, $check)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			return false;
  		}
  		if (sizeof($check) < 1) {
  			// Eintrag existiert nicht
  			$data = array();
  			$data[self::field_label] = $item[0];
  			$data[self::field_name] = $item[1];
  			$data[self::field_type] = $item[2];
  			$data[self::field_value] = $item[3];
  			$data[self::field_description] = $item[4];
  			if (!$this->sqlInsertRecord($data)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  				return false;
  			}
  		}
  	}
  	return true;
  }

} // class dbEventCfg

?>