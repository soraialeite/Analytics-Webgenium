<?php
/**
 * Joomla! 1.5 component Analytics Webgenium
 *
 * @version $Id: analytics.php 2011-09-09 18:00:00 svn $
 * @author Luiz Felipe Weber
 * @author Kinshuk Kulshreshtha
 * @website webgenium.com.br
 * @package Joomla
 * @subpackage Analytics Webgenium
 * @license GNU/GPL
 *
 * Show Google Analytics in Joomla Backend 
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport('joomla.application.component.model');

class AnalyticsModelAnalytics extends JModel {
    function __construct() {
		parent::__construct();
    }
}
?>