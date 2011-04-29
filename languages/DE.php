<?php
/**
 * kitRegistry
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 * 
 * 	IMPORTANT NOTE:
 * 
 * If you are editing this file or creating a new language file
 * you must ensure that you SAVE THIS FILE UTF-8 ENCODED.
 * Otherwise all special chars will be destroyed and displayed improper!
 * 
 * It is NOT NECESSARY to mask special chars as HTML entities!
 * 
 * Translated to German (Original Source) by Ralf Hertsch
 *   
**/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die('invalid call of '.$_SERVER['SCRIPT_NAME']);

// Deutsche Modulbeschreibung
$module_description 	= 'kitRegistry - Dokumentenablage für KeepInTouch (KIT)';

// name of the person(s) who translated and edited this language file
$module_translation_by = 'Ralf Hertsch (phpManufaktur)';

define('reg_btn_abort',												'Abbruch');
define('reg_btn_ok',													'Übernehmen');
define('reg_btn_replicate',										'Starten');
define('reg_btn_account',											'Konto');
define('reg_btn_logout',											'Abmelden');

define('reg_cfg_currency',										'%s €'); 
define('reg_cfg_date_separator',							'.');
define('reg_cfg_date_str',										'd.m.Y');
define('reg_cfg_datetime_str',								'd.m.Y H:i');
define('reg_cfg_day_names',										"Sonntag, Montag, Dienstag, Mittwoch, Donnerstag, Freitag, Samstag");
define('reg_cfg_decimal_separator',          	',');
define('reg_cfg_month_names',									"Januar,Februar,März,April,Mai,Juni,Juli,August,September,Oktober,November,Dezember");
define('reg_cfg_thousand_separator',					'.');
define('reg_cfg_time_long_str',								'H:i:s');
define('reg_cfg_time_str',										'H:i'); 
define('reg_cfg_time_zone',										'Europe/Berlin');

define('reg_content_login_wb',								'<p>Damit Sie auf die Dateien in diesem Verzeichnis zugreifen können, müssen Sie sich zunächst <a href="%s">mit Ihrem Benutzernamen und Ihrem Passwort anmelden</a>.</p><p>Nach der Anmeldung werden Sie automatisch wieder hierher geleitet.</p>');
define('reg_content_login_kit',								'<p>Damit Sie auf die Dateien in diesem Verzeichnis zugreifen können, müssen Sie sich zunächst <a href="%s">mit Ihrem Benutzernamen und Ihrem Passwort anmelden</a>.</p><p>Nach der Anmeldung werden Sie automatisch wieder hierher geleitet.</p>');

define('reg_desc_cfg_allowed_files',					'Zulässige Dateitypen, legen Sie die entsprechenden Dateiendungen (Typen) fest - ohne führenden Punkt, Kleinschreibung, trennen Sie die einzelnen Dateiendungen mit einem Komma');
define('reg_desc_cfg_exec',										'Legen Sie fest, ob kitRegistry ausgeführt wird (1=JA, 0=Nein).');
define('reg_desc_cfg_list_tabs',							'Bestimmen Sie die Reihenfolge der TABs, die in der Übersichtsliste verwendet werden.');
define('reg_desc_cfg_registry_droplet',				'Geben Sie die PAGE ID der Seite an, auf der Sie das Droplet [[kit_registry]] einsetzen. kitRegistry benötigt diese Information, damit bei einem direkten Aufruf von Dateien ggf. der LOGIN Dialog aufgerufen werden kann.');

