<?php
/**
 * @copyright   &copy; 2005-2020 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Julien BRISWALTER <j1.seth@phpboost.com>
 * @version     PHPBoost 5.3 - last update: 2019 12 18
 * @since       PHPBoost 5.3 - 2019 12 18
*/

class DefaultAdminDisplayResponse extends AdminMenuDisplayResponse
{
	public function __construct($view)
	{
		parent::__construct($view);

		$title = StringVars::replace_vars(LangLoader::get_message('configuration.module.title', 'admin-common'), array('module_name' => $this->module->get_configuration()->get_name()));
		$this->set_title($title);

		$this->add_link(LangLoader::get_message('configuration', 'admin-common'), $this->module->get_configuration()->get_admin_main_page());
		$this->add_link(LangLoader::get_message('module.documentation', 'admin-modules-common'), $this->module->get_configuration()->get_documentation());

		$env = $this->get_graphical_environment();
		$env->set_page_title($title);
	}
}
?>