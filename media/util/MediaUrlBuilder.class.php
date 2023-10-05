<?php
/**
 * @copyright 	&copy; 2005-2019 PHPBoost
 * @license 	https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Julien BRISWALTER <j1.seth@phpboost.com>
 * @version   	PHPBoost 5.2 - last update: 2023 10 05
 * @since   	PHPBoost 4.0 - 2015 02 02
 * @contributor Sebastien LARTIGUE <babsolune@phpboost.com>
*/

class MediaUrlBuilder
{
	private static $dispatcher = '/media';

	/**
	 * @return Url
	 */
	public static function configuration()
	{
		return DispatchManager::get_url(self::$dispatcher, '/admin/config');
	}

	/**
	 * @return Url
	 */
	public static function add_category()
	{
		return DispatchManager::get_url(self::$dispatcher, '/categories/add/');
	}

	/**
	 * @return Url
	 */
	public static function edit_category($id)
	{
		return DispatchManager::get_url(self::$dispatcher, '/categories/'. $id .'/edit/');
	}

	/**
	 * @return Url
	 */
	public static function delete_category($id)
	{
		return DispatchManager::get_url(self::$dispatcher, '/categories/'. $id .'/delete/');
	}

	/**
	 * @return Url
	 */
	public static function manage_categories()
	{
		return DispatchManager::get_url(self::$dispatcher, '/categories/');
	}

	/**
	 * @return Url
	 */
	public static function manage()
	{
		return new Url(PATH_TO_ROOT. '/media/moderation_media.php');
	}

	/**
	 * @return Url
	 */
	public static function display_category($id, $rewrited_name, $page = 1, $subcategories_page = 1)
	{
		$page = $page !== 1 || $subcategories_page !== 1 ? '&p=' . $page : '';
		return new Url(PATH_TO_ROOT. '/media/' . url('media.php?cat=' . $id . $page, 'media-0-' . $id . ($page > 1 ? '-' . $page : '') . '-' . $rewrited_name . '.php'));
	}

	/**
	 * @return Url
	 */
	public static function add($id_category = null)
	{
		return new Url(PATH_TO_ROOT. '/media/media_action.php');
	}

	/**
	 * @return Url
	 */
	public static function home()
	{
		return DispatchManager::get_url(self::$dispatcher, '/');
	}
}
?>
