<?php
/**
 * @copyright   &copy; 2005-2021 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Regis VIARRE <crowkait@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2021 11 25
 * @since       PHPBoost 1.2 - 2005 10 30
 * @contributor Julien BRISWALTER <j1.seth@phpboost.com>
 * @contributor Arnaud GENET <elenwii@phpboost.com>
 * @contributor mipel <mipel@phpboost.com>
 * @contributor Sebastien LARTIGUE <babsolune@phpboost.com>
*/

require_once('../admin/admin_begin.php');

$lang = array_merge(
	LangLoader::get('common-lang'),
	LangLoader::get('form-lang'),
	LangLoader::get('errors'),
	LangLoader::get('upload-lang'),
	LangLoader::get('warning-lang'),
	LangLoader::get('common', 'forum')
);

define('TITLE', $lang['forum.ranks.management']);
require_once('../admin/admin_header.php');

$request = AppContext::get_request();

$add = $request->get_postbool('add', false);

$view = new FileTemplate('forum/admin_ranks_add.tpl');
$view->add_lang($lang);

//Ajout du rang.
if ($add)
{
	$name = $request->get_poststring('name', '');
	$msg_number = $request->get_postint('msg', 0);
	$icon = $request->get_poststring('icon', '');

	if (!empty($name) && $msg_number >= 0)
	{
		//On insere le nouveau lien, tout en précisant qu'il s'agit d'un lien ajouté et donc supprimable
		PersistenceContext::get_querier()->insert(PREFIX . "forum_ranks", array('name' => $name, 'msg' => $msg_number, 'icon' => $icon, 'special' => 0));

		###### Régénération du cache des rangs #######
		ForumRanksCache::invalidate();

		$view->put('MESSAGE_HELPER', MessageHelper::display($lang['warning.process.success'], MessageHelper::SUCCESS, 4));
	}
	else
		$view->put('MESSAGE_HELPER', MessageHelper::display($lang['warning.incomplete'], MessageHelper::NOTICE));
}
elseif (!empty($_FILES['upload_ranks']['name'])) //Upload
{
	//Si le dossier n'est pas en écriture on tente un CHMOD 777
	@clearstatcache();
	$dir = PATH_TO_ROOT . '/forum/templates/images/ranks/';
	if (!is_writable($dir))
		$is_writable = @chmod($dir, 0777);

	$error = '';
	if (is_writable($dir)) //Dossier en écriture, upload possible
	{
		$authorized_pictures_extensions = FileUploadConfig::load()->get_authorized_picture_extensions();

		if (!empty($authorized_pictures_extensions))
		{
			$Upload = new Upload($dir);
			$Upload->disableContentCheck();
			if (!$Upload->file('upload_ranks', '`\.(' . implode('|', array_map('preg_quote', $authorized_pictures_extensions)) . ')+$`iu'))
				$error = $Upload->get_error();
		}
		else
			$error = 'e_upload_invalid_format';
	}
	else
		$error = 'e_upload_failed_unwritable';

	if (!empty($error))
		$view->put('MESSAGE_HELPER', MessageHelper::display($lang[$error], MessageHelper::WARNING));
	else
		$view->put('MESSAGE_HELPER', MessageHelper::display(LangLoader::get_message('warning.process.success', 'warning-lang'), MessageHelper::SUCCESS, 4));
}

//On recupère les images des groupes
$rank_options = '<option value="">--</option>';


$image_folder_path = new Folder(PATH_TO_ROOT . '/forum/templates/images/ranks/');
foreach ($image_folder_path->get_files('`\.(png|jpg|bmp|gif)$`iu') as $image)
{
	$file = $image->get_name();
	$rank_options .= '<option value="' . $file . '">' . $file . '</option>';
}

$view->put_all(array(
	'RANK_OPTIONS'       => $rank_options,
	'MAX_FILE_SIZE'      => ServerConfiguration::get_upload_max_filesize(),
	'MAX_FILE_SIZE_TEXT' => File::get_formated_size(ServerConfiguration::get_upload_max_filesize()),
	'ALLOWED_EXTENSIONS' => implode('", "',FileUploadConfig::load()->get_authorized_picture_extensions()),
));

$view->display();

require_once('../admin/admin_footer.php');
?>