define('reg_error_cfg_id',										'<p>Der Konfigurationsdatensatz mit der <b>ID %05d</b> konnte nicht ausgelesen werden!</p>');
define('reg_error_cfg_name',									'<p>Zu dem Bezeichner <b>%s</b> wurde kein Konfigurationsdatensatz gefunden!</p>');
define('reg_error_file_no_protection_defined','<p>Sorry, diese Datei ist nicht f&uuml;r den Download freigegeben.</p>'); // please mask this string!
define('reg_error_file_not_available',				'<p>Die gew&uuml;nschte Datei steht nicht zur Verf&uuml;gung. Status: %s</p>'); // please mask this string!!!
define('reg_error_file_id_invalid',						'<p>Die übergebene Dateikennung ist ungültig!</p>');
define('reg_error_invalid_id',								'<p>Der Datensatz mit der <b>ID %05d</b> wurde nicht gefunden!</p>');
define('reg_error_kit_id_missing',						'<p>Der KeepInTouch (KIT) Datensatz mit der <b>ID %05d</b> wurde nicht gefunden!</p>');
define('reg_error_kit_not_installed',					'<p>KeepInTouch (KIT) ist nicht installiert!</p>');
define('reg_error_kit_param_rejected',				'<p>Da KeepInTouch (KIT) nicht auf diesem System installiert ist, kann der Parameter <b>%s</b> nicht verwendet werden!</p>');
define('reg_error_kit_register_id_missing',	 	'<p>Für die KIT Registrierung mit der <b>ID %05d<b> existiert kein gültiger Eintrag. Bitte wenden Sie sich an den Systemadministrator!</p>');
define('reg_error_missing_kit_category',			'<p>Die mit dem Parameter <b>%s</b> genannte(n) Kategorien <b>%s</b> wurden nicht gefunden! Prüfen Sie Ihre Angaben!</p>');
define('reg_error_missing_wb_group',					'<p>Die mit dem Parameter <b>%s</b> genannete Gruppe <b>%s</b> wurde nicht gefunden! Prüfen Sie Ihre Angaben!</p>');
define('reg_error_mkdir',											'<p>Das Verzeichnis <b>%s</b> konnte nicht angelegt werden!</p>');
define('reg_error_protection_undefined',			'<p>Es wurde nicht definiert auf welche Weise der Zugriff auf das Verzeichnis kontrolliert werden soll. Legen Sie eine KeepInTouch (KIT) Kategorie oder eine WebsiteBaker Benutzergruppe fest!</p>');
define('reg_error_registry_mkdir',						'<p>Das Dokumenten Verzeichnis für kitRegistry konnte nicht angelegt werden!</p>');
define('reg_error_rename_file',								'<p>Die Datei <b>%s</b> konnte nicht in <b>%s</b> umbenannt werden!</p>');
define('reg_error_template_error',						'<p>Fehler bei der Ausführung des Template <b>%s</b>:</p><p>%s</p>');
define('reg_error_unknown_param',							'<p>Der Parameter <b>%s</b> ist nicht definiert. Programmausführung gestoppt.</p>');
define('reg_error_unknown_param_key',					'<p>Der Parameter <b>%s</b> ist nicht definiert, bitte prüfen Sie die übergebenen Parameter!</p>');
define('reg_error_upload_form_size',					'<p>Die hochgeladene Datei überschreitet die in dem HTML Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigröße.</p>');
define('reg_error_upload_ini_size',						'<p>Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Größe von %s</p>');
define('reg_error_upload_move_file',					'<p>Die Datei <b>%s</b> konnte nicht in das Zielverzeichnis verschoben werden!</p>');
define('reg_error_upload_partial',						'<p>Die Datei <b>%s</b> wurde nur teilweise hochgeladen.</p>');
define('reg_error_upload_undefined_error',		'<p>Während der Datenübertragung ist ein nicht näher beschriebener Fehler aufgetreteten.</p>');
define('reg_error_wb_groups',									'<p>Fataler Fehler: Die WebsiteBaker Gruppen konnten nicht ausgelesen werden!</p>');
define('reg_error_wb_groups_undefined',				'<p>Fataler Fehler: WebsiteBaker Gruppen sind nicht gesetzt!</p>');
define('reg_error_wb_login_not_enabled',			'<p>Der Parameter <b>%s</b> kann nicht verwendet werden, da die Anmeldung ausgeschaltet ist.</p>');

define('reg_header_access_denied',						'Zugriff verweigert');
define('reg_header_cfg',											'Einstellungen');
define('reg_header_cfg_description',					'Erläuterung');
define('reg_header_cfg_identifier',						'Bezeichner');
define('reg_header_cfg_value',								'Wert');
define('reg_header_download_file',						'Datei herunterladen');
define('reg_header_error',										'kitRegistry Fehlermeldung');
define('reg_header_groups',										'Gruppen erstellen und bearbeiten');
define('reg_header_login',										'Anmeldung erforderlich');
define('reg_header_registry_edit',						'Dokument zu dem Archiv hinzufügen oder bearbeiten');
define('reg_header_registry_list',						'Übersicht über die archivierten Dokumente');
define('reg_header_search',										'Archiv durchsuchen');

