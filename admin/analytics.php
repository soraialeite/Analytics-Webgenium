<?php
/*------------------------------------------------------------------------
# com_analytics - Webgenium Analytics
# ------------------------------------------------------------------------
# author    Luiz Felipe Weber - Webgenium System
# copyright Copyright (C) 2011 webgenium.com.br. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://loja.weber.eti.br / http://webgenium.com.br
# Technical Support:  Forum - https://github.com/webgenium/Analytics-Webgenium
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Define constants for all pages
 */
define( 'COM_ANALYTICS_DIR', 'images'.DS.'analytics'.DS );
define( 'COM_ANALYTICS_BASE', JPATH_ROOT.DS.COM_ANALYTICS_DIR );
define( 'COM_ANALYTICS_BASEURL', JURI::root().str_replace( DS, '/', COM_ANALYTICS_DIR ));

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';

// Require the base controller
require_once JPATH_COMPONENT.DS.'helpers'.DS.'helper.php';

// Initialize the controller
$controller = new AnalyticsController( );

// Perform the Request task
$controller->execute( JRequest::getCmd('task'));
$controller->redirect();
?>