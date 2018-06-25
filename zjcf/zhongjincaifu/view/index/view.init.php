<?php
/**
 * iNewS Project
 *
 * LICENSE
 *
 * http://www.inews.com.cn/license/inews
 *
 * @category iNewS
 * @package ChangeMe
 * @subpackage ChangeMe
 * @copyright Copyright (c) 2008 Zeed Technologies PRC Inc. (http://www.inews.com.cn)
 * @author Cyrano ( GTalk: cyrano0919@gmail.com )
 * @since Apr 23, 2010
 * @version SVN: $Id$
 */

error_reporting(E_ALL & ~ E_NOTICE);

// 读取 smarty 配置文件
$config_smarty = Zeed_Config::loadGroup('smarty');

// 设置模块主题, 可改进为从个性化配置中加载.
$_module = strtolower(basename(ZEED_PATH_MODULE));
$_controller = strtolower(basename(ZEED_PATH_CONTROLLER));
$_action = strtolower(basename(ZEED_PATH_ACTION));
$_theme = $config_smarty['theme']['frontend'];

$smarty = Zeed_Smarty::instance()->setModule($_module)->setTheme($_theme);
$smarty->addTemplateDir(ZEED_PATH_VIEW . $_module . '/' . ZEED_PATH_ADMIN_OR_FRONT . $_theme);
$smarty->addTemplateDir(ZEED_PATH_VIEW . 'admin/' . $_theme); // Default template
$smarty->addTemplateDir(ZEED_PATH_VIEW . 'panel/' . $_theme); // Panel template folder

// 注册插件
$smarty->addPluginsDir(ZEED_PATH_LIB . 'smarty/plugins');

// 赋值 moduleman 数组对象及主题
$smarty->assign('moduleman', array('module' => $_module, 'controller' => $_controller, 'action' => $_action, 'panel' => 'panel'));
$smarty->assign('theme', $_theme);

// 获取频道导航
$smarty->assign('menus', System_Model_Frontend_Menu::instance()->fetchByGroupId());

// 登陆用户信息
if (class_exists('Cas_Authorization')) {
    $loggedInUser = Cas_Authorization::getLoggedInUserInfo();
    $smarty->assign('loggedInUser', $loggedInUser);
}

// 登录跳转到上一个页面
$smarty->assign('continue', urlencode($_SERVER["REQUEST_URI"]));

// 加载单页
$page_groups = Page_Model_Group::instance()->getAllGroups();
if ($page_groups) {
	foreach($page_groups as $key => $group) {
		$temp_page_list = Page_Model_Listing::instance()->fetchByFV("group_id", $group['group_id']);
		$page_groups[$key]["page_list"] = empty($temp_page_list) ? array() : $temp_page_list;
	}
	$smarty->assign('page_groups', $page_groups);
}

// 加载页面头尾
$smarty->assign('wrapper_prefix', $smarty->fetch('index/' . $_theme . '/wrapper.prefix.html'));
$smarty->assign('wrapper_suffix', $smarty->fetch('index/' . $_theme . '/wrapper.suffix.html'));

// End ^ LF ^ encoding
