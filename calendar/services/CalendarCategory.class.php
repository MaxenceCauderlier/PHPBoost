<?php
/**
 * @copyright   &copy; 2005-2020 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Julien BRISWALTER <j1.seth@phpboost.com>
 * @version     PHPBoost 5.3 - last update: 2019 12 24
 * @since       PHPBoost 4.0 - 2013 02 25
*/

class CalendarCategory extends Category
{
	public static function __static()
	{
		self::add_additional_attribute('color', array('type' => 'string', 'length' => 250, 'default' => "''"));
	}
	
	public function get_color()
	{
		return $this->get_additional_property('color');
	}
}
?>
