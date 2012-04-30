<?php
/*##################################################
 *                              BugtrackerComments.class.php
 *                            -------------------
 *   begin                : April 27, 2012
 *   copyright            : (C) 2012 Julien BRISWALTER
 *   email                : julien.briswalter@gmail.com
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

class BugtrackerComments extends AbstractCommentsExtensionPoint
{
	public function get_authorizations($module_id, $id_in_module)
	{
		global $BUGS_CONFIG;
		
		require_once(PATH_TO_ROOT .'/'. $module_id . '/bugtracker_constants.php');
		
		$authorizations = new CommentsAuthorizations();
		$authorizations->set_authorized_access_module(AppContext::get_current_user()->check_auth($BUGS_CONFIG['auth'], BUG_READ_AUTH_BIT));
		return $authorizations;
	}
	
	public function is_display($module_id, $id_in_module)
	{
		return true;
	}
	
	public function get_url_built($module_id, $id_in_module, Array $parameters)
	{
		$url_parameters = '';
		foreach ($parameters as $name => $value)
		{
				$url_parameters .= '&' . $name . '=' . $value;
		}
		return new Url('/bugtracker/bugtracker.php?view=true&id=' . $id_in_module . '&com=0' . $url_parameters);
	}
}
?>
