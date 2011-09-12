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

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';

// Initialize the controller
$controller = new AnalyticsController();
$controller->execute( null );

// Redirect if set by the controller
$controller->redirect();
?>