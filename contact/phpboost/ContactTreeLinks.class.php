<?php
/**
 * @copyright   &copy; 2005-2020 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Julien BRISWALTER <j1.seth@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2020 12 03
 * @since       PHPBoost 4.0 - 2013 11 23
 * @contributor xela <xela@phpboost.com>
 * @contributor Sebastien LARTIGUE <babsolune@phpboost.com>
*/

class ContactTreeLinks implements ModuleTreeLinksExtensionPoint
{
	public function get_actions_tree_links()
	{
		$tree = new ModuleTreeLinks();

		$manage_fields_link = new AdminModuleLink(LangLoader::get_message('contact.fields.management', 'common', 'contact'), ContactUrlBuilder::manage_fields());
		$manage_fields_link->add_sub_link(new AdminModuleLink(LangLoader::get_message('contact.fields.management', 'common', 'contact'), ContactUrlBuilder::manage_fields()));
		$manage_fields_link->add_sub_link(new AdminModuleLink(LangLoader::get_message('fields.action.add_field', 'admin-user-common'), ContactUrlBuilder::add_field()));
		$tree->add_link($manage_fields_link);

		$tree->add_link(new AdminModuleLink(LangLoader::get_message('configuration', 'admin-common'), ContactUrlBuilder::configuration()));

		if (ModulesManager::get_module('contact')->get_configuration()->get_documentation())
			$tree->add_link(new AdminModuleLink(LangLoader::get_message('module.documentation', 'admin-modules-common'), ModulesManager::get_module('contact')->get_configuration()->get_documentation()));

		return $tree;
	}
}
?>
