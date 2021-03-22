<?php
/**
 * @copyright   &copy; 2005-2020 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Julien BRISWALTER <j1.seth@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2021 03 22
 * @since       PHPBoost 4.0 - 2014 03 04
 * @contributor Kevin MASSY <reidlos@phpboost.com>
 * @contributor Arnaud GENET <elenwii@phpboost.com>
 * @contributor Sebastien LARTIGUE <babsolune@phpboost.com>
*/

class CalendarAjaxEventsController extends AbstractController
{
	private $lang;
	private $view;
	private $year;
	private $month;
	private $day;
	private $id_category;

	public function set_year($year)
	{
		$this->year = $year;
	}

	public function set_month($month)
	{
		$this->month = $month;
	}

	public function set_day($day)
	{
		$this->day = $day;
	}

	public function set_id_category($id_category)
	{
		$this->id_category = $id_category;
	}

	public function execute(HTTPRequestCustom $request)
	{
		$this->init($request);
		$this->build_view($request);
		return new SiteNodisplayResponse($this->view);
	}

	private function build_view(HTTPRequestCustom $request)
	{
		$db_querier = PersistenceContext::get_querier();

		$date_lang = LangLoader::get('date-common');
		$items_list = $participants = array();

		$config = CalendarConfig::load();
		$comments_config = CommentsConfig::load();
		$id_category = $this->id_category ? $this->id_category : $request->get_int('id_category', 0);
		$authorized_categories = $id_category == Category::ROOT_CATEGORY ? CategoriesService::get_authorized_categories($id_category) : array($id_category);
		
		$year = $this->year ? $this->year : $request->get_int('calendar_ajax_year', date('Y'));
		$month = $this->month ? $this->month : $request->get_int('calendar_ajax_month', date('n'));
		$day = $this->day ? $this->day : $request->get_int('calendar_ajax_day', 0);
		$bissextile = (date("L", mktime(0, 0, 0, 1, 1, $year)) == 1) ? 29 : 28;

		$array_month = array(31, $bissextile, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		$array_l_month = array($date_lang['january'], $date_lang['february'], $date_lang['march'], $date_lang['april'], $date_lang['may'], $date_lang['june'], $date_lang['july'], $date_lang['august'], $date_lang['september'], $date_lang['october'], $date_lang['november'], $date_lang['december']);

		$month_days = $array_month[$month - 1];

		$items_number = 0;

		$result = $db_querier->select("SELECT *
		FROM " . CalendarSetup::$calendar_events_table . " event
		LEFT JOIN " . CalendarSetup::$calendar_events_content_table . " event_content ON event_content.id = event.content_id
		LEFT JOIN " . DB_TABLE_MEMBER . " member ON member.user_id = event_content.author_user_id
		LEFT JOIN " . DB_TABLE_COMMENTS_TOPIC . " com ON com.id_in_module = event.id_event AND com.module_id = 'calendar'
		WHERE approved = 1
		AND ((start_date BETWEEN :first_day_hour AND :last_day_hour) OR (end_date BETWEEN :first_day_hour AND :last_day_hour) OR (:first_day_hour BETWEEN start_date AND end_date))
		AND id_category IN :authorized_categories
		ORDER BY start_date ASC", array(
			'first_day_hour' => mktime(0, 0, 0, $month, ($day ? $day : 1), $year),
			'last_day_hour' => mktime(23, 59, 59, $month, ($day ? $day : $month_days), $year),
			'authorized_categories' => $authorized_categories
		));

		while ($row = $result->fetch())
		{
			$item = new CalendarItem();
			$item->set_properties($row);

			if (CategoriesAuthorizationsService::check_authorizations($item->get_content()->get_id_category(), 'calendar')->read())
			{
				$items_list[$item->get_id()] = $item;
				$items_number++;
			}
		}
		$result->dispose();

		$this->view->put_all(array(
			'C_COMMENTS_ENABLED' => $comments_config->module_comments_is_enabled('calendar'),
			'C_ITEMS' => $items_number > 0,
			'C_DAY' => $day,
			'DATE' => ($day ? $day . ' ' : '') . $array_l_month[$month - 1] . ' ' . $year,
			'L_ITEMS_NUMBER' => $items_number > 1 ? StringVars::replace_vars($this->lang['calendar.labels.events.number'], array('items_number' => $items_number)) : $this->lang['calendar.labels.one.event'],
		));

		if (!empty($items_list))
		{
			$result = $db_querier->select('SELECT event_id, member.user_id, display_name, level, user_groups
			FROM ' . CalendarSetup::$calendar_users_relation_table . ' participants
			LEFT JOIN ' . DB_TABLE_MEMBER . ' member ON member.user_id = participants.user_id
			WHERE event_id IN :events_list', array(
				'events_list' => array_keys($items_list)
			));

			while($row = $result->fetch())
			{
				if (!empty($row['display_name']))
				{
					$participant = new CalendarEventParticipant();
					$participant->set_properties($row);
					$participants[$row['event_id']][$participant->get_user_id()] = $participant;
				}
			}
			$result->dispose();

			foreach ($items_list as $item)
			{
				if (isset($participants[$item->get_id()]))
					$item->set_participants($participants[$item->get_id()]);

				$this->view->assign_block_vars('items', $item->get_array_tpl_vars());

				$participants_number = count($item->get_participants());
				$i = 0;
				foreach ($item->get_participants() as $participant)
				{
					$i++;
					$this->view->assign_block_vars('items.participant', array_merge($participant->get_array_tpl_vars(), array(
						'C_LAST_PARTICIPANT' => $i == $participants_number
					)));
				}
			}
		}
	}

	private function init(HTTPRequestCustom $request)
	{
		$this->lang = LangLoader::get('common', 'calendar');
		$this->view = new FileTemplate('calendar/CalendarAjaxEventsController.tpl');
		$this->view->add_lang($this->lang);

		if ($request->has_getparameter('id_category'))
			$this->set_id_category($request->get_getvalue('id_category'));
	}

	public static function get_view($year = 0, $month = 0, $day = 0, $id_category = Category::ROOT_CATEGORY)
	{
		$request = AppContext::get_request();
		$object = new self();
		$object->init($request);
		if ($year)
			$object->set_year($year);
		if ($month)
			$object->set_month($month);
		if ($day)
			$object->set_day($day);
		$object->set_id_category($id_category);
		$object->build_view($request);
		return $object->view;
	}
}
?>
