<?php
/**
 * Joomla! 1.5 component Analytics
 *
 * @version $Id: analytics.php 2009-07-17 10:34:47 svn $
 * @author Kinshuk Kulshreshtha
 * @package Joomla
 * @subpackage Analytics
 * @license GNU/GPL
 *
 * Show Google Analytics in Joomla Backend
 *
 * This component file was created using the Joomla Component Creator by Not Web Design
 * http://www.notwebdesign.com/joomla_component_creator/
 *
 */

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