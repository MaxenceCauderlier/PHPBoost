<?php
/**
 * @copyright   &copy; 2005-2020 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Sebastien LARTIGUE <babsolune@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2021 03 16
 * @since       PHPBoost 5.2 - 2020 06 15
 * @contributor Julien BRISWALTER <j1.seth@phpboost.com>
*/

class PagesHomeController extends DefaultSeveralItemsController
{
	protected function build_view()
	{
		$authorized_categories = CategoriesService::get_authorized_categories(Category::ROOT_CATEGORY, true, self::$module_id);
		
		$this->view->put_all(array(
			'C_CONTROLS'             => AppContext::get_current_user()->get_level() == User::ADMIN_LEVEL,
			'C_CATEGORY_DESCRIPTION' => !empty($this->config->get_root_category_description()),
			'CATEGORY_DESCRIPTION'   => FormatingHelper::second_parse($this->config->get_root_category_description()),
			'TOTAL_ITEMS'            => self::get_items_manager()->count()
		));

		// Root category pages
		foreach (self::get_items_manager()->get_items($this->sql_condition, $this->sql_parameters) as $item)
		{
			$this->view->assign_block_vars('root_items', $item->get_template_vars());
		}

		foreach (CategoriesService::get_categories_manager(self::$module_id)->get_categories_cache()->get_categories() as $id => $category)
		{
			if ($id != Category::ROOT_CATEGORY && in_array($id, $authorized_categories))
			{
				$this->view->assign_block_vars('categories', array(
					'C_ITEMS'            => $category->get_elements_number() > 0,
					'C_SEVERAL_ITEMS'    => $category->get_elements_number() > 1,
					'ITEMS_NUMBER'       => $category->get_elements_number(),
					'CATEGORY_ID'        => $category->get_id(),
					'CATEGORY_SUB_ORDER' => $category->get_order(),
					'CATEGORY_PARENT_ID' => $category->get_id_parent(),
					'CATEGORY_NAME'      => $category->get_name(),
					'U_CATEGORY'         => ItemsUrlBuilder::display_category($category->get_id(), $category->get_rewrited_name(), self::$module_id)->rel(),
					'U_REORDER_ITEMS'    => PagesUrlBuilder::reorder_items($category->get_id(), $category->get_rewrited_name())->rel()
				));

				foreach (self::get_items_manager()->get_items($this->sql_condition, array('id_category' => $id)) as $item)
				{
					$this->view->assign_block_vars('categories.items', $item->get_template_vars());
				}
			}
		}
	}

	protected function get_template_to_use()
	{
		return new FileTemplate('pages/PagesHomeController.tpl');
	}
}
?>
