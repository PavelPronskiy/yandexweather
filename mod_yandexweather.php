<?php
/**
 * @package         Asikart.Module
 * @subpackage      mod_example
 * @copyright       Copyright (C) 2012 Asikart.com, Inc. All rights reserved.
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;

JLoader::register('ModYandexWeatherHelper', __DIR__ . '/helper.php');
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

$document = Factory::getDocument();
$document->addScript('modules' . DS . $module->module . DS . 'assets' . DS . 'js' . DS . 'weather.js');
// $document->addStyleSheet('modules' . DS . $module->module . DS . 'assets' . DS . 'css' . DS . 'weather.css');

require JModuleHelper::getLayoutPath('mod_yandexweather', $params->get('layout', 'default'));

