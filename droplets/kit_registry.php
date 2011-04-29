//:Access to kitRegistry - the document registry for KeepInTouch (KIT)
//:Please visit http://phpManufaktur.de for informations about kitRegistry!
/**
 * kitRegistry
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 */
if (file_exists(WB_PATH.'/modules/kit_registry/class.frontend.php')) {
  require_once(WB_PATH.'/modules/kit_registry/class.frontend.php');
  $kitRegistry = new kitRegistry(); 
	$params = $kitRegistry->getParams();
	if (isset($kit_intern)) $params[kitRegistry::param_kit_intern] = $kit_intern;
  if (isset($kit_news)) $params[kitRegistry::param_kit_news] = $kit_news;
  if (isset($kit_dist)) $params[kitRegistry::param_kit_dist] = $kit_dist;
  if (isset($wb_group)) $params[kitRegistry::param_wb_group] = $wb_group;
  
	$kitRegistry->setParams($params);
	return $kitRegistry->action();
}
else {
	return "kitRegistry is not installed!";
}  
?>