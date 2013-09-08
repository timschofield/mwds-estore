<?php
/**
* @version		$Id: mod_banners.php 6138 2007-01-02 03:44:18Z eddiea $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$headerText	= trim( $params->get( 'header_text' ) );
$footerText	= trim( $params->get( 'footer_text' ) );

$list = modBannersHelper::getList($params);
require(JModuleHelper::getLayoutPath('mod_banners'));