define('reg_hint_content_groups',							'Legen Sie fest, welchen Gruppen das Dokument zugeordnet werden soll, es ist eine Mehrfachauswahl möglich');
define('reg_hint_file_upload',								'Laden Sie ein Dokument in das Archiv hoch oder ersetzen Sie ein bereits im Archiv befindliches Dokument durch eine andere Datei. Auf diesem Server sind Uploads bis zu einer Größe von <b>%s</b> pro Datei zulässig.');
define('reg_hint_filename_original',					'Ursprünglicher Dateiname des Dokument. kitRegistry benennt Dokument beim Upload ggf. um und entfernt Leerzeichen, Sonderzeichen usw.');
define('reg_hint_filename_registry',					'Dateiname des Dokument, wie er innerhalb von kitRegistry verwendet wird.');
define('reg_hint_group_desc',									'');
define('reg_hint_group_id',										'Legen Sie einen <b>Bezeichner</b> für die Gruppe fest. Dieser wird bei Aufrufen der der Dokumente als Parameter zur Programmsteuerung verwendet, verzichten Sie auf Leerzeichen, Sonderzeichen usw. und schreiben Sie den Bezeichner möglichst in Kleinbuchstaben.');
define('reg_hint_group_name',									'Legen Sie einen beliebigen Namen für die Gruppe fest, dieser wird Ihnen in den Dialogen angezeigt.');
define('reg_hint_registry_file_content',			'Entweder der vollständige Inhalt (um z.B. eine komplette Indizierung von PDF\'s sicherzustellen) oder eine ausführliche Beschreibung des Dokument (optional) - wird bei der Dokumentensuche berücksichtigt.');
define('reg_hint_registry_file_keywords',			'Schlüsselwörter bzw. -Begriffe - die Keywords werden bei der Dokumentensuche berücksichtigt.');
define('reg_hint_registry_file_description',	'Kurze Beschreibung des Inhaltes der Datei - die Beschreibung wird bei der Dokumentensuche berücksichtigt.');
define('reg_hint_registry_protect_groups',		'Führen Sie die entsprechende(n) Gruppe(n) auf, die dem Dokumentenschutz Typ zugeordnet werden.');
define('reg_hint_registry_protect_type',			'Legen Sie fest, ob und wenn ja in welcher Form dieses Dokument vor einem Zugriff geschützt wird.');
define('reg_hint_status',											''); 

define('reg_intro_cfg',												'<p>Bearbeiten Sie die Einstellungen für <b>kitRegistry</b>.</p>');
define('reg_intro_groups',										'<p>Mit diesem Dialog können Sie die Gruppen für <b>kitRegistry</b> erstellen und bearbeiten.</p><p>Um einen neuen Eintrag zu erstellen, geben Sie unten die Angaben für die Gruppe ein. Um eine bestehende Gruppe zu bearbeiten, wählen Sie diese in der Liste aus.</p>');
define('reg_intro_registry_edit',							'<p>Mit diesem Dialog fügen Sie ein neues Dokument zu <b>kitRegistry</b> hinzu oder bearbeiten ein bereits im Archiv befindliches Dokument.</p>');
define('reg_intro_registry_list',							'xx');
define('reg_intro_search',										'Geben Sie den Begriff ein, nach dem das Archiv durchsucht werden soll.');

define('reg_label_cfg_allowed_files',					'Erlaubte Dateitypen');
define('reg_label_cfg_exec',									'kitRegistry ausführen');
define('reg_label_cfg_list_tabs',							'Listen TAB\'s');
define('reg_label_cfg_registry_droplet',			'Login PAGE_ID');
define('reg_label_content',										'Inhalt der Datei');
define('reg_label_content_groups',						'zugeordnete Gruppen');
define('reg_label_description',								'Beschreibung');
define('reg_label_downloads',									'Downloads gesamt');
define('reg_label_download_last',							'letzter Download');
define('reg_label_file_upload',								'Datei übertragen');
define('reg_label_filemtime',									'Datei: letzte Änderung');
define('reg_label_filename_orginal',					'Original Dateiname');
define('reg_label_filename_registry',					'kitRegistry Dateiname');
define('reg_label_filepath',									'relativer Dateipfad');
define('reg_label_filesize',									'Dateigröße');
define('reg_label_group_desc',								'Beschreibung');
define('reg_label_group_id',									'Gruppen Bezeichner');
define('reg_label_group_name',								'Gruppen Name');
define('reg_label_id',												'ID');
define('reg_label_keywords',									'Schlüsselwörter');
define('reg_label_protect_groups',						'Dokumentenschutz, Gruppe(n)');
define('reg_label_protect_type',							'Dokumentenschutz, Typ');
define('reg_label_status',										'Status'); 

