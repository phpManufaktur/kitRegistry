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

class kitRegistry {

	const request_action					= 'ract';
	const request_file						= 'file';
	const request_search					= 'sear';

	const action_account					= 'acc';
	const action_default					= 'def';
	const action_login						= 'log';
	const action_logout						= 'out';
	const action_search						= 'sea';
	const action_search_check			= 'sec';

	const session_prefix		= 'kdl_';
	const session_protect		= 'pct';		// protected access?
	const session_user			= 'usr';		// username
	const session_auth			= 'aut';		// is user authorized?
	const session_wb_grps		= 'grps';   // IDs of WB Groups
	const session_admin			= 'adm';
	const session_temp_act	= 'tact';
	const session_temp_file = 'tfil';

	const protect_none			= 'nn';
	const protect_undefined	= 'udf';
	const protect_group			= 'grp';
	const protect_kit				= 'kit';
	const protect_wb				= 'wb';

	const param_kit_auto		= 'kit_auto';
	const param_kit_intern	= 'kit_intern';
	const param_kit_news		= 'kit_news';
	const param_kit_dist		= 'kit_dist';
	const param_wb_group		= 'wb_group';
	const param_wb_auto			= 'wb_auto';
	const param_preset				= 'preset';

	private $params = array(
		self::param_kit_intern	=> '',
		self::param_kit_news		=> '',
		self::param_kit_dist		=> '',
		self::param_wb_group		=> '',
		self::param_preset			=> 1
	);

	const registry_anchor		= 'regan';

	private $template_path	= '';
	private $kit_installed	= false;
	private $is_authenticated = false;
	private $wb_login = false;
	private $silent = true;
	private $download_link = '';
	private $page_link = '';
	private $error = '';
	private $message = '';

	public function __construct($silent=true) {
		global $registryTools;
		$this->silent = $silent;
		//$this->template_path = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/htt/';
		$this->template_path = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/frontend/'.$this->params[self::param_preset].'/'.KIT_REGISTRY_LANGUAGE.'/';
		$this->kit_installed = (file_exists(WB_PATH.'/modules/kit/class.contact.php')) ? true : false;
		// check if $_SESSIONs are already defined - protect access by default!
		if (!isset($_SESSION[self::session_prefix.self::session_protect])) $_SESSION[self::session_prefix.self::session_protect] = self::protect_undefined;
		if (!isset($_SESSION[self::session_prefix.self::session_user])) $_SESSION[self::session_prefix.self::session_user] = '';
		if (!isset($_SESSION[self::session_prefix.self::session_auth])) $_SESSION[self::session_prefix.self::session_auth] = 0;
		$this->is_authenticated = ($_SESSION[self::session_prefix.self::session_auth] == 1) ? true : false;
		// check if WB LOGIN is allowed
		$this->wb_login = (defined('LOGIN_URL')) ? true : false;
		$url = '';
		$registryTools->getPageLinkByPageID(PAGE_ID, $url);
		$this->page_link = $url;
		unset($_SESSION['KIT_EXTENSION']);
		$this->download_link = WB_URL.'/modules/kit_registry/rdl.php';
	} // __construct()

	public function getParams() {
		return $this->params;
	} // getParams()

	public function setParams($params=array()) {
		// set default values
		foreach ($this->params as $key => $value) {
			switch($key):
			case self::param_preset:
				$this->params[$key] = 1; break;
			case self::param_kit_intern:
			case self::param_kit_news:
			case self::param_kit_dist:
			case self::param_wb_group:
				$this->params[$key] = ''; break;
			default:
				$this->params[$key] = 'undefined'; break;
			endswitch;
		}
		// get the new values
		$skip_param_media = false;
		foreach ($params as $key => $value) {
			if (key_exists($key, $this->params)) {
				switch ($key):
				case self:: param_preset:
					$this->params[$key] = $value;
					$this->template_path = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/frontend/'.$this->params[self::param_preset].'/'.KIT_REGISTRY_LANGUAGE.'/';
					break;
				case self::param_kit_intern:
				case self::param_kit_news:
				case self::param_kit_dist:
					if (empty($value)) break;
					if (!$this->kit_installed) {
						$this->setError(sprintf(reg_error_kit_param_rejected, $key));
						return false;
					}
					$arr = explode(',', $value);
					$para = array();
					foreach ($arr as $item) {
						$para[] = trim($item);
					}
					$this->params[$key] = implode(',', $para);
					break;
				case self::param_wb_group:
					$para = array();
					$arr = explode(',', $value);
					foreach ($arr as $item) {
						$val = trim($item);
						$para[] = $val;
					}
					$this->params[$key] = implode(',', $para);
					break;
				endswitch;
			}
			else {
				$this->setError(sprintf(reg_error_unknown_param_key, $key));
				return false;
			}
		}
		return true;
	} // setParams()

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

