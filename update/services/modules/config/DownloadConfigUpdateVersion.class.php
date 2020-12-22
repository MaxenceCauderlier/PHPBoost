<?php
/**
 * @copyright   &copy; 2005-2020 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Julien BRISWALTER <j1.seth@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2020 12 22
 * @since       PHPBoost 6.0 - 2020 05 06
 * @contributor Sebastien LARTIGUE <babsolune@phpboost.com>
*/

class DownloadConfigUpdateVersion extends ConfigUpdateVersion
{
	public function __construct()
	{
		parent::__construct('download-config', false);
	}

	protected function build_new_config()
	{
		$old_config = $this->get_old_config();

		if (class_exists('DownloadConfig') && !empty($old_config))
		{
			$config = DownloadConfig::load();

			switch ($old_config->get_items_default_sort_field())
			{
				case 'name':
					$config->set_items_default_sort_field('title');
				break;
				case 'updated_date':
					$config->set_items_default_sort_field('update_date');
				break;
				case 'number_downloads':
					$config->set_items_default_sort_field('downloads_number');
				break;
				case 'number_view':
					$config->set_items_default_sort_field('views_number');
				break;
				default:
					$config->set_items_default_sort_field($old_config->get_items_default_sort_field());
				break;
			}

			$config->set_items_default_sort_mode(in_array(TextHelper::strtoupper($old_config->get_items_default_sort_field()), array(Item::ASC, Item::DESC)) ? TextHelper::strtolower($old_config->get_items_default_sort_field()) : TextHelper::strtolower(Item::DESC));

			switch ($old_config->get_sort_type())
			{
				case 'name':
					$config->set_sort_type('title');
				break;
				case 'updated_date':
					$config->set_sort_type('update_date');
				break;
				case 'number_downloads':
					$config->set_sort_type('downloads_number');
				break;
				case 'number_view':
					$config->set_sort_type('views_number');
				break;
				default:
					$config->set_sort_type($old_config->get_sort_type());
				break;
			}

			DownloadConfig::save();

			return true;
		}
		return false;
	}
}
?>
