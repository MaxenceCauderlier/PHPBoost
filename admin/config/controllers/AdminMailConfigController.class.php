<?php
/*##################################################
 *                       AdminMailConfigController.class.php
 *                            -------------------
 *   begin                : April 12, 2010
 *   copyright            : (C) 2010 Benoit Sautel
 *   email                : ben.popeye@phpboost.com
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

class AdminMailConfigController extends AbstractAdminFormPageController
{
	private $lang;

	/**
	 * @var MailServiceConfig
	 */
	private $config;
	
	private $submit_button;
	
	public function __construct()
	{
		$this->lang = LangLoader::get('admin-config-common');
		parent::__construct(LangLoader::get_message('message.success.config', 'status-messages-common'));
		$this->config = MailServiceConfig::load();
	}

	protected function create_form()
	{
		$form = new HTMLForm(__CLASS__);

		$fieldset = new FormFieldsetHTML('general_config', $this->lang['mail-config.general_mail_config']);
		$form->add_fieldset($fieldset);

		$fieldset->add_field(new FormFieldMailEditor('default_mail_sender', $this->lang['mail-config.default_mail_sender'], $this->config->get_default_mail_sender(), array('required' => true, 'description' => $this->lang['mail-config.default_mail_sender_explain'])));

		$fieldset->add_field(new FormFieldTextEditor('admin_addresses', $this->lang['mail-config.administrators_mails'], implode(',', $this->config->get_administrators_mails()), array('required' => true, 'description' => $this->lang['mail-config.administrators_mails_explain']), array(new FormFieldConstraintRegex($this->get_multi_mail_regex()))));

		$fieldset->add_field(new FormFieldMultiLineTextEditor('mail_signature', $this->lang['mail-config.mail_signature'], $this->config->get_mail_signature(), array('description' => $this->lang['mail-config.mail_signature_explain'])));

		$smtp_enabled = $this->config->is_smtp_enabled();

		$fieldset = new FormFieldsetHTML('send_configuration', $this->lang['mail-config.send_protocol'], array('description' => $this->lang['mail-config.send_protocol_explain']));
		$form->add_fieldset($fieldset);
		$fieldset->add_field(new FormFieldCheckbox('use_smtp', $this->lang['mail-config.use_custom_smtp_configuration'], $smtp_enabled,
		array('events' => array('click' => '
			if (HTMLForms.getField("use_smtp").getValue()) {
				HTMLForms.getFieldset("smtp_configuration").enable();
			} else {
				HTMLForms.getFieldset("smtp_configuration").disable();
			}'
		))));


		$fieldset = new FormFieldsetHTML('smtp_configuration', $this->lang['mail-config.custom_smtp_configuration'], array('disabled' => !$smtp_enabled));

		$form->add_fieldset($fieldset);

		$fieldset->add_field(new FormFieldTextEditor('smtp_host', $this->lang['mail-config.smtp_host'], $this->config->get_smtp_host(), array('required' => true, 'disabled' => !$smtp_enabled), array(new FormFieldConstraintRegex('`^[a-z0-9-]+(?:\.[a-z0-9-]+)*$`i'))));
		$fieldset->add_field(new FormFieldTextEditor('smtp_port', $this->lang['mail-config.smtp_port'], $this->config->get_smtp_port(), array('disabled' => !$smtp_enabled), array(new FormFieldConstraintIntegerRange(0, 65535))));
		$fieldset->add_field(new FormFieldTextEditor('smtp_login', $this->lang['mail-config.smtp_login'], $this->config->get_smtp_login(), array('disabled' => !$smtp_enabled), array()));
		$fieldset->add_field(new FormFieldPasswordEditor('smtp_password', $this->lang['mail-config.smtp_password'], $this->config->get_smtp_password(), array('disabled' => !$smtp_enabled)));

		$none_protocol_option = new FormFieldSelectChoiceOption($this->lang['mail-config.smtp_secure_protocol_none'], 'none');
		$ssl_protocol_option = new FormFieldSelectChoiceOption($this->lang['mail-config.smtp_secure_protocol_ssl'], 'ssl');
		$tls_protocol_option = new FormFieldSelectChoiceOption($this->lang['mail-config.smtp_secure_protocol_tls'], 'tls');
		$default_protocol_option = $none_protocol_option;
		switch ($this->config->get_smtp_protocol())
		{
			case 'ssl':
				$default_protocol_option = $ssl_protocol_option;
				break;
			case 'tls':
				$default_protocol_option = $tls_protocol_option;
				break;
			default:
				$default_protocol_option = $none_protocol_option;
		}
		$fieldset->add_field(new FormFieldSimpleSelectChoice('smtp_protocol', $this->lang['mail-config.smtp_secure_protocol'], $default_protocol_option, array($none_protocol_option, $tls_protocol_option, $ssl_protocol_option), array('disabled' => !$smtp_enabled)));

		$this->submit_button = new FormButtonDefaultSubmit();
		$form->add_button($this->submit_button);
		$this->set_form($form);
		$this->set_submit_button($this->submit_button);
	}

	private function get_multi_mail_regex()
	{
		$simple_regex = AppContext::get_mail_service()->get_mail_checking_raw_regex();
		return '`^' . $simple_regex . '(?:,' . $simple_regex . ')*$`i';
	}

	protected function handle_submit()
	{
		$form = $this->get_form();
		$config = $this->config;

		$config->set_default_mail_sender($form->get_value('default_mail_sender'));
		$config->set_administrators_mails(explode(',', $form->get_value('admin_addresses')));
		$config->set_mail_signature($form->get_value('mail_signature'));

		if ($form->get_value('use_smtp'))
		{
			$config->enable_smtp();

			$config->set_smtp_host($form->get_value('smtp_host'));
			$config->set_smtp_port($form->get_value('smtp_port'));
			$config->set_smtp_login($form->get_value('smtp_login'));
			$config->set_smtp_password($form->get_value('smtp_password'));
			$config->set_smtp_protocol($form->get_value('smtp_protocol')->get_raw_value());
		}
		else
		{
			$config->disable_smtp();
		}

		MailServiceConfig::save();
	}

	protected function generate_response(View $view)
	{
		if ($this->submit_button->has_been_submited() && $this->get_form()->validate())
		{
			$this->create_form();
		}
		return new AdminConfigDisplayResponse($view, $this->lang['mail-config']);
	}
}
?>
