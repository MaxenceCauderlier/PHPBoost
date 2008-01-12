<?php
/*##################################################
 *                               admin_groups.php
 *                            -------------------
 *   begin                : June 01, 2006
 *   copyright          : (C) 2006 Viarre R�gis
 *   email                : crowkait@phpboost.com
 *
 *
###################################################
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
###################################################*/

require_once('../includes/admin_begin.php');
define('TITLE', $LANG['administration']);
require_once('../includes/admin_header.php');

$idgroup = !empty($_GET['id']) ? numeric($_GET['id']) : '' ;
$idgroup_post = !empty($_POST['id']) ? numeric($_POST['id']) : '' ;
$add = !empty($_GET['add']) ? numeric($_GET['add']) : '' ;
$add_post = !empty($_POST['add']) ? numeric($_POST['add']) : '' ;
$del_group = !empty($_GET['del']) ? true : false;
$add_mbr = !empty($_POST['add_mbr']) ? true : false;
$del_mbr = !empty($_GET['del_mbr']) ? true : false;
$user_id = !empty($_GET['user_id']) ? numeric($_GET['user_id']) : 0;

if( !empty($_POST['valid']) && !empty($idgroup_post) ) //Modification du groupe.
{
	$name = !empty($_POST['name']) ? securit($_POST['name']) : '';
	$img = !empty($_POST['img']) ? securit($_POST['img']) : '';
	$auth_flood = isset($_POST['auth_flood']) ? numeric($_POST['auth_flood']) : '1';
	$pm_no_limit = isset($_POST['pm_no_limit']) ? numeric($_POST['pm_no_limit']) : '1';	
	$data_no_limit = isset($_POST['data_no_limit']) ? numeric($_POST['data_no_limit']) : '1';	
		
	//Attention 31 droits max!
	$group_auth = 0;
	$array_group_auth = array($auth_flood, $pm_no_limit, $data_no_limit);
	foreach($array_group_auth as $key => $auth)
		$group_auth |= ($auth << $key);
			
	$sql->query_inject("UPDATE ".PREFIX."group SET name = '" . $name . "', img = '" . $img . "', auth = '" . $group_auth . "' WHERE id = '" . $idgroup_post . "'", __LINE__, __FILE__);
	
	###### On r�g�n�re le fichier de cache des groupes #######
	$cache->generate_file('groups');
	
	header('location:' . HOST . DIR . '/admin/admin_groups.php?id=' . $idgroup_post);
	exit;
}
elseif( !empty($_POST['valid']) && $add_post ) //ajout  du groupe.
{
	$name = !empty($_POST['name']) ? securit($_POST['name']) : '';
	$img = !empty($_POST['img']) ? securit($_POST['img']) : '';
	$auth_flood = isset($_POST['auth_flood']) ? numeric($_POST['auth_flood']) : '1';
	$pm_no_limit = isset($_POST['pm_no_limit']) ? numeric($_POST['pm_no_limit']) : '1';	
	$data_no_limit = isset($_POST['data_no_limit']) ? numeric($_POST['data_no_limit']) : '1';	
	
	if( !empty($name) )
	{
		//Attention 31 droits max!
		$group_auth = 0;
		$array_group_auth = array($auth_flood, $pm_no_limit, $data_no_limit);
		foreach($array_group_auth as $key => $auth)
			$group_auth |= ($auth << $key);

		//Insertion
		$sql->query_inject("INSERT INTO ".PREFIX."group 
		(name,img,auth, members) VALUES ('" . $name . "', '" . $img . "', '" . $group_auth . "', '')", __LINE__, __FILE__);
			
		###### On r�g�n�re le fichier de cache des groupes #######
		$cache->generate_file('groups');	
		
		header('location:' . HOST . DIR . '/admin/admin_groups.php?id=' . $sql->sql_insert_id("SELECT MAX(id) FROM ".PREFIX."group"));		
		exit;
	}
	else
	{
		header('location:' . HOST . DIR . '/admin/admin_groups.php?error=incomplete#errorh');
		exit;
	}
}
elseif( !empty($idgroup) && $del_group ) //Suppression du groupe.
{
	$members = $sql->query("SELECT members FROM ".PREFIX."group WHERE id = '" . $idgroup . "'", __LINE__, __FILE__);
	$array_members = explode('|', $members);
	foreach($array_members as $key => $user_id)
	{
		//Mise � jour des membres �tant dans le groupe supprim�.
		$user_groups = $sql->query("SELECT user_groups FROM ".PREFIX."member WHERE user_id = '" . $user_id . "'", __LINE__, __FILE__);
		if( !empty($user_groups) )
		{
			$user_groups = explode('|', $user_groups);
			$user_groups_key = array_search($idgroup, $user_groups);
			unset($user_groups[$user_groups_key]);
			
			$sql->query_inject("UPDATE ".PREFIX."member SET user_groups = '" . implode('|', $user_groups) . "' WHERE user_id = '" . $user_id . "'", __LINE__, __FILE__);
		}
	}
	
	//On supprime dans la bdd.
	$sql->query_inject("DELETE FROM ".PREFIX."group WHERE id = '" . $idgroup . "'", __LINE__, __FILE__);	
		
	###### On r�g�n�re le fichier de cache des groupes #######
	$cache->generate_file('groups');	
	
	header('location:' . HOST . SCRIPT);
	exit;
}
elseif( !empty($idgroup) && $add_mbr ) //Ajout du membre au groupe.
{
	$login = !empty($_POST['login_mbr']) ? securit($_POST['login_mbr']) : '';
	$user_id = $sql->query("SELECT user_id FROM ".PREFIX."member WHERE login = '" . $login . "'", __LINE__, __FILE__);
	if( !empty($user_id) )
	{	
		//On ins�re le groupe au champ membre.
		$user_groups = $sql->query("SELECT user_groups FROM ".PREFIX."member WHERE user_id = '" . numeric($user_id) . "'", __LINE__, __FILE__);
		$user_groups_key = array_search($idgroup, explode('|', $user_groups));
		
		if( !is_numeric($user_groups_key) ) //Le membre n'appartient pas d�j� au groupe.
		{
			$sql->query_inject("UPDATE ".PREFIX."member SET user_groups = '" . $user_groups . $idgroup . "|' WHERE user_id = '" . numeric($user_id) . "'", __LINE__, __FILE__);
		}
		else
		{
			header('location:' . HOST . DIR . '/admin/admin_groups.php?id=' . $idgroup . '&error=already_group#errorh');
			exit;
		}
	
		//On ins�re le membre dans le groupe.
		$members = $sql->query("SELECT members FROM ".PREFIX."group WHERE id = '" . $idgroup . "'", __LINE__, __FILE__);
		$members_key = array_search($user_id, explode('|', $members));
		if( !is_numeric($members_key) ) //Le membre n'appartient pas d�j� au groupe.
		{
			$sql->query_inject("UPDATE ".PREFIX."group SET members = CONCAT(members, '" . $user_id . "|') WHERE id = '" . $idgroup . "'", __LINE__, __FILE__);
		}
		else
		{
			header('location:' . HOST . DIR . '/admin/admin_groups.php?id=' . $idgroup . '&error=already_group#errorh');
			exit;
		}
		
		header('location:' . HOST . DIR . '/admin/admin_groups.php?id=' . $idgroup . '#add'); 	
		exit;
	}
	else
	{
		header('location:' . HOST . DIR . '/admin/admin_groups.php?id=' . $idgroup . '&error=incomplete#errorh');
		exit;
	}
}
elseif( $del_mbr && !empty($user_id) && !empty($idgroup) ) //Suppression du membre du groupe.
{
	$user_groups = $sql->query("SELECT user_groups FROM ".PREFIX."member WHERE user_id = '" . $user_id . "'", __LINE__, __FILE__);
	if( !empty($user_groups) )
	{
		$user_groups = explode('|', $user_groups);
		$user_groups_key = array_search($idgroup, $user_groups);
		unset($user_groups[$user_groups_key]);

		$sql->query_inject("UPDATE ".PREFIX."member SET user_groups = '" . implode('|', $user_groups) . "' WHERE user_id = '" . $user_id . "'", __LINE__, __FILE__);
		
		$members_group = $sql->query("SELECT members FROM ".PREFIX."group WHERE id = '" . $idgroup . "'", __LINE__, __FILE__);
		$members_group = explode('|', $members_group);
		$members_group_key = array_search($user_id, $members_group);
		unset($members_group[$members_group_key]);
		
		$sql->query_inject("UPDATE ".PREFIX."group SET members = '" . implode('|', $members_group) . "' WHERE id = '" . $idgroup . "'", __LINE__, __FILE__);
	
		header('location:' . HOST . DIR . '/admin/admin_groups.php?id=' . $idgroup . '#add');	
		exit;
	}	
	else
	{
		header('location:' . HOST . DIR . '/admin/admin_groups.php?id=' . $idgroup . '&error=incomplete#errorh');
		exit;
	}
}
elseif( !empty($idgroup) ) //Interface d'�dition du groupe.
{		
	$template->set_filenames(array(
	'admin_groups_management2' => '../templates/' . $CONFIG['theme'] . '/admin/admin_groups_management2.tpl'
	));
	
	$group = $sql->query_array('group', 'id', 'name', 'img', 'auth', 'members', "WHERE id = '" . $idgroup . "'", __LINE__, __FILE__);
	
	if( !empty($group['id']) )
	{
		$template->assign_block_vars('edit_group', array(	
			'NAME' => $group['name'],
			'IMG' => $group['img'],
			'GROUP_ID' => $idgroup
		));

		//Gestion erreur.
		$get_error = !empty($_GET['error']) ? securit($_GET['error']) : '';
		if( $get_error == 'incomplete' )
			$errorh->error_handler($LANG['e_incomplete'], E_USER_NOTICE, NO_LINE_ERROR, NO_FILE_ERROR, 'edit_group.');
		elseif( $get_error == 'already_group' )
			$errorh->error_handler($LANG['e_already_group'], E_USER_NOTICE, NO_LINE_ERROR, NO_FILE_ERROR, 'edit_group.');
		
		$nbr_member_group = $sql->query("SELECT COUNT(*) FROM ".PREFIX."member WHERE user_groups = '" . $group['id'] . "'", __LINE__, __FILE__);
		//On cr�e une pagination si le nombre de membre est trop important.
		include_once('../includes/pagination.class.php'); 
		$pagination = new Pagination();
		
		//R�cup�ration des autorisations.
		$array_var = array('auth_flood', 'pm_no_limit', 'data_no_limit');
		foreach($array_var as $key => $value)
		{
			$pow = 1 << $key;
			if( ($group['auth'] & $pow) == $pow )
				${$value} = 1;
			else
				${$value} = 0;
		}
				
		$template->assign_vars(array(
			'PAGINATION' => $pagination->show_pagin('admin_groups.php?id=' . $idgroup . '&amp;p=%d', $nbr_member_group, 'p', 25, 3),
			'THEME' => $CONFIG['theme'],
			'LANG' => $CONFIG['lang'],	
			'AUTH_FLOOD_ENABLED' => $auth_flood == 1 ? 'checked="checked"' : '',
			'AUTH_FLOOD_DISABLED' => $auth_flood == 0 ? 'checked="checked"' : '',
			'PM_NO_LIMIT_ENABLED' => $pm_no_limit == 1 ? 'checked="checked"' : '',
			'PM_NO_LIMIT_DISABLED' => $pm_no_limit == 0 ? 'checked="checked"' : '',
			'DATA_NO_LIMIT_ENABLED' => $data_no_limit == 1 ? 'checked="checked"' : '',
			'DATA_NO_LIMIT_DISABLED' => $data_no_limit == 0 ? 'checked="checked"' : '',
			'L_REQUIRE_PSEUDO' => $LANG['require_pseudo'],
			'L_REQUIRE_NAME' => $LANG['require_name'],
			'L_CONFIRM_DEL_MEMBER_GROUP' => $LANG['confirm_del_member_group'],			
			'L_GROUPS_MANAGEMENT' => $LANG['groups_management'],
			'L_ADD_GROUPS' => $LANG['groups_add'],
			'L_REQUIRE' => $LANG['require'],
			'L_NAME' => $LANG['name'],		
			'L_IMG_ASSOC_GROUP' => $LANG['img_assoc_group'],	
			'L_IMG_ASSOC_GROUP_EXPLAIN' => $LANG['img_assoc_group_explain'],	
			'L_AUTH_FLOOD' => $LANG['auth_flood'],
			'L_PM_NO_LIMIT' => $LANG['pm_no_limit'],
			'L_DATA_NO_LIMIT' => $LANG['data_no_limit'],
			'L_YES' => $LANG['yes'],
			'L_NO' => $LANG['no'],
			'L_ADD' => $LANG['add'],		
			'L_MBR_GROUP' => $LANG['mbrs_group'],
			'L_PSEUDO' => $LANG['pseudo'],
			'L_SEARCH' => $LANG['search'],
			'L_UPDATE' => $LANG['update'],
			'L_RESET' => $LANG['reset'],
			'L_DELETE' => $LANG['delete'],
			'L_ADD_MBR_GROUP' => $LANG['add_mbr_group']
		));		

		//On recup�re les dossier des images des groupes contenu dans le dossier /images/group.
		$rep = '../images/group';
		$y = 0;
		if( is_dir($rep) ) //Si le dossier existe
		{
			$dh = @opendir($rep);
			while( !is_bool($file = readdir($dh)) )
			{	
				if( $file != '.' && $file != '..' && $file != 'index.php' && $file != 'Thumbs.db' )
					$fichier_array[] = $file; //On cr�e un array, avec les different fichiers.
			}	
			closedir($dh); //On ferme le dossier

			if( is_array($fichier_array) )
			{			
				foreach($fichier_array as $img_group)
				{
					if( $y == 0)
					{
						$option = '<option value="" selected="selected">--</option>';
						$y++;
					}
					else
					{
						$selected = ($img_group == $group['img']) ? ' selected="selected"' : '';
						$option = '<option value="' . $img_group . '"' . $selected . '>' . $img_group . '</option>';
					}
					
					$template->assign_block_vars('edit_group.select', array(
						'IMG_GROUP' => $option
					));
				}
			}
		}	
		
		//Liste des membres du groupe.
		$members = $sql->query("SELECT members FROM ".PREFIX."group WHERE id = '" . numeric($group['id']) . "'", __LINE__, __FILE__);
		$members = explode('|', $members);
		foreach($members as $key => $user_id)
		{
			$login = $sql->query("SELECT login FROM ".PREFIX."member WHERE user_id = '" . numeric($user_id) . "'", __LINE__, __FILE__);
			if( !empty($login) )
			{	
				$template->assign_block_vars('edit_group.member', array(
					'USER_ID' => $user_id,
					'LOGIN' => $login,
					'U_USER_ID' => transid('.php?id=' . $user_id, '-' . $user_id . '.php')
				));
			}
		}
	}
	else
	{
		header('location:' . HOST . SCRIPT);
		exit;
	}
	
	$template->pparse('admin_groups_management2');
}
elseif( $add ) //Interface d'ajout du groupe.
{		
	$template->set_filenames(array(
	'admin_groups_management2' => '../templates/' . $CONFIG['theme'] . '/admin/admin_groups_management2.tpl'
	));
	
	$template->assign_block_vars('add_group', array(	
	));

	$template->assign_vars(array(
		'THEME' => $CONFIG['theme'],
		'LANG' => $CONFIG['lang'],	
		'L_REQUIRE_PSEUDO' => $LANG['require_pseudo'],
		'L_REQUIRE_NAME' => $LANG['require_name'],
		'L_CONFIRM_DEL_MEMBER_GROUP' => $LANG['confirm_del_member_group'],	
		'L_GROUPS_MANAGEMENT' => $LANG['groups_management'],
		'L_ADD_GROUPS' => $LANG['groups_add'],
		'L_REQUIRE' => $LANG['require'],
		'L_NAME' => $LANG['name'],		
		'L_IMG_ASSOC_GROUP' => $LANG['img_assoc_group'],	
		'L_IMG_ASSOC_GROUP_EXPLAIN' => $LANG['img_assoc_group_explain'],	
		'L_AUTH_FLOOD' => $LANG['auth_flood'],
		'L_PM_NO_LIMIT' => $LANG['pm_no_limit'],
		'L_DATA_NO_LIMIT' => $LANG['data_no_limit'],
		'L_YES' => $LANG['yes'],
		'L_NO' => $LANG['no'],
		'L_ADD' => $LANG['add']
	));		
	
	//On recup�re les images des groupes contenu dans le dossier /images/group.
	$rep = '../images/group';
	$y = 0;
	if( is_dir($rep) ) //Si le dossier existe
	{
		$dh = @opendir($rep);
		while( !is_bool($file = readdir($dh)) )
		{	
			if( $file != '.' && $file != '..' && $file != 'index.php' && $file != 'Thumbs.db' )
				$fichier_array[] = $file; //On cr�e un array, avec les different fichiers.
		}	
		closedir($dh); //On ferme le dossier

		if( is_array($fichier_array) )
		{			
			foreach($fichier_array as $img_group)
			{
				if( $y == 0 )
				{
					$option = '<option value="">--</option>';
					$y++;
				}
				else
					$option = '<option value="' . $img_group . '">' . $img_group . '</option>';
				
				$template->assign_block_vars('add_group.select', array(
					'IMG_GROUP' => $option
				));
			}
		}
	}	
			
	$template->pparse('admin_groups_management2');
}
else //Liste des groupes.
{
	$template->set_filenames(array(
		'admin_groups_management' => '../templates/' . $CONFIG['theme'] . '/admin/admin_groups_management.tpl'
	 ));
	 
	$nbr_group = $sql->count_table("group", __LINE__, __FILE__);
	//On cr�e une pagination si le nombre de group est trop important.
	include_once('../includes/pagination.class.php'); 
	$pagination = new Pagination();
	
	$template->assign_vars(array(
		'PAGINATION' => $pagination->show_pagin('admin_groups', $nbr_group, 'p', 25, 3),	
		'THEME' => $CONFIG['theme'],
		'LANG' => $CONFIG['lang'],
		'L_CONFIRM_DEL_GROUP' => $LANG['confirm_del_group'],
		'L_GROUPS_MANAGEMENT' => $LANG['groups_management'],
		'L_ADD_GROUPS' => $LANG['groups_add'],
		'L_NAME' => $LANG['name'],
		'L_IMAGE' => $LANG['image'],
		'L_UPDATE' => $LANG['update'],
		'L_DELETE' => $LANG['delete']
	));
	  
	$result = $sql->query_while("SELECT id, name, img
	FROM ".PREFIX."group 
	" . $sql->sql_limit($pagination->first_msg(25, 'p'), 25), __LINE__, __FILE__);
	while( $row = $sql->sql_fetch_assoc($result) )
	{
		$template->assign_block_vars('group', array(
			'LINK' => transid('.php?g=' . $row['id'], '-0.php?g=' . $row['id']),
			'ID' => $row['id'],
			'NAME' => $row['name'],
			'IMAGE' => !empty($row['img']) ? '<img src="../images/group/' . $row['img'] . '" alt="" />' : ''
		));
	}
	$sql->close($result);
	
	include_once('../includes/bbcode.php');
	
	$template->pparse('admin_groups_management'); 
}

require_once('../includes/admin_footer.php');

?>