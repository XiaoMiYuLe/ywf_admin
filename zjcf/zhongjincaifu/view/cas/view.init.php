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

// 设置模块主题, 可改进为从个性化配置中加载.
$_module = strtolower(basename(ZEED_PATH_MODULE));
$_controller = strtolower(basename(ZEED_PATH_CONTROLLER));
$_action = strtolower(basename(ZEED_PATH_ACTION));
$_theme = 'template';

$smarty = Zeed_Smarty::instance()->setModule($_module)->setTheme($_theme);
$smarty->addTemplateDir(ZEED_PATH_VIEW . $_module . '/' . ZEED_PATH_ADMIN_OR_FRONT . $_theme);
$smarty->addTemplateDir(ZEED_PATH_VIEW . 'admin/' . $_theme); // Default template
$smarty->addTemplateDir(ZEED_PATH_VIEW . 'panel/' . $_theme); // Panel template folder

// 注册插件
$smarty->addPluginsDir(ZEED_PATH_LIB . 'smarty/plugins');

// 赋值 moduleman 数组对象
$smarty->assign('moduleman', array('module' => $_module, 'controller' => $_controller, 'action' => $_action, 'panel' => 'panel'));


$smarty->assign('user_info',$this->getData('data.user_info'));
$smarty->assign('cart_count',$this->getData('data.cart_count'));
$smarty->assign('keyword',$this->getData('data.keyword'));

// End ^ LF ^ encoding