	/**
    * Set $this->error to $error
    *
    * @param STR $error
    */
  public function setError($error, $line=-1) {
  	$debug = debug_backtrace();
  	$caller = next($debug);
  	$this->error = sprintf('[%s::%s - %s] %s', basename($caller['file']), $caller['function'], ($line > 0) ? $line : $caller['line'], $error);
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
   * Prevents XSS Cross Site Scripting
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

  /**
   * Action handler of class kitRegistry
   */
	public function action() {
		// check if there are errors...
		if ($this->isError()) return $this->show();

		// get params to $_SESSION...
		foreach ($this->params as $key => $value) {
			if (!isset($_SESSION[self::session_prefix.$key]) || ($_SESSION[self::session_prefix.$key] !== $value)) $_SESSION[self::session_prefix.$key] = $value;
		}
		// fields with HTML code
  	$html_allowed = array();
  	foreach ($_REQUEST as $key => $value) {
  		if (!in_array($key, $html_allowed)) {
  			$_REQUEST[$key] = $this->xssPrevent($value);
  		}
  	}
  	isset($_REQUEST[self::request_action]) ? $action = $_REQUEST[self::request_action] : $action = self::action_default;
  	if (isset($_SESSION[self::session_prefix.self::session_temp_act])) {
  		$action = $_SESSION[self::session_prefix.self::session_temp_act];
  		unset($_SESSION[self::session_prefix.self::session_temp_act]);
  	}
  	if (($action != self::action_login) && ($_SESSION[self::session_prefix.self::session_protect] == self::protect_undefined)) {
	  	if (!$this->checkProtection()) return $this->show();
	  	if ((!$this->is_authenticated) && (is_string($login = $this->checkAuthentication()))) return $login;
  	}
  	$account = false;
  	switch ($action):
		case self::action_logout:
			$result = $this->logout();
			break;
		case self::action_account:
			$result = $this->dlgKITaccount();
			$account = true;
			break;
		case self::action_login:
			if (isset($_SESSION[self::session_prefix.self::session_temp_file])) {
				$_REQUEST[self::request_file] = $_SESSION[self::session_prefix.self::session_temp_file];
				unset($_SESSION[self::session_prefix.self::session_temp_file]);
			}
			$result = $this->execLoginDlg();
			break;
		case self::action_search_check:
			$result = $this->checkSearch();
			break;
		case self::action_search:
		default:
			$result = $this->showSearchDlg();
		endswitch;

		return $this->show($result, $account);
	} // action()

	/**
	 * ECHO or RETURN the result dialog depending on switch SILENT
	 * @param STR $result
	 */
	public function show($result='- no content -', $account=false) {
		// check if there was an error...
		if ($this->isError()) $result = sprintf('<div class="registry_error"><h1>%s</h1>%s</div>', reg_header_error, $this->getError());
		if (!$account &&
				//(isset($_SESSION[self::session_prefix.self::session_auth]) && ($_SESSION[self::session_prefix.self::session_auth] == 1)) &&
				isset($_SESSION[self::session_prefix.self::session_protect]) &&
				($_SESSION[self::session_prefix.self::session_protect] == self::protect_kit)) {
			// display logout link if necessary...
			$result = sprintf('<a name="%s"></a><div class="registry_body"><div class="registry_logout"><a href="%s">%s</a> &bull; <a href="%s">%s</a></div>%s</div>',
												self::registry_anchor,
												sprintf('%s?%s=%s', $this->page_link, self::request_action, self::action_account),
												reg_btn_account,
												sprintf('%s?%s=%s', $this->page_link, self::request_action, self::action_logout),
												reg_btn_logout,
												$result);
		}
		elseif (//(isset($_SESSION[self::session_prefix.self::session_auth]) && ($_SESSION[self::session_prefix.self::session_auth] == 1)) &&
						isset($_SESSION[self::session_prefix.self::session_protect]) &&
						(($_SESSION[self::session_prefix.self::session_protect] == self::protect_wb) ||
						 ($_SESSION[self::session_prefix.self::session_protect] == self::protect_group))) {
			// display logout link if necessary...
			$result = sprintf('<a name="%s"></a><div class="registry_body"><div class="registry_logout"><a href="%s">%s</a></div>%s</div>',
												self::registry_anchor,
												sprintf('%s?%s=%s', $this->page_link, self::request_action, self::action_logout),
												reg_btn_logout,
												$result);
		}
		else {
			$result = sprintf('<a name="%s"></a><div class="kdl_body">%s</div>', self::registry_anchor, $result);
		}

		if ($this->silent) return $result;
		echo $result;
	} // show()

	public function logout() {
		// unset all session vars...
		unset($_SESSION[self::session_prefix.self::session_user]);
		unset($_SESSION[self::session_prefix.self::session_auth]);
		unset($_SESSION[self::session_prefix.self::session_wb_grps]);
		unset($_SESSION[self::session_prefix.self::session_admin]);
		foreach ($this->params as $param) {
			unset($_SESSION[self::session_prefix.$param]);
		}
		if (($_SESSION[self::session_prefix.self::session_protect] == self::protect_group) ||
				($_SESSION[self::session_prefix.self::session_protect] == self::protect_wb)) {
			// WebsiteBaker Logout
			unset($_SESSION[self::session_prefix.self::session_protect]);
			header("Location: ".LOGOUT_URL);
		}
		elseif ($_SESSION[self::session_prefix.self::session_protect] == self::protect_kit) {
			// KeepInTouch Logout
			unset($_SESSION[self::session_prefix.self::session_protect]);
			return $this->dlgKITaccount('out');
		}
		// otherwise only unset the protected session...
		unset($_SESSION[self::session_prefix.self::session_protect]);
	} // logout()

	/**
	 * Art des Schutzes pruefen und festlegen
	 */
	private function checkProtection() {
		if (!empty($_SESSION[self::session_prefix.self::param_kit_news]) ||
				!empty($_SESSION[self::session_prefix.self::param_kit_dist]) ||
				!empty($_SESSION[self::session_prefix.self::param_kit_intern])) {
			// ok - pruefen ob der angeforderte Schutz mit KIT moeglich ist.
			if (!$this->kit_installed) {
				$this->setError(reg_error_kit_not_installed);
				return false;
			}
			// KIT einbinden
			require_once(WB_PATH.'/modules/kit/initialize.php');
			$dbContactArray = new dbKITcontactArrayCfg();
			$categories = array();
			if (!empty($_SESSION[self::session_prefix.self::param_kit_news])) {
				$categories[] = self::param_kit_news;
			}
			elseif (!empty($_SESSION[self::session_prefix.self::param_kit_dist])) {
				$categories[] = self::param_kit_dist;
			}
			elseif (!empty($_SESSION[self::session_prefix.self::param_kit_intern])) {
				$categories[] = self::param_kit_intern;
			}
			foreach ($categories as $category) {
				// Pruefen ob die Kategorien existieren
				$x = explode(',', $_SESSION[self::session_prefix.$category]);
				$cats = '';
				foreach ($x as $c) {
					if (!empty($cats)) $cats .= ' OR ';
					$cats .= sprintf("%s='%s'", dbKITcontactArrayCfg::field_identifier, $c);
				}
				$SQL = sprintf( "SELECT * FROM %s WHERE %s AND %s='%s'",
												$dbContactArray->getTableName(),
												$cats,
												dbKITcontactArrayCfg::field_status,
												dbKITcontactArrayCfg::status_active);
				$cfgArray = array();
				if (!$dbContactArray->sqlExec($SQL, $cfgArray)) {
					$this->setError($dbContactArray->getError());
					return false;
				}
				if (count($cfgArray) < 1) {
					$this->setError(sprintf(reg_error_missing_kit_category, $category, $_SESSION[self::session_prefix.$category]));
					return false;
				}
			}
			$_SESSION[self::session_prefix.self::session_protect] = self::protect_kit;
		}
		elseif (!empty($_SESSION[self::session_prefix.self::param_wb_group])) {
			if (!$this->wb_login) {
				// WB Anmeldung ist ausgeschaltet
				$this->setError(sprintf(reg_error_wb_login_not_enabled, self::param_wb_group));
				return false;
			}
			global $database;
			$groups = explode(',', $_SESSION[self::session_prefix.self::param_wb_group]);
			$wb_groups_id = array();
			foreach ($groups as $group) {
				$SQL = sprintf(	"SELECT group_id FROM %sgroups WHERE name='%s'", TABLE_PREFIX, $group);
				if (false ===($result = $database->query($SQL))) {
					$this->setError($database->get_error());
					return false;
				}
				$data = $result->fetchRow(MYSQL_ASSOC);
				if (!isset($data['group_id'])) {
					$this->setError(sprintf(reg_error_missing_wb_group, self::param_wb_group, $group));
					return false;
				}
				$wb_groups_id[] = $data['group_id'];
			}
			$_SESSION[self::session_prefix.self::session_protect] = self::protect_group;
			$_SESSION[self::session_prefix.self::session_wb_grps] = implode(',', $wb_groups_id);
		}
		elseif (!empty($_SESSION[self::session_prefix.self::param_wb_auto])) {
			// use automatic user directories
			if (!$this->wb_login) {
				// WB Anmeldung ist ausgeschaltet
				$this->setError(sprintf(reg_error_wb_login_not_enabled, self::param_wb_group));
				return false;
			}
			$_SESSION[self::session_prefix.self::session_protect] = self::protect_wb;
		}
		elseif (!empty($_SESSION[self::session_prefix.self::param_kit_auto])) {
			// use automatic user directories
			if (!$this->kit_installed) {
				$this->setError(reg_error_kit_not_installed);
				return false;
			}
			$_SESSION[self::session_prefix.self::session_protect] = self::protect_kit;
		}
		else {
			// oeffentlicher Zugriff!
			$_SESSION[self::session_prefix.self::session_protect] = self::protect_none;
		}
		return true;
	} // checkProtection()

	/**
	 * Check the authentication by the desired access method.
	 * Set the different $_SESSION vars for further checks and controls
	 * @return BOOL true on success or STR login dialog or error message
	 */
	private function checkAuthentication() {
		global $registryTools;
		global $wb;

		if ($_SESSION[self::session_prefix.self::session_protect] == self::protect_none) {
			// no protection needed
			$_SESSION[self::session_prefix.self::session_auth] = 0;
			$this->is_authenticated = true;
			return true;
		}
		elseif ($_SESSION[self::session_prefix.self::session_protect] == self::protect_kit) {
			// Protection by KeepInTouch login
			if (!$this->kit_installed) {
				$this->setError(reg_error_kit_not_installed, __LINE__);
				return $this->getError();
			}
			if (isset($_SESSION['kit_aid']) && isset($_SESSION['kit_key'])) {
				// KIT User ist bereits angemeldet
				require_once(WB_PATH.'/modules/kit/initialize.php');
				global $dbContact;
				global $dbRegister;
				$where = array(
					dbKITregister::field_id => $_SESSION['kit_aid'],
					dbKITregister::field_status => dbKITregister::status_active
				);
				$register = array();
				if (!$dbRegister->sqlSelectRecord($where, $register)) {
					$this->setError($dbRegister->getError());
					return $this->getError();
				}
				if (count($register) < 1) {
					$this->setError(sprintf(reg_error_kit_register_id_missing, $_SESSION['kit_aid']));
					return $this->getError();
				}
				$register = $register[0];
				// E-Mail Adresse des Users festhalten
				$_SESSION[self::session_prefix.self::session_user] = $register[dbKITregister::field_email];
				// read contact
				$contact = array();
				if (!$dbContact->getContactByID($register[dbKITregister::field_contact_id], $contact)) {
					$this->setError($dbContact->getError());
					return $this->getError();
				}
				if (count($contact) < 1) {
					$this->setError(sprintf(reg_error_kit_id_missing, $register[0][dbKITregister::field_contact_id]));
					return $this->getError();
				}

				// Kategorien des Users nur pruefen, wenn kit_auto NICHT aktiv ist
				$kg = $contact[dbKITcontact::field_category];
				if (!empty($contact[dbKITcontact::field_distribution])) {
					if (!empty($kg)) $kg .= ',';
					$kg .= $contact[dbKITcontact::field_distribution];
				}
				if (!empty($contact[dbKITcontact::field_newsletter])) {
					if (!empty($kg)) $kg .= ',';
					$kg .= $contact[dbKITcontact::field_newsletter];
				}
				$kit_groups = explode(',', $kg);

				$grps = $_SESSION[self::session_prefix.self::param_kit_dist];
				if (!empty($_SESSION[self::session_prefix.self::param_kit_intern])) {
					if (!empty($grps)) $grps .= ',';
					$grps .= $_SESSION[self::session_prefix.self::param_kit_intern];
				}
				if (!empty($_SESSION[self::session_prefix.self::param_kit_news])) {
					if (!empty($grps)) $grps .= ',';
					$grps .= $_SESSION[self::session_prefix.self::param_kit_news];
				}
				$groups = explode(',', $grps);
				$group_ok = false;
				foreach ($groups as $group) {
					if (in_array($group, $kit_groups)) {
						$group_ok = true;
						break;
					}
				}
				if (!$group_ok) {
					// nicht berechtigt
					$data = array(
						'header'		=> reg_header_access_denied,
						'content'		=> reg_msg_access_denied
					);
					return $this->getTemplate('frontend.prompt.dwoo', $data);
				}
				// Benutzer freigeben
				$_SESSION[self::session_prefix.self::session_auth] = 1;
				$this->is_authenticated = true;

				// check if user is admin...
				$admin_emails = array();
				if ($dbRegister->getAdmins($admin_emails) && (in_array($_SESSION[self::session_prefix.self::session_user], $admin_emails))) {
					$_SESSION[self::session_prefix.self::session_admin] = true;
				}
				return true;
			}
			else {
				// KIT User ist nicht angemeldet
				return $this->dlgKITaccount();
			}
		}
		elseif ($_SESSION[self::session_prefix.self::session_protect] == self::protect_group) {
			// Protection by WB Group
			if (!$wb->is_authenticated()) {
				// Benutzer ist nicht angemeldet
				$url = '';
				$registryTools->getPageLinkByPageID(PAGE_ID, $url);
				$data = array(
					'header'		=> reg_header_login,
					'content'		=> sprintf(reg_content_login_wb, LOGIN_URL.'?redirect='.$url)
				);
				// Anmeldedialog anzeigen
				return $this->getTemplate('frontend.prompt.dwoo', $data);
			}
			else {
				// pruefen ob der Anwender berechtigt ist auf die Daten zuzugreifen
				if (!isset($_SESSION['GROUPS_ID']) || !isset($_SESSION[self::session_prefix.self::session_wb_grps])) {
					$this->setError(reg_error_wb_groups_undefined);
					return $this->getError();
				}
				$groups = explode(',', $_SESSION['GROUPS_ID']);
				$group_ok = false;
				$kdl_wb_groups = explode(',', $_SESSION[self::session_prefix.self::session_wb_grps]);
				foreach ($groups as $group) {
					if (in_array($group, $kdl_wb_groups)) {
						$group_ok = true;
						break;
					}
				}
				if (!$group_ok) {
					// nicht berechtigt
					$data = array(
						'header'		=> reg_header_access_denied,
						'content'		=> reg_msg_access_denied
					);
					return $this->getTemplate('frontend.prompt.dwoo', $data);
				}
				$_SESSION[self::session_prefix.self::session_user] = $_SESSION['EMAIL'];
				$_SESSION[self::session_prefix.self::session_auth] = 1;
				$this->is_authenticated = true;
				return true;
			}
		}
		elseif ($_SESSION[self::session_prefix.self::session_protect] == self::protect_wb) {
			// Protection by WB USER Authentication
			if (!$wb->is_authenticated()) {
				// Benutzer ist nicht angemeldet
				$url = '';
				$registryTools->getPageLinkByPageID(PAGE_ID, $url);
				$data = array(
					'header'		=> reg_header_login,
					'content'		=> sprintf(reg_content_login_wb, LOGIN_URL.'?redirect='.$url)
				);
				// Anmeldedialog anzeigen
				return $this->getTemplate('frontend.prompt.dwoo', $data);
			}
			require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.users.php');
			$dbGroups = new dbWBgroups();
			$where = array(dbWBgroups::field_name => 'Administrators');
			$groups = array();
			if (!$dbGroups->sqlSelectRecord($where, $groups)) {
				$this->setError($dbGroups->getError());
				return $this->getError();
			}
			if (count($groups) < 1) {
				$this->setError(kdl_error_wb_groups);
				return $this->getError();
			}
			// check if user is admin...
			if ($_SESSION['GROUP_ID'] == $groups[0][dbWBgroups::field_group_id]) {
				$_SESSION[self::session_prefix.self::session_admin] = true;
			}
			return true;
		}
		else {
			$this->setError(sprintf(reg_error_unknown_param, $_SESSION[self::session_prefix.self::session_protect]));
			return $this->getError();
		}
	} // checkAuthentication()

	public function dlgKITaccount($action='') {
		global $dbCfg; // KIT config
		global $dbDialogsRegister;
		global $registryTools;
		if (!$this->kit_installed) {
			$this->setError(reg_error_kit_not_installed);
			return $this->getError();
		}
		// get KIT Dialog Framework
		require_once(WB_PATH.'/modules/kit/class.dialogs.php');
		// get the KIT Account dialog which should be used
  	$dialog_account = $dbCfg->getValue(dbKITcfg::cfgRegisterDlgACC);
  	$where = array(dbKITdialogsRegister::field_name => $dialog_account);
  	$regDialogs = array();
  	if (!$dbDialogsRegister->sqlSelectRecord($where, $regDialogs)) {
  		$this->setError($dbDialogsRegister->getError());
  		return $this->getError();
  	}
  	if (count($regDialogs) < 1) {
  		$this->setError(sprintf(kit_error_dlg_missing, $dialog_account));
  		return $this->getError();
  	}
		if (file_exists(WB_PATH.'/modules/kit/dialogs/'.strtolower($dialog_account).'/'.strtolower($dialog_account).'.php')) {
  		require_once(WB_PATH.'/modules/kit/dialogs/'.strtolower($dialog_account).'/'.strtolower($dialog_account).'.php');
  		// call Account Dialog
  		unset($_SESSION['KIT_REDIRECT']);
  		$_SESSION['KIT_EXTENSION'] = array('link' => $this->page_link, 'name' => MENU_TITLE);
			$callDialog = new $dialog_account(true);
			$callDialog->setDlgID($regDialogs[0][dbKITdialogsRegister::field_id]);
			if (!empty($action)) $_REQUEST['acc_act'] = $action;
			$result = $callDialog->action();
			$url = '';
			$registryTools->getPageLinkByPageID(PAGE_ID, $url);
			$_SESSION['KIT_REDIRECT'] = $url;
			return $result;
		}
		else {
			$this->setError(sprintf(kit_error_dlg_missing, $dialog));
			return $this->getError();
		}
	} // dlgKITaccount()


	public function showSearchDlg() {
		global $registryTools;
		$results = '';
		$data = array(
			'header'				=> reg_header_search,
			'is_message'		=> ($this->isMessage()) ? 1 : 0,
			'intro'					=> ($this->isMessage()) ? $this->getMessage() : reg_intro_search,
			'form_action'		=> $this->page_link,
			'action_name'		=> self::request_action,
			'action_value'	=> self::action_search_check,
			'search_name'		=> self::request_search,
			'btn_ok'				=> reg_btn_ok,
		);
		return $this->getTemplate('frontend.search.dwoo', $data);
	} // showSearchDlg()

	public function checkSearch() {
		global $dbKITregistryFiles;
		global $registryTools;
		global $dbKITregistryCfg;
		
		if (!isset($_REQUEST[self::request_search]) || empty($_REQUEST[self::request_search])) {
			$this->setMessage(reg_msg_search_empty);
			return $this->showSearchDlg();
		}
	  $search = trim(strip_tags($_REQUEST[self::request_search]));
		$min_length = $dbKITregistryCfg->getValue(dbKITregistryCfg::cfgMinSearchLength);
	  if (strlen($search) < $min_length) {
	  	$this->setMessage(sprintf(reg_msg_search_str_length, $min_length));
	  	return $this->showSearchDlg();
	  }	
		
		$SQL = sprintf( 'SELECT * FROM %1$s WHERE %2$s=\'%3$s\' AND
										((%4$s LIKE \'%5$s%%\' OR %4$s LIKE \'%%%5$s%%\' OR %4$s LIKE \'%%%5$s\') OR
										 (%6$s LIKE \'%5$s%%\' OR %6$s LIKE \'%%%5$s%%\' OR %6$s LIKE \'%%%5$s\') OR
										 (%7$s LIKE \'%5$s%%\' OR %7$s LIKE \'%%%5$s%%\' OR %7$s LIKE \'%%%5$s\') OR
										 (%8$s LIKE \'%5$s%%\' OR %8$s LIKE \'%%%5$s%%\' OR %8$s LIKE \'%%%5$s\') OR
										 (%9$s LIKE \'%5$s%%\' OR %9$s LIKE \'%%%5$s%%\' OR %9$s LIKE \'%%%5$s\'))',
										$dbKITregistryFiles->getTableName(),
										dbKITregistryFiles::field_status,
										dbKITregistryFiles::status_active,
										dbKITregistryFiles::field_filename_original,
										$search,
										dbKITregistryFiles::field_filename_registry,
										dbKITregistryFiles::field_description,
										dbKITregistryFiles::field_keywords,
										dbKITregistryFiles::field_content);
		if (!$dbKITregistryFiles->sqlExec($SQL, $result)) {
			$this->setError($dbKITregistryFiles->getError(), __LINE__); return false;
		}

		$allowed = array();
		foreach ($result as $item) {
			switch ($item[dbKITregistryFiles::field_protect]):
			case dbKITregistryFiles::protect_kit_dist:
				if (!empty($_SESSION[self::session_prefix.self::param_kit_dist])) {
					$user_grps = explode(',', $_SESSION[self::session_prefix.self::param_kit_dist]);
					$reg_grps = explode(',', $item[dbKITregistryFiles::field_protect_groups]);
					foreach ($user_grps as $grp) {
						if (in_array($grp, $reg_grps)) {
							$allowed[] = $item;
							continue;
						}
					}
				}
				break;
			case dbKITregistryFiles::protect_kit_intern:
				if (!empty($_SESSION[self::session_prefix.self::param_kit_intern])) {
					$user_grps = explode(',', $_SESSION[self::session_prefix.self::param_kit_intern]);
					$reg_grps = explode(',', $item[dbKITregistryFiles::field_protect_groups]);
					foreach ($user_grps as $grp) {
						if (in_array($grp, $reg_grps)) {
							$allowed[] = $item;
							continue;
						}
					}
				}
				break;
			case dbKITregistryFiles::protect_kit_news:
				if (!empty($_SESSION[self::session_prefix.self::param_kit_news])) {
					$user_grps = explode(',', $_SESSION[self::session_prefix.self::param_kit_news]);
					$reg_grps = explode(',', $item[dbKITregistryFiles::field_protect_groups]);
					foreach ($user_grps as $grp) {
						if (in_array($grp, $reg_grps)) {
							$allowed[] = $item;
							continue;
						}
					}
				}
				break;
			case dbKITregistryFiles::protect_wb_group:
				if (!empty($_SESSION[self::session_prefix.self::param_wb_group])) {
					$user_grps = explode(',', $_SESSION[self::session_prefix.self::param_wb_group]);
					$reg_grps = explode(',', $item[dbKITregistryFiles::field_protect_groups]);
					foreach ($user_grps as $grp) {
						if (in_array($grp, $reg_grps)) {
							$allowed[] = $item;
							continue;
						}
					}
				}
				break;
			case dbKITregistryFiles::protect_none:
				$allowed[] = $item;
				break;
			default:
				continue;
			endswitch;
		} // foreach
		if (count($allowed) < 1) {
			$this->setMessage(sprintf(reg_msg_search_no_hit, $search));
			return $this->showSearchDlg();
		}
		$items = array();
		foreach ($allowed as $hit) {
			$items[] = array(
				'file_registry'			=> $hit[dbKITregistryFiles::field_filename_registry],
				'file_original'			=> $hit[dbKITregistryFiles::field_filename_original],
				'file_size'					=> $registryTools->bytes2Str($hit[dbKITregistryFiles::field_filesize]),
				'file_datetime'			=> date(reg_cfg_datetime_str, $hit[dbKITregistryFiles::field_filemtime]),
				'description'				=> $hit[dbKITregistryFiles::field_description],
				'keywords'					=> $hit[dbKITregistryFiles::field_keywords],
				'content'						=> $hit[dbKITregistryFiles::field_content],
				'download'					=> sprintf('%s?%s=%s', $this->download_link, self::request_file, $hit[dbKITregistryFiles::field_id])
			);
		}
		$data = array(
			'header'			=> reg_header_search,
			'is_message'	=> $this->isMessage() ? 1 : 0,
			'info'				=> sprintf(reg_msg_search_hits, $search, count($allowed)),
			'hits'				=> $items,
			'go_back'			=> sprintf('%s?%s=%s', $this->page_link, self::request_action, self::action_search)
		);
		return $this->getTemplate('frontend.search.hits.dwoo', $data);
	} // checkSearch()

	/**
	 * Wird direkt von rdl.php aufgerufen, weil eine
	 * Berechtigung erforderlich ist
	 */
	public function execLoginDlg() {
		global $dbKITregistryFiles;
		if (!isset($_REQUEST[self::request_file]) || !is_numeric($_REQUEST[self::request_file])) {
			$this->setError(reg_error_file_id_invalid, __LINE__); return false;
		}
		$id = $_REQUEST[self::request_file];
		$where = array(dbKITregistryFiles::field_id => $id);
		$file = array();
		if (!$dbKITregistryFiles->sqlSelectRecord($where, $file)) {
			$this->setError($dbKITregistryFiles->getError(), __LINE__); return false;
		}
		if (count($file) < 1) {
			$this->setError(sprintf(reg_error_invalid_id, $id), __LINE__); return false;
		}
		$file = $file[0];
		$protect = $file[dbKITregistryFiles::field_protect];
		switch ($protect):
		case dbKITregistryFiles::protect_kit_dist:
			$_SESSION[self::session_prefix.self::param_kit_dist] = $file[dbKITregistryFiles::field_protect_groups];
			break;
		case dbKITregistryFiles::protect_kit_intern:
			$_SESSION[self::session_prefix.self::param_kit_intern] = $file[dbKITregistryFiles::field_protect_groups];
			break;
		case dbKITregistryFiles::protect_kit_news:
			$_SESSION[self::session_prefix.self::param_kit_news] = $file[dbKITregistryFiles::field_protect_groups];
			break;
		case dbKITregistryFiles::protect_wb_group:
			$_SESSION[self::session_prefix.self::param_wb_group] = $file[dbKITregistryFiles::field_protect_groups];
			break;
		default:
			$_SESSION[self::session_prefix.self::session_protect] = self::protect_none;
		endswitch;
		$this->checkProtection();

		$_SESSION[self::session_prefix.self::session_temp_act] = self::action_login;
		$_SESSION[self::session_prefix.self::session_temp_file] = $id;
		// check authentication and return login if neccessary...
  	if ((!$this->is_authenticated) && (is_string($login = $this->checkAuthentication()))) return $login;
		unset($_SESSION[self::session_prefix.self::session_temp_act]);
		unset($_SESSION[self::session_prefix.self::session_temp_file]);
 		$link = sprintf('%s?%s=%s', $this->download_link, self::request_file, $id);
		$data = array(
			'header'	=> reg_header_download_file,
			'content'	=> sprintf(reg_msg_download_now, $link, $file[dbKITregistryFiles::field_filename_registry])
		);
  	return $this->getTemplate('frontend.prompt.dwoo', $data);
	} // execLoginDlg()

} // class kitRegistry

?>