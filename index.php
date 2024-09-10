<?php
/**
 * @copyright   &copy; 2005-2024 PHPBoost
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL-3.0
 * @author      Regis VIARRE <crowkait@phpboost.com>
 * @version     PHPBoost 6.0 - last update: 2016 10 24
 * @since       PHPBoost 1.6 - 2007 08 23
 * @contributor Arnaud GENET <elenwii@phpboost.com>
*/

define('PATH_TO_ROOT', '.');

require_once PATH_TO_ROOT . '/kernel/init.php';

$url_controller_mappers = array(
	new UrlControllerMapper('PHPBoostIndexController')
);

$users = new UserModel();
Debug::dump($users->find_by('display_name', 'SchyzoO2'));
Debug::dump($users->authentication_method()->get());
var_dump($users);
Debug::dump($users->count());
var_dump( $users->where('user_id', '<', 2)->count());

$schyzo = (new UserModel())->has_many_through('FAQCategoryModel', 'FAQItemModel', 'FaqCats','author_user_id', 'id_category', 'user_id', 'id')->find(1);
Debug::dump($schyzo);
$schyzo->display_name = 'SchyzoO2';
$schyzo->save();
echo $schyzo->FaqCats[0]->name;

$cats_two = (new FAQCategoryModel())->items()->get();
Debug::dump($cats_two);





$item = (new FaqItemModel())->category()->find(1);
Debug::dump($item->category->name . ' by ' . $item->author->display_name);
$item->category->name = 'PHPBoost_bien bien modifé';
$item->category->save();
$sameitem = (new FaqItemModel())->category()->where('id', '=', 1)->category()->get();
Debug::dump($sameitem);




/* $p = PersistenceContext::get_querier()->select('SELECT *, faq.id as item_id, faq.title, faq.id_category as item_title FROM ' . PREFIX . 'faq_cats faq_cats
LEFT JOIN  ' . PREFIX . 'faq faq ON faq.id_category = faq_cats.id', array(), SelectQueryResult::FETCH_ASSOC);
//Debug::dump($p);
while ($row = $p->fetch())
{
	Debug::dump($row);
}

 */










/* $plop = FAQCategoryModel::select()->execute();
var_dump($plop);
Debug::dump(FAQCategoryModel::count('id')->where('id', '=', 1)->execute());
foreach ($plop as $category)
{
	echo $category->title . " - ";
	echo $category->member->display_name;
	// Qu'est-ce que PHPBoost ? - SchyzoO
	// Qu'est ce qu'un CMS ? - SchyzoO 
}
echo "<br />";
//var_dump(FAQCategoryModel::find_by_pk(1)->execute());
$pl = FAQCategoryModel::find_by_pk(1)->execute();
$pl->name = "PHPBoost4";
var_dump($pl->save());
Debug::dump(FAQCategoryModel::raw('SELECT *
		FROM '. FaqSetup::$faq_table .' faq
		LEFT JOIN '. DB_TABLE_MEMBER .' member ON member.user_id = faq.author_user_id
		WHERE approved = 1
		AND faq.id_category = :id_category
		ORDER BY q_order ASC')->execute(array(
			'id_category' => 1
		)));

		echo '_______________';

$p = FAQCategoryModel::find_by_pk(1)->execute();
Debug::dump($p);  */
// PHPBoost - Dictionnaire - 
DispatchManager::dispatch($url_controller_mappers);
?>