define('reg_msg_access_denied',								'<p>Sie sind nicht berechtigt auf diese Daten zuzugreifen.</p>');
define('reg_msg_cfg_id_updated',							'<p>Der Konfigurationsdatensatz mit dem Bezeichner <b>%s</b> wurde aktualisiert.</p>');
define('reg_msg_download_now',								'<p>Sie können die <a href="%s">Datei <b>%s</b> jetzt herunterladen</a>.</p>');
define('reg_msg_file_ext_not_allowed',				'<p>Die Datei <b>%s</b> wird nicht akzeptiert, es sind nur Dateien mit den Endungen <b>%s</b> zulässig.</p>');
define('reg_msg_group_id_invalid',						'<p>Der Gruppenbezeichner darf nicht leer sein und muss mindestens 5 Zeichen lang sein.</p>');
define('reg_msg_group_id_in_usage',						'<p>Der Gruppenbezeichner <b>%s</b> kann nicht verwendet werden, dieser Bezeicher ist bereits dem Datensatz mit der <b>ID %03d</b> zugeordnet!</p>');
define('reg_msg_group_inserted',							'<p>Die Gruppe mit der <b>ID %03d</b> wurde angelegt.</p>');
define('reg_msg_group_status_invalid',				'<p>Der Gruppenstatus <b>%s</b> kann nicht geändert werden, da ein gleichlautender Bezeicher <b>%s</b> bereits von dem Datensatz mit der <b>ID %03d</b> verwendet!');
define('reg_msg_group_update_successfull',		'<p>Die Gruppe mit der <b>ID %03d</b> wurde aktualisiert.</p>');
define('reg_msg_invalid_email',								'<p>Die E-Mail Adresse <b>%s</b> ist nicht gültig, bitte prüfen Sie Ihre Eingabe.</p>');
define('reg_msg_mkdir',												'<p>Das Verzeichnis <b>%s</b> wurde angelegt.</p>');
define('reg_msg_problem_unlink_registry_file','<p>Die dem Datensatz bisher zugeordnete Datei <b>%s</b> konnte nicht gelöscht werden!</p>');
define('reg_msg_registry_file_added',					'<p>Die Datei <b>%s</b> wurde kitRegistry hinzugefügt.</p>');
define('reg_msg_registry_file_moved',					'<p>Die Datei <b>%s</b> wurde in das Unterverzeichnis <b>/%s</b> verschoben.</p>');
define('reg_msg_registry_file_renamed',				'<p>Die Datei <b>%s</b> wurde in <b>%s</b> umbenannt.</p>');
define('reg_msg_registry_file_updated',				'<p>Die Daten der Datei <b>%s</b> wurden aktualisiert.</p>');
define('reg_msg_registry_file_undeleted',			'<p>Der Datensatz für die Datei <b>%s</b> wurde wieder aktiviert - die Datei war als gelöscht eingetragen.</p>');
define('reg_msg_registry_incomplete',					'<p>Der Datensatz kann nicht übernommen werden, es fehlt zumindest eine zugeordenete Datei.</p>');
define('reg_msg_registry_inserted',						'<p>Der Datensatz für die Datei <b>%s</b> wurde erfolgreich angelegt.</p>');
define('reg_msg_registry_mkdir',							'<p>Das Dokumenten Verzeichnis für kitRegistry wurde erfolgreich angelegt.</p>');
define('reg_msg_registry_updated',						'<p>Der Datensatz für die Datei <b>%s</b> wurde aktualisiert.</p>');
define('reg_msg_search_empty',								'<p>Sie haben keinen Suchbegriff eingegeben!</p>');
define('reg_msg_search_no_hit',								'<p>Die Suche nach dem Begriff <b>%s</b> ergab leider keinen Treffer!</p>');
define('reg_msg_search_hits',									'<p>Die Suche nach dem Begriff <b>%s</b> ergab <b>%d</b> Treffer:</p>');
define('reg_msg_tab_empty',										'<p>Dieser TAB enhält keine Einträge!</p>');

define('reg_protect_none',										'- nicht geschützt -');
define('reg_protect_undefined',								'- nicht festgelegt -');
define('reg_protect_kit_dist',								'KeepInTouch (KIT) - Verteiler');
define('reg_protect_kit_intern',							'KeepInTouch (KIT) - Intern');
define('reg_protect_kit_news',								'KeepInTouch (KIT) - Newsletter');
define('reg_protect_wb_group',								'WebsiteBaker - Gruppe(n)');
 
define('reg_status_active',										'Aktiv');
define('reg_status_deleted',									'Gelöscht');
define('reg_status_locked',										'Gesperrt');
define('reg_status_removed',									'Entfernt');
define('reg_status_outdated',									'Abgelaufen');

define('reg_tab_about',												'?');
define('reg_tab_config',											'Einstellungen');
define('reg_tab_edit',												'Bearbeiten');
define('reg_tab_group',												'Gruppen');
define('reg_tab_list',												'Übersicht');

define('reg_text_replicate',									'Registry Verzeichnis und Datenbank abgleichen');
define('reg_text_undetermined',								'- nicht festgelegt -');

define('reg_th_id',														'ID');
define('reg_th_filename',											'Datei');
define('reg_th_group',												'Gruppe');
define('reg_th_group_id',											'Bezeichner');
define('reg_th_group_name',										'Gruppenname');
define('reg_th_status',												'Status');
define('reg_th_filemtime',										'letzte Änderung');
define('reg_th_filesize',											'Dateigröße');

?>