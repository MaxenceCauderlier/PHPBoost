<?php
/*##################################################
 *                      AdminNewsletterStreamsListController.class.php
 *                            -------------------
 *   begin                : March 11, 2011
 *   copyright            : (C) 2011 Kevin MASSY
 *   email                : kevin.massy@phpboost.com
 *
 *
 ###################################################
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 ###################################################*/

class AdminNewsletterStreamsListController extends AdminModuleController
{
	private $lang;
	private $view;
	private $user;
	
	private $nbr_categories_per_page = 25;

	public function execute(HTTPRequestCustom $request)
	{
		$this->init();
		$this->build_form($request);

		return new AdminNewsletterDisplayResponse($this->view, $this->lang['streams.add']);
	}

	private function build_form($request)
	{
		$field = $request->get_value('field', 'name');
		$sort = $request->get_value('sort', 'top');
		$current_page = $request->get_int('page', 1);
		
		if (!$this->user->check_auth(NewsletterConfig::load()->get_authorizations(), NewsletterConfig::AUTH_READ_SUBSCRIBERS))
		{
			$error_controller = PHPBoostErrors::unexisting_page();
			DispatchManager::redirect($error_controller);
		}
		
		$mode = ($sort == 'top') ? 'ASC' : 'DESC';
		
		switch ($field)
		{
			case 'name' :
				$field_bdd = 'name';
			break;
			case 'status' :
				$field_bdd = 'visible';
			break;
			default :
				$field_bdd = 'name';
		}
		
		$nbr_cats = PersistenceContext::get_sql()->count_table(NewsletterSetup::$newsletter_table_streams, __LINE__, __FILE__);
		
		$pagination = new ModulePagination($current_page, $nbr_cats, $this->nbr_categories_per_page);
		$pagination->set_url(NewsletterUrlBuilder::streams($field .'/'. $sort .'/%d'));

		if ($pagination->current_page_is_empty() && $current_page > 1)
		{
			$error_controller = PHPBoostErrors::unexisting_page();
			DispatchManager::redirect($error_controller);
		}
		
		$this->view->put_all(array(
			'C_STREAMS_EXIST' => (float)$nbr_cats,
			'C_ADD_STREAM' => NewsletterUrlBuilder::add_stream()->rel(),
			'C_PAGINATION' => $pagination->has_several_pages(),
			'SORT_NAME_TOP' => NewsletterUrlBuilder::streams('name/top/'. $current_page)->rel(),
			'SORT_NAME_BOTTOM' => NewsletterUrlBuilder::streams('name/bottom/'. $current_page)->rel(),
			'SORT_STATUS_TOP' => NewsletterUrlBuilder::streams('status/top/'. $current_page)->rel(),
			'SORT_STATUS_BOTTOM' => NewsletterUrlBuilder::streams('status/bottom/'. $current_page)->rel(),
			'PAGINATION' => $pagination->display()
		));

		$result = PersistenceContext::get_querier()->select("SELECT cat.id, cat.name, cat.description, cat.visible
		FROM " . NewsletterSetup::$newsletter_table_streams . " cat
		ORDER BY ". $field_bdd ." ". $mode ."
		LIMIT :number_items_per_page OFFSET :display_from",
			array(
				'number_items_per_page' => $pagination->get_number_items_per_page(),
				'display_from' => $pagination->get_display_from()
			), SelectQueryResult::FETCH_ASSOC
		);
		while ($row = $result->fetch())
		{
			$this->view->assign_block_vars('streams_list', array(
				'EDIT_LINK' => NewsletterUrlBuilder::edit_stream($row['id'])->rel(),
				'DELETE_LINK' => NewsletterUrlBuilder::delete_stream($row['id'])->rel(),
				'NAME' => $row['name'],
				'DESCRIPTION' => $row['description'],
				'STATUS' => !$row['visible'] ? $this->lang['streams.visible-no'] : $this->lang['streams.visible-yes']
			));
		}
		$result->dispose();
	}
	
	private function init()
	{
		$this->lang = LangLoader::get('common', 'newsletter');
		$this->view = new FileTemplate('newsletter/AdminNewsletterStreamsListController.tpl');
		$this->view->add_lang($this->lang);
		$this->user = AppContext::get_current_user();
	}
}
?>