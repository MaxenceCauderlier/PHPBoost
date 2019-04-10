<?php
/**
 * @copyright 	&copy; 2005-2019 PHPBoost
 * @license 	https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Julien BRISWALTER <j1.seth@phpboost.com>
 * @version   	PHPBoost 5.3 - last update: 2019 04 09
 * @since   	PHPBoost 4.0 - 2014 05 22
*/

class WebModuleUpdateVersion extends ModuleUpdateVersion
{
	public function __construct()
	{
		parent::__construct('web');
		
		$this->content_tables = array(PREFIX . 'web');
		$this->delete_old_files_list = array(
			'/phpboost/WebNewContent.class.php',
			'/phpboost/WebNotation.class.php'
		);
	}
}
?>
