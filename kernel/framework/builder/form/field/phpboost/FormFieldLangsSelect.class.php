<?php
/*##################################################
 *                             FormFieldLangsSelect.class.php
 *                            -------------------
 *   begin                : September 26, 2011
 *   copyright            : (C) 2011 Kevin MASSY
 *   email                : kevin.massy@phpboost.com
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
 * @desc
 * @package {@package}
 */
class FormFieldLangsSelect extends FormFieldSimpleSelectChoice
{
	private $check_authorizations = false;
	
    /**
     * @desc Constructs a FormFieldLangsSelect.
     * @param string $id Field id
     * @param string $label Field label
     * @param mixed $value Default value (either a FormFieldEnumOption object or a string corresponding to the FormFieldEnumOption's raw value)
     * @param string[] $field_options Map of the field options (this field has no specific option, there are only the inherited ones)
     * @param FormFieldConstraint List of the constraints
     */
    public function __construct($id, $label, $value = 0, $field_options = array(), array $constraints = array())
    {
        parent::__construct($id, $label, $value, $this->generate_options(), $field_options, $constraints);
    }

    private function generate_options()
	{
		$options = array();
		foreach (LangsManager::get_activated_langs_map() as $lang)
		{
			if ($this->check_authorizations)
			{
				if ($lang->check_auth())
				{
					$options[] = new FormFieldSelectChoiceOption($lang->get_configuration()->get_name(), $lang->get_id());
				}
			}
			else
			{
				$options[] = new FormFieldSelectChoiceOption($lang->get_configuration()->get_name(), $lang->get_id());
			}
		}
		return $options;
	}
	
	protected function compute_options(array &$field_options)
    {
        foreach ($field_options as $attribute => $value)
        {
            $attribute = TextHelper::strtolower($attribute);
            switch ($attribute)
            {
				case 'check_authorizations' :
                    $this->check_authorizations = (bool)$value;
                    unset($field_options['check_authorizations']);
                    break;
            }
        }
        parent::compute_options($field_options);
    }
}
?>