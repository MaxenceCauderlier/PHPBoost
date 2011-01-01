<?php
/*##################################################
 *                      FormFieldMultipleCheckbox.class.php
 *                            -------------------
 *   begin                : November 20, 2010
 *   copyright            : (C) 2010 Sautel Benoit
 *   email                : ben.popeye@phpboost.com
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
 * @author Benoit Sautel <ben.popeye@phpboost.com>
 * @desc This class represents a field which contains several options that can be selected simultaneously.
 * @package {@package}
 */
class FormFieldMultipleCheckbox extends AbstractFormField
{
	private $available_options;
	
    /**
     * @desc Constructs a FormFieldCheckbox.
     * @param string $id Field identifier
     * @param string $label Field label
     * @param FormFieldMultipleCheckboxOption[] $selected_options The selected options (can also be an array of string where strings are identifiers of selected options)
     * @param FormFieldMultipleCheckboxOption[] $available_options All the options managed by the field
     * @param string[] $field_options Map containing the options
     * @param FormFieldConstraint[] $constraints The constraints checked during the validation
     */
    public function __construct($id, $label, array $selected_options, array $available_options, array $field_options = array(), array $constraints = array())
    {
        parent::__construct($id, $label, null, $field_options, $constraints);
    	$this->available_options = $available_options;
    	$this->set_selected_options($selected_options);
    }
    
    private function set_selected_options(array $selected_options)
    {
    	$value = array();
    	foreach ($selected_options as $option)
    	{
    		if (is_string($option))
    		{
    			$value[] = $this->get_option($option);
    		}
    		else if ($option instanceof FormFieldMultipleCheckboxOption)
    		{
    			$value[] = $option;
    		}
    		else
    		{
    			throw new FormBuilderException('option ' . $option . ' isn\'t recognized');
    		}
    	}
    	$this->set_value($value);
    }
    
    private function get_option($identifier)
    {
    	foreach ($this->available_options as $option)
    	{
    		if ($option->get_id() == $identifier)
    		{
    			return $option;
    		}
    	}
    	throw new FormBuilderException('option ' . $identifier . ' not found');
    }

    /**
     * {@inheritdoc}
     */
    public function display()
    {
        $template = $this->get_template_to_use();

        $this->assign_common_template_variables($template);

        $template->assign_block_vars('fieldelements', array(
			'ELEMENT' => $this->generate_html_code()->render()
        ));

        return $template;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieve_value()
    {
        $request = AppContext::get_request();
        if ($request->has_parameter($this->get_html_id()))
        {
            $this->set_value($request->get_value($this->get_html_id()) == 'on' ? true : false);
        }
        else
        {
            $this->set_value(false);
        }
    }

    /**
	 * @return Template
     */
    private function generate_html_code($option)
    {
        $tpl_src = '# START choice #
        <input type="checkbox" name="${escape(choice.NAME)}" id="${escape(choice.ID)}" # IF choice.C_CHECKED # checked="checked" # ENDIF # />&nbsp;${escape(choice.NAME)}<br />
        # END choice #';
		
        $rows = array();
        foreach ($this->available_options as $option)
        {
        	$rows[] = array(
        		'NAME' => $option->get_name(),
        		'ID' => $option->get_id(),
        		'C_CHECKED' => $this->is_selected($option)
        	);
        }
        
        $tpl = new StringTemplate($tpl_src);
        $tpl->put_all(array('choice' => $rows));

        return $tpl;
    }
    
    private function is_selected(FormFieldMultipleCheckboxOption $option)
    {
    	return in_array($option, $this->get_value()); 
    }

    protected function get_default_template()
    {
        return new FileTemplate('framework/builder/form/FormField.tpl');
    }
}

?>