<?php
/*##################################################
 *                       UserEditProfileController.class.php
 *                            -------------------
 *   begin                : October 09, 2011
 *   copyright            : (C) 2011 K�vin MASSY
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

class UserEditProfileController extends AbstractController
{
	private $lang;
	/**
	 * @var HTMLForm
	 */
	private $form;
	/**
	 * @var FormButtonDefaultSubmit
	 */
	private $submit_button;
	
	private $user;
	private $internal_auth_infos;
	private $user_auth_types;
	
	private $member_extended_fields_service;

	public function execute(HTTPRequestCustom $request)
	{
		$this->init();
		
		$user_id = $request->get_getint('user_id', AppContext::get_current_user()->get_id());
		
		try {
			$this->user = UserService::get_user($user_id);
		} catch (RowNotFoundException $e) {
			$error_controller = PHPBoostErrors::unexisting_element();
			DispatchManager::redirect($error_controller);
		}

		try {
			$this->internal_auth_infos = PHPBoostAuthenticationMethod::get_auth_infos($user_id);
		} catch (RowNotFoundException $e) {
		}

		$this->user_auth_types = AuthenticationService::get_user_types_authentication($user_id);
		
		if (!$this->check_authorizations($user_id))
		{
			$error_controller = PHPBoostErrors::user_not_authorized();
			DispatchManager::redirect($error_controller);
		}

		$associate_type = $request->get_getvalue('associate', false);
		if ($associate_type)
		{
			if (!in_array($associate_type, $this->user_auth_types))
			{
				$authentication_method = AuthenticationService::get_authentication_method($associate_type);
				AuthenticationService::associate($authentication_method, $user_id);
				AppContext::get_response()->redirect(UserUrlBuilder::edit_profile($user_id));
			}
		}

		$dissociate_type = $request->get_getvalue('dissociate', false);
		if ($dissociate_type)
		{
			if (in_array($dissociate_type, $this->user_auth_types) && count($this->user_auth_types) > 1)
			{
				$authentication_method = AuthenticationService::get_authentication_method($dissociate_type);
				AuthenticationService::dissociate($authentication_method, $user_id);
				AppContext::get_response()->redirect(UserUrlBuilder::edit_profile($user_id));
			}
		}
		
		$this->build_form();

		if ($this->submit_button->has_been_submited() && $this->form->validate())
		{
			$this->save();
		}
		
		$this->tpl->put('FORM', $this->form->display());

		return $this->build_response();
	}

	private function init()
	{
		$this->lang = LangLoader::get('user-common');
		$this->tpl = new StringTemplate('# INCLUDE MSG # # INCLUDE FORM #');
		$this->tpl->add_lang($this->lang);
		$this->user_accounts_config = UserAccountsConfig::load();
	}
	
	private function check_authorizations()
	{
		return AppContext::get_current_user()->get_id() == $this->user->get_id() || AppContext::get_current_user()->check_level(User::ADMIN_LEVEL);
	}
	
	private function build_form()
	{
		$auth_types = AuthenticationService::get_activated_types_authentication();
		
		$form = new HTMLForm(__CLASS__);
		$this->member_extended_fields_service = new MemberExtendedFieldsService($form);
		
		$fieldset = new FormFieldsetHTML('edit_profile', $this->lang['profile.edit']);
		$form->add_fieldset($fieldset);
		
		$fieldset->add_field(new FormFieldTextEditor('display_name', $this->lang['display_name'], $this->user->get_display_name(), array('description'=> $this->lang['display_name.explain'], 'required' => true),
			array(new FormFieldConstraintLengthRange(3, 20))
		));	

		$fieldset->add_field(new FormFieldTextEditor('email', $this->lang['email'], $this->user->get_email(), array(
			'required' => true, 'description' => LangLoader::get_message('valid', 'main')),
			array(new FormFieldConstraintMailAddress(), new FormFieldConstraintMailExist($this->user->get_id()))
		));
				
		$fieldset->add_field(new FormFieldCheckbox('user_hide_mail', $this->lang['email.hide'], !$this->user->get_show_email()));

		$fieldset->add_field(new FormFieldCheckbox('delete_account', $this->lang['delete-account'], FormFieldCheckbox::UNCHECKED));
		
		if (AppContext::get_current_user()->is_admin())
		{
			$manage_fieldset = new FormFieldsetHTML('member_management', $this->lang['member-management']);
			$form->add_fieldset($manage_fieldset);
			
			$manage_fieldset->add_field(new FormFieldCheckbox('approbation', $this->lang['approbation'], $this->internal_auth_infos['approved']));
			
			$manage_fieldset->add_field(new FormFieldRanksSelect('rank', $this->lang['rank'], $this->user->get_level()));
			
			$manage_fieldset->add_field(new FormFieldGroups('groups', $this->lang['groups'], $this->user->get_groups()));
		}

		$connect_fieldset = new FormFieldsetHTML('connect', $this->lang['connection']);
		$form->add_fieldset($connect_fieldset);

		$has_custom_login = $this->user->get_email() !== $this->internal_auth_infos['login'];
		if (in_array(PHPBoostAuthenticationMethod::AUTHENTICATION_METHOD, $this->user_auth_types))
		{
			$connect_fieldset->add_field(new FormFieldFree('internal_auth', $this->lang['internal_connection'] . ' <i class="fa fa-success"></i>', '<a onclick="javascript:HTMLForms.getField(\'custom_login\').enable();'. ($has_custom_login ? 'HTMLForms.getField(\'login\').enable();' : '') .'HTMLForms.getField(\'password\').enable();HTMLForms.getField(\'password_bis\').enable();HTMLForms.getField(\'old_password\').enable();">'. LangLoader::get_message('edit', 'common') .'</a>'));
		}
		else
		{
			$connect_fieldset->add_field(new FormFieldFree('internal_auth', $this->lang['internal_connection'] . ' <i class="fa fa-error"></i>', '<a onclick="javascript:HTMLForms.getField(\'custom_login\').enable();HTMLForms.getField(\'password\').enable();HTMLForms.getField(\'password_bis\').enable();">Créer une authentification interne</a>'));
		}

		$connect_fieldset->add_field(new FormFieldCheckbox('custom_login', $this->lang['login.custom'], $has_custom_login, array('description'=> $this->lang['login.custom.explain'], 'hidden' => true, 'events' => array('click' => '
			if (HTMLForms.getField("custom_login").getValue()) { HTMLForms.getField("login").enable(); } else { HTMLForms.getField("login").disable();}'))));

		$connect_fieldset->add_field(new FormFieldTextEditor('login', $this->lang['login'], ($has_custom_login ? $this->internal_auth_infos['login'] : ''), array('required' => true, 'hidden' => true),
			array(new FormFieldConstraintLengthRange(3, 25), new FormFieldConstraintPHPBoostAuthLoginExists($this->user->get_id()))
		));

		$connect_fieldset->add_field(new FormFieldPasswordEditor('old_password', $this->lang['password.old'], '', array(
			'description' => $this->lang['password.old.explain'], 'hidden' => true))
		);

		$connect_fieldset->add_field($password = new FormFieldPasswordEditor('password', $this->lang['password'], '', array(
			'description' => $this->lang['password.explain'], 'hidden' => true),
			array(new FormFieldConstraintLengthRange(6, 12))
		));
		$connect_fieldset->add_field($password_bis = new FormFieldPasswordEditor('password_bis', $this->lang['password.confirm'], '', array('hidden' => true),
			array(new FormFieldConstraintLengthRange(6, 12))
		));

		$form->add_constraint(new FormConstraintFieldsEquality($password, $password_bis));

		if (in_array(FacebookAuthenticationMethod::AUTHENTICATION_METHOD, $this->user_auth_types))
		{
			$connect_fieldset->add_field(new FormFieldFree('fb_auth', $this->lang['fb_connection'] . ' <i class="fa fa-success"></i>', '<a href="'. UserUrlBuilder::edit_profile($this->user->get_id())->absolute() .'?dissociate=fb">'. $this->lang['dissociate_account'] .'</a>'));
		}
		else
		{
			$connect_fieldset->add_field(new FormFieldFree('fb_auth', $this->lang['fb_connection'] . ' <i class="fa fa-error"></i>', '<a href="'. UserUrlBuilder::edit_profile($this->user->get_id())->absolute() .'?associate=fb">'. $this->lang['associate_account'] .'</a>'));
		}

		if (in_array(GoogleAuthenticationMethod::AUTHENTICATION_METHOD, $this->user_auth_types))
		{
			$connect_fieldset->add_field(new FormFieldFree('google_auth', $this->lang['google_connection'] . ' <i class="fa fa-success"></i>', '<a href="'. UserUrlBuilder::edit_profile($this->user->get_id())->absolute() .'?dissociate=google">'. $this->lang['dissociate_account'] .'</a>'));
		}
		else
		{
			$connect_fieldset->add_field(new FormFieldFree('google_auth', $this->lang['google_connection'] . ' <i class="fa fa-error"></i>', '<a href="'. UserUrlBuilder::edit_profile($this->user->get_id())->absolute() .'?associate=google">'. $this->lang['associate_account'] .'</a>'));
		}


		$options_fieldset = new FormFieldsetHTML('options', LangLoader::get_message('options', 'main'));
		$form->add_fieldset($options_fieldset);
		
		$options_fieldset->add_field(new FormFieldTimezone('timezone', $this->lang['timezone.choice'], 
			$this->user->get_timezone(), array('description' => $this->lang['timezone.choice.explain'])
		));
		
		if (count(ThemesManager::get_activated_and_authorized_themes_map()) > 1)
		{
			$options_fieldset->add_field(new FormFieldThemesSelect('theme', $this->lang['theme'], $this->user->get_theme(),
				array('check_authorizations' => true, 'events' => array('change' => $this->build_javascript_picture_themes()))
			));
			$options_fieldset->add_field(new FormFieldFree('preview_theme', $this->lang['theme.preview'], '<img id="img_theme" src="'. $this->get_picture_theme($this->user->get_theme()) .'" alt="" style="vertical-align:top; max-height:180px;" />'));
		}
		
		$options_fieldset->add_field(new FormFieldEditors('text-editor', $this->lang['text-editor'], $this->user->get_editor()));
		
		$options_fieldset->add_field(new FormFieldLangsSelect('lang', $this->lang['lang'], $this->user->get_locale(), array('check_authorizations' => true)));	
		
		if (AppContext::get_current_user()->is_admin())
		{
			$fieldset_punishment = new FormFieldsetHTML('punishment_management', $this->lang['punishment-management']);
			$form->add_fieldset($fieldset_punishment);
			
			$fieldset_punishment->add_field(new FormFieldMemberCaution('user_warning', $this->lang['caution'], $this->user->get_warning_percentage()));
			
			$fieldset_punishment->add_field(new FormFieldMemberSanction('user_readonly', $this->lang['readonly'], $this->user->get_delay_readonly()));
			
			$fieldset_punishment->add_field(new FormFieldMemberSanction('user_ban', $this->lang['banned'], $this->user->get_delay_banned()));
		}

		$this->member_extended_fields_service->display_form_fields($this->user->get_id());
		
		$this->submit_button = new FormButtonDefaultSubmit();
		$form->add_button($this->submit_button);
		$form->add_button(new FormButtonReset());

		$this->form = $form;
	}
	
	private function save()
	{
		$has_error = false;
		
		$user_id = $this->user->get_id();
		
		if ($this->form->get_value('delete_account'))
		{
			UserService::delete_by_id($user_id);
		}
		else
		{
			$approbation = $this->internal_auth_infos['approved'];
			if (AppContext::get_current_user()->is_admin())
			{
				$old_approbation = $approbation;
				$approbation = $this->form->get_value('approbation');

				$groups = array();
				foreach ($this->form->get_value('groups') as $field => $option)
				{
					$groups[] = $option->get_raw_value();
				}
				
				GroupsService::edit_member($user_id, $groups);
				$this->user->set_groups($groups);
				$this->user->set_level($this->form->get_value('rank')->get_raw_value());
			}

			if ($this->form->has_field('theme'))
			{
				$this->user->set_theme($this->form->get_value('theme')->get_raw_value());
			}
			
			$this->user->set_locale($this->form->get_value('lang')->get_raw_value());
			$this->user->set_display_name($this->form->get_value('display_name'));
			$this->user->set_email($this->form->get_value('email'));
			$this->user->set_locale($this->form->get_value('lang')->get_raw_value());
			$this->user->set_editor($this->form->get_value('text-editor')->get_raw_value());
			$this->user->set_show_email(!$this->form->get_value('user_hide_mail'));
			$this->user->set_timezone($this->form->get_value('timezone')->get_raw_value());
			
			try {
				UserService::update($this->user, $this->member_extended_fields_service);
			} catch (MemberExtendedFieldErrorsMessageException $e) {
				$has_error = true;
				$this->tpl->put('MSG', MessageHelper::display($e->getMessage(), MessageHelper::NOTICE));
			}

			$login = $this->form->get_value('email');
			if ($this->form->get_value('custom_login', false))
			{
				$login = $this->form->get_value('login');
			}

			$password = $this->form->get_value('password');
			if ($this->internal_auth_infos === null && !empty($password))
			{
				$authentication_method = new PHPBoostAuthenticationMethod($login, $password);
				AuthenticationService::associate($authentication_method, $user_id);
			}
			elseif (!empty($password))
			{
				$old_password = $this->form->get_value('old_password');
				if (!empty($old_password))
				{
					$old_password_hashed = KeyGenerator::string_hash($old_password);

					if ($old_password_hashed == $this->internal_auth_infos['password'])
					{
						PHPBoostAuthenticationMethod::update_auth_infos($user_id, $login, $approbation, KeyGenerator::string_hash($password));
						$has_error = false;
					}
					else
					{
						$has_error = true;
						$this->tpl->put('MSG', MessageHelper::display($this->lang['profile.edit.password.error'], MessageHelper::NOTICE));
					}
				}
			}
			else
			{
				PHPBoostAuthenticationMethod::update_auth_infos($user_id, $login, $approbation);
			}

			if (AppContext::get_current_user()->is_admin())
			{
				if ($old_approbation != $approbation && $old_approbation == 0)
				{
					//Recherche de l'alerte correspondante
					$matching_alerts = AdministratorAlertService::find_by_criteria($user_id, 'member_account_to_approbate');
				
					//L'alerte a été trouvée
					if (count($matching_alerts) == 1)
					{
						$alert = $matching_alerts[0];
						$alert->set_status(AdministratorAlert::ADMIN_ALERT_STATUS_PROCESSED);
						AdministratorAlertService::save_alert($alert);
						
						$site_name = GeneralConfig::load()->get_site_name();
						$subject = StringVars::replace_vars($this->user_lang['registration.subject-mail'], array('site_name' => $site_name));
						$content = StringVars::replace_vars($this->user_lang['registration.email.mail-administrator-validation'], array(
							'pseudo' => $this->user->get_display_name(),
							'site_name' => $site_name,
							'signature' => MailServiceConfig::load()->get_mail_signature()
						));
						AppContext::get_mail_service()->send_from_properties($this->user->get_email(), $subject, $content);
					}
				}

				$user_warning = $this->form->get_value('user_warning')->get_raw_value();
				if (!empty($user_warning) && $user_warning != $this->user->get_warning_percentage())
				{
					MemberSanctionManager::caution($user_id, $user_warning, MemberSanctionManager::SEND_MP, str_replace('%level%', $user_warning, $this->main_lang['user_warning_level_changed']));
				}
				elseif ($user_warning != $this->user->get_warning_percentage())
				{
					MemberSanctionManager::cancel_caution($user_id);
				}
				
				$user_readonly = $this->form->get_value('user_readonly')->get_raw_value();
				if (!empty($user_readonly) && $user_readonly != $this->user->get_delay_readonly())
				{
					MemberSanctionManager::remove_write_permissions($user_id, time() + $user_readonly, MemberSanctionManager::SEND_MP, str_replace('%date%', $this->form->get_value('user_readonly')->get_label(), $this->main_lang['user_readonly_changed']));
				}
				elseif ($user_readonly != $this->user->get_delay_readonly())
				{
					MemberSanctionManager::restore_write_permissions($user_id);
				}
				
				$user_ban = $this->form->get_value('user_ban')->get_raw_value();
				if (!empty($user_ban) && $user_ban != $this->user->get_delay_banned())
				{
					MemberSanctionManager::banish($user_id, time() + $user_ban, MemberSanctionManager::SEND_MAIL);
				}
				elseif ($user_ban != $this->user->get_delay_banned())
				{
					MemberSanctionManager::cancel_banishment($user_id);
				}
			}
		}
		
		if (!$has_error)
		{
			AppContext::get_response()->redirect(UserUrlBuilder::edit_profile($user_id));
		}
	}

	private function build_response()
	{
		$response = new SiteDisplayResponse($this->tpl);
		$graphical_environment = $response->get_graphical_environment();
		$graphical_environment->set_page_title($this->lang['profile.edit'], $this->lang['user']);
		
		$breadcrumb = $graphical_environment->get_breadcrumb();
		$breadcrumb->add($this->lang['user'], UserUrlBuilder::users()->rel());
		$breadcrumb->add($this->lang['profile.edit'], UserUrlBuilder::edit_profile($this->user->get_id())->rel());
		
		return $response;
	}
	
	private function build_javascript_picture_themes()
	{
		$text = 'var theme = new Array;' . "\n";
		foreach (ThemesManager::get_activated_themes_map() as $theme)
		{
			$picture = $theme->get_configuration()->get_first_picture();
			$text .= 'theme["' . $theme->get_id() . '"] = "' . TPL_PATH_TO_ROOT .'/templates/' . $theme->get_id() . '/' . $picture . '";' . "\n";
		}
		$text .= 'var theme_id = HTMLForms.getField("theme").getValue(); document.images[\'img_theme\'].src = theme[theme_id];';
		return $text;
	}
	
	private function get_picture_theme($user_theme)
	{
		$picture = ThemesManager::get_theme($user_theme)->get_configuration()->get_first_picture();
		return TPL_PATH_TO_ROOT .'/templates/' . $user_theme . '/' . $picture;
	}
}
?>