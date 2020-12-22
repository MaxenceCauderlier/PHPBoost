<?php
/**
 * @copyright   &copy; 2005-2020 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Julien BRISWALTER <j1.seth@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2020 12 22
 * @since       PHPBoost 5.0 - 2017 04 05
 * @contributor Sebastien LARTIGUE <babsolune@phpboost.com>
*/

class WebConfigUpdateVersion extends ConfigUpdateVersion
{
	public function __construct()
	{
		parent::__construct('web-config', false);
	}

	protected function build_new_config()
	{
		$old_config = $this->get_old_config();

		if (class_exists('WebConfig') && !empty($old_config))
		{
			$config = WebConfig::load();
			$sort_type = $sort_mode = '';

			try {
				$sort_type = $old_config->get_property('sort_type');
			} catch (PropertyNotFoundException $e) {}
			if ($sort_type)
				$config->set_partners_sort_field($sort_type);

			try {
				$sort_mode = $old_config->get_property('sort_mode');
			} catch (PropertyNotFoundException $e) {}
			if ($sort_mode)
				$config->set_partners_sort_mode($sort_mode);

			switch ($old_config->get_partners_sort_field())
			{
				case 'name':
					$config->set_partners_sort_field('title');
				break;
			}

			switch ($old_config->get_partners_sort_mode())
			{
				case 'name':
					$config->set_partners_sort_mode('title');
				break;
			}

			WebConfig::save();

			return true;
		}
		return false;
	}
}
?>
