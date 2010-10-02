<?php
/*##################################################
 *                    SimpleVarTemplateSyntaxElement.class.php
 *                            -------------------
 *   begin                : July 10 2010
 *   copyright            : (C) 2010 Loic Rouchon
 *   email                : horn@phpboost.com
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

class SimpleVarTemplateSyntaxElement extends AbstractTemplateSyntaxElement
{
	public static function is_element(StringInputStream $input)
	{
		return $input->assert_next('\w+');
	}
	
	public function parse(StringInputStream $input, StringOutputStream $output)
	{
		$matches = array();
		if ($input->consume_next('(?P<var>\w+)', '', $matches))
		{
			$varname = $matches['var'];
			$output->write(TemplateSyntaxElement::DATA . '->get_var(\'' . $varname . '\')');
		}
		else
		{
			throw new TemplateParserException('invalid simple variable name', $input);
		}
	}
}
?>