<?php
/*##################################################
 *             ExtendedField.class.php
 *                            -------------------
 *   begin                : August 14, 2010
 *   copyright            : (C) 2010 Kevin MASSY
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
 
 /**
 * @author Kevin MASSY <kevin.massy@phpboost.com>
 * @desc This class represente a extended field
 * @package {@package}
 */
class ExtendedField
{
	const READ_PROFILE_AUTHORIZATION = 1;
	const READ_EDIT_AND_ADD_AUTHORIZATION = 2;
	
	private $id;
	private $name;
	private $position;
	private $field_name;
	private $field_type;
	private $description;
	private $possible_values;
	private $default_value;
	private $required;
	private $display;
	private $regex;
	private $freeze;
	private $authorization;
	private $is_not_installer = false;
	
	public function set_id($id)
	{
		$this->id = $id;
	}
	
	public function get_id()
	{
		return !empty($this->id) ? $this->id : 0;
	}
	
	public function set_name($name)
	{
		$this->name = $name;
	}
	
	public function get_name()
	{
		return !empty($this->name) ? $this->name : '';
	}
	
	public function set_position($position)
	{
		$this->position = $position;
	}
	
	public function get_position()
	{
		if (empty($this->position))
		{
			$request = PersistenceContext::get_querier()->get_column_value(DB_TABLE_MEMBER_EXTENDED_FIELDS_LIST, 'MAX(position) + 1', '');
			$this->position = !empty($request) ? $request : 1;
		}
		return $this->position;
	}
	
	public function set_field_name($field_name)
	{
		$this->field_name = $field_name;
	}
	
	public function get_field_name()
	{
		if (empty($this->field_name) && !empty($this->name))
		{
			$this->field_name = $this->rewrite_field_name($this->name);
		}
		return !empty($this->field_name) ? $this->field_name : '';
	}
	
	public function set_description($description)
	{
		$this->description = $description;
	}
	
	public function get_description()
	{
		return !empty($this->description) ? $this->description : '';
	}
	
	/*
	 * Containing $field_type personal classe name herite of AbstractMemberExtendedField
	*/
	public function set_field_type($field_type)
	{
		$this->field_type = $field_type;
	}
	
	public function get_field_type()
	{
		return $this->field_type;
	}       
	
	public function set_possible_values($possible_values)
	{
		$this->possible_values = $possible_values;
	}
	
	public function get_possible_values()
	{
		return !empty($this->possible_values) ? $this->possible_values : '';
	}
	
	public function set_default_value($default_value)
	{
		$this->default_value = $default_value;
	}
	
	public function get_default_value()
	{
		return !empty($this->default_value) ? $this->default_value : '';
	}
	
	public function set_is_required($required)
	{
		$this->required = $required;
	}
	
	public function get_required()
	{
		return !empty($this->required) ? $this->required : false;
	}
	
	public function set_display($values)
	{
		$this->display = $values;
	}
	
	public function get_display()
	{
		return !empty($this->display) ? $this->display : false;
	}
	
	public function set_regex($regex)
	{
		$this->regex = $regex;
	}
	
	public function get_regex()
	{
		return !empty($this->regex) ? $this->regex : 0;
	}
	
	public function set_is_freeze($freeze)
	{
		$this->freeze = $freeze;
	}
	
	public function get_is_freeze()
	{
		return !empty($this->freeze) ? $this->freeze : false;
	}
	
	public function set_authorization($authorization)
	{
		$this->authorization = $authorization;
	}
	
	public function get_authorization()
	{
		return !empty($this->authorization) ? $this->authorization : array('r1' => 3, 'r0' => 3, 'r-1' => 2);
	}
	
	public function set_is_not_installer($is_not_installer)
	{
		$this->is_not_installer = $is_not_installer;
	}
	
	public function get_is_not_installer()
	{
		return $this->is_not_installer;
	}
	
	public function get_instance()
	{
		$field_type = $this->get_field_type();
		if (!empty($field_type))
		{
			$class = (string)$field_type;
			return new $class();
		}
	}
	
	public static function rewrite_field_name($field_name)
	{
		$field = TextHelper::strtolower($field_name);
		$field = Url::encode_rewrite($field);
		$field = str_replace('-', '_', $field);
		return 'f_' . $field;
	}
}
?